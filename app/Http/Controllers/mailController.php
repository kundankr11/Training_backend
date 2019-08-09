<?php

namespace App\Http\Controllers;

use App\Mail\tasking;
use App\Mail\passwordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Vmuser;
use Validator;
use Firebase\JWT\JWT;

class mailController extends Controller
{
    /**
     * Ship the given order.
     *
     * @param  Request  $request
     * @param  int  $orderId
     * @return Response
     */
    public function createToken(Vmuser $user)
    {

        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->email, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 3600 ,// Expiration time
        ];
        return JWT::encode($payload, env('JWT_SECRET'));
    }

    public function taskmail()
    {
        Mail::to('en11kundan@gmail.com')
        ->queue(new tasking());
    }

    public function password_reset_mail(Request $request)
    {
        $rules = [
            'email' => 'required|email',
        ];
        $this->validate($request, $rules);
        $email = $request->input('email');
        $user = Vmuser::where('email', $email)->first();
        if ((empty($user))) {

            return response()->json([
                'error' => 'User does not exist.'
            ], 400);}

            
            $token = $this->createToken($user);
                            Mail::to($email)
                            ->send( new passwordReset($token));
        }
    }