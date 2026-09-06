<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_mark_tour_as_seen(): void
    {
        $response = $this->post(route('onboarding-tours.store'), ['tour' => 'dashboard']);

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_mark_a_tour_as_seen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('onboarding-tours.store'), ['tour' => 'dashboard']);

        $response->assertOk();
        $this->assertTrue($user->fresh()->hasSeenOnboardingTour('dashboard'));
        $this->assertFalse($user->fresh()->hasSeenOnboardingTour('builder'));
    }

    public function test_marking_the_same_tour_twice_does_not_duplicate_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('onboarding-tours.store'), ['tour' => 'dashboard']);
        $this->actingAs($user)->postJson(route('onboarding-tours.store'), ['tour' => 'dashboard']);

        $this->assertSame(['dashboard'], $user->fresh()->onboarding_tours_seen);
    }

    public function test_unknown_tour_name_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding-tours.store'), ['tour' => 'not-a-real-tour']);

        $response->assertSessionHasErrors('tour');
        $this->assertNull($user->fresh()->onboarding_tours_seen);
    }
}
