<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::group(['prefix' => 'images'], function () {
    Route::post('/upload', [ImageController::class, 'uploadImages'])->name('images.upload');
    Route::get('/all', [ImageController::class, 'getImages'])->name('images.index');
    Route::post('/download', [ImageController::class, 'downloadImage'])->name('download.image');
});

Route::group(['prefix' => 'api'], function () {
    Route::get('/images-all', [ImageController::class, 'getAllImages'])->name('get_all_images');
    Route::get('/images/{id}', [ImageController::class, 'getImageById'])->name('get_image_by_id');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
