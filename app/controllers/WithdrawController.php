<?php

class WithdrawController extends BaseController {

	public function postWithdraw()
	{
		$amount = Bitcoin::toSatoshi(floatval(Input::get('amount', 0)));
		$address = Input::get('address');

		if ($amount < Config::get('bitcoin.minimum_withdrawal'))
		{
			return Redirect::back()->withInput()->with('error', 'Amount is less than the minimum.');
		}
		else if ($amount > Auth::user()->getBalance())
		{
			return Redirect::back()->withInput()->with('error', 'You do not have the required funds.');
		}

		if (!Bitcoin::checkAddress($address))
		{
			return Redirect::back()->withInput()->with('error', 'Invalid bitcoin address.');
		}

		$api_url = 'https://blockchain.info/merchant/' . urlencode(Config::get('bitcoin.guid')) . '/payment';
		$api_url .= '?password=' . urlencode(Config::get('bitcoin.password'));
		$api_url .= '&to=' . urlencode($address);
		$withdraw_amount = $amount - Config::get('bitcoin.withdrawal_fee');
		$api_url .= '&amount=' . urlencode($withdraw_amount);

		$response = file_get_contents($api_url);
		$response = json_decode($response);

		if (!property_exists($response, 'tx_hash'))
		{
			return Redirect::back()->withInput()->with('error', $response->error);
		}
		else {
			Auth::user()->subtractFromBalance($amount);
		}

		$withdraw = new Withdraw();
		$withdraw->user_id = Auth::user()->id;
		$withdraw->amount = $amount;
		$withdraw->btc_address = $address;

		$withdraw->transaction_hash = $response->tx_hash;

		if (property_exists($response, 'notice')) {
			$withdraw->notice = $response->notice;
		}
		if (property_exists($response, 'message')) {
			$withdraw->message = $response->message;
		}

		if (!$withdraw->save())
		{
			Notify::alert('Withdraw couldn\'t be saved!');
		}

		return Redirect::back()->with('success', 'Withdraw processed.');
	}
}