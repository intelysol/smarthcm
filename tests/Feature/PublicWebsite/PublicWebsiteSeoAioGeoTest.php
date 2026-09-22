<?php

declare(strict_types=1);

namespace Tests\Feature\PublicWebsite;

use Tests\TestCase;

class PublicWebsiteSeoAioGeoTest extends TestCase
{
    /**
     * Test structured data JSON-LD on homepage and subpages.
     */
    public function test_structured_data_schemas_present(): void
    {
        // Homepage: Organization + WebSite + SoftwareApplication
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('"@type": "Organization"', false);
        $response->assertSee('"@type": "WebSite"', false);
        $response->assertSee('"@type": "SoftwareApplication"', false);
        $response->assertSee('"name": "SmartHCM"', false);

        // Mobile Attendance: SoftwareApplication + FAQPage + BreadcrumbList
        $response = $this->get('/mobile-attendance');
        $response->assertStatus(200);
        $response->assertSee('"@type": "SoftwareApplication"', false);
        $response->assertSee('"@type": "FAQPage"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);

        // Blog Article: BlogPosting
        $response = $this->get('/blog/mobile-gps-attendance-guide');
        $response->assertStatus(200);
        $response->assertSee('"@type": "BlogPosting"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
    }

    /**
     * Test Open Graph and Twitter Card tags.
     */
    public function test_open_graph_and_meta_tags(): void
    {
        $response = $this->get('/mobile-attendance');
        $response->assertStatus(200);
        $response->assertSee('<meta property="og:site_name" content="SmartHCM">', false);
        $response->assertSee('<meta property="og:type"', false);
        $response->assertSee('<meta property="og:url"', false);
        $response->assertSee('<meta name="twitter:card"', false);
    }

    /**
     * Test sitemap.xml returns valid XML containing public routes.
     */
    public function test_sitemap_xml_generation(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('/mobile-attendance');
        $response->assertSee('/platform');
        $response->assertSee('/payroll');
        $response->assertSee('/attendance');
        $response->assertSee('</urlset>', false);
    }

    /**
     * Test robots.txt adheres to public allow and private disallow policy.
     */
    public function test_robots_txt_rules(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $response->assertSee('Allow: /');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Disallow: /hr');
        $response->assertSee('Disallow: /manager');
        $response->assertSee('Disallow: /employee');
        $response->assertSee('Disallow: /portal');
        $response->assertSee('Sitemap:');
    }
}
