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

## Authentication Flow

The mobile application now uses a token-based Sanctum flow with persistent storage and route protection.

### Storage

- The app stores the last authenticated session in AsyncStorage under the `auth-session` key.
- The stored payload contains the bearer token and the authenticated user profile.
- Session data is cleared on logout or when the backend returns a 401.

### Interceptor

- The shared Axios client attaches `Authorization: Bearer <token>` to every request through a request interceptor.
- A response interceptor watches for 401 responses and immediately clears the stored session before redirecting the user back to the login screen.

### Startup Flow

1. The app renders a splash screen while the auth bootstrap runs.
2. The session is loaded from AsyncStorage.
3. If a token exists, the app calls `/api/auth/me` to validate it.
4. A valid session opens the home feed; an invalid or missing session clears storage and shows the login screen.

### Sequence Diagram

```text
User -> LoginScreen: Enter credentials
LoginScreen -> API: POST /api/auth/login
API -> LoginScreen: token + user
LoginScreen -> AsyncStorage: Persist token + user
LoginScreen -> AuthContext: Set authenticated state
AuthContext -> Navigation: Navigate to Home

HomeScreen -> API: Protected request with Bearer token
API -> HomeScreen: 200/401
401 -> Axios interceptor: Clear storage + redirect to Login
```

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


## Demo Credentials

The project ships with seeded demo users.

| User | Email | Password |
|------|-------|----------|
| Kamal | kamal@guisedup.test | password |
| Kishore | kishore@guisedup.test | password |
| Venu | venu@guisedup.test | password |

These accounts can be used immediately after running:

php artisan migrate:fresh --seed




## Demo Video

Loom Recording

https://loom.com/xxxxxxxx



## Assignment Coverage

✅ Laravel API

✅ Sanctum Authentication

✅ React Native Feed

✅ Infinite Scroll

✅ Semantic Search

✅ Feed Ranking

✅ pgvector

✅ PostgreSQL

✅ SQL Queries

✅ Unit Tests

✅ Authentication Persistence

✅ Protected Routes

✅ Docker Support


## Design Decisions

The recommendation engine intentionally ignores:

- Likes
- Followers
- Shares
- Popularity

Instead, posts are ranked using:

- Semantic Similarity
- Relationship Depth
- Authenticity
- Time Decay

This aligns with the product philosophy of surfacing meaningful content rather than viral content.



Authentication Features

✓ Secure Sanctum authentication

✓ Token persistence

✓ Axios request interceptor

✓ Axios response interceptor

✓ Auto Login

✓ Auto Logout on 401

✓ Protected routes

✓ Splash screen authentication bootstrap



---

# Running the Project

Open four separate terminals and start each service.

## 1. PostgreSQL

Ensure the PostgreSQL Docker container is running.

```bash
docker start guisedup-postgres
```

Verify:

```bash
docker ps
```

---

## 2. Laravel Backend

```bash
cd backend

php artisan serve --host=0.0.0.0 --port=8000
```

The API will be available at:

```
http://localhost:8000
```

or from a mobile device on the same Wi-Fi network:

```
http://<YOUR_LOCAL_IP>:8000
```

---

## 3. Python Embedding Service

```bash
cd embedding-service

uvicorn app.main:app --host 0.0.0.0 --port 8001
```

Embedding API:

```
http://localhost:8001
```

---

## 4. React Native Mobile App

```bash
cd mobile

npx expo start --clear
```

Scan the QR code using **Expo Go** on Android or iOS.

> Ensure `EXPO_PUBLIC_API_URL` in `mobile/.env` points to your machine's local IP address.

Example:

```env
EXPO_PUBLIC_API_URL=http://192.168.1.8:8000/api
```

---

## Verify Services

| Service | URL |
|----------|-----|
| Laravel API | http://localhost:8000 |
| Embedding Service | http://localhost:8001 |
| PostgreSQL | localhost:5432 |
| React Native | Expo Metro Bundler |
