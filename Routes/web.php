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

/*
*
* Backend Routes
*
* --------------------------------------------------------------------
*/
Route::group(['namespace' => '\Modules\Inboxer\Http\Controllers\Backend', 'as' => 'backend.', 'middleware' => ['web', 'auth', 'can:view_backend'], 'prefix' => 'admin'], function () {
    /*
    * These routes need view-backend permission
    * (good if you want to allow more than one group in the backend,
    * then limit the backend features by different roles or permissions)
    *
    * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
    */


    $module_name = 'lists';
    $controller_name = 'MailListController';
    Route::get("$module_name/index_list", ['as' => "$module_name.index_list", 'uses' => "$controller_name@index_list"]);
    Route::get("$module_name/index_data", ['as' => "$module_name.index_data", 'uses' => "$controller_name@index_data"]);
    Route::get("$module_name/trashed", ['as' => "$module_name.trashed", 'uses' => "$controller_name@trashed"]);
    Route::patch("$module_name/trashed/{id}", ['as' => "$module_name.restore", 'uses' => "$controller_name@restore"]);


    Route::get("$module_name/{uid}/verification/progress", 'MailListController@verificationProgress');
    Route::get("$module_name/{uid}/verification", 'MailListController@verification');
    Route::post("$module_name/{uid}/verification/start", 'MailListController@startVerification');
    Route::post("$module_name/{uid}/verification/stop", 'MailListController@stopVerification');
    Route::post("$module_name/{uid}/verification/reset", 'MailListController@resetVerification');
    Route::post("$module_name/copy", 'MailListController@copy');
    Route::get("$module_name/quick-view", 'MailListController@quickView');

    Route::get("$module_name/{uid}/list-growth", 'MailListController@listGrowthChart');
    Route::get("$module_name/{uid}/list-statistics-chart", 'MailListController@statisticsChart');
    Route::resource("$module_name", "$controller_name");


    $module_sub = 'subscribers';
    $controller_name = 'SubscriberController';
    Route::get("$module_name/{uid}/$module_sub/index_list", ['as' => "$module_sub.index_list", 'uses' => "$controller_name@index_list"]);
    Route::get("$module_name/{uid}/$module_sub/index_data", ['as' => "$module_sub.index_data", 'uses' => "$controller_name@index_data"]);

    // Mail list
    Route::get('/lists/{uid}/verification/progress', 'MailListController@verificationProgress');
    Route::get('/lists/{uid}/verification', 'MailListController@verification');
    Route::post('/lists/{uid}/verification/start', 'MailListController@startVerification');
    Route::post('/lists/{uid}/verification/stop', 'MailListController@stopVerification');
    Route::post('/lists/{uid}/verification/reset', 'MailListController@resetVerification');
    Route::post('/lists/copy', 'MailListController@copy');
    Route::get('/lists/quick-view', 'MailListController@quickView');
    Route::get('/lists/{uid}/list-growth', 'MailListController@listGrowthChart');
    Route::get('/lists/{uid}/list-statistics-chart', 'MailListController@statisticsChart');
    Route::get('/lists/sort', 'MailListController@sort');
    Route::get('/lists/listing/{page?}', 'MailListController@listing');
    Route::get('/lists/delete', 'MailListController@delete');
    Route::get('/lists/delete/confirm', 'MailListController@deleteConfirm');
    Route::get('/lists/{uid}/overview', 'MailListController@overview')->name('mail_list');
    Route::resource('/lists', 'MailListController');
    Route::get('/lists/{uid}/edit', 'MailListController@edit');
    Route::patch('lists/{uid}/update', 'MailListController@update');
    Route::get('/lists/{uid}/embedded-form', 'MailListController@embeddedForm');
    Route::get('/lists/{uid}/embedded-form-frame', 'MailListController@embeddedFormFrame');

    // Field
    Route::get('/lists/{list_uid}/fields', 'FieldController@index');
    Route::get('/lists/{list_uid}/fields/sort', 'FieldController@sort');
    Route::post('lists/{list_uid}/fields/store', 'FieldController@store');
    Route::get('/lists/{list_uid}/fields/sample/{type}', 'FieldController@sample');
    Route::get('/lists/{list_uid}/fields/{uid}/delete', 'FieldController@delete');

    // Subscriber
    Route::post('lists/{list_uid}/subscribers/resend/confirmation-email/{uids?}', 'SubscriberController@resendConfirmationEmail');
    Route::post('subscriber/{uid}/verification/start', 'SubscriberController@startVerification');
    Route::post('subscriber/{uid}/verification/reset', 'SubscriberController@resetVerification');
    Route::get('/lists/{from_uid}/copy-move-from/{action}', 'SubscriberController@copyMoveForm');
    Route::post('subscribers/move', 'SubscriberController@move');
    Route::post('subscribers/copy', 'SubscriberController@copy');
    Route::get('/lists/{list_uid}/subscribers', 'SubscriberController@index')->name('lists.subscribers');
    Route::get('/lists/{list_uid}/subscribers/create', 'SubscriberController@create')->name('subscribers.create');
    Route::get('/lists/{list_uid}/subscribers/listing', 'SubscriberController@listing');
    Route::post('lists/{list_uid}/subscribers/store', 'SubscriberController@store');
    Route::get('/lists/{list_uid}/subscribers/{uid}/edit', 'SubscriberController@edit')->name('subscribers.edit');
    Route::patch('lists/{list_uid}/subscribers/{uid}/update', 'SubscriberController@update');
    Route::get('/lists/{list_uid}/subscribers/delete', 'SubscriberController@delete');
    Route::get('/lists/{list_uid}/subscribers/subscribe', 'SubscriberController@subscribe');
    Route::get('/lists/{list_uid}/subscribers/unsubscribe', 'SubscriberController@unsubscribe');
    Route::get('/lists/{list_uid}/subscribers/import', 'SubscriberController@import');
    Route::post('lists/{list_uid}/subscribers/import', 'SubscriberController@import');
    Route::get('/lists/{list_uid}/subscribers/import/list', 'SubscriberController@importList');
    Route::get('/lists/{list_uid}/subscribers/import/log', 'SubscriberController@downloadImportLog');
    Route::get('/lists/{list_uid}/subscribers/import/proccess', 'SubscriberController@importProccess');
    Route::get('/lists/{list_uid}/subscribers/export', 'SubscriberController@export');
    Route::post('lists/{list_uid}/subscribers/export', 'SubscriberController@export');
    Route::get('/lists/{list_uid}/subscribers/export/proccess', 'SubscriberController@exportProccess');
    Route::get('/lists/{list_uid}/subscribers/export/download', 'SubscriberController@downloadExportedCsv');
    Route::get('/lists/{list_uid}/subscribers/export/list', 'SubscriberController@exportList');

    // Segment
    Route::get('/segments/condition-value-control', 'SegmentController@conditionValueControl');
    Route::get('/segments/select_box', 'SegmentController@selectBox');
    Route::get('/lists/{list_uid}/segments', 'SegmentController@index');
    Route::get('/lists/{list_uid}/segments/{uid}/subscribers', 'SegmentController@subscribers');
    Route::get('/lists/{list_uid}/segments/{uid}/listing_subscribers', 'SegmentController@listing_subscribers');
    Route::get('/lists/{list_uid}/segments/create', 'SegmentController@create');
    Route::get('/lists/{list_uid}/segments/listing', 'SegmentController@listing');
    Route::post('lists/{list_uid}/segments/store', 'SegmentController@store');
    Route::get('/lists/{list_uid}/segments/{uid}/edit', 'SegmentController@edit');
    Route::patch('lists/{list_uid}/segments/{uid}/update', 'SegmentController@update');
    Route::get('/lists/{list_uid}/segments/delete', 'SegmentController@delete');
    Route::post('lists/{list_uid}/segments/{uid}/export', 'SegmentController@export');
    Route::get('/lists/{list_uid}/segments/{uid}/export', 'SegmentController@viewExport');
    Route::get('/lists/{list_uid}/segments/{uid}/export/list', 'SegmentController@exportList');
    Route::get('/lists/{list_uid}/segments/sample_condition', 'SegmentController@sample_condition');

    /*Route::get("$module_name/{uid}/$module_sub/trashed", ['as' => "$module_sub.trashed", 'uses' => "$controller_name@trashed"]);
    Route::patch("$module_name/$module_sub/trashed/{id}", ['as' => "$module_sub.restore", 'uses' => "$controller_name@restore"]);
    Route::resource("$module_name/{list_uid}/$module_sub", "$controller_name");*/

    $module_name = 'templates';
    $controller_name = 'TemplateController';
    Route::get("$module_name/index_list", ['as' => "$module_name.index_list", 'uses' => "$controller_name@index_list"]);
    Route::get("$module_name/index_data", ['as' => "$module_name.index_data", 'uses' => "$controller_name@index_data"]);
    Route::get("$module_name/trashed", ['as' => "$module_name.trashed", 'uses' => "$controller_name@trashed"]);
    Route::patch("$module_name/trashed/{id}", ['as' => "$module_name.restore", 'uses' => "$controller_name@restore"]);


    // Template
    Route::post('templates/{uid}/copy', 'TemplateController@copy');
    Route::get('/templates/{uid}/copy', 'TemplateController@copy');
    Route::get('/templates/{uid}/content', 'TemplateController@content');
    Route::get('/templates/sort', 'TemplateController@sort');
    Route::get('/templates/listing/{page?}', 'TemplateController@listing');
    Route::get('/templates/choosing/{campaign_uid}/{page?}', 'TemplateController@choosing');
    Route::get('/templates/upload', 'TemplateController@upload')->name('templates.upload');
    Route::post('templates/upload', 'TemplateController@upload');
    Route::get('/templates/{uid}/image', 'TemplateController@image');
    Route::post('templates/{uid}/saveImage', 'TemplateController@saveImage');
    Route::get('/templates/{uid}/preview', 'TemplateController@preview');
    Route::get('/templates/delete', 'TemplateController@delete');
    Route::get('/templates/build/select', 'TemplateController@buildSelect')->name('templates.build.select');
    Route::get('/templates/build/{style?}', 'TemplateController@build');
    Route::get('/templates/{uid}/rebuild', 'TemplateController@rebuild')->name('templates.rebuild');
    Route::resource('templates', 'TemplateController');
    Route::get('/templates/{uid}/edit', 'TemplateController@edit');
    Route::patch('templates/{uid}/update', 'TemplateController@update');

    Route::resource("$module_name", "$controller_name");



    $module_name = 'campaigns';
    $controller_name = 'CampaignController';
    Route::get("$module_name/index_list", ['as' => "$module_name.index_list", 'uses' => "$controller_name@index_list"]);
    Route::get("$module_name/index_data", ['as' => "$module_name.index_data", 'uses' => "$controller_name@index_data"]);
    Route::get("$module_name/trashed", ['as' => "$module_name.trashed", 'uses' => "$controller_name@trashed"]);
    Route::patch("$module_name/trashed/{id}", ['as' => "$module_name.restore", 'uses' => "$controller_name@restore"]);

    // Campaign
    Route::get('/campaigns/{from_uid}/copy-move-from/{action}', 'CampaignController@copyMoveForm');
    Route::post('/campaigns/{uid}/resend', 'CampaignController@resend');

    Route::get('/campaigns/{uid}/template/review-iframe', 'CampaignController@templateReviewIframe');
    Route::get('/campaigns/{uid}/template/review', 'CampaignController@templateReview');
    Route::get('/campaigns/select-type', 'CampaignController@selectType');
    Route::get('/campaigns/{uid}/list-segment-form', 'CampaignController@listSegmentForm');
    Route::post('/campaigns/{uid}/image/save', 'CampaignController@saveImage');
    Route::get('/campaigns/{uid}/image', 'CampaignController@image');
    Route::get('/campaigns/{uid}/preview', 'CampaignController@preview');
    Route::get('/campaigns/templates/list', 'CampaignController@templateList');
    Route::patch('/campaigns/{uid}/templates/choose/from/{from_uid}', 'CampaignController@campaignTemplateChoose');
    Route::post('/campaigns/send-test-email', 'CampaignController@sendTestEmail');
    Route::get('/campaigns/delete/confirm', 'CampaignController@deleteConfirm');
    Route::post('/campaigns/copy', 'CampaignController@copy');
    Route::get('/campaigns/{uid}/subscribers', 'CampaignController@subscribers');
    Route::get('/campaigns/{uid}/subscribers/listing', 'CampaignController@subscribersListing');
    Route::get('/campaigns/{uid}/open-map', 'CampaignController@openMap');
    Route::get('/campaigns/{uid}/tracking-log', 'CampaignController@trackingLog');
    Route::get('/campaigns/{uid}/tracking-log/listing', 'CampaignController@trackingLogListing');
    Route::get('/campaigns/{uid}/bounce-log', 'CampaignController@bounceLog');
    Route::get('/campaigns/{uid}/bounce-log/listing', 'CampaignController@bounceLogListing');
    Route::get('/campaigns/{uid}/feedback-log', 'CampaignController@feedbackLog');
    Route::get('/campaigns/{uid}/feedback-log/listing', 'CampaignController@feedbackLogListing');
    Route::get('/campaigns/{uid}/open-log', 'CampaignController@openLog');
    Route::get('/campaigns/{uid}/open-log/listing', 'CampaignController@openLogListing');
    Route::get('/campaigns/{uid}/click-log', 'CampaignController@clickLog');
    Route::get('/campaigns/{uid}/click-log/listing', 'CampaignController@clickLogListing');
    Route::get('/campaigns/{uid}/unsubscribe-log', 'CampaignController@unsubscribeLog');
    Route::get('/campaigns/{uid}/unsubscribe-log/listing', 'CampaignController@unsubscribeLogListing');

    Route::get('/campaigns/quick-view', 'CampaignController@quickView');
    Route::get('/campaigns/{uid}/chart24h', 'CampaignController@chart24h');
    Route::get('/campaigns/{uid}/chart', 'CampaignController@chart');
    Route::get('/campaigns/{uid}/chart/countries/open', 'CampaignController@chartCountry');
    Route::get('/campaigns/{uid}/chart/countries/click', 'CampaignController@chartClickCountry');
    Route::get('/campaigns/{uid}/overview', 'CampaignController@overview');
    Route::get('/campaigns/{uid}/links', 'CampaignController@links');
    Route::get('/campaigns/sort', 'CampaignController@sort');
    Route::get('/campaigns/listing/{page?}', 'CampaignController@listing');
    Route::get('/campaigns/{uid}/recipients', 'CampaignController@recipients');
    Route::post('/campaigns/{uid}/recipients', 'CampaignController@recipients');
    Route::get('/campaigns/{uid}/setup', 'CampaignController@setup');
    Route::post('/campaigns/{uid}/setup', 'CampaignController@setup');
    Route::get('/campaigns/{uid}/template', 'CampaignController@template');
    Route::post('/campaigns/{uid}/template', 'CampaignController@template');
    Route::get('/campaigns/{uid}/template/select', 'CampaignController@templateSelect');
    Route::get('/campaigns/{uid}/template/choose/{template_uid}', 'CampaignController@templateChoose');
    Route::get('/campaigns/{uid}/template/preview', 'CampaignController@templatePreview');
    Route::get('/campaigns/{uid}/template/iframe', 'CampaignController@templateIframe');
    Route::get('/campaigns/{uid}/template/build/{style}', 'CampaignController@templateBuild');
    Route::get('/campaigns/{uid}/template/rebuild', 'CampaignController@templateRebuild');
    Route::get('/campaigns/{uid}/schedule', 'CampaignController@schedule');
    Route::post('/campaigns/{uid}/schedule', 'CampaignController@schedule');
    Route::get('/campaigns/{uid}/confirm', 'CampaignController@confirm');
    Route::post('/campaigns/{uid}/confirm', 'CampaignController@confirm');
    Route::get('/campaigns/delete', 'CampaignController@delete');
    Route::get('/campaigns/select2', 'CampaignController@select2');
    Route::get('/campaigns/pause', 'CampaignController@pause');
    Route::get('/campaigns/restart', 'CampaignController@restart');
    // Route::resource('/campaigns', 'CampaignController');
    Route::get('/campaigns/{uid}/edit', 'CampaignController@edit');
    Route::patch('/campaigns/{uid}/update', 'CampaignController@update');

    Route::resource("$module_name", "$controller_name");

});
