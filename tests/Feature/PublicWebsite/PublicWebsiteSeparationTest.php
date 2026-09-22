<?php

declare(strict_types=1);

namespace Tests\Feature\PublicWebsite;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteSeparationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test that private application areas require authentication and redirect guests to login.
     */
    public function test_private_application_areas_are_protected_from_public_access(): void
    {
        $privateRoutes = [
            '/platform/control-center',
            '/admin/dashboard',
            '/hr/dashboard',
            '/manager/workbench',
            '/employee/home',
            '/executive/overview',
            '/operations/dashboard',
        ];

        foreach ($privateRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test that public marketing pages remain accessible to guests.
     */
    public function test_public_marketing_pages_accessible_to_guests(): void
    {
        $publicRoutes = [
            '/',
            '/platform',
            '/mobile-attendance',
            '/core-hr',
            '/attendance',
            '/payroll',
            '/solutions',
            '/industries',
            '/pricing',
            '/about',
            '/security',
            '/demo',
            '/contact',
            '/docs',
            '/glossary',
            '/faq',
            '/blog',
        ];

        foreach ($publicRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }
}
