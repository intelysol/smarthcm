<?php

declare(strict_types=1);

namespace Tests\Feature\PublicWebsite;

use Tests\TestCase;

class PublicWebsiteRoutesTest extends TestCase
{
    /**
     * Test primary marketing landing routes return status 200 and SEO tags.
     */
    public function test_primary_marketing_routes_render_successfully(): void
    {
        $routes = [
            '/',
            '/platform',
            '/product',
            '/mobile-attendance',
            '/pricing',
            '/about',
            '/security',
            '/privacy',
            '/terms',
            '/contact',
            '/demo',
            '/resources',
            '/glossary',
            '/faq',
            '/blog',
            '/docs',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
            $response->assertSee('<link rel="canonical"', false);
            $response->assertSee('<title>', false);
            $response->assertSee('<meta name="description"', false);
        }
    }

    /**
     * Test core module feature landing pages return status 200 and concrete content.
     */
    public function test_feature_module_pages_render_successfully(): void
    {
        $features = [
            'core-hr',
            'attendance',
            'payroll',
            'leave-management',
            'recruitment',
            'onboarding',
            'performance-management',
            'learning-management',
            'employee-self-service',
            'workforce-management',
            'workforce-analytics',
        ];

        foreach ($features as $slug) {
            $response = $this->get('/' . $slug);
            $response->assertStatus(200);
            $response->assertSee('Key Capabilities');
            $response->assertSee('The Operational Challenge');
            $response->assertSee('Request a Demo');
        }
    }

    /**
     * Test solution pages render with problem-solution model.
     */
    public function test_solution_pages_render_successfully(): void
    {
        $response = $this->get('/solutions');
        $response->assertStatus(200);
        $response->assertSee('Enterprise HCM Business Solutions');

        $solutions = [
            'hr-digitization',
            'workforce-management',
            'attendance-management',
            'payroll-operations',
            'multi-tenant-hr-saas',
        ];

        foreach ($solutions as $slug) {
            $response = $this->get('/solutions/' . $slug);
            $response->assertStatus(200);
            $response->assertSee('The Problem');
            $response->assertSee('SmartHCM Approach');
        }
    }

    /**
     * Test industry landing pages answer the industry content model.
     */
    public function test_industry_pages_render_successfully(): void
    {
        $response = $this->get('/industries');
        $response->assertStatus(200);
        $response->assertSee('Industry Workforce Solutions');

        $industries = [
            'manufacturing',
            'healthcare',
            'retail',
            'logistics',
            'construction',
            'technology',
        ];

        foreach ($industries as $slug) {
            $response = $this->get('/industries/' . $slug);
            $response->assertStatus(200);
            $response->assertSee('Core HR Challenges');
            $response->assertSee('How Employee Self-Service (ESS) Helps');
            $response->assertSee('How Workforce Management (WFM) Helps');
        }
    }

    /**
     * Test dedicated documentation and blog article pages.
     */
    public function test_docs_and_blog_pages_render_successfully(): void
    {
        $response = $this->get('/docs/getting-started');
        $response->assertStatus(200);
        $response->assertSee('Getting Started with SmartHCM');

        $response = $this->get('/docs/mobile-attendance');
        $response->assertStatus(200);
        $response->assertSee('Mobile GPS Attendance & Geofencing Setup');

        $response = $this->get('/blog/mobile-gps-attendance-guide');
        $response->assertStatus(200);
        $response->assertSee('SmartHCM Workforce Intelligence Team');
        $response->assertSee('Published:');
    }
}
