import React, { memo, useEffect, useRef } from 'react';
import { Animated, StyleSheet, View, type DimensionValue, type StyleProp, type ViewStyle } from 'react-native';

import { theme } from '../../theme';

const SKELETON_COUNT = 5;

function SkeletonBar({ style, width, height, borderRadius }: { style?: StyleProp<ViewStyle>; width?: DimensionValue; height?: number; borderRadius?: number }) {
  const shimmer = useRef(new Animated.Value(-1)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.timing(shimmer, {
        toValue: 1,
        duration: 1200,
        useNativeDriver: true,
      }),
    );

    animation.start();

    return () => animation.stop();
  }, [shimmer]);

  const translateX = shimmer.interpolate({
    inputRange: [-1, 1],
    outputRange: [-120, 120],
  });

  return (
    <View style={[styles.baseBlock, { width, height, borderRadius }, style]}>
      <Animated.View
        style={[
          StyleSheet.absoluteFillObject,
          styles.shimmerOverlay,
          {
            transform: [{ translateX }],
          },
        ]}
      />
    </View>
  );
}

const FeedSkeleton = memo(function FeedSkeleton() {
  return (
    <View style={styles.container}>
      {Array.from({ length: SKELETON_COUNT }, (_, index) => (
        <View key={index} style={styles.card}>
          <View style={styles.cardHeader}>
            <SkeletonBar style={styles.avatar} width={42} height={42} borderRadius={21} />
            <View style={styles.meta}>
              <SkeletonBar style={styles.usernameLine} width="70%" height={14} borderRadius={8} />
              <SkeletonBar style={styles.timeLine} width="40%" height={12} borderRadius={6} />
            </View>
          </View>

          <SkeletonBar style={styles.image} width="100%" height={180} borderRadius={12} />

          <SkeletonBar style={styles.bodyLine} width="100%" height={14} borderRadius={6} />
          <SkeletonBar style={styles.bodyLine} width="88%" height={14} borderRadius={6} />
          <SkeletonBar style={styles.bodyLine} width="62%" height={14} borderRadius={6} />

          <View style={styles.actions}>
            <SkeletonBar style={styles.actionButton} width={96} height={32} borderRadius={999} />
            <SkeletonBar style={styles.scoreLine} width={64} height={12} borderRadius={6} />
          </View>
        </View>
      ))}
    </View>
  );
});

export { FeedSkeleton };

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: theme.spacing.md,
    gap: theme.spacing.md,
    backgroundColor: theme.colors.background,
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
    marginRight: theme.spacing.sm,
    backgroundColor: '#E9DDD0',
  },
  meta: {
    flex: 1,
  },
  usernameLine: {
    marginBottom: theme.spacing.xs,
    backgroundColor: '#E9DDD0',
  },
  timeLine: {
    backgroundColor: '#EDE3D8',
  },
  image: {
    marginBottom: theme.spacing.sm,
    backgroundColor: '#EDE3D8',
  },
  bodyLine: {
    marginBottom: theme.spacing.xs,
    backgroundColor: '#EDE3D8',
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: theme.spacing.sm,
  },
  actionButton: {
    backgroundColor: '#E9DDD0',
  },
  scoreLine: {
    backgroundColor: '#EDE3D8',
  },
  baseBlock: {
    overflow: 'hidden',
    backgroundColor: '#EDE3D8',
  },
  shimmerOverlay: {
    width: '200%',
    height: '100%',
    backgroundColor: 'rgba(255,255,255,0.4)',
    transform: [{ skewX: '-20deg' }],
  },
});
