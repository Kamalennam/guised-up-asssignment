<?php

return [
    'candidate_limit' => (int) env('FEED_CANDIDATE_LIMIT', 500),
    'candidate_days' => (int) env('FEED_CANDIDATE_DAYS', 30),
    'per_page' => (int) env('FEED_PER_PAGE', 20),
];
