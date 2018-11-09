<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function guests_can_not_favorite_anything()
    {
        $this->withExceptionHandling()
            ->post('replies/1/favorites')
            ->assertRedirect('/login');

    }

    /** @test */
    function an_authenticated_user_can_favorite_any_reply()
    {
        $this->signIn();
        $reply = create('App\Reply'); //it already creates a thread

        // $this->withoutExceptionHandling();
        // if I post to a favorite endpoint
        $this->post('replies/' . $reply->id . '/favorites');

        // it should be recorded in the database.
        $this->assertCount(1, $reply->favorites);
    }

    /** @test */
    function an_authenticated_user_may_only_favorite_a_reply_once()
    {
        $this->signIn();

        $reply = create('App\Reply');

        try {
        $this->post('replies/' . $reply->id . '/favorites');
        $this->post('replies/' . $reply->id . '/favorites');
        } catch (\Exception $e) {
            $this->fail('Did not expect to insert the same record set twice.');
        }

        $this->assertCount(1, $reply->favorites);

    }

}