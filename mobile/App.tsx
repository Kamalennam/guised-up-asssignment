import { NavigationContainer } from '@react-navigation/native';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { StatusBar } from 'expo-status-bar';
import { useEffect } from 'react';

import { AppNavigator } from './src/navigation/AppNavigator';
import { useAppStore } from './src/store/appStore';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 30_000,
    },
  },
});

export default function App() {
  const setReady = useAppStore((state) => state.setReady);

  useEffect(() => {
    setReady(true);
  }, [setReady]);

  return (
    <QueryClientProvider client={queryClient}>
      <NavigationContainer>
        <StatusBar style="dark" />
        <AppNavigator />
      </NavigationContainer>
    </QueryClientProvider>
  );
}
