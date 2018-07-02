<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', function (Request $request) {
        Log::info('req user');
        $user = $request->user()->with(['userRoleSI'=>
                function($query){
                    $query->with(['userRole'])->where('id_si',3);
                }])->where('id',$request->user()->id)->first(); 
        Log::info($user);
        return $user;
    });
    Route::post('transaksi/store','TransaksiController@storeTransaksi');
    Route::get('downloadexcel/laporantahunan','TransaksiController@downloadLaporan');
    
    Route::get('downloadexcel/laporantahunanbank','TransaksiBankController@downloadExcelBank');
    Route::get('downloadexcel/laporantahunanumum','TransaksiUmumController@downloadExcelUmum');
    Route::get('downloadexcel/laporantahunanproyek','TransaksiProyekController@downloadExcelProyek');
    Route::get('downloadexcel/laporantahunanunit','TransaksiUnitController@downloadExcelUnit');
    Route::get('transaksi/rekap/fetchrekap/{tahun}','TransaksiController@downloadRekap');
    
    Route::post('pengajuandanaproyek/store','TransaksiProyekController@storePengajuanDana');
    Route::post('pengajuandanaunit/store','TransaksiUnitController@storePengajuanDana');
    Route::post('transaksiumum/storekategori','TransaksiUmumController@storeKategori');
    Route::patch('transaksiumum/editkategori','TransaksiUmumController@storeKategori');
    Route::patch('transaksiumum/deletekategori','TransaksiUmumController@deleteKategori');
    Route::patch('transaksi/delete','TransaksiController@deleteTransaksi');
    Route::patch('transaksi/edit','TransaksiController@forceEditTransaksi');
    Route::post('transaksibank/storesimpanan','TransaksiBankController@storeSimpanan');
    Route::patch('transaksibank/editsimpanan','TransaksiBankController@storeSimpanan');
    Route::patch('transaksibank/deletesimpanan','TransaksiBankController@deleteSimpanan');
    Route::patch('settings/profile', 'Settings\ProfileController@update');
    Route::patch('settings/password', 'Settings\PasswordController@update');
    
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
    Route::get('transaksiedit/showedittrans','TransaksiController@fetchEditedData');
    Route::get('transaksi/fetch','TransaksiController@getAllTransaksi');
    Route::get('transaksi/rekap/fetch/{bulan}/{tahun}','TransaksiController@fetchRekapBulanan');
    Route::get('generateexcel/laporantahunan','TransaksiController@generateLaporan');
    Route::get('transaksibank/fetch','TransaksiBankController@getAllTransaksiBank');
    Route::get('transaksiumum/fetch','TransaksiUmumController@getAllTransaksiUmum');
    Route::get('transaksiproyek/fetch','TransaksiProyekController@getAllTransaksiProyek');
    Route::get('transaksiunit/fetch','TransaksiUnitController@getAllTransaksiUnit');
    Route::get('pegawai/getallpegawailist','PegawaiController@getAllPegawaiList');
    Route::get('transaksiumum/getallkategorilist','TransaksiUmumController@getAllKategoriList');
    Route::get('transaksiunit/getallunitlist','TransaksiUnitController@getAllUnitList');
    Route::get('transaksibank/getallsimpananlist','TransaksiBankController@getAllSimpananList');
    Route::get('transaksibank/getallsimpananlistnoncash','TransaksiBankController@getAllSimpananListNonCash');
    Route::get('transaksiproyek/getallproyeklist','TransaksiProyekController@getAllProyekList');
	Route::get('transaksiproyek/getallpeneliti','TransaksiProyekController@getAllPeneliti');
    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
    Route::get('pengajuandanaproyek/fetch','TransaksiProyekController@getAllPengajuanDanaProyek');
    Route::get('pengajuandanaunit/fetch','TransaksiUnitController@getAllPengajuanDanaUnit');
    Route::get('transaksi/rekap/generaterekap/{tahun}','TransaksiController@generateRekap');
    Route::get('generateexcel/laporantahunanbank','TransaksiBankController@generateExcelBank');
    Route::get('generateexcel/laporantahunanunit','TransaksiUnitController@generateExcelUnit');
    Route::get('generateexcel/laporantahunanumum','TransaksiUmumController@generateExcelUmum');
    Route::get('generateexcel/laporantahunanproyek','TransaksiProyekController@generateExcelProyek');
	Route::get('historisimpanan/fetch','DashboardController@getHistoriSimpanan');
});


