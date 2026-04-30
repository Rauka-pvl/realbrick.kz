<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('real-brick.index');
});

Route::get('/about', function () {
    return view('real-brick.pages', ['page' => 'about']);
});

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/contacts', function () {
    return view('real-brick.pages', ['page' => 'contacts']);
});

Route::get('/calculator', [CalculatorController::class, 'index'])->name('calculator');

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/collection/{slug}', [CatalogController::class, 'collection'])->name('catalog.collection');
Route::get('/catalog/product/{slug}', [CatalogController::class, 'product'])->name('catalog.product');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/submit', [CartController::class, 'submit'])->name('cart.submit');
