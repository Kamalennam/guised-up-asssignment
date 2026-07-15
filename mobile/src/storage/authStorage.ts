import AsyncStorage from '@react-native-async-storage/async-storage';

import type { AuthUser } from '../types/auth';

export type StoredAuthSession = {
  token: string;
  user: AuthUser;
};

const AUTH_SESSION_KEY = 'auth-session';

export async function getStoredAuthSession(): Promise<StoredAuthSession | null> {
  const rawSession = await AsyncStorage.getItem(AUTH_SESSION_KEY);

  if (!rawSession) {
    return null;
  }

  try {
    return JSON.parse(rawSession) as StoredAuthSession;
  } catch {
    await AsyncStorage.removeItem(AUTH_SESSION_KEY);
    return null;
  }
}

export async function saveAuthSession(session: StoredAuthSession): Promise<void> {
  await AsyncStorage.setItem(AUTH_SESSION_KEY, JSON.stringify(session));
}

export async function clearAuthSession(): Promise<void> {
  await AsyncStorage.removeItem(AUTH_SESSION_KEY);
}
