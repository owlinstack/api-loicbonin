<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_panel_returns_true_when_email_matches_admin_email(): void
    {
        config(['app.admin_email' => 'admin@loicbonin.com']);

        $user = new User(['email' => 'admin@loicbonin.com']);
        $panel = \Mockery::mock(Panel::class);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_can_access_panel_returns_false_when_email_does_not_match(): void
    {
        config(['app.admin_email' => 'admin@loicbonin.com']);

        $user = new User(['email' => 'hacker@example.com']);
        $panel = \Mockery::mock(Panel::class);

        $this->assertFalse($user->canAccessPanel($panel));
    }
}
