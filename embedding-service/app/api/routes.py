from __future__ import annotations

import asyncio
import logging
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, Request, status

from app.config import Settings, settings
from app.models import (
    BatchEmbedRequest,
    BatchEmbedResponse,
    EmbedRequest,
    EmbedResponse,
    HealthResponse,
)
from app.services import EmbeddingService

logger = logging.getLogger(__name__)

router = APIRouter()


def get_settings() -> Settings:
    return settings


def get_embedding_service(request: Request) -> EmbeddingService:
    service: EmbeddingService | None = getattr(request.app.state, "embedding_service", None)
    if service is None:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Embedding service is not initialized",
        )
    return service


SettingsDep = Annotated[Settings, Depends(get_settings)]
EmbeddingServiceDep = Annotated[EmbeddingService, Depends(get_embedding_service)]


@router.get("/health", response_model=HealthResponse)
def health(cfg: SettingsDep, service: EmbeddingServiceDep) -> HealthResponse:
    if not service.is_ready:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Embedding model is not ready",
        )
    return HealthResponse(
        status="ok",
        model=cfg.model_name,
        version=cfg.sentence_transformers_version,
    )


@router.post("/embed", response_model=EmbedResponse)
async def embed_text(
    payload: EmbedRequest,
    cfg: SettingsDep,
    service: EmbeddingServiceDep,
) -> EmbedResponse:
    logger.info("embed.request", extra={"text_length": len(payload.text)})
    try:
        embedding = await asyncio.wait_for(
            asyncio.to_thread(service.embed, payload.text),
            timeout=cfg.inference_timeout_seconds,
        )
    except TimeoutError as exc:
        logger.warning("embed.timeout", extra={"timeout": cfg.inference_timeout_seconds})
        raise HTTPException(
            status_code=status.HTTP_504_GATEWAY_TIMEOUT,
            detail=f"Embedding timed out after {cfg.inference_timeout_seconds}s",
        ) from exc
    except Exception as exc:  # noqa: BLE001 — surface inference failures as 500
        logger.exception("embed.failed")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Failed to generate embedding",
        ) from exc

    logger.info("embed.success", extra={"dimensions": len(embedding)})
    return EmbedResponse(embedding=embedding, model=cfg.model_name)


@router.post("/embed/batch", response_model=BatchEmbedResponse)
async def embed_batch(
    payload: BatchEmbedRequest,
    cfg: SettingsDep,
    service: EmbeddingServiceDep,
) -> BatchEmbedResponse:
    logger.info(
        "embed.batch.request",
        extra={"count": len(payload.texts)},
    )
    try:
        embeddings = await asyncio.wait_for(
            asyncio.to_thread(service.embed_batch, payload.texts),
            timeout=cfg.inference_timeout_seconds,
        )
    except ValueError as exc:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=str(exc),
        ) from exc
    except TimeoutError as exc:
        logger.warning(
            "embed.batch.timeout",
            extra={"timeout": cfg.inference_timeout_seconds, "count": len(payload.texts)},
        )
        raise HTTPException(
            status_code=status.HTTP_504_GATEWAY_TIMEOUT,
            detail=f"Batch embedding timed out after {cfg.inference_timeout_seconds}s",
        ) from exc
    except Exception as exc:  # noqa: BLE001
        logger.exception("embed.batch.failed")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Failed to generate batch embeddings",
        ) from exc

    logger.info("embed.batch.success", extra={"count": len(embeddings)})
    return BatchEmbedResponse(embeddings=embeddings, model=cfg.model_name)
