<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TutorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['cors']], function () {
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/request-register-tutor', [TutorController::class, 'requestRegister']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/students/{tutor}', [StudentController::class, 'studentList']);
        Route::get('/student/{student}/{user}', [StudentController::class, 'studentData']);

        Route::get('/tutors/', [TutorController::class, 'index']);
        Route::post('/concern/', [TutorController::class, 'reportConcern']);
        Route::get('/concerns/{student}', [TutorController::class, 'getConcerns']);
        Route::get('/tutor-requests/{tutor}', [TutorController::class, 'getTutorRequests']);
        Route::post('/accept-tutor/{tutor}/{requestId}', [TutorController::class, 'acceptTutorRequests']);
        Route::post('/decline-tutor/{tutor}/{requestId}', [TutorController::class, 'declineTutorRequests']);
    });
});



