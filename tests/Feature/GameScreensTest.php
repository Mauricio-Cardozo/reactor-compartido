<?php

namespace Tests\Feature;

use Tests\TestCase;

class GameScreensTest extends TestCase
{
    public function test_the_index_lists_both_roles(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('game.emisor'));
        $response->assertSee(route('game.receptor'));
    }

    public function test_the_emisor_screen_is_reachable(): void
    {
        $response = $this->get('/emisor');

        $response->assertOk();
        $response->assertSee('data-screen="emisor"', false);
    }

    public function test_the_receptor_screen_is_reachable(): void
    {
        $response = $this->get('/receptor');

        $response->assertOk();
        $response->assertSee('data-screen="receptor"', false);
    }
}
