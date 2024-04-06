<?php

namespace Tests\Feature\services;

use App\Services\TwitterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TwitterServiceTest extends TestCase
{
    private TwitterService $twitterService;

    public function setUp(): void
    {
        parent::setUp();
        $this->twitterService = new TwitterService();
    }

    public function test_can_get_me(): void {
        $response = $this->twitterService->getMe();
        $this->assertEquals(200, $response->status());
        $response = json_decode($response->getContent(), true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('user_id', $response);




    }
}
