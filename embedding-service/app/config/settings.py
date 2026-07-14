from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )

    model_name: str = "all-MiniLM-L6-v2"
    embedding_dimension: int = 384
    max_input_tokens: int = 512
    max_batch_size: int = 32
    inference_timeout_seconds: float = 5.0
    host: str = "0.0.0.0"
    port: int = 8001
    log_level: str = "info"
    transformers_cache: str = ".cache"
    sentence_transformers_version: str = "3.3.1"


settings = Settings()
