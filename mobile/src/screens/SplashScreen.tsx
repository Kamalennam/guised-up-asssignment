import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';

import { theme } from '../theme';

export function SplashScreen() {
  return (
    <View style={styles.container}>
      <Text style={styles.title}>Guised Up</Text>
      <Text style={styles.subtitle}>Preparing your feed…</Text>
      <ActivityIndicator color={theme.colors.primary} size="large" />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: theme.colors.background,
  },
  title: {
    color: theme.colors.text,
    fontSize: 28,
    fontWeight: '700',
  },
  subtitle: {
    color: theme.colors.textMuted,
    marginTop: theme.spacing.sm,
    marginBottom: theme.spacing.lg,
  },
});
