<?php

use Carbon\Carbon;

class BetController extends BaseController {

	private $valid_types = array('above', 'under');

	public function postCreate()
	{
		if (!Auth::check())
		{
			return array('success' => false, 'errors' => 'You must be logged in to place any bets.');
		}

		$bet_amount = Bitcoin::toSatoshi(floatval(Input::get('bet_amount')));
		$cross_bet_amount = Bitcoin::toSatoshi(floatval(Input::get('cross_bet_amount')));

		if ($bet_amount > Auth::user()->getBalance())
		{
			return array('success' => false, 'errors' => array('bet_amount' => 'You don\'t have enough funds to place that bet.'));
		}

		if ($bet_amount < Config::get('bitcoin.minimum_bet'))
		{
			return array('success' => false, 'errors' => array('bet_amount' => 'Minimum bet amount is ' . Bitcoin::toBTC(Config::get('bitcoin.minimum_bet')) . ' BTC'));
		}

		if ($cross_bet_amount < Config::get('bitcoin.minimum_bet'))
		{
			return array('success' => false, 'errors' => array('cross_bet_amount' => 'Minimum bet amount is ' . Bitcoin::toBTC(Config::get('bitcoin.minimum_bet')) . ' BTC'));
		}

		$type = Input::get('type');
		if (!in_array($type, $this->valid_types))
		{
			return array('success' => false, 'errors' => array('type' => 'Invalid.'));
		}

		$expiration = intval(Input::get('expiration'));
		if ($expiration < Config::get('bitcoin.minimum_bet_expiration') || 
			$expiration > Config::get('bitcoin.maximum_bet_expiration'))
		{
			return array('success' => false, 'errors' => array('expiration' => 'Invalid.'));
		}

		$bet = new Bet();
		$bet->type = $type;
		$bet->created_by_user_id = Auth::user()->id;
		$bet->bet_amount = $bet_amount;
		$bet->cross_bet_amount = $cross_bet_amount;
		$bet->target_price = floatval(Input::get('target_price'));
		$bet->price_when_created = Bitcoin::toUSD();

		$expiresAt = Carbon::now()->addSeconds($expiration);
		$bet->expires_at = $expiresAt;

		if ($bet->save())
		{
			Auth::user()->subtractFromBalance($bet_amount);
			return array('success' => true);
		}
		else {
			return array('success' => false, 'errors' => 'Bet couldn\'t be created.');
		}
	}

	public function postCrossbet()
	{
		if (!Auth::check())
		{
			return array('success' => false, 'errors' => 'You must be logged in to place any bets.');
		}

		$bet = Bet::find(Input::get('bet_id'));

		if (is_null($bet)) 
		{
			return array('success' => false, 'errors' => 'This is not a valid bet!');
		}
		if (time() > strtotime($bet->expires_at))
		{
			return array('success' => false, 'errors' => 'This bet has expired!');
		}
		if (!is_null($bet->accepted_by_user_id))
		{
			return array('success' => false, 'errors' => 'Somebody else just placed this bet!');
		}
		if ($bet->deleted != 0)
		{
			return array('success' => false, 'errors' => 'This bet has been deleted!');
		}
		if ($bet->cross_bet_amount > Auth::user()->getBalance()) 
		{
			return array('success' => false, 'errors' => 'You don\'t have enough funds to place this bet!');
		}

		$bet->accepted_by_user_id = Auth::user()->id;
		$bet->accepted_at = time();

		if ($bet->save())
		{
			Auth::user()->subtractFromBalance($bet->cross_bet_amount);
			return array('success' => true);
		}
		else {
			return array('success' => false, 'errors' => 'Bet couldn\'t be placed, please contact support if this persists.');
		}
	}

	public function postDelete()
	{
		if (!Input::has('bet_id'))
		{
			return array('success' => false);
		}

		$bet_id = Input::get('bet_id');
		$bet = Bet::where('id', $bet_id)->where('created_by_user_id', Auth::user()->id)->first();
		if (is_null($bet))
		{
			return array('success' => false);
		}
		else {
			if ($bet->canBeDeleted()) {
				$bet->deleted = 1;
				$bet->price_when_deleted = Bitcoin::toUSD();
				$bet->deleted_at = time();
				$bet->save();
				Auth::user()->addToBalance($bet->bet_amount);
				return array('success' => true);
			}
			else {
				return array('success' => false);
			}
		}
	}
}