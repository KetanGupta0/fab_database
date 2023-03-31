<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddController;
use App\Http\Controllers\Transactions;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\WishlistController;

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
Route::post('setpassword',[AuthController::class,'resetPassword']);
Route::post('verify',[AuthController::class,'verifyOTP']);
Route::post('complete-profile',[AuthController::class,'profileComplition']);
Route::post('update-name',[AuthController::class,'updateName']);
Route::post('update-password',[AuthController::class,'updatePassword']);

// Categories and subcategories
Route::get('country',[AuthController::class,'countryCode']);
Route::get('index',[AddController::class,'indexCategories']);
Route::post('newform',[AddController::class,'formFields']);
Route::post('subcategory',[AddController::class,'subCategories']);

// Saving Adds
Route::post('partialSave',[AddController::class,'saveAddInfo']);
Route::post('storeImg',[AddController::class,'addImageUpload']);
Route::post('addTitle',[AddController::class,'saveAddTitle']);
Route::post('post-ads',[AddController::class,'saveAdsFinal']);

// Fetch ads
Route::post('fetch-ads',[AddController::class,'fetchAds']);

// Route::post('storeImg',[AddController::class,'storeBlobData']);
Route::post('aiduid',[AddController::class,'imageAidUid']);

// Comment Routes
Route::post('make-comment',[AddController::class,'adsComments']);
Route::post('fetch-comment',[AddController::class,'fetchAdsComments']);
Route::post('fetch-owner-comment',[AddController::class,'fetchOwnerComment']);

// Transaction Routes
Route::post('redeem-points',[Transactions::class,'redeemPoints']);

//Test Routes
// Route::get('imageUpload',[AddController::class,'addImageUpload']);
Route::get('newform',[AddController::class,'formFields']);
Route::get('subcategory',[AddController::class,'subCategories']);
Route::get('partialSave',[AddController::class,'saveAddInfo']);
Route::get('adds',[AddController::class,'displayAds']);
Route::post('adds',[AddController::class,'displayAds']);
Route::get('fetch-ads',[AddController::class,'fetchAds']);
Route::get('test',[AddController::class,'test']);
Route::post('fetch-logged-ads',[AddController::class,'fetchLoggedAds']);

// Chat to admin routes
Route::post('admin/send-message',[ChatController::class,'messageSendToAdmin']);
Route::post('admin/load-message',[ChatController::class,'loadAllChatsFromAdmin']);
Route::post('admin/change-seen-flag',[ChatController::class,'adminChatSeenFlagChange']);

// Wishlist routes
Route::post('add-to-favorites',[WishlistController::class,'addToWishlist']);
Route::post('remove-from-favorites',[WishlistController::class,'removeFromWishlist']);
Route::post('favorites',[WishlistController::class,'fetchUserWishList']);

// Unknown Routes
// Route::get('showForm',[AddController::class,'showForm']);
// Route::get('showAdd',[AddController::class,'showAdds']);