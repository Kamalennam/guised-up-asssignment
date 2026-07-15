import axios from 'axios';

import { navigateToLogin } from '../navigation/navigationRef';
import { clearAuthSession } from '../storage/authStorage';

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? 'http://localhost:8000/api';

export const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 15000,
});

apiClient.interceptors.request.use((config) => {
  const token = apiClient.defaults.headers.common.Authorization;
  if (typeof token === 'string' && token.startsWith('Bearer ')) {
    config.headers.set('Authorization', token);
  }

  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      apiClient.defaults.headers.common.Authorization = undefined;
      await clearAuthSession();
      // navigateToLogin();
    }

    return Promise.reject(error);
  },
);

export default apiClient;
