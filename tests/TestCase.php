<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function postWithCsrf(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $token = 'test-csrf-token';

        return $this->withSession(['_token' => $token])
            ->post($uri, array_merge(['_token' => $token], $data), $headers);
    }
}
