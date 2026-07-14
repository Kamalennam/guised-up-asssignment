<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (in_array(\Tests\Concerns\RefreshPostgresDatabase::class, class_uses_recursive(static::class), true)) {
            $this->setUpTheDatabase();
        }
    }
}
