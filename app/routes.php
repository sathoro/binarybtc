<?php

date_default_timezone_set('UTC');

App::missing(function($exception) {
    return View::make('404');
});

Route::filter('custom_csrf', function() {
	$token = Request::ajax() ? Request::header('X-CSRF-Token') : Input::get('_token');
	if (Session::token() != $token) {
		throw new Illuminate\Session\TokenMismatchException;
	}
});

Route::filter('internal_only', function() {
	if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
		die(var_dump($_SERVER['REMOTE_ADDR']));
	}
});

App::before(function($request){
	if ($request->getMethod() === 'POST') {
		Route::callRouteFilter('custom_csrf', array(), '', $request);
	}
});

$btc_price = Bitcoin::toUSD();
View::share('btc_price', $btc_price);
$updated_at = strtotime(DB::table('price_hist')->orderBy('id', 'desc')->pluck('created_at'));
View::share('updated_at', $updated_at);

Bet::checkExpired();

Route::get('logout', function() {
	Auth::logout();
	return Redirect::to('/');
});

Route::get('/', function()
{
	$prices = DB::table('price_hist')->get();
	$bets = Bet::available()->paginate(4);
	$server_time = date('H:i:s');
	return View::make('hello', compact('server_time', 'bets', 'prices'));
});

Route::post('/contact/submit', function()
{
	$data = array(
		'from' => Input::get('email'),
		'body' => Input::get('message')
	);

	Mail::queue('emails.contact', $data, function($message)
	{
	    $message->to('connor@sphinx.io', 'Connor Smith')->subject('BinaryBTC Contact Form');
	});

	return array();
});

Route::get('/faq', function() 
{
	return View::make('faq');
});

Route::get('/latest_price', function() 
{
	if (!Request::ajax()) App::abort(404);
	$latest = DB::table('price_hist')->orderBy('id', 'desc')->first();
	return array('price' => number_format($latest->price, 4), 'time' => strtotime($latest->created_at), 'date' => $latest->created_at);
});

Route::get('/chart_data', function() {
	if (!Request::ajax()) App::abort(404);
	return DB::table('price_hist')->select('price as value', 'created_at as date')->remember(.5)->get();
});

Route::get('/update_price', array('before' => 'internal_only', function() {
	if (!Cache::has('last_updated_at') || (time() - Cache::get('last_updated_at') >= 50)) {
		Cache::forever('last_updated_at', time());
		Bitcoin::getLatestPrice();
	}
}));

Route::controller('users', 'UsersController');
Route::controller('withdraw', 'WithdrawController');
Route::controller('blockchain', 'BlockchainController');
Route::controller('bet', 'BetController');