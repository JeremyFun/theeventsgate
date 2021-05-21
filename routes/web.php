<?php

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

Route::get('/', function () {
    return view('welcome');
});


Eventmie::routes();

//--- redirect after extra auhentication stripe 3d after3DAuthentication
Route::get('extra/authentication/{id}','App\Http\Controllers\Eventmie\BookingsController@after3DAuthentication')->name('after3DAuthentication');

