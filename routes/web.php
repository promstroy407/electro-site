<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LeadPulseController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\ForHomeController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubcontractController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');
Route::get('/{key}.txt', [SeoController::class, 'indexNowKey'])
    ->where('key', '[A-Za-z0-9-]{8,128}')
    ->name('indexnow.key');

Route::get('/uslugi', [ServiceController::class, 'index'])->name('services.index');
Route::get('/uslugi/{slug}', [ServiceController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('services.show');

Route::get('/subpodryad', SubcontractController::class)->name('subcontract');
Route::get('/dlya-doma', ForHomeController::class)->name('for-home');

Route::get('/ceny', [PriceController::class, 'index'])->name('prices.index');

Route::get('/obekty', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/obekty/{slug}', [ProjectController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('projects.show');

Route::get('/foto', GalleryController::class)->name('gallery');

Route::get('/partnery', PartnerController::class)->name('partners');

Route::get('/kalkulyator', CalculatorController::class)->name('calculator');

Route::get('/stati', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/stati/{slug}', [ArticleController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('articles.show');

// Лимит — в AppServiceProvider: защита от потока заявок с одного адреса.
Route::post('/zayavka', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('leads.store');

Route::view('/politika-konfidencialnosti', 'pages.privacy')->name('privacy');

// Админка спрашивает, не пришли ли новые заявки (звук и сообщение). Права — в контроллере.
Route::get('/admin/zayavki/pulse', LeadPulseController::class)
    ->middleware('auth')
    ->name('admin.leads.pulse');
