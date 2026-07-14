from pydantic import BaseModel, Field, field_validator


class EmbedRequest(BaseModel):
    text: str = Field(..., min_length=1, description="Text to embed")

    @field_validator("text")
    @classmethod
    def text_must_not_be_blank(cls, value: str) -> str:
        stripped = value.strip()
        if not stripped:
            raise ValueError("text must not be blank")
        return stripped


class EmbedResponse(BaseModel):
    embedding: list[float]
    model: str


class BatchEmbedRequest(BaseModel):
    texts: list[str] = Field(..., min_length=1, description="Texts to embed")

    @field_validator("texts")
    @classmethod
    def texts_must_be_non_blank(cls, value: list[str]) -> list[str]:
        cleaned: list[str] = []
        for index, text in enumerate(value):
            stripped = text.strip()
            if not stripped:
                raise ValueError(f"texts[{index}] must not be blank")
            cleaned.append(stripped)
        return cleaned


class BatchEmbedResponse(BaseModel):
    embeddings: list[list[float]]
    model: str


class HealthResponse(BaseModel):
    status: str
    model: str
    version: str
