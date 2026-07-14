export const colors = {
  background: '#F5F0EB',
  surface: '#FFFFFF',
  text: '#2C2416',
  textMuted: '#6B5E4F',
  primary: '#8B6914',
  border: '#E8DFD4',
} as const;

export const spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
} as const;

export const theme = {
  colors,
  spacing,
} as const;

export type Theme = typeof theme;
