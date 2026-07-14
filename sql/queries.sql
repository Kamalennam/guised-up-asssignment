-- Guised Up — SQL Challenge Queries
-- These statements are written for PostgreSQL and align with the assignment requirements.

-- D1: Top 10 most active users in the last 7 days
SELECT u.id, u.email, COUNT(i.id) AS total_interactions
FROM users u
JOIN interactions i ON i.user_id = u.id
WHERE i.created_at >= NOW() - INTERVAL '7 days'
GROUP BY u.id, u.email
ORDER BY total_interactions DESC
LIMIT 10;

-- D2: Posts from users a given viewer interacts with most (last 30 days)
SELECT p.id, p.user_id AS author_id, p.created_at, COUNT(i.id) AS interaction_count
FROM posts p
JOIN interactions i ON i.post_id = p.id
WHERE i.user_id = $1
  AND p.created_at >= NOW() - INTERVAL '30 days'
GROUP BY p.id, p.user_id, p.created_at
ORDER BY interaction_count DESC, p.created_at DESC
LIMIT 20;

-- D3: Posts viewed more than 100 times but with zero reactions
SELECT p.id AS post_id, p.user_id AS author_id, COUNT(*) FILTER (WHERE i.type = 'view') AS view_count, p.created_at
FROM posts p
LEFT JOIN interactions i ON i.post_id = p.id
GROUP BY p.id, p.user_id, p.created_at
HAVING COUNT(*) FILTER (WHERE i.type = 'view') > 100
   AND COUNT(*) FILTER (WHERE i.type = 'reaction') = 0;

-- D4: Users who created more than 20 posts in the last 24 hours
SELECT u.email, COUNT(p.id) AS post_count
FROM users u
JOIN posts p ON p.user_id = u.id
WHERE p.created_at >= NOW() - INTERVAL '24 hours'
GROUP BY u.id, u.email
HAVING COUNT(p.id) > 20;
