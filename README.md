# Guised Up — Real Connections Feed

> Full-Stack Developer Assessment — Real Connections Feed MVP

## Project Overview

Guised Up is a social platform for authentic online connection. This repository implements the Real Connections Feed — a personalized recommendation stream ranked by semantic similarity, relationship depth, authenticity, and time decay rather than likes, shares, or follower counts.

The implementation covers the core assessment workflow: Laravel API authentication, post creation, personalized feed ranking, semantic search, interaction logging, a React Native feed screen, SQL analytics queries, and tests.

**Architecture document:** [docs/TSD.md](docs/TSD.md)

---

## Architecture

### Stack

| Layer | Technology |
|-------|------------|
| Mobile | React Native + Expo |
| API | Laravel 11 + Sanctum |
| ML Service | FastAPI + Python |
| Database | PostgreSQL + pgvector |
| Tooling | Docker Compose, Node.js |

### Ranking Weights

| Signal | Weight |
|--------|--------|
| Semantic Similarity | 40% |
| Relationship Depth | 30% |
| Authenticity | 20% |
| Time Decay | 10% |

### Vector DB Choice

pgvector is used inside PostgreSQL so the backend can keep relational data and vector embeddings in a single system for the assessment MVP.

---

## Prerequisites

- Docker Engine 27+
- Docker Compose 2.32+
- Node.js 22.14 LTS
- PHP 8.3+
- Composer 2.x
- Python 3.12+
- Git

---

## Setup Instructions

```bash
# 1. Clone the repository
git clone <repository-url>
cd guised-up

# 2. Copy environment files
cp backend/.env.example backend/.env
cp embedding-service/.env.example embedding-service/.env
cp mobile/.env.example mobile/.env

# 3. Start the stack
cd docker
docker compose up -d

# 4. Run migrations and seed data
cd ../backend
php artisan migrate --seed
```

---

## Environment Variables

### Backend

- APP_URL
- DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD
- EMBEDDING_SERVICE_URL
- EMBEDDING_PROVIDER
- FEED_WEIGHT_SEMANTIC / FEED_WEIGHT_RELATIONSHIP / FEED_WEIGHT_AUTHENTICITY / FEED_WEIGHT_TIME

### Embedding Service

- MODEL_NAME
- LOG_LEVEL

### Mobile

- EXPO_PUBLIC_API_URL

---

## Run Backend

```bash
cd backend
php artisan serve
```

---

## Run Python Service

```bash
cd embedding-service
uvicorn app.main:app --host 0.0.0.0 --port 8001
```

---

## Run Mobile App

```bash
cd mobile
npm install
npx expo start
```

---

## Run Tests

```bash
cd backend
php artisan test
```

---

## Docker

```bash
cd docker
docker compose up
```

Service ports:
- Laravel API: 8000
- Embedding API: 8001
- PostgreSQL: 5432
- Redis: 6379

---

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | /api/auth/login | Obtain Sanctum token | No |
| POST | /api/posts | Create a new post | Yes |
| GET | /api/feed | Personalized feed (20/page) | Yes |
| GET | /api/search?q= | Semantic search (top 10) | Yes |
| POST | /api/interactions | Log view/reply/reaction/share | Yes |

---

## SQL Queries

Analytics queries are located in [sql/queries.sql](sql/queries.sql).

---

## AI Tools Used

| Tool | Usage |
|------|-------|
| GitHub Copilot | Implementation, refactoring, verification |
| Cursor / editor agent tools | Scaffolding and rapid iteration |

---

## Known Limitations

- Embedding generation uses a deterministic mock fallback when the Python service is unavailable so the MVP remains functional offline.
- Search is semantic and heuristic rather than a full production vector index tuning layer.

---

## Future Improvements

- Replace the mock embedding fallback with a hosted or local transformer service for richer embeddings.
- Add caching for feed generation and expand ranking features with more interaction signals.
- Add mobile auth screens and a more polished feed UI.

---

## License

Confidential — Guised Up © 2026. Do not distribute.
