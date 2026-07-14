from unittest.mock import MagicMock

import numpy as np
import pytest

from app.config import Settings
from app.services import EmbeddingService


def test_prepare_text_truncates_and_logs(caplog: pytest.LogCaptureFixture) -> None:
    settings = Settings(max_input_tokens=4)
    service = EmbeddingService(settings)

    tokenizer = MagicMock()
    tokenizer.encode.return_value = [1, 2, 3, 4, 5, 6, 7, 8]
    tokenizer.decode.return_value = "short"

    service._model = MagicMock()
    service._model.tokenizer = tokenizer

    with caplog.at_level("WARNING"):
        result = service._prepare_text("a very long piece of text")

    assert result == "short"
    tokenizer.decode.assert_called_once()
    assert "Truncating input text exceeding max tokens" in caplog.text


def test_embed_batch_validates_dimension() -> None:
    settings = Settings(embedding_dimension=384, max_batch_size=8)
    service = EmbeddingService(settings)

    model = MagicMock()
    model.tokenizer = None
    model.encode.return_value = np.ones((1, 8), dtype=np.float32)
    service._model = model

    with pytest.raises(RuntimeError, match="Unexpected embedding dimension"):
        service.embed_batch(["hello"])
