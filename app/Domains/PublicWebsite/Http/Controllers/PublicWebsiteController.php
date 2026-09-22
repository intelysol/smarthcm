<?php

declare(strict_types=1);

namespace App\Domains\PublicWebsite\Http\Controllers;

use App\Domains\PublicWebsite\Services\PublicContentService;
use App\Domains\PublicWebsite\Services\PublicLeadService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicWebsiteController extends Controller
{
    public function __construct(
        protected PublicContentService $contentService,
        protected PublicLeadService $leadService
    ) {}

    /**
     * Display the Enterprise SmartHCM Homepage.
     */
    public function home(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $modules = $this->contentService->getPlatformModules();
        $solutions = $this->contentService->getSolutions();
        $industries = $this->contentService->getIndustries();
        $faqs = $this->contentService->getFaqs();

        return view('public.home', [
            'org' => $org,
            'modules' => $modules,
            'solutions' => $solutions,
            'industries' => $industries,
            'faqs' => $faqs,
            'canonical' => url('/'),
            'title' => 'SmartHCM — Enterprise Human Capital Management Platform',
            'meta_description' => 'Manage workforce operations, HR administration, GPS attendance, automated payroll, and people intelligence with SmartHCM enterprise cloud platform.',
        ]);
    }

    /**
     * Display the Integrated HCM Platform Overview.
     */
    public function platform(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $modules = $this->contentService->getPlatformModules();

        return view('public.platform', [
            'org' => $org,
            'modules' => $modules,
            'canonical' => url('/platform'),
            'title' => 'SmartHCM Platform Architecture — Integrated Enterprise HCM',
            'meta_description' => 'Explore the SmartHCM integrated architecture covering Core HR, Workforce Management, Payroll, and Responsible AI with verified lifecycle maturity statuses.',
        ]);
    }

    /**
     * Dedicated Pillar Landing Page: Mobile GPS Attendance.
     */
    public function mobileAttendance(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $details = $this->contentService->getFeatureDetails('mobile-attendance');

        return view('public.mobile-attendance', [
            'org' => $org,
            'feature' => $details,
            'canonical' => url('/mobile-attendance'),
            'title' => 'Mobile GPS Attendance & Geo-Fencing Software | SmartHCM',
            'meta_description' => 'Location-aware mobile attendance for field and enterprise workforces. Geofencing, work-location binding, offline mode, and photo verification without continuous tracking.',
        ]);
    }

    /**
     * Display individual feature landing pages.
     */
    public function feature(Request $request, string $slug): View
    {
        if ($slug === 'mobile-attendance') {
            return $this->mobileAttendance($request);
        }

        $details = $this->contentService->getFeatureDetails($slug);

        if (!$details) {
            abort(404, "Feature '{$slug}' not found.");
        }

        $org = $this->contentService->getOrganizationData();
        $allModules = $this->contentService->getPlatformModules();

        return view('public.feature', [
            'slug' => $slug,
            'org' => $org,
            'feature' => $details,
            'allModules' => $allModules,
            'canonical' => url('/' . $slug),
            'title' => $details['meta_title'] ?? $details['title'],
            'meta_description' => $details['meta_description'],
        ]);
    }

    /**
     * Solutions Index Directory.
     */
    public function solutions(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $solutions = $this->contentService->getSolutions();

        return view('public.solutions.index', [
            'org' => $org,
            'solutions' => $solutions,
            'canonical' => url('/solutions'),
            'title' => 'Enterprise HCM Solutions & Business Transformation | SmartHCM',
            'meta_description' => 'Explore SmartHCM enterprise solutions for HR digitization, employee self-service, operational workforce management, and multi-tenant SaaS architectures.',
        ]);
    }

    /**
     * Individual Solution Page.
     */
    public function solution(Request $request, string $slug): View
    {
        $solutions = $this->contentService->getSolutions();

        if (!isset($solutions[$slug])) {
            abort(404, "Solution '{$slug}' not found.");
        }

        $solution = $solutions[$slug];
        $org = $this->contentService->getOrganizationData();

        return view('public.solutions.show', [
            'solution' => $solution,
            'org' => $org,
            'canonical' => url('/solutions/' . $slug),
            'title' => "{$solution['title']} | SmartHCM Solutions",
            'meta_description' => $solution['tagline'] . ' ' . $solution['approach'],
        ]);
    }

    /**
     * Industries Index Directory.
     */
    public function industries(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $industries = $this->contentService->getIndustries();

        return view('public.industries.index', [
            'org' => $org,
            'industries' => $industries,
            'canonical' => url('/industries'),
            'title' => 'Industry Workforce Solutions | SmartHCM',
            'meta_description' => 'Tailored HCM and workforce management solutions for manufacturing, healthcare, retail, logistics, construction, education, and technology enterprises.',
        ]);
    }

    /**
     * Individual Industry Page.
     */
    public function industry(Request $request, string $slug): View
    {
        $industries = $this->contentService->getIndustries();

        if (!isset($industries[$slug])) {
            abort(404, "Industry '{$slug}' not found.");
        }

        $industry = $industries[$slug];
        $org = $this->contentService->getOrganizationData();

        return view('public.industries.show', [
            'industry' => $industry,
            'org' => $org,
            'canonical' => url('/industries/' . $slug),
            'title' => "{$industry['name']} Workforce Management & HCM | SmartHCM",
            'meta_description' => $industry['tagline'] . ' Addressing critical shift rostering, attendance, and compliance challenges.',
        ]);
    }

    /**
     * Pricing Page.
     */
    public function pricing(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $tiers = $this->contentService->getPricingTiers();

        return view('public.pricing', [
            'org' => $org,
            'tiers' => $tiers,
            'canonical' => url('/pricing'),
            'title' => 'Enterprise HCM Pricing & Editions | SmartHCM',
            'meta_description' => 'Transparent enterprise HCM plans for growing teams, mid-market businesses, and global multi-tenant corporations. Request custom pricing and quote.',
        ]);
    }

    /**
     * About Page.
     */
    public function about(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();

        return view('public.about', [
            'org' => $org,
            'canonical' => url('/about'),
            'title' => 'About SmartHCM — Enterprise Workforce Innovation',
            'meta_description' => 'Learn about SmartHCM, our enterprise mission, zero-trust cloud architecture, and commitment to privacy and workforce excellence.',
        ]);
    }

    /**
     * Enterprise Security Overview.
     */
    public function security(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();

        return view('public.security', [
            'org' => $org,
            'canonical' => url('/security'),
            'title' => 'Enterprise Security, Compliance & Data Protection | SmartHCM',
            'meta_description' => 'Review SmartHCM security standards: multi-tenant cryptographic isolation, AES-256 encryption, zero-trust RBAC, audit logging, and automated backups.',
        ]);
    }

    /**
     * Privacy Policy.
     */
    public function privacy(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();

        return view('public.privacy', [
            'org' => $org,
            'canonical' => url('/privacy'),
            'title' => 'Privacy Policy & Data Protection | SmartHCM',
            'meta_description' => 'SmartHCM privacy policy detailing data categories collected, processing purposes, zero continuous tracking commitment, and employee data rights.',
        ]);
    }

    /**
     * Terms of Service.
     */
    public function terms(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();

        return view('public.terms', [
            'org' => $org,
            'canonical' => url('/terms'),
            'title' => 'Enterprise Terms of Service | SmartHCM',
            'meta_description' => 'Review the official SmartHCM SaaS terms of service, subscription provisions, service level commitments, and acceptable use guidelines.',
        ]);
    }

    /**
     * Contact Page.
     */
    public function contact(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();

        return view('public.contact', [
            'org' => $org,
            'canonical' => url('/contact'),
            'title' => 'Contact SmartHCM Enterprise Sales & Support',
            'meta_description' => 'Get in touch with the SmartHCM solutions team for technical consultations, sales inquiries, and platform demonstrations.',
        ]);
    }

    /**
     * Submit Contact Inquiry Form.
     */
    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'work_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:64',
            'message' => 'required|string|max:2000',
        ]);

        $validated['type'] = 'contact';
        $this->leadService->captureLead($validated, $request);

        return redirect()->route('public.contact')->with('success', 'Thank you for contacting SmartHCM. Our enterprise specialist will respond within one business day.');
    }

    /**
     * Request a Demo Page.
     */
    public function demo(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();

        return view('public.demo', [
            'org' => $org,
            'canonical' => url('/demo'),
            'title' => 'Request an Enterprise SmartHCM Product Demo',
            'meta_description' => 'Schedule a personalized demonstration of SmartHCM with an HCM product specialist. Explore Core HR, GPS attendance, payroll, and WFM live.',
        ]);
    }

    /**
     * Submit Demo Request Form.
     */
    public function submitDemo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'work_email' => 'required|email|max:255',
            'phone' => 'required|string|max:64',
            'country' => 'nullable|string|max:100',
            'organization_size' => 'required|string|max:64',
            'hcm_requirements' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $validated['type'] = 'demo';
        $this->leadService->captureLead($validated, $request);

        return redirect()->route('public.demo')->with('success', 'Your demo request has been received! Our enterprise team will contact you to schedule your custom walkthrough.');
    }

    /**
     * Resources Directory.
     */
    public function resources(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $docs = $this->contentService->getDocs();
        $articles = $this->contentService->getBlogArticles();

        return view('public.resources', [
            'org' => $org,
            'docs' => $docs,
            'articles' => $articles,
            'canonical' => url('/resources'),
            'title' => 'SmartHCM Resource Center & Knowledge Base',
            'meta_description' => 'Access technical guides, public documentation, workforce articles, and HCM glossary resources from the SmartHCM team.',
        ]);
    }

    /**
     * Public Documentation Center.
     */
    public function docs(Request $request, string $slug = 'getting-started'): View
    {
        $docs = $this->contentService->getDocs();

        if (!isset($docs[$slug])) {
            abort(404, "Documentation topic '{$slug}' not found.");
        }

        $currentDoc = $docs[$slug];
        $org = $this->contentService->getOrganizationData();

        return view('public.docs.show', [
            'doc' => $currentDoc,
            'allDocs' => $docs,
            'org' => $org,
            'canonical' => url('/docs/' . $slug),
            'title' => "{$currentDoc['title']} | SmartHCM Documentation",
            'meta_description' => $currentDoc['summary'],
        ]);
    }

    /**
     * Entity-First HCM Glossary.
     */
    public function glossary(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $terms = $this->contentService->getGlossary();

        return view('public.glossary', [
            'org' => $org,
            'terms' => $terms,
            'canonical' => url('/glossary'),
            'title' => 'Enterprise HCM Glossary & Definitions | SmartHCM',
            'meta_description' => 'Authoritative glossary of Human Capital Management terms, including HRIS, HRMS, ESS, WFM, Geofencing, and GPS attendance.',
        ]);
    }

    /**
     * FAQ Directory.
     */
    public function faq(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $faqs = $this->contentService->getFaqs();

        return view('public.faq', [
            'org' => $org,
            'faqs' => $faqs,
            'canonical' => url('/faq'),
            'title' => 'SmartHCM Frequently Asked Questions (FAQ)',
            'meta_description' => 'Find verified answers to common questions regarding SmartHCM attendance tracking, payroll compliance, data security, and deployment.',
        ]);
    }

    /**
     * Public Blog Hub.
     */
    public function blog(Request $request): View
    {
        $org = $this->contentService->getOrganizationData();
        $articles = $this->contentService->getBlogArticles();

        return view('public.blog.index', [
            'org' => $org,
            'articles' => $articles,
            'canonical' => url('/blog'),
            'title' => 'SmartHCM Enterprise Workforce & HR Insights Blog',
            'meta_description' => 'In-depth perspectives on modern workforce management, mobile attendance, payroll automation, and strategic employee experience.',
        ]);
    }

    /**
     * Individual Blog Article.
     */
    public function blogPost(Request $request, string $slug): View
    {
        $articles = $this->contentService->getBlogArticles();

        if (!isset($articles[$slug])) {
            abort(404, "Blog article '{$slug}' not found.");
        }

        $article = $articles[$slug];
        $org = $this->contentService->getOrganizationData();

        return view('public.blog.show', [
            'article' => $article,
            'org' => $org,
            'canonical' => url('/blog/' . $slug),
            'title' => "{$article['title']} | SmartHCM Blog",
            'meta_description' => $article['summary'],
        ]);
    }

    /**
     * Generate dynamic sitemap.xml.
     */
    public function sitemap(Request $request): Response
    {
        $baseUrl = url('/');
        $now = now()->toAtomString();

        $routes = [
            ['url' => "{$baseUrl}/", 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => "{$baseUrl}/platform", 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => "{$baseUrl}/mobile-attendance", 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => "{$baseUrl}/solutions", 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => "{$baseUrl}/industries", 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => "{$baseUrl}/pricing", 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/demo", 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/contact", 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/about", 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/security", 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/privacy", 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/terms", 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/resources", 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => "{$baseUrl}/docs", 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => "{$baseUrl}/glossary", 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/faq", 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => "{$baseUrl}/blog", 'priority' => '0.8', 'changefreq' => 'weekly'],
        ];

        // Add feature landing pages
        foreach (array_keys($this->contentService->getPlatformModules()) as $slug) {
            $routes[] = [
                'url' => "{$baseUrl}/{$slug}",
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        // Add solutions
        foreach (array_keys($this->contentService->getSolutions()) as $slug) {
            $routes[] = [
                'url' => "{$baseUrl}/solutions/{$slug}",
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        // Add industries
        foreach (array_keys($this->contentService->getIndustries()) as $slug) {
            $routes[] = [
                'url' => "{$baseUrl}/industries/{$slug}",
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        // Add docs
        foreach (array_keys($this->contentService->getDocs()) as $slug) {
            $routes[] = [
                'url' => "{$baseUrl}/docs/{$slug}",
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        // Add blog posts
        foreach ($this->contentService->getBlogArticles() as $slug => $article) {
            $routes[] = [
                'url' => "{$baseUrl}/blog/{$slug}",
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($routes as $route) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($route['url']) . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$route['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$route['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Deliver production robots.txt.
     */
    public function robots(Request $request): Response
    {
        $baseUrl = url('/');
        $txt = "User-agent: *\n";
        $txt .= "Allow: /\n";
        $txt .= "Disallow: /app\n";
        $txt .= "Disallow: /admin\n";
        $txt .= "Disallow: /hr\n";
        $txt .= "Disallow: /manager\n";
        $txt .= "Disallow: /employee\n";
        $txt .= "Disallow: /executive\n";
        $txt .= "Disallow: /operations\n";
        $txt .= "Disallow: /portal\n";
        $txt .= "Disallow: /storage/\n";
        $txt .= "\n";
        $txt .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return response($txt, 200, ['Content-Type' => 'text/plain']);
    }
}
