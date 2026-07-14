def test_health_endpoint_returns_ok(client) -> None:
    response = client.get("/health")

    assert response.status_code == 200
    assert response.json() == {
        "status": "ok",
        "model": "all-MiniLM-L6-v2",
        "version": "3.3.1",
    }
