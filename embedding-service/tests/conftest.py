from __future__ import annotations

from contextlib import asynccontextmanager
from typing import AsyncIterator, Sequence
from unittest.mock import MagicMock

import numpy as np
import pytest
from fastapi import FastAPI
from fastapi.testclient import TestClient

from app.api import router
from app.config import settings
from app.services import EmbeddingService


def _unit_vector(seed: int, dim: int = 384) -> list[float]:
    rng = np.random.default_rng(seed)
    vec = rng.standard_normal(dim).astype(np.float32)
    vec /= np.linalg.norm(vec)
    return vec.tolist()


class FakeEmbeddingService(EmbeddingService):
    """Deterministic stand-in that avoids loading sentence-transformers."""

    def __init__(self) -> None:
        super().__init__(settings)
        self._model = MagicMock()  # marks service ready
        self.calls: list[list[str]] = []

    def load(self) -> None:  # pragma: no cover - unused in API tests
        return None

    def embed_batch(self, texts: Sequence[str]) -> list[list[float]]:
        self.calls.append(list(texts))
        if len(texts) > settings.max_batch_size:
            raise ValueError(
                f"Batch size {len(texts)} exceeds max_batch_size {settings.max_batch_size}"
            )
        return [_unit_vector(abs(hash(text)) % 10_000) for text in texts]


@pytest.fixture
def fake_service() -> FakeEmbeddingService:
    return FakeEmbeddingService()


@pytest.fixture
def client(fake_service: FakeEmbeddingService) -> TestClient:
    @asynccontextmanager
    async def lifespan(app: FastAPI) -> AsyncIterator[None]:
        app.state.embedding_service = fake_service
        yield

    test_app = FastAPI(lifespan=lifespan)
    test_app.include_router(router)

    with TestClient(test_app) as test_client:
        yield test_client
