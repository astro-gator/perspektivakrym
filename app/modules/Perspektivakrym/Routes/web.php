<?php

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

use Illuminate\Support\Facades\Route;

Route::prefix('perspektivakrym')->group(function() {
    Route::any('/', 'PerspektivakrymController@index');

    Route::post('/calculate-payments', 'PerspektivakrymController@calculatePayments')->name('perspektivakrym_perspektivakrym_calculatePayments');
    Route::post('/recalculate-payments', 'PerspektivakrymController@reCalculatePayments')->name('perspektivakrym_perspektivakrym_recalculatePayments');
    Route::post('/block', 'PerspektivakrymController@block')->name('perspektivakrym_perspektivakrym_block');
    Route::post('/unblock', 'PerspektivakrymController@unblock')->name('perspektivakrym_perspektivakrym_unblock');
    Route::post('/sync-with-1s', 'PerspektivakrymController@syncWith1S')->name('perspektivakrym_perspektivakrym_syncWith1S');
    Route::post('/generate-pdf', 'PerspektivakrymController@getPdf')->name('perspektivakrym_perspektivakrym_getpdf');
    Route::post('/generate-general-pdf', 'PerspektivakrymController@getGeneralPdf')->name('perspektivakrym_perspektivakrym_get_general_pdf');
    Route::post('/generate-plan-excel', 'PerspektivakrymController@getExcelForLawyer')->name('perspektivakrym_perspektivakrym_get_excel_for_lawyer');
    Route::post('/change-plan-amount', 'PerspektivakrymController@changePlanAmount')->name('perspektivakrym_perspektivakrym-planAmount');
    Route::post('/change-plan-date', 'PerspektivakrymController@changePlanDate')->name('perspektivakrym_perspektivakrym-planDate');
    Route::post('/get-payment', 'PerspektivakrymController@getPayment')->name('perspektivakrym_perspektivakrym_getPayment');
    Route::post('/add-plan-payment', 'PerspektivakrymController@addPlanPayment')->name('perspektivakrym_perspektivakrym_addPlanPayment');
    Route::post('/add-fact-payment', 'PerspektivakrymController@addFactPayment')->name('perspektivakrym_perspektivakrym_addFactPayment');
    Route::post('/edit-fact-payment', 'PerspektivakrymController@editFactPayment')->name('perspektivakrym_perspektivakrym_editFactPayment');
    Route::post('/delete-fact-payment', 'PerspektivakrymController@deleteFactPayment')->name('perspektivakrym_perspektivakrym_deleteFactPayment');
    Route::post('/delete-plan-payment', 'PerspektivakrymController@deletePlanPayment')->name('perspektivakrym_perspektivakrym_deletePlanPayment');
    Route::post('/freeze-payment', 'PerspektivakrymController@freezePayment')->name('perspektivakrym_perspektivakrym_freezePayment');
    Route::post('/unfreeze-payment', 'PerspektivakrymController@unfreezePayment')->name('perspektivakrym_perspektivakrym_unfreezePayment');
    Route::post('/edit-comment', 'PerspektivakrymController@editComment')->name('perspektivakrym_perspektivakrym_edit_comment');

    //
    Route::any('/test', 'UnisenderController@test');
//    Route::any('/test', 'PerspektivakrymController@getAllFactPaymentByDealId');
//    Route::any('/test', 'ProfitXmlController@parseXml');

//    Route::get('/parse-deals', 'UnisenderController@getDealAllLead');
//    Route::get('/parse-deals', 'UnisenderController@getDealInterested');
//    Route::get('/parse-deals', 'UnisenderController@getDealBought');

//    Route::get('/parse-deals', 'UnisenderController@getContactInfo');
});

