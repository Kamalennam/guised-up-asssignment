-- PostgreSQL initialization
-- Enable pgvector extension on first boot
-- See docs/TSD.md Section 10

CREATE EXTENSION IF NOT EXISTS vector;
