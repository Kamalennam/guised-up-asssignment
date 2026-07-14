import { useInfiniteQuery, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Image,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import { createPost, fetchFeed, logInteraction, searchFeed, type FeedPost } from '../api/feed';
import { theme } from '../theme';

export function HomeScreen() {
  const queryClient = useQueryClient();
  const [query, setQuery] = useState('');
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    const timer = setTimeout(() => setSearchTerm(query), 250);
    return () => clearTimeout(timer);
  }, [query]);

  const {
    data,
    fetchNextPage,
    hasNextPage,
    isFetching,
    isFetchingNextPage,
    isError: feedError,
    refetch,
  } = useInfiniteQuery({
    queryKey: ['feed'],
    queryFn: ({ pageParam = 1 }) => fetchFeed(pageParam as number),
    initialPageParam: 1,
    getNextPageParam: (lastPage) => {
      const meta = lastPage.meta;
      if (!meta) {
        return undefined;
      }

      return meta.page * meta.per_page < meta.total ? meta.page + 1 : undefined;
    },
  });

  const posts = useMemo(() => data?.pages.flatMap((page) => page.data ?? []) ?? [], [data]);

  const { data: searchResults = [] } = useQuery({
    queryKey: ['search', searchTerm],
    queryFn: () => searchFeed(searchTerm),
    enabled: searchTerm.trim().length >= 2,
    staleTime: 30_000,
  });

  const createPostMutation = useMutation({
    mutationFn: (text: string) => createPost(text),
    onSuccess: () => {
      setQuery('');
      void queryClient.invalidateQueries({ queryKey: ['feed'] });
    },
  });

  const reactionMutation = useMutation({
    mutationFn: ({ postId, authorId }: { postId: number; authorId: number }) => logInteraction(postId, authorId, 'reaction'),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['feed'] });
    },
  });

  const loading = isFetching && posts.length === 0;
  const refreshing = isFetching && posts.length > 0;
  const error = feedError ? 'Unable to load the feed right now.' : null;

  const emptyState = useMemo(() => {
    if (loading) {
      return <ActivityIndicator size="large" color={theme.colors.primary} />;
    }

    if (!loading && posts.length === 0) {
      return <Text style={styles.emptyText}>No posts yet. Write the first one.</Text>;
    }

    return null;
  }, [loading, posts.length]);

  function handleRefresh() {
    void refetch();
  }

  function handleReaction(post: FeedPost) {
    void reactionMutation.mutateAsync({ postId: post.id, authorId: post.author.id });
  }

  function handleCompose() {
    const text = query.trim();
    if (!text) {
      return;
    }

    void createPostMutation.mutateAsync(text);
  }

  return (
    <View style={styles.container}>
      <View style={styles.headerCard}>
        <Text style={styles.title}>Guised Up</Text>
        <Text style={styles.subtitle}>Real Connections Feed</Text>
      </View>

      <View style={styles.searchBox}>
        <TextInput
          value={query}
          onChangeText={setQuery}
          placeholder="Search for travel, food, code..."
          style={styles.input}
          placeholderTextColor={theme.colors.textMuted}
        />
        <Pressable onPress={handleCompose} style={styles.composeButton}>
          <Text style={styles.composeText}>Post</Text>
        </Pressable>
      </View>

      {searchTerm.trim().length >= 2 && (
        <View style={styles.searchPanel}>
          {(searchResults as { data?: Array<{ id: number; text: string; author?: { name?: string } }> }).data?.length ? (
            (searchResults as { data?: Array<{ id: number; text: string; author?: { name?: string } }> }).data?.map((item) => (
              <View key={item.id} style={styles.searchItem}>
                <Text style={styles.searchTitle}>{item.author?.name ?? 'Anonymous'}</Text>
                <Text style={styles.searchBody}>{item.text}</Text>
              </View>
            ))
          ) : (
            <Text style={styles.searchText}>No semantically similar posts found.</Text>
          )}
        </View>
      )}

      {error ? <Text style={styles.errorText}>{error}</Text> : null}

      {emptyState}

      <FlatList
        data={posts}
        keyExtractor={(item) => String(item.id)}
        onRefresh={handleRefresh}
        refreshing={refreshing}
        onEndReached={() => {
          if (!isFetchingNextPage && hasNextPage) {
            void fetchNextPage();
          }
        }}
        onEndReachedThreshold={0.3}
        contentContainerStyle={styles.listContent}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <View style={styles.cardHeader}>
              <View style={styles.avatar}>
                <Text style={styles.avatarText}>{(item.author?.username ?? 'u').slice(0, 2).toUpperCase()}</Text>
              </View>
              <View style={styles.meta}>
                <Text style={styles.username}>@{item.author?.username ?? 'unknown'}</Text>
                <Text style={styles.time}>{new Date(item.created_at).toLocaleDateString()}</Text>
              </View>
            </View>

            {item.image_url ? <Image source={{ uri: item.image_url }} style={styles.image} /> : null}

            <Text style={styles.postText}>{item.text}</Text>

            <View style={styles.actions}>
              <Pressable onPress={() => void handleReaction(item)} style={styles.reactionButton}>
                <Text style={styles.reactionText}>♡ React</Text>
              </Pressable>
              <Text style={styles.score}>score {item.feed_score?.toFixed(2)}</Text>
            </View>
          </View>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.colors.background,
  },
  headerCard: {
    paddingHorizontal: theme.spacing.lg,
    paddingTop: theme.spacing.lg,
    paddingBottom: theme.spacing.md,
    backgroundColor: theme.colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: theme.colors.border,
  },
  title: {
    color: theme.colors.text,
    fontSize: 28,
    fontWeight: '700',
    marginBottom: theme.spacing.xs,
  },
  subtitle: {
    color: theme.colors.textMuted,
    fontSize: 16,
  },
  searchBox: {
    flexDirection: 'row',
    gap: theme.spacing.sm,
    padding: theme.spacing.md,
    backgroundColor: theme.colors.surface,
  },
  input: {
    flex: 1,
    borderWidth: 1,
    borderColor: theme.colors.border,
    borderRadius: 12,
    paddingHorizontal: theme.spacing.md,
    paddingVertical: theme.spacing.sm,
    color: theme.colors.text,
    backgroundColor: theme.colors.background,
  },
  composeButton: {
    backgroundColor: theme.colors.primary,
    paddingHorizontal: theme.spacing.md,
    justifyContent: 'center',
    borderRadius: 12,
  },
  composeText: {
    color: '#fff',
    fontWeight: '700',
  },
  searchPanel: {
    paddingHorizontal: theme.spacing.md,
    paddingTop: theme.spacing.sm,
    gap: theme.spacing.sm,
  },
  searchItem: {
    backgroundColor: theme.colors.surface,
    borderRadius: 12,
    padding: theme.spacing.md,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  searchTitle: {
    fontWeight: '700',
    color: theme.colors.text,
  },
  searchBody: {
    color: theme.colors.textMuted,
    marginTop: theme.spacing.xs,
  },
  searchText: {
    color: theme.colors.textMuted,
  },
  errorText: {
    color: '#b45309',
    marginHorizontal: theme.spacing.md,
    marginTop: theme.spacing.sm,
  },
  listContent: {
    padding: theme.spacing.md,
    gap: theme.spacing.md,
  },
  card: {
    backgroundColor: theme.colors.surface,
    borderRadius: 16,
    padding: theme.spacing.md,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: theme.spacing.sm,
  },
  avatar: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: theme.colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: theme.spacing.sm,
  },
  avatarText: {
    color: '#fff',
    fontWeight: '700',
  },
  meta: {
    flex: 1,
  },
  username: {
    color: theme.colors.text,
    fontWeight: '700',
  },
  time: {
    color: theme.colors.textMuted,
    fontSize: 12,
    marginTop: 2,
  },
  image: {
    width: '100%',
    height: 180,
    borderRadius: 12,
    marginBottom: theme.spacing.sm,
  },
  postText: {
    color: theme.colors.text,
    fontSize: 15,
    lineHeight: 22,
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: theme.spacing.sm,
  },
  reactionButton: {
    backgroundColor: theme.colors.background,
    borderRadius: 999,
    paddingVertical: theme.spacing.xs,
    paddingHorizontal: theme.spacing.md,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  reactionText: {
    color: theme.colors.primary,
    fontWeight: '700',
  },
  score: {
    color: theme.colors.textMuted,
    fontSize: 12,
  },
  emptyText: {
    color: theme.colors.textMuted,
    textAlign: 'center',
    marginTop: theme.spacing.lg,
  },
});
