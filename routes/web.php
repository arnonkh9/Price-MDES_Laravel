<?php

use App\Http\Controllers\ExportController;
use App\Livewire\Auth\Login;
use App\Livewire\CompareView;
use App\Livewire\ComparisonList;
use App\Livewire\Dashboard;
use App\Livewire\ProductList;
use App\Livewire\ReportPage;
use App\Http\Controllers\ReportExportController;
use App\Livewire\CharacteristicsList;
use App\Livewire\GuidelineList;
use App\Livewire\RecommendationList;
use App\Livewire\CategoryListPage;
use App\Livewire\BrandListPage;
use App\Livewire\UserList;
use App\Livewire\UserProfile;
use App\Livewire\RoleList;
use App\Livewire\AuditLogPage;
use App\Livewire\MenuPermissionMatrix;
use App\Livewire\SearchPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'));

Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', UserProfile::class)->name('profile');
    Route::get('/products', ProductList::class)->name('products');
    Route::get('/specs', CharacteristicsList::class)->name('specs');
    Route::get('/comparisons', ComparisonList::class)->name('comparisons');
    Route::get('/compare', CompareView::class)->name('compare');
    Route::get('/guidelines', GuidelineList::class)->name('guidelines');
    Route::get('/recommendations', RecommendationList::class)->name('recommendations');
    Route::get('/categories', CategoryListPage::class)->name('categories');
    Route::get('/brands', BrandListPage::class)->name('brands');
    Route::get('/users', UserList::class)->name('users');
    Route::get('/roles', RoleList::class)->name('roles');
    Route::get('/permissions', MenuPermissionMatrix::class)->name('permissions');
    Route::get('/audit-log', AuditLogPage::class)->name('audit-log');
    Route::get('/search', SearchPage::class)->name('search');
    Route::get('/reports', ReportPage::class)->name('reports');
    Route::get('/reports/export/pdf', [ReportExportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [ReportExportController::class, 'excel'])->name('reports.export.excel');

    Route::get('/products/export', [ExportController::class, 'products'])->name('products.export');
    Route::get('/products/sample', [ExportController::class, 'sampleProductsTemplate'])->name('products.sample');
    Route::get('/comparisons/export/bulk', [ExportController::class, 'comparisonsMultipleSheets'])->name('comparisons.export.bulk');
    Route::get('/comparisons/{comparison}/export', [ExportController::class, 'comparison'])->name('comparisons.export');
    Route::get('/comparisons/{comparison}/export/pdf', [ExportController::class, 'comparisonPdf'])->name('comparisons.export.pdf');
    Route::get('/specs/sample', [ExportController::class, 'sampleCharacteristicsTemplate'])->name('specs.sample');
    Route::get('/specs/{spec}/export', [ExportController::class, 'characteristics'])->name('specs.export');
    Route::get('/specs/{spec}/export/pdf', [ExportController::class, 'specPdf'])->name('specs.export.pdf');
    Route::get('/specs/export/bulk', [ExportController::class, 'bulkCharacteristics'])->name('specs.export.bulk');
});
