# API Contracts

See [TSD.md](TSD.md) for the approved architecture and design standards.

## Authentication

All endpoints under `/api/*` (except login) require a Sanctum personal access token:

```
Authorization: Bearer {token}
```

### `POST /api/auth/login`

Obtain a personal access token.

**Request**

```json
{
  "email": "kamal@guisedup.test",
  "password": "password"
}
```

**Response `200 OK`**

```json
{
  "data": {
    "token": "1|plainTextTokenValue",
    "user": {
      "id": 1,
      "username": "kamal",
      "name": "Kamal",
      "email": "kamal@guisedup.test",
      "avatar_url": null
    }
  }
}
```

**Validation error `422 Unprocessable Entity`**

```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

**Rate limit:** 10 requests per minute per IP.

---

### `POST /api/auth/logout`

Revoke the current personal access token.

**Headers:** `Authorization: Bearer {token}`

**Response `200 OK`**

```json
{
  "message": "Logged out successfully."
}
```

**Unauthenticated `401 Unauthorized`**

```json
{
  "message": "Unauthenticated."
}
```

**Rate limit:** 60 requests per minute per authenticated user.

---

### `GET /api/auth/me`

Return the authenticated user profile.

**Headers:** `Authorization: Bearer {token}`

**Response `200 OK`**

```json
{
  "data": {
    "id": 1,
    "username": "kamal",
    "name": "Kamal",
    "email": "kamal@guisedup.test",
    "avatar_url": null
  }
}
```

**Rate limit:** 60 requests per minute per authenticated user.

---

## Demo Users

| Name | Email | Password |
|------|-------|----------|
| Kamal | kamal@guisedup.test | password |
| Kishore | kishore@guisedup.test | password |
| Venu | venu@guisedup.test | password |

---

## Protected Routes (Future Phases)

Future API endpoints (`/api/posts`, `/api/feed`, `/api/search`, `/api/interactions`) will be registered inside the `auth:sanctum` middleware group.

## Embedding Service

See `shared/contracts/embedding-api.openapi.json`.
