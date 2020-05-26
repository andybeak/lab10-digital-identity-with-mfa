<?php

namespace App\Http\Controllers;

use Google2FA;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function enableTwoFactor(Request $request)
    {
        $secret = Google2FA::generateSecretKey();

        //get user
        $user = $request->user();

        //encrypt and then save secret
        $user->mfa_secret = encrypt($secret);
        $user->save();

        //generate image for QR barcode
        $imageDataUri = Google2FA::getQRCodeInline(
            $request->getHttpHost(),
            $user->email,
            $secret,
            200
        );

        return view('mfa/enableTwoFactor', [
            'image' => $imageDataUri,
            'secret' => $secret
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();

        //make secret column blank
        $user->mfa_secret = null;
        $user->save();

        return view('mfa/disableTwoFactor');
    }
}
