import { apiClient } from './client';

export type FeedPost = {
  id: number;
  text: string;
  image_url?: string | null;
  created_at: string;
  feed_score: number;
  author: {
    id: number;
    name: string;
    username: string;
    avatar_url?: string | null;
  };
};

export async function fetchFeed(page = 1) {
  const { data } = await apiClient.get('/feed', { params: { page } });
  return data;
}

export async function searchFeed(query: string) {
  const { data } = await apiClient.get('/search', { params: { q: query } });
  return data;
}

export async function createPost(text: string, imageUrl?: string | null) {
  const { data } = await apiClient.post('/posts', { text, image_url: imageUrl ?? null });
  return data;
}

export async function logInteraction(postId: number, authorId: number, type: string) {
  const { data } = await apiClient.post('/interactions', { post_id: postId, author_id: authorId, type });
  return data;
}
