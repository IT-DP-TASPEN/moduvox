<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\CareerController as AdminCareerController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\SocialMediaController as AdminSocialMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::get('/fix-slugs', function () {
    $services = \App\Models\Service::whereNull('slug')->orWhere('slug', '')->get();
    $count = 0;
    foreach ($services as $s) {
        $s->slug = \Illuminate\Support\Str::slug($s->name);
        $s->save();
        $count++;
    }
    return "Berhasil memperbarui slug untuk {$count} layanan.";
});

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/services', 'services')->name('services');
    Route::get('/services/{slug}', 'serviceShow')->name('services.show');
    Route::get('/projects', 'projects')->name('projects');
    Route::get('/projects/{project:slug}', 'projectShow')->name('projects.show');
    Route::get('/career', 'career')->name('career');
    Route::get('/career/{slug}', 'careerShow')->name('career.show');
    Route::get('/contact', 'contact')->name('contact');
});
Route::post('/contact', [MessageController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::redirect('/', '/admin/dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', AdminProjectController::class)->except(['show']);
    Route::delete('projects/gallery/{image}', [AdminProjectController::class, 'deleteGalleryImage'])->name('projects.gallery.destroy');
    Route::resource('services', AdminServiceController::class)->except(['show']);
    Route::get('backup-db', [DashboardController::class, 'backupDb'])->name('backup.db');
    Route::resource('clients', AdminClientController::class)->except(['show']);
    Route::resource('socials', AdminSocialMediaController::class)->except(['show']);
    Route::resource('careers', AdminCareerController::class)->except(['show']);
    Route::patch('careers/{career}/toggle', [AdminCareerController::class, 'toggle'])->name('careers.toggle');

    Route::controller(AdminMessageController::class)->group(function () {
        Route::get('messages', 'index')->name('messages.index');
        Route::get('messages/{message}', 'show')->name('messages.show');
        Route::delete('messages/{message}', 'destroy')->name('messages.destroy');
    });

    Route::controller(\App\Http\Controllers\Admin\PageSettingController::class)->group(function () {
        Route::get('pages/home', 'home')->name('pages.home');
        Route::get('pages/project', 'project')->name('pages.project');
        Route::get('pages/about', 'about')->name('pages.about');
        Route::get('pages/services', 'services')->name('pages.services');
        Route::get('pages/contact', 'contact')->name('pages.contact');
        Route::get('pages/career', 'career')->name('pages.career');
        Route::post('pages/{page}', 'store')->where('page', 'home|project|about|services|contact|career')->name('pages.store');
        Route::delete('pages/{page}/{key}', 'destroySetting')->name('pages.destroy');
    });
});
