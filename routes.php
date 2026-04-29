<?php
Route::prefix('berkas')->group(function () {
    Route::post('{bonWarkahId}', 'BerkasController@store');
    Route::put('{berkasId}', 'BerkasController@update');
    Route::post('{berkasId}', 'BerkasController@update');
    Route::delete('{berkasId}', 'BerkasController@destroy');
    Route::get('bon/{bonWarkahId}', 'BerkasController@getByBonWarkah');
    Route::get('{berkasId}/download', 'BerkasController@download');
});