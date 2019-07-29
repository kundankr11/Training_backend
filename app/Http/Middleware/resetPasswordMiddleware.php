<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use App\Vmuser;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
class resetPasswordMiddleware
{ 

public function handle($request, Closure $next, $guard = null)
{
        $token = $request->input('token'); // get token from request header
        
        if(!$token) {

          // Unauthorized response if token not there
            return response()->json([
                'status' => 401,
                'error' => 'Token required'
            ], 401);
        }

        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {

            return response()->json([
                'error' => 'Provided token is expired.'
            ], 401);

        } catch(Exception $e) {
            return response()->json([
                'error' => $token

            ], 401);
        }

        $user = Vmuser::where($credentials->sub);

        // Now let's put the user in the request class so that you can grab it from there
        if(!empty($user)){

            $request->auth = $user;

        }else{

            return response()->json([
                'error' => 'Provided token is invalid.'
            ], 401);
        }
        
        return $next($request);
    }
}