# Guised Up — Real Connections Feed

> Full-Stack Developer Assessment — Real Connections Feed MVP

## Project Overview

<!-- TODO: Describe what Guised Up is and what this repository demonstrates -->

Guised Up is a social platform for authentic online connection. This repository implements the **Real Connections Feed** — a personalized recommendation stream ranked by semantic similarity, relationship depth, authenticity, and time decay — **not** by likes, shares, or follower counts.

**Assessment scope:** Feed API, Semantic Search, Interactions, Ranking Engine, React Native Feed Screen, SQL Analytics.

**Architecture document:** [docs/TSD.md](docs/TSD.md)

---

## Architecture

<!-- TODO: Add stack diagram and service responsibilities table -->

### Stack (Frozen)

| Layer | Technology | Version |
|-------|------------|---------|
| Mobile | React Native + Expo | 0.76.5 / SDK 52 |
| API | Laravel | 11.45 |
| Runtime | PHP | 8.3.20 |
| ML Service | FastAPI + Python | 0.115.6 / 3.12.8 |
| Database | PostgreSQL + pgvector | 16.6 / 0.8.0 |
| Tooling | Node.js | 22.14.0 LTS |
| Containers | Docker + Compose | 27.4.0 / 2.32.1 |

### Ranking Weights

| Signal | Weight |
|--------|--------|
| Semantic Similarity | 40% |
| Relationship Depth | 30% |
| Authenticity | 20% |
| Time Decay | 10% |

### Vector DB

<!-- TODO: Brief rationale for pgvector choice -->

---

## Prerequisites

<!-- TODO: List required tools -->

- Docker Engine 27.4+
- Docker Compose 2.32+
- Node.js 22.14.0 LTS (for mobile development)
- Git

Optional for local non-Docker development:
- PHP 8.3.20
- Composer 2.x
- Python 3.12.8

---

## Setup Instructions

<!-- TODO: Complete setup steps after implementation -->

```bash
# 1. Clone the repository
git clone <repository-url>
cd guised-up

# 2. Copy environment files
cp backend/.env.example backend/.env
cp embedding-service/.env.example embedding-service/.env
cp mobile/.env.example mobile/.env

# 3. Start services
docker compose -f docker/docker-compose.yml up -d

# 4. Run migrations and seed data
# docker compose exec laravel php artisan migrate --seed
```

---

## Environment Variables

### Backend (Laravel) — `backend/.env.example`

| Variable | Description |
|----------|-------------|
| `APP_URL` | Laravel application URL |
| `DB_*` | PostgreSQL connection settings |
| `EMBEDDING_SERVICE_URL` | Python embedding service URL |
| `EMBEDDING_PROVIDER` | `http` or `mock` |
| `FEED_WEIGHT_*` | Ranking weight overrides |

### Embedding Service — `embedding-service/.env.example`

| Variable | Description |
|----------|-------------|
| `MODEL_NAME` | Sentence-transformer model name |
| `LOG_LEVEL` | Logging verbosity |

### Mobile — `mobile/.env.example`

| Variable | Description |
|----------|-------------|
| `EXPO_PUBLIC_API_URL` | Laravel API base URL |

---

## Running Backend

<!-- TODO: Complete after Laravel implementation -->

```bash
# Via Docker
docker compose -f docker/docker-compose.yml up laravel postgres redis

# Local development
# cd backend && php artisan serve
```

---

## Running Python Service

<!-- TODO: Complete after FastAPI implementation -->

```bash
# Via Docker
docker compose -f docker/docker-compose.yml up embedding

# Local development
# cd embedding-service && uvicorn app.main:app --host 0.0.0.0 --port 8001
```

---

## Running Mobile App

<!-- TODO: Complete after React Native implementation -->

```bash
cd mobile
npm install
npx expo start
```

---

## Running Tests

<!-- TODO: Complete after test implementation -->

```bash
# All tests
./scripts/run-tests.sh

# Laravel only
# cd backend && php artisan test

# Python only
# cd embedding-service && pytest
```

---

## Docker

<!-- TODO: Complete service port mapping after Docker implementation -->

```bash
# Start full stack
docker compose -f docker/docker-compose.yml up

# Service ports (planned)
# Laravel API:  :8000
# Embedding API: :8001
# PostgreSQL:    :5432
# Redis:         :6379
```

---

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/login` | Obtain Sanctum token | No |
| POST | `/api/posts` | Create a new post | Yes |
| GET | `/api/feed` | Personalized feed (20/page) | Yes |
| GET | `/api/search?q=` | Semantic search (top 10) | Yes |
| POST | `/api/interactions` | Log view/reply/reaction | Yes |

---

## SQL Queries

Analytics queries are located in [`sql/queries.sql`](sql/queries.sql):

- **D1:** Top 10 most active users (last 7 days)
- **D2:** Posts from most-interacted authors (last 30 days)
- **D3:** High-view, zero-reaction posts
- **D4:** Spam detection (>20 posts in 24 hours)

---

## AI Tools Used

<!-- TODO: Document honest AI tool usage during development -->

| Tool | Usage |
|------|-------|
| Cursor (Agent) | Architecture, scaffolding, implementation |
| Claude | Design review, edge case analysis |

---

## Demo Video

<!-- TODO: Add link to recorded walkthrough -->

[Demo video link pending]

---

## Known Limitations

<!-- TODO: Document deferred items and time-box trade-offs -->

- Implementation in progress — see project milestones in TSD.

---

## License

Confidential — Guised Up © 2026. Do not distribute.
