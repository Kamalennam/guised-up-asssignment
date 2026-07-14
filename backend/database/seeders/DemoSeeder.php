<?php

namespace Database\Seeders;

use App\Enums\InteractionType;
use App\Models\Interaction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo users, posts, and interactions for feed/search demonstrations.
     */
    public function run(): void
    {
        $kamal = User::query()->create([
            'name' => 'Kamal',
            'username' => 'kamal',
            'email' => 'kamal@guisedup.test',
            'password' => Hash::make('password'),
            'avatar_url' => null,
            'interest_interaction_count' => 5,
        ]);

        $kishore = User::query()->create([
            'name' => 'Kishore',
            'username' => 'kishore',
            'email' => 'kishore@guisedup.test',
            'password' => Hash::make('password'),
            'avatar_url' => null,
            'interest_interaction_count' => 8,
        ]);

        $venu = User::query()->create([
            'name' => 'Venu',
            'username' => 'venu',
            'email' => 'venu@guisedup.test',
            'password' => Hash::make('password'),
            'avatar_url' => null,
            'interest_interaction_count' => 6,
        ]);

        $kamalPosts = $this->seedPosts($kamal, [
            ['text' => 'Shipped a Laravel migration today that finally supports pgvector. Small win, big dopamine.', 'days_ago' => 2, 'score' => 0.9100],
            ['text' => 'Hot take: the best code review feedback is a question, not a command.', 'days_ago' => 5, 'score' => 0.9400],
            ['text' => 'Debugging a ranking algorithm at midnight. The weights look right but the feed feels wrong.', 'days_ago' => 8, 'score' => 0.8800],
            ['text' => 'Started reading about semantic search again. Vector databases are having their moment.', 'days_ago' => 12, 'score' => 0.9200],
            ['text' => 'Remote work pro tip: walk before your first standup. Your future commits will thank you.', 'days_ago' => 18, 'score' => 0.9300],
            ['text' => 'Building in public is scary until you realize nobody is watching as closely as you think.', 'days_ago' => 25, 'score' => 0.9000],
        ]);

        $kishorePosts = $this->seedPosts($kishore, [
            ['text' => 'Woke up above the cloud line in Munnar. No filter needed — just cold air and a cheap chai.', 'days_ago' => 1, 'score' => 0.9500],
            ['text' => 'Travel lesson #47: the best stories start when the plan falls apart.', 'days_ago' => 4, 'score' => 0.9300],
            ['text' => 'Shot 200 frames at golden hour. Kept 3. Photography is mostly deletion.', 'days_ago' => 9, 'score' => 0.9100],
            ['text' => 'Funny how a week in the mountains resets priorities faster than any productivity book.', 'days_ago' => 14, 'score' => 0.9200],
            ['text' => 'Backpacking with one pair of socks is a personality trait. I will not be taking questions.', 'days_ago' => 20, 'score' => 0.8900],
            ['text' => 'Found a trail nobody mentioned on any blog. Sometimes the internet is wrong and that is beautiful.', 'days_ago' => 28, 'score' => 0.9400],
        ]);

        $venuPosts = $this->seedPosts($venu, [
            ['text' => 'Sunday meal prep: sambar, rasam, and enough rice to survive a busy week. Boring? Reliable.', 'days_ago' => 3, 'score' => 0.9300],
            ['text' => 'Tried a 5K after two weeks off. Legs protested. Mood improved anyway.', 'days_ago' => 6, 'score' => 0.9000],
            ['text' => 'Cooking tip: toast your spices before they touch the pan. Your kitchen will smell like home.', 'days_ago' => 10, 'score' => 0.9400],
            ['text' => 'Protein without obsession — dosa, chutney, and a long walk. Balance beats extremes.', 'days_ago' => 15, 'score' => 0.9100],
            ['text' => 'Experimented with overnight oats and jaggery instead of sugar. Surprisingly good.', 'days_ago' => 22, 'score' => 0.9200],
            ['text' => 'Hydration is the most underrated performance hack. Coffee does not count. Sorry.', 'days_ago' => 27, 'score' => 0.8800],
        ]);

        $this->seedInteraction($kamal, $kishorePosts[0], InteractionType::View, 1);
        $this->seedInteraction($kamal, $kishorePosts[1], InteractionType::View, 2);
        $this->seedInteraction($kamal, $kishorePosts[2], InteractionType::Reaction, 3);
        $this->seedInteraction($kamal, $kishorePosts[3], InteractionType::Reply, 4);
        $this->seedInteraction($kamal, $venuPosts[0], InteractionType::View, 2);

        $this->seedInteraction($kishore, $venuPosts[0], InteractionType::View, 1);
        $this->seedInteraction($kishore, $venuPosts[1], InteractionType::View, 3);
        $this->seedInteraction($kishore, $venuPosts[2], InteractionType::Reaction, 4);
        $this->seedInteraction($kishore, $venuPosts[2], InteractionType::Share, 5);
        $this->seedInteraction($kishore, $kamalPosts[0], InteractionType::View, 2);

        $this->seedInteraction($venu, $kamalPosts[0], InteractionType::View, 1);
        $this->seedInteraction($venu, $kamalPosts[1], InteractionType::Reaction, 2);
        $this->seedInteraction($venu, $kamalPosts[1], InteractionType::Reply, 3);
        $this->seedInteraction($venu, $kamalPosts[3], InteractionType::Share, 4);
        $this->seedInteraction($venu, $kishorePosts[0], InteractionType::View, 2);
        $this->seedInteraction($venu, $kishorePosts[0], InteractionType::View, 5);
    }

    /**
     * @param  list<array{text: string, days_ago: int, score: float}>  $posts
     * @return list<Post>
     */
    private function seedPosts(User $author, array $posts): array
    {
        $created = [];

        foreach ($posts as $post) {
            $createdAt = now()->subDays($post['days_ago']);

            $created[] = Post::query()->create([
                'user_id' => $author->id,
                'text' => $post['text'],
                'image_url' => null,
                'authenticity_score' => $post['score'],
                'authenticity_breakdown' => [
                    'all_caps_penalty' => 1.0,
                    'link_density' => 0.98,
                    'duplicate_hash' => 1.0,
                ],
                'embedding_model' => null,
                'embedding_status' => 'pending',
                'content_hash' => hash('sha256', $post['text']),
                'metadata_json' => [
                    'word_count' => str_word_count($post['text']),
                    'has_question' => str_contains($post['text'], '?'),
                ],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        return $created;
    }

    private function seedInteraction(User $actor, Post $post, InteractionType $type, int $daysAgo): void
    {
        Interaction::query()->create([
            'user_id' => $actor->id,
            'post_id' => $post->id,
            'author_id' => $post->user_id,
            'type' => $type,
            'created_at' => now()->subDays($daysAgo),
        ]);
    }
}
