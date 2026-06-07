<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\Http\Controllers\Web\Admin\ActivityLogController;
use Modules\AdminModule\Http\Controllers\Web\Admin\DashboardController;
use Modules\AdminModule\Http\Controllers\Web\Admin\FirebaseSubscribeController;
use Modules\AdminModule\Http\Controllers\Web\Admin\FleetMapViewController;
use Modules\AdminModule\Http\Controllers\Web\Admin\SettingController;
use Modules\AdminModule\Http\Controllers\Web\Admin\SharedController;

// ==========================================
// YOU MUST ADD THIS IMPORT FOR IT TO WORK!
// ==========================================
use Modules\AdminModule\Http\Controllers\Web\Admin\BookNowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {

    Route::controller(FirebaseSubscribeController::class)->group(function () {
        Route::post('/subscribe-topic', 'subscribeToTopic')->name('subscribe-topic');
    });

    Route::controller(FleetMapViewController::class)->group(function(){
        Route::get('fleet-map/{type}', 'fleetMap')->name('fleet-map');
        Route::get('fleet-map-driver-list/{type}', 'fleetMapDriverList')->name('fleet-map-driver-list');
        Route::get('fleet-map-driver-details/{id}', 'fleetMapDriverDetails')->name('fleet-map-driver-details');
        Route::get('fleet-map-view-single-driver/{id}', 'fleetMapViewSingleDriver')->name('fleet-map-view-single-driver');
        Route::get('fleet-map-customer-list/{type}', 'fleetMapCustomerList')->name('fleet-map-customer-list');
        Route::get('fleet-map-customer-details/{id}', 'fleetMapCustomerDetails')->name('fleet-map-customer-details');
        Route::get('fleet-map-view-single-customer/{id}', 'fleetMapViewSingleCustomer')->name('fleet-map-view-single-customer');
        Route::get('fleet-map-view-using-ajax', 'fleetMapViewUsingAjax')->name('fleet-map-view-using-ajax');
        Route::get('fleet-map-safety-alert-icon-in-map', 'fleetMapSafetyAlertIconInMap')->name('fleet-map-safety-alert-icon-in-map');
        Route::get('fleet-map-zone-message', 'fleetMapZoneMessage')->name('fleet-map-zone-message');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
        Route::get('heat-map', 'heatMap')->name('heat-map');
        Route::get('heat-map-overview-data', 'heatMapOverview')->name('heat-map-overview-data');
        Route::get('heat-map-compare', 'heatMapCompare')->name('heat-map-compare');
        Route::get('recent-trip-activity', 'recentTripActivity')->name('recent-trip-activity');
        Route::get('leader-board-driver', 'leaderBoardDriver')->name('leader-board-driver');
        Route::get('leader-board-customer', 'leaderBoardCustomer')->name('leader-board-customer');
        Route::get('earning-statistics', 'adminEarningStatistics')->name('earning-statistics');
        Route::get('zone-wise-statistics', 'zoneWiseStatistics')->name('zone-wise-statistics');
        Route::get('chatting', 'chatting')->name('chatting');
        Route::get('driver-conversation/{channelId}', 'getDriverConversation')->name('driver-conversation');
        Route::post('send-message-to-driver', 'sendMessageToDriver')->name('send-message-to-driver');
        Route::get('search-drivers', 'searchDriversList')->name('search-drivers');
        Route::get('search-saved-topic-answers', 'searchSavedTopicAnswer')->name('search-saved-topic-answers');
        Route::put('create-channel-with-admin', 'createChannelWithAdmin')->name('create-channel-with-admin');
    });

    Route::controller(ActivityLogController::class)->group(function () {
        Route::get('log', 'log')->name('log');
    });

    Route::controller(SettingController::class)->group(function () {
        Route::get('settings', 'index')->name('settings');
        Route::post('update-profile/{id}', 'update')->name('update-profile');
    });

    Route::controller(SharedController::class)->group(function () {
        Route::get('seen-notification', 'seenNotification')->name('seen-notification');
        Route::get('get-notifications', 'getNotifications')->name('get-notifications');
        Route::get('get-safety-alert', 'getSafetyAlert')->name('get-safety-alert');
    });

  Route::controller(BookNowController::class)
        ->prefix('book-now')
        ->name('book-now.')
        ->group(function () {

            // List  — GET  admin/book-now/list/{status}
            Route::get('list/{status}', 'index')->name('index');

            // Create form — GET  admin/book-now/create
            Route::get('create', 'create')->name('create');

            // Store — POST  admin/book-now/store
            Route::post('store', 'store')->name('store');

            // Details — GET  admin/book-now/details/{id}
            Route::get('details/{id}', 'details')->name('details');

            // Update — PUT  admin/book-now/update/{id}
            Route::put('update/{id}', 'update')->name('update');

            // Delete — DELETE  admin/book-now/delete/{id}
            Route::delete('delete/{id}', 'destroy')->name('destroy');
   Route::get('driver/{id}/vehicle-category', 'getDriverVehicleCategory')->name('driver-vehicle-category');
   // Store Customer (AJAX) — POST admin/book-now/store-customer
        Route::post('store-customer', 'storeCustomer')->name('store-customer');
   // Zone Fleet (AJAX) — GET admin/book-now/zone-fleet/{id}
        Route::get('zone-fleet/{id}', 'getZoneFleet')->name('zone-fleet');


        });



});

Route::controller(SharedController::class)->group(function () {
    Route::get('lang/{locale}', 'lang')->name('lang');
});
