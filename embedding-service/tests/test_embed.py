import math


def test_embed_returns_384_dimensional_vector(client) -> None:
    response = client.post("/embed", json={"text": "funny travel stories"})

    assert response.status_code == 200
    body = response.json()
    assert body["model"] == "all-MiniLM-L6-v2"
    assert len(body["embedding"]) == 384
    assert all(isinstance(value, float) for value in body["embedding"])

    norm = math.sqrt(sum(value * value for value in body["embedding"]))
    assert abs(norm - 1.0) < 1e-5


def test_embed_rejects_blank_text(client) -> None:
    response = client.post("/embed", json={"text": "   "})

    assert response.status_code == 422


def test_embed_rejects_missing_text(client) -> None:
    response = client.post("/embed", json={})

    assert response.status_code == 422


def test_embed_batch_returns_matching_count(client) -> None:
    texts = ["alpha", "beta", "gamma"]
    response = client.post("/embed/batch", json={"texts": texts})

    assert response.status_code == 200
    body = response.json()
    assert body["model"] == "all-MiniLM-L6-v2"
    assert len(body["embeddings"]) == 3
    assert all(len(vector) == 384 for vector in body["embeddings"])


def test_embed_batch_rejects_empty_list(client) -> None:
    response = client.post("/embed/batch", json={"texts": []})

    assert response.status_code == 422


def test_embed_batch_rejects_blank_item(client) -> None:
    response = client.post("/embed/batch", json={"texts": ["ok", "  "]})

    assert response.status_code == 422


def test_embed_batch_rejects_oversized_batch(client, monkeypatch) -> None:
    from app.config import settings

    monkeypatch.setattr(settings, "max_batch_size", 2)
    response = client.post(
        "/embed/batch",
        json={"texts": ["one", "two", "three"]},
    )

    assert response.status_code == 422
