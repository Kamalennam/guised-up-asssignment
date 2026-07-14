"""Optional integration smoke test against the real MiniLM model.

Skipped unless RUN_MODEL_TESTS=1 so default unit runs stay fast/offline.
"""

from __future__ import annotations

import math
import os

import pytest
from fastapi.testclient import TestClient

from app.main import app

pytestmark = pytest.mark.skipif(
    os.getenv("RUN_MODEL_TESTS") != "1",
    reason="Set RUN_MODEL_TESTS=1 to load all-MiniLM-L6-v2",
)


@pytest.fixture(scope="module")
def live_client() -> TestClient:
    with TestClient(app) as client:
        yield client


def test_live_embed_dimension_and_normalization(live_client: TestClient) -> None:
    response = live_client.post(
        "/embed",
        json={"text": "looking for authentic travel connections"},
    )
    assert response.status_code == 200
    embedding = response.json()["embedding"]
    assert len(embedding) == 384
    norm = math.sqrt(sum(v * v for v in embedding))
    assert abs(norm - 1.0) < 1e-3


def test_live_health(live_client: TestClient) -> None:
    response = live_client.get("/health")
    assert response.status_code == 200
    assert response.json()["status"] == "ok"
