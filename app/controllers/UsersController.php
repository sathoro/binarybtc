<?php

class UsersController extends BaseController {

	public function getBets()
	{
		if (!Auth::check()) App::abort(404);
		$bets = Auth::user()->bets()->lists('id');
		$cross_bets = Auth::user()->crossBets()->lists('id');
		if (count($bets) || count($cross_bets)) {
			$bets = Bet::whereIn('id', array_merge($bets, $cross_bets))->orderBy('expires_at', 'asc')->get();
		}
		else {
			$bets = array();
		}
		return View::make('users.bets', compact('bets'));
	}

	public function getDeposit()
	{
		if (!Auth::check()) App::abort(404);
		return View::make('users.deposit');
	}

	public function getWithdraw()
	{
		if (!Auth::check()) App::abort(404);
		return View::make('users.withdraw');
	}

	public function postLogin()
	{
		$userdata = array(
	        'email'      	=> Input::get('email'),
	        'password'      => Input::get('password')
	    );

	    if (Auth::attempt($userdata))
	    {
	        return array('success' => true);
	    }
	    else
	    {
	    	return array('success' => false);
	    }
	}

	public function postRegister()
	{
		$rules = array(
	    	'email' => 'required|email|unique:users',
	    	'password' => 'required|confirmed'
	    );

	    $validator = Validator::make(Input::all(), $rules);

	    if ($validator->fails())
	    {
	    	return array('success' => false, 'errors' => $validator->messages()->toJson());
	    }
	    else {
	    	$user = new User();
	    	$user->password = Hash::make(Input::get('password'));
	    	$user->email = Input::get('email');
	    	$user->deposit_btc_address = Bitcoin::createAddress();
	    	$user->save();
	    	
	    	Auth::loginUsingId($user->id);
	    	return array('success' => true);
	    }
	}

	// public function postUpdate()
	// { 
	// 	$user = Auth::user();
	// 	$input = Input::all();

	// 	Validator::extend('password_check', function($attribute, $value, $parameters)
	// 	{
	// 		return Hash::check($value, $parameters[0]);
	// 	});

	// 	$rules = array(
	//     	'name' => 'required|min:3|max:100', 
	//     	'email' => 'required|email|unique:users,email,' . $user->id, 
	//     	'password' => 'required|password_check:' . $user->password
	//     );

	//     $validator = Validator::make($input, $rules);

	//     if ($validator->fails())
	//     {
	//         return Redirect::to('user/profile/edit')->withInput()->withErrors($validator);
	//     }
	//     else {
	//     	die();
	//     	$user->fill(array(
	//     		'slug' => $user->slugify($input['name']),
 //    			'website' => $input['website'],
 //    			'name' => $input['name'],
 //    			'email' => $input['email']
	//     	));
	//     	$user->save();
	//     	return Redirect::to('user/profile/edit');
	//     }
	//}
}