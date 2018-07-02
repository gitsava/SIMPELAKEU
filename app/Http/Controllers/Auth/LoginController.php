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
        Log::info($userRoleSI->isEmpty());
        //Log::info($userRoleSI);
        if(!$userRoleSI->isEmpty()){
            return [
                'status'=> true,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expiration - time(),
            ];
        }else {
            $this->guard()->logout();
            return $this->accountNotRegisteredResponse($request);
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
        Log::info('logged out');
        Log::info($request->user());
        $this->guard()->logout();
    }
}
