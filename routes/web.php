<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddController;
use App\Http\Controllers\test;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

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

// Route::get('/',[AuthController::class,'default']);
Route::view('/','welcome');
// Route::get('/',function(){
//     return redirect('https://www.youtube.com/watch?v=8yqlnrNsYP4');
// });

// Authentication
Route::post('login',[AuthController::class,'userLogin']);
Route::post('logout',[AuthController::class,'logoutUser']);
Route::post('register',[AuthController::class,'registration']);
Route::post('reset',[AuthController::class,'resetPasswordValidation']);
Route::post('setpassword',[AuthController::class,'updatePassword']);
Route::post('verify',[AuthController::class,'verifyOTP']);

// Categories and subcategories
Route::get('country',[AuthController::class,'countryCode']);
Route::get('index',[AddController::class,'indexCategories']);
Route::post('newform',[AddController::class,'formFields']);
Route::post('subcategory',[AddController::class,'subCategories']);

// Saving Adds
Route::post('partialSave',[AddController::class,'saveAddInfo']);
Route::post('imageUpload',[AddController::class,'addImageUpload']);
Route::post('addTitle',[AddController::class,'saveAddTitle']);
Route::post('post-ads',[AddController::class,'saveAdsFinal']);

//Test Routes
Route::get('imageUpload',[AddController::class,'addImageUpload']);
Route::get('newform',[AddController::class,'formFields']);
Route::get('showForm',[AddController::class,'showForm']);
Route::get('subcategory',[AddController::class,'subCategories']);
Route::get('showAdd',[AddController::class,'showAdds']);
Route::get('partialSave',[AddController::class,'saveAddInfo']);
Route::get('adds',[AddController::class,'displayAds']);
Route::post('adds',[AddController::class,'displayAds']);