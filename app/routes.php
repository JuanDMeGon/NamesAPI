<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/*
|
| $api variable have the definition of all the routes
| The definition is inside a function which will be passes 
| as argument to the route:group
|
*/

$api = function()
{
	Route::get('/', 'ApiController@index');
	Route::get('/names/custom', array('uses' => 'ApiController@customQuery'));
	Route::get('/test', array('uses' => 'ApiController@test'));
	Route::get('/test2', array('uses' => 'ApiController@test2'));
	Route::get('/tojson', array('uses' => 'ApiController@tojson'));
	Route::get('/names/{name}', array('uses' => 'ApiController@getByName'))->where(array('name' => '^([A-Za-z]{2,})( [A-Za-z]{2,}){0,1}$'));

	Route::pattern('badname', '^!([A-Za-z]{2,})!([A-Za-z]{2,}){0,1}$');
	Route::get('/names/{badname}', function()
	{
		return Response::view('bad-name', array(), 400);
	});


	Route::pattern('all', '.*');

	Route::any('/{all}', function()
	{
		return Response::view('bad-request', array(), 400);
	});
};

/*
|--------------------------------------------------------------------------
| Application Routes groups
|--------------------------------------------------------------------------
|
| Here we define the respective groups by domain
| and his own routes function respectively
|
*/
Route::group(array('domain' => 'api.localjuandmegon.com'), $api);
Route::group(array('domain' => 'api.juandmegon.com'), $api);