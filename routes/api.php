<?php

use App\Http\Controllers\OutfitController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClothingItemController;
use App\Http\Controllers\WeatherController;


Route::post('/register', [UserController::class, 'registerUser']);
Route::post('/login', [UserController::class, 'loginUser']);
Route::get('/outfits', [OutfitController::class, 'allOutfits']);

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello World']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logoutUser']);
    Route::get('/users', [UserController::class, 'getUserAttributes']);
    Route::patch('/users', [UserController::class, 'updateUserAttributes']);

    Route::get('/clothing-images/{clothingItemId}', [ClothingItemController::class, 'showImage'])->name('clothing-images.show');    
    Route::post('/clothingitems', [ClothingItemController::class, 'addClothingItem']);
    Route::get('/my-clothingitems', [ClothingItemController::class, 'getMyClothingItems']);
    Route::get('/clothingitems/{itemId}', [ClothingItemController::class, 'getClothingItemById']);
    Route::delete('/clothingitems/{itemId}', [ClothingItemController::class, 'deleteClothingItem']);
    Route::patch('/clothingitems/{itemId}', [ClothingItemController::class, 'updateClothingItem']);

    Route::post('/createpost', [PostController::class, 'createpost']);
    Route::get('/showmyPosts', [PostController::class, 'showmyPosts']);

    Route::get('/outfits/liked', [OutfitController::class, 'liked']);
    Route::get('/outfits/disliked', [OutfitController::class, 'disliked']);

});

Route::apiResource('/outfits', OutfitController::class)->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/debug-clothingitems', function (Request $request) {
    // This will print all request data to your console and stop the script.
    // Check your php artisan serve terminal for the output.

    return dd($request->all());
    // You can also inspect files separately
    // dd($request->file('image'));
});
