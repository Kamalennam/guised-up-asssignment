<?php

return [

    /*
    |--------------------------------------------------------------------------
    | pgvector Configuration
    |--------------------------------------------------------------------------
    |
    | PostgreSQL pgvector extension settings for semantic search and feed
    | ranking. The extension is enabled via docker/postgres/init.sql on
    | first database boot (CREATE EXTENSION IF NOT EXISTS vector).
    |
    */

    'extension' => 'vector',

    'dimension' => (int) env('EMBEDDING_DIMENSION', 384),

    'model' => env('EMBEDDING_MODEL', 'all-MiniLM-L6-v2'),

];
