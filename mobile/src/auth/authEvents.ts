const listeners = new Set<() => void>();

export function subscribeToAuthSessionCleared(listener: () => void): () => void {
  listeners.add(listener);

  return () => {
    listeners.delete(listener);
  };
}

export function emitAuthSessionCleared(): void {
  listeners.forEach((listener) => listener());
}
