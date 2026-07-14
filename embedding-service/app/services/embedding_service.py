from __future__ import annotations

import logging
import os
from typing import Sequence

import numpy as np
from sentence_transformers import SentenceTransformer

from app.config import Settings

logger = logging.getLogger(__name__)


class EmbeddingService:
    """Wraps sentence-transformers inference for Guised Up."""

    def __init__(self, settings: Settings) -> None:
        self.settings = settings
        self._model: SentenceTransformer | None = None

    @property
    def is_ready(self) -> bool:
        return self._model is not None

    def load(self) -> None:
        """Load the embedding model once at process startup."""
        cache_dir = self.settings.transformers_cache
        os.makedirs(cache_dir, exist_ok=True)
        os.environ.setdefault("TRANSFORMERS_CACHE", cache_dir)
        os.environ.setdefault("HF_HOME", cache_dir)

        logger.info(
            "Loading embedding model",
            extra={
                "model": self.settings.model_name,
                "cache": cache_dir,
            },
        )
        self._model = SentenceTransformer(
            self.settings.model_name,
            cache_folder=cache_dir,
        )
        # Align tokenizer window with TSD max input (model default may be lower).
        self._model.max_seq_length = self.settings.max_input_tokens
        logger.info(
            "Embedding model ready",
            extra={
                "model": self.settings.model_name,
                "dimensions": self.settings.embedding_dimension,
                "max_seq_length": self._model.max_seq_length,
            },
        )

    def embed(self, text: str) -> list[float]:
        vectors = self.embed_batch([text])
        return vectors[0]

    def embed_batch(self, texts: Sequence[str]) -> list[list[float]]:
        if self._model is None:
            raise RuntimeError("Embedding model is not loaded")

        if len(texts) > self.settings.max_batch_size:
            raise ValueError(
                f"Batch size {len(texts)} exceeds max_batch_size "
                f"{self.settings.max_batch_size}"
            )

        prepared = [self._prepare_text(text) for text in texts]
        encoded = self._model.encode(
            prepared,
            normalize_embeddings=True,
            convert_to_numpy=True,
            show_progress_bar=False,
        )
        matrix = np.asarray(encoded, dtype=np.float32)
        if matrix.ndim == 1:
            matrix = matrix.reshape(1, -1)

        if matrix.shape[1] != self.settings.embedding_dimension:
            raise RuntimeError(
                f"Unexpected embedding dimension {matrix.shape[1]}; "
                f"expected {self.settings.embedding_dimension}"
            )

        return matrix.tolist()

    def _prepare_text(self, text: str) -> str:
        """Truncate overlong input using the model tokenizer when available."""
        assert self._model is not None
        tokenizer = getattr(self._model, "tokenizer", None)
        max_tokens = self.settings.max_input_tokens

        if tokenizer is None:
            # Character heuristic fallback (~4 chars/token).
            char_limit = max_tokens * 4
            if len(text) > char_limit:
                logger.warning(
                    "Truncating input text (no tokenizer available)",
                    extra={"original_chars": len(text), "limit_chars": char_limit},
                )
                return text[:char_limit]
            return text

        token_ids = tokenizer.encode(text, add_special_tokens=True)
        if len(token_ids) <= max_tokens:
            return text

        logger.warning(
            "Truncating input text exceeding max tokens",
            extra={"token_count": len(token_ids), "max_tokens": max_tokens},
        )
        truncated_ids = token_ids[:max_tokens]
        return tokenizer.decode(truncated_ids, skip_special_tokens=True)
