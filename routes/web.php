<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LanguageController;
use App\Http\Controllers\Frontend\NewspaperController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('news', [HomeController::class, 'news'])->name('news');
Route::get('news/{slug}', [HomeController::class, 'ShowNews'])->name('news-details');

Route::get('journal/telecharger', [NewspaperController::class, 'download'])->name('newspaper.download');

Route::get('about', [HomeController::class, 'about'])->name('about');
Route::get('contact', [HomeController::class, 'contact'])->name('contact');
Route::post('contact', [HomeController::class, 'handleContactFrom'])->name('contact.submit');

Route::post('subscribe-newsletter', [HomeController::class, 'SubscribeNewsLetter'])->name('subscribe-newsletter');
Route::post('language', LanguageController::class)->name('language');

Route::middleware('auth')->group(function () {
    Route::post('news-comment', [HomeController::class, 'handleComment'])->name('news-comment');
    Route::post('news-comment-replay', [HomeController::class, 'handleReplay'])->name('news-comment-replay');
    Route::delete('news-comment', [HomeController::class, 'commentDestory'])->name('news-comment-destroy');
});

require __DIR__.'/auth.php';
