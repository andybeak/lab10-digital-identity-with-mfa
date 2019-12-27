<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Cache;
use Illuminate\Http\Request;
use Auth;
use App\Http\Requests\ValidateSecretRequest;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function authenticated(Request $request, $user)
    {
        if (!is_null($user->mfa_secret)) {
            Auth::logout();
            $request->session()->put('2fa:user:id', $user->id);
            return redirect('2fa/validate');
        }

        return redirect()->intended($this->redirectTo);
    }

    public function getValidateToken()
    {
        if (session('2fa:user:id')) {
            return view('mfa/validate');
        }

        return redirect('login');
    }

    public function postValidateToken(ValidateSecretRequest $request)
    {
        $userId = $request->session()->pull('2fa:user:id', null);
        $key = $userId . ':' . $request->totp;
        Cache::add($key, true, 4);

        Auth::loginUsingId($userId);
        return redirect()->intended($this->redirectTo);
    }
}
