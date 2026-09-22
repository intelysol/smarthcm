<?php

declare(strict_types=1);

use App\Domains\PublicWebsite\Http\Controllers\PublicWebsiteController;
use Illuminate\Support\Facades\Route;

// Public Website Routes
Route::middleware(['web'])->group(function () {
    // 1. Homepage
    Route::get('/', [PublicWebsiteController::class, 'home'])->name('public.home');

    // 2. Product & Platform Pages
    Route::get('/product', [PublicWebsiteController::class, 'platform'])->name('public.product');

    // 3. Specialized Mobile GPS Attendance Landing Page
    Route::get('/mobile-attendance', [PublicWebsiteController::class, 'mobileAttendance'])->name('public.mobile-attendance');

    // 4. Core Feature Pages
    Route::get('/core-hr', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'core-hr')->name('public.core-hr');
    Route::get('/employee-management', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'core-hr')->name('public.employee-management');
    Route::get('/attendance', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'attendance')->name('public.attendance');
    Route::get('/leave-management', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'leave-management')->name('public.leave-management');
    Route::get('/payroll', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'payroll')->name('public.payroll');
    Route::get('/recruitment', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'recruitment')->name('public.recruitment');
    Route::get('/onboarding', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'onboarding')->name('public.onboarding');
    Route::get('/performance-management', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'performance-management')->name('public.performance-management');
    Route::get('/learning-management', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'learning-management')->name('public.learning-management');
    Route::get('/benefits', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'benefits')->name('public.benefits');
    Route::get('/expense-management', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'expense-management')->name('public.expense-management');
    Route::get('/employee-self-service', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'employee-self-service')->name('public.employee-self-service');
    Route::get('/workforce-management', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'workforce-management')->name('public.workforce-management');
    Route::get('/workforce-planning', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'workforce-planning')->name('public.workforce-planning');
    Route::get('/workforce-analytics', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'workforce-analytics')->name('public.workforce-analytics');
    Route::get('/employee-engagement', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'employee-engagement')->name('public.employee-engagement');
    Route::get('/hr-service-delivery', [PublicWebsiteController::class, 'feature'])->defaults('slug', 'hr-service-delivery')->name('public.hr-service-delivery');

    // 5. Solutions Directory & Detail Pages
    Route::get('/solutions', [PublicWebsiteController::class, 'solutions'])->name('public.solutions.index');
    Route::get('/solutions/{slug}', [PublicWebsiteController::class, 'solution'])->name('public.solutions.show');

    // 6. Industries Directory & Detail Pages
    Route::get('/industries', [PublicWebsiteController::class, 'industries'])->name('public.industries.index');
    Route::get('/industries/{slug}', [PublicWebsiteController::class, 'industry'])->name('public.industries.show');

    // 7. Pricing, About, Security, Privacy, Terms
    Route::get('/pricing', [PublicWebsiteController::class, 'pricing'])->name('public.pricing');
    Route::get('/about', [PublicWebsiteController::class, 'about'])->name('public.about');
    Route::get('/security', [PublicWebsiteController::class, 'security'])->name('public.security');
    Route::get('/privacy', [PublicWebsiteController::class, 'privacy'])->name('public.privacy');
    Route::get('/terms', [PublicWebsiteController::class, 'terms'])->name('public.terms');

    // 8. Contact & Lead Inquiries
    Route::get('/contact', [PublicWebsiteController::class, 'contact'])->name('public.contact');
    Route::post('/contact', [PublicWebsiteController::class, 'submitContact'])->name('public.contact.submit');

    // 9. Request a Demo
    Route::get('/demo', [PublicWebsiteController::class, 'demo'])->name('public.demo');
    Route::get('/request-demo', [PublicWebsiteController::class, 'demo'])->name('public.request-demo');
    Route::post('/demo', [PublicWebsiteController::class, 'submitDemo'])->name('public.demo.submit');

    // 10. Knowledge Resources, Docs, Glossary, FAQ, Blog
    Route::get('/resources', [PublicWebsiteController::class, 'resources'])->name('public.resources');
    Route::get('/docs', [PublicWebsiteController::class, 'docs'])->name('public.docs.index');
    Route::get('/docs/{slug}', [PublicWebsiteController::class, 'docs'])->name('public.docs.show');
    Route::get('/glossary', [PublicWebsiteController::class, 'glossary'])->name('public.glossary');
    Route::get('/faq', [PublicWebsiteController::class, 'faq'])->name('public.faq');
    Route::get('/blog', [PublicWebsiteController::class, 'blog'])->name('public.blog.index');
    Route::get('/blog/{slug}', [PublicWebsiteController::class, 'blogPost'])->name('public.blog.show');

    // 11. Search Engine Infrastructure (Sitemap & Robots fallback)
    Route::get('/sitemap.xml', [PublicWebsiteController::class, 'sitemap'])->name('public.sitemap');
    Route::get('/robots.txt', [PublicWebsiteController::class, 'robots'])->name('public.robots');
});
