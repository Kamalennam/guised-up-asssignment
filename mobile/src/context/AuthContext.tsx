import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import type { PropsWithChildren } from 'react';

import { apiClient } from '../api/client';
import { emitAuthSessionCleared, subscribeToAuthSessionCleared } from '../auth/authEvents';
import { clearAuthSession, getStoredAuthSession, saveAuthSession, type StoredAuthSession } from '../storage/authStorage';
// import { navigateToHome, navigateToLogin } from '../navigation/navigationRef';
import type { AuthUser } from '../types/auth';
import axios from 'axios';

type AuthContextValue = {
  user: AuthUser | null;
  token: string | null;
  loading: boolean;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refreshSession: () => Promise<boolean>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: PropsWithChildren) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  const clearSession = useCallback(async () => {
    setUser(null);
    setToken(null);
    await clearAuthSession();
    emitAuthSessionCleared();
    apiClient.defaults.headers.common.Authorization = undefined;
    // navigateToLogin();
  }, []);

  const refreshSession = useCallback(async () => {
    const storedSession = await getStoredAuthSession();

    if (!storedSession?.token) {
      await clearSession();
      return false;
    }

    apiClient.defaults.headers.common.Authorization = `Bearer ${storedSession.token}`;

    try {
      const { data } = await apiClient.get('/auth/me');
      setUser((data as { data?: AuthUser }).data ?? null);
      setToken(storedSession.token);
      return true;
    } catch {
      await clearSession();
      return false;
    }
  }, [clearSession]);

//   const login = useCallback(async (email: string, password: string) => {
//     const { data } = await apiClient.post('/auth/login', { email, password });
//     const tokenValue = (data as { data?: { token?: string } }).data?.token;
//     const userValue = (data as { data?: { user?: AuthUser } }).data?.user;

//     if (!tokenValue || !userValue) {
//       throw new Error('Unable to complete sign-in.');
//     }

//     const session: StoredAuthSession = {
//       token: tokenValue,
//       user: userValue,
//     };

//     apiClient.defaults.headers.common.Authorization = `Bearer ${session.token}`;
//     await saveAuthSession(session);
//     setToken(session.token);
//     setUser(session.user);
//     navigateToHome();
//   }, []);

  
    const login = useCallback(async (email: string, password: string) => {
    try {
        console.log("STEP 1 - Sending login request");

        const response = await apiClient.post("/auth/login", {
        email,
        password,
        });

        console.log("STEP 2 - Response:", response.data);

        const tokenValue = response.data?.data?.token;
        const userValue = response.data?.data?.user;

        console.log("STEP 3 - Token:", tokenValue);
        console.log("STEP 4 - User:", userValue);

        if (!tokenValue || !userValue) {
        throw new Error("Missing token or user.");
        }

        await saveAuthSession({
        token: tokenValue,
        user: userValue,
        });

        console.log("STEP 5 - Session saved");

        apiClient.defaults.headers.common.Authorization = `Bearer ${tokenValue}`;

        setToken(tokenValue);
        setUser(userValue);

        console.log("STEP 6 - Login completed");

        // navigateToHome();
    } catch (error) {
        console.log("LOGIN ERROR:", error);

        if (axios.isAxiosError(error)) {
        console.log("Status:", error.response?.status);
        console.log("Response:", error.response?.data);
        }

        throw error;
    }
    }, []);

const logout = useCallback(async () => {
    const activeToken = token;

    if (activeToken) {
      apiClient.defaults.headers.common.Authorization = undefined;
      try {
        await apiClient.post('/auth/logout');
      } catch {
        // Ignore backend failures and continue with local cleanup.
      }
    }

    await clearSession();
  }, [clearSession, token]);

  useEffect(() => {
    let isActive = true;

    void (async () => {
      try {
        const session = await getStoredAuthSession();
        if (!session?.token) {
          await clearSession();
          return;
        }

        apiClient.defaults.headers.common.Authorization = `Bearer ${session.token}`;
        const { data } = await apiClient.get('/auth/me');
        if (!isActive) {
          return;
        }

        setToken(session.token);
        setUser((data as { data?: AuthUser }).data ?? null);
        // navigateToHome();    
      } catch {
        if (!isActive) {
          return;
        }
        await clearSession();
      } finally {
        if (isActive) {
          setLoading(false);
        }
      }
    })();

    return () => {
      isActive = false;
    };
  }, [clearSession]);

  useEffect(() => {
    const unsubscribe = subscribeToAuthSessionCleared(() => {
      setUser(null);
      setToken(null);
      setLoading(false);
    });

    return () => unsubscribe();
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      token,
      loading,
      isAuthenticated: Boolean(token && user),
      login,
      logout,
      refreshSession,
    }),
    [loading, login, logout, refreshSession, token, user],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }

  return context;
}
