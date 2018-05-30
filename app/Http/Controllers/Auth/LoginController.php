<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\tblUserSI;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $token = $this->guard()->attempt($this->credentials($request));
        Log::info($this->credentials($request));
        if ($token) {
            $this->guard()->setToken($token);

            return true;
        }

        return false;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        \DB::connection()->enableQueryLog();
        $this->clearLoginAttempts($request);

        $token = (string) $this->guard()->getToken();
        $expiration = $this->guard()->getPayload()->get('exp');
        $users = $request->user();
        $where = ['id_user'=>$users->id,'id_si'=>3];
        $userRoleSI = tblUserSI::where($where)->get();
        $queries = \DB::getQueryLog();
        Log::info(isset($userRoleSI));
        //Log::info($userRoleSI);
        if(isset($userRoleSI)==1){
            return [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expiration - time(),
            ];
        }else {
            $this->guard()->logout();
            return ['error'=>'Login Failed! Akun ini tidak terdaftar di Sistem Informasi Keuangan'];
        }
        
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
    }
}
