<?php

namespace App\Http\Controllers;

use App\Vmuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTAuth;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class VmuserController extends Controller
{

  public function register(Request $request)
  {
    $rules = [
      'name' => 'required|max:220',
      'email' => 'required|email|unique:vmusers|max:200',
      'password' => 'required|regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[0-9])(?=\S*[\W])\S*$/',
    ];



      $this->validate($request, $rules);
      $hasher = app()->make('hash');
      $name = $request->input('name');
      $email = $request->input('email');
      $password = $hasher->make($request->input('password'));

      $save = Vmuser::create([
        'name'=> $name,
        'email'=> $email,
        'password'=> $password,
        'created_by'=>0,
        'role' => 1,
      ]);

      $login = Vmuser::where('email', $email)->first();
      $updateCB= Vmuser::find($login->id);
      $updateCB->update(['created_by'=>$login->id]);
      return response()->json([
        'data' => 'User Registered Successfully'
      ], 201);

    // } catch(Exception $e) {
    //   return response()->json([
    //     'error' => "Please Enter Correct Details"
    //   ], 401);
    // }
  }



  public function forget(Request $request)
  {

    $rules = [
      'email' => 'required|email',
      'password' => 'required|regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[0-9])(?=\S*[\W])\S*$/',
    ];


    try {

      $this->validate($request, $rules);
      $email    = $request->input('email');
      $password = $request->input('password');
      $hasher = app()->make('hash');
      $password = $hasher->make($request->input('password'));
      $login = Vmuser::where('email', $email)->first();

      if(empty($login))
      {
        return response()->json([
          'error' => "Enter Correct Email"
        ], 401);
      }
      $forget_user= Vmuser::find($login->id);
      $forget_user->update(['password'=>$password]); 
      return response()->json([
        'data' => 'Password Updated Successfully'
      ], 201);
    } catch(Exception $e) {
      return response()->json([
        'error' => "Please Enter Correct Details"
      ], 401);
    }

    $this->validate($request, $rules);
    $email    = $request->input('email');
    $password = $request->input('password');
    $hasher = app()->make('hash');
    $password = $hasher->make($request->input('password'));
    $login = Vmuser::where('email', $email)->first();

    if(empty($login))
    {
      return response('Enter correct email', 401);
    }
    $forget_user= Vmuser::find($login->id);
    $forget_user->update(['password'=>$password]); 
    return response('Password has been set Successfully');   
  }


  public function deleteuser( Request $request)
  {

    $rules = [
      'id' => 'integer',
    ];
    $id  = $request->input('id');
    try{
      $this->validate($request, $rules);
      $user=$request->auth ;
      if(empty($user) || ($user->role===0))
      {
        return response()->json([
          'error' => "Unauthorized Access: Please Login With Admin Credentials"
        ], 401);
      }

      $delete_user= Vmuser::find($id);
      if(!empty($delete_user) && $delete_user->deleted_by===null && $delete_user->role===0)
      {
        $delete_user->update(['deleted_by'=>$user->id]);

        return response()->json([
          'table' => "User Deleted Successfully"
        ], 201);
      }
      else
      {
        return response()->json([
          'error' => "No user exist with given id or user already deleted or cannot delete admin user"
        ], 401);
      } 
     }
      catch(Exception $e) {
        return response()->json([
          'error' => "Please Enter Correct Details"
        ], 401);
      }  
    }



    public function updateuser( Request $request)
    {

      $rules = [
        'id' => 'integer',
      ];
      $id    = $request->input('id');
      try{
      $this->validate($request, $rules);
      $user=$request->auth ;
      if(empty($user) || $user->role===0)
      {
        return response()->json([
          'error' => "Unauthorized Access: Please Login With Admin Credentials"
        ], 401);
      }

      if($user->role===1)
      {
        $update_user= Vmuser::find($id);
        if(!empty($update_user)&& $update_user->deleted_by===null && $update_user->role===0)
        {
          $update_user->update(['role'=>'1']);
          $update_user->update(['updated_by'=>$user->id]);
          return response()->json([
            'table' => "User Updated Successfully"
          ], 201);
        }
        else
        {
          return response()->json([
            'error' => "No user exist with given id or user already deleted or cannot update admin user"
          ], 401);
        } 
      }
    }catch(Exception $e) {
        return response()->json([
          'error' => "Please Enter Correct Details"
        ], 401);
      } 

    }

    public function createUser(Request $request)
    {
      $curr_user = $request->auth;
      $rules = [
        'name' => 'required|max:1|min:0',
        'name' => 'required|max:220',
        'email' => 'required|email|unique:vmusers|max:200',
        'password' => 'required|regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[0-9])(?=\S*[\W])\S*$/',
      ];


      if($curr_user->role === 1){
        try {

          $this->validate($request, $rules);
          $hasher = app()->make('hash');
          $name = $request->input('name');
          $email = $request->input('email');
          $password = $hasher->make($request->input('password'));
          $role = $request->input('role');

          $save = Vmuser::create([
            'name'=> $name,
            'email'=> $email,
            'password'=> $password,
            'created_by'=> $curr_user->id,
            'role' => $role,
          ]);

          return response()->json([
            'data' => 'User Registered Successfully'
          ], 201);

        } catch(Exception $e) {
          return response()->json([
            'error' => "Please Enter Correct Details"
          ], 401);
        }
      }
      else
      {
        return response()->json([
          'error' => "Unauthorized Access. Please Login with Admin Credentials"
        ], 401);
      }
    }


    public function userlist(Request $request)
    {
    // $curr_user=$request->auth ;

      $rules = [
        'name' => 'string|nullable',
        'email' => 'string|nullable',
        'role' => 'integer|nullable|min:0|max:1',
        'created_by' => 'string|nullable',
      ];


    //documentation of


      $email    = $request->input('email');
      $name = $request->input('name');
      $role = $request->input('role');
      $created_by = $request->input('created_by');

      $this->validate($request, $rules);

    // $new = array();
    // $name2id = DB::table('vmusers')->select('id')->where('name', 'LIKE' , '%'.$created_by.'%')->get()->toArray();
    // echo count($name2id);
    // foreach ($name2id as $key) {

      if(1)
      {

        $result = DB::table('vmusers')
        ->leftJoin('vmusers as new', 'vmusers.created_by', '=', 'new.id')
        ->leftJoin('vmusers as vm', 'vmusers.deleted_by', '=', 'vm.id')
        ->select('vmusers.id','vmusers.name', 'vmusers.email', 'new.name as created_by', 'vmusers.role', 'vm.name as deleted_by')
        ->where('new.name', 'LIKE' , '%'.$created_by.'%')    
        ->where('vmusers.name', 'LIKE', '%'.$name.'%')
      ->where('vmusers.email', 'LIKE', $email) //data type 
      ->where('vmusers.role', 'LIKE', $role)
      ->paginate(5);

      //echo $curr_user->role;
      return response()->json([
        'table' => $result
      ], 201);
    }
    

  }
}