<?php

use Illuminate\Support\Facades\Mail;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});




$router->post('api/auth/login', ['uses' => 'AuthController@userAuthenticate']);
$router->post('api/register',[ 'uses' => 'VmuserController@register']);


  $router->get('/email', ['uses' => 'mailController@taskmail']);
  $router->get('/resetpassword', ['uses' => 'mailController@password_reset_mail']);



$router->group(['prefix' => 'api', 'middleware' => 'jwt.auth'], function () use ($router) {

  $router->post('/delete', ['uses' => 'VmuserController@deleteuser']);
  $router->post('/update', ['uses' => 'VmuserController@updateuser']);
  $router->get('/userlist', ['uses' => 'VmuserController@userlist']);
  $router->post('/createuser', ['uses' => 'VmuserController@createuser']);
  $router->post('/newtask', ['uses' => 'taskController@newTask']);
  $router->post('/updatetask', ['uses' => 'taskController@creatorUpdate']);
  $router->post('/updatestatus', ['uses' => 'taskController@statusUpdate']);
  $router->post('/deletetask', ['uses' => 'taskController@delete']);
  $router->get('/tasklist', ['uses' => 'taskController@tasklisting']);
  $router->get('/updatelist', ['uses' => 'taskController@updatelisting']);
  $router->get('/statuslist', ['uses' => 'taskController@statuslisting']);
  $router->get('/datapie', ['uses' => 'hichartsController@taskpie']);


});

$router->group(['prefix' => 'api', 'middleware' => 'reset'], function () use ($router) {

  $router->post('/forget', 'VmuserController@forget');

});
