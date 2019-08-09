<?php
namespace App\Http\Controllers;
use Validator;
use Event;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller;
use App\Events\LoginEvent;
use App\Vmuser;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller 
{
    //private $request;
    protected function jwt(Vmuser $user) {
     $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 3600 ,// Expiration time
            'role' => $user->role,
            'name' => $user->name,
        ];

        

  // As you can see we are passing `JWT_SECRET` as the second parameter that will 
  // be used to decode the token in the future.
        
        return JWT::encode($payload, env('JWT_SECRET'));    //Header?
    }
    // public function __construct(Request $request) {
    //     $this->request = $request;
    // }
    
    public function userAuthenticate(Request $request) {
        $rules = [
          'email' => 'required',
          'password' => 'required'
      ];

      $this->validate($request, $rules);
      $email    = $request->input('email');
      $login = Vmuser::where('email', $email)->first();
      

      if ((empty($login))) {
        
        return response()->json([
            'error' => 'User does not exist.'
        ], 400);
    }

    
        // Verify the password and generate the token
 
    if(Hash::check($request->input('password'), $login->password)){
      Event::dispatch(new LoginEvent($login));
        return response()->json([
            'status'  => 200,
            'message' => 'Login Successful',
                'data'    => ['token' => $this->jwt($login)] ,
            ], 200)
    ->withCookie( new Cookie('token', $this->jwt($login),time()+ (60*60), '/', null, false, false))
    ->withCookie( new Cookie('Name', $login->name,time()+ (60*60), '/', null, false, false));
    }
    return response()->json([
        'error' => 'Login details provided does not exit.'
    ], 400);
} 
}