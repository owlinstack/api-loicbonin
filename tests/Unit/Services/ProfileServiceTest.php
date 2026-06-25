<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Profile;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfileService $profileService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profileService = new ProfileService;
    }

    public function test_get_profile_data_returns_fallback_when_database_is_empty(): void
    {
        // Supprime tout profil existant en base (par précaution)
        Profile::query()->delete();

        $data = $this->profileService->getProfileData();

        $this->assertEquals('Loïc Bonin', $data->name);
        $this->assertStringContainsString('Lyon', $data->bio);
        $this->assertTrue($data->showTimeline);
        $this->assertNotEmpty($data->skills);
        $this->assertNotEmpty($data->timeline);
        $this->assertTrue($data->showEducation);
        $this->assertNotEmpty($data->education);
        $this->assertNull($data->cvPath);
        $this->assertNull($data->avatarPath);
    }

    public function test_get_profile_data_returns_database_values_when_present(): void
    {
        // Supprime tout profil existant en base
        Profile::query()->delete();

        Profile::create([
            'name' => 'Jean Michel',
            'bio' => 'Ma biographie personnalisée',
            'skills' => [
                ['term' => 'Kubernetes', 'description' => 'Gestion de conteneurs'],
            ],
            'timeline' => [
                ['date' => '2026', 'title' => 'DevOps Specialist', 'description' => 'Job'],
            ],
            'education' => [
                ['date' => '2020', 'title' => 'Master Info', 'description' => 'School'],
            ],
            'show_timeline' => true,
            'show_education' => true,
            'cv_url' => 'cvs/personal.pdf',
            'avatar_url' => 'avatars/personal.jpg',
        ]);

        $data = $this->profileService->getProfileData();

        $this->assertEquals('Jean Michel', $data->name);
        $this->assertEquals('Ma biographie personnalisée', $data->bio);
        $this->assertEquals([['term' => 'Kubernetes', 'description' => 'Gestion de conteneurs']], $data->skills);
        $this->assertTrue($data->showTimeline);
        $this->assertEquals([['date' => '2026', 'title' => 'DevOps Specialist', 'description' => 'Job']], $data->timeline);
        $this->assertTrue($data->showEducation);
        $this->assertEquals([['date' => '2020', 'title' => 'Master Info', 'description' => 'School']], $data->education);
        $this->assertEquals('cvs/personal.pdf', $data->cvPath);
        $this->assertEquals('avatars/personal.jpg', $data->avatarPath);
    }

    public function test_get_profile_data_respects_visibility_toggles(): void
    {
        Profile::query()->delete();

        Profile::create([
            'name' => 'Jean Michel Toggles',
            'bio' => 'Ma biographie personnalisée toggles',
            'skills' => [],
            'timeline' => [
                ['date' => '2026', 'title' => 'DevOps Specialist', 'description' => 'Job'],
            ],
            'education' => [
                ['date' => '2020', 'title' => 'Master Info', 'description' => 'School'],
            ],
            'show_timeline' => false,
            'show_education' => false,
            'cv_url' => null,
            'avatar_url' => null,
        ]);

        $data = $this->profileService->getProfileData();

        $this->assertFalse($data->showTimeline);
        $this->assertNull($data->timeline);
        $this->assertFalse($data->showEducation);
        $this->assertNull($data->education);
    }

    public function test_get_profile_data_toggles_are_independent(): void
    {
        Profile::query()->delete();

        Profile::create([
            'name' => 'Jean Michel Asymétrique',
            'bio' => 'Bio',
            'skills' => [],
            'timeline' => [
                ['date' => '2026', 'title' => 'Job', 'description' => 'Desc'],
            ],
            'education' => [
                ['date' => '2020', 'title' => 'Master', 'description' => 'School'],
            ],
            'show_timeline' => false,  // masquée
            'show_education' => true,  // visible
            'cv_url' => null,
            'avatar_url' => null,
        ]);

        $data = $this->profileService->getProfileData();

        // timeline est masquée
        $this->assertFalse($data->showTimeline);
        $this->assertNull($data->timeline);

        // education reste visible
        $this->assertTrue($data->showEducation);
        $this->assertNotNull($data->education);
        $this->assertCount(1, $data->education);
    }
}
