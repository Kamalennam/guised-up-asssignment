from __future__ import annotations

import logging
import sys
from contextlib import asynccontextmanager
from typing import AsyncIterator

from fastapi import FastAPI

from app.api import router
from app.config import settings
from app.services import EmbeddingService

logger = logging.getLogger("embedding_service")


def configure_logging(level: str) -> None:
    logging.basicConfig(
        level=getattr(logging, level.upper(), logging.INFO),
        format="%(asctime)s %(levelname)s [%(name)s] %(message)s",
        stream=sys.stdout,
        force=True,
    )


@asynccontextmanager
async def lifespan(app: FastAPI) -> AsyncIterator[None]:
    configure_logging(settings.log_level)
    service = EmbeddingService(settings)
    logger.info("Starting embedding service")
    service.load()
    app.state.embedding_service = service
    logger.info("Embedding service startup complete")
    try:
        yield
    finally:
        logger.info("Shutting down embedding service")


app = FastAPI(
    title="Guised Up Embedding Service",
    version="0.1.0",
    description="Semantic embedding inference service for Guised Up (TSD §24).",
    lifespan=lifespan,
)

app.include_router(router)
