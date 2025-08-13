<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormUserSubmitController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/action-survey', [FormUserSubmitController::class, 'showForm'])->name('survey.show');
Route::post('/action-survey/submit', [FormUserSubmitController::class, 'submitForm'])->name('survey.submit');
Route::post('/action-survey/retake', [FormUserSubmitController::class, 'retakeSurvey'])->name('survey.retake');


Route::post('/webhook', [\App\Http\Controllers\WebHookController::class, 'receive'])->name('webhook.receive');
