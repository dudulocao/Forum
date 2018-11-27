<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LockThreadsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function non_adm_may_not_lock_threads()
    {
        $this->withExceptionHandling();
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);

        $this->post(route('locked-threads.store', $thread)) //it is post bc we are creating a new thread in the locked state
            ->assertStatus(403);

        $this->assertFalse($thread->fresh()->locked);
    }

    /** @test */
    function adm_may_unlock_threads()
    {
        $this->signIn(factory('App\User')->states('administrator')->create());

        $thread = create('App\Thread', ['user_id' => auth()->id(), 'locked' => true]);

        $this->delete(route('locked-threads.destroy', $thread));

        $this->assertFalse($thread->fresh()->locked, 'Failed asserting that the thread was unlocked.');

    }

    /** @test */
    function adm_may_lock_threads()
    {
        $this->signIn(factory('App\User')->states('administrator')->create());

        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        
        $this->post(route('locked-threads.store', $thread));
        
        $this->assertTrue($thread->fresh()->locked, 'Failed asserting that the thread was locked.');
    }

    /** @test */
    function once_locked_a_thread_may_not_receive_new_replies()
    {
        $this->signIn();

        $thread = create('App\Thread', ['locked' => true]);

        $this->post($thread->path() . '/replies', [
            'body' => 'Foobar',
            'user_id' => auth()->id()
        ])->assertStatus(422);
    }
}
