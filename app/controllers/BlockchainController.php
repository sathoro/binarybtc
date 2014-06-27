<?php

class BlockchainController extends BaseController {

	public function getCallback()
	{
		if (Input::get('secret') != Config::get('bitcoin.secret'))
		{
			Notify::alert('Invalid secret provided: ' . Input::get('secret', ''));
			App::abort(404);
		}

		$destination_address = Input::get('destination_address');

		if ($destination_address != Config::get('bitcoin.wallet_address'))
		{
			Notify::alert('Invalid destination address: ' . $destination_address);
			App::abort(404);
		}

		$input_address = Input::get('input_address');

		$user = User::where('deposit_btc_address', $input_address)->first();
		if (count($user) != 1)
		{
			Notify::alert('Invalid user deposit address: ' . $input_address);
			App::abort(404);
		}

		$transaction_hash = Input::get('transaction_hash');
		$input_transaction_hash = Input::get('input_transaction_hash');
		$value_in_satoshi = Input::get('value');

		$deposit = new ExternalDeposit();
		$deposit->amount = $value_in_satoshi;
		$deposit->user_id = $user->id;
		$deposit->confirmations = Input::get('confirmations');
		$deposit->input_address = $input_address;
		$deposit->destination_address = $destination_address;
		$deposit->transaction_hash = $transaction_hash;
		$deposit->input_transaction_hash = $input_transaction_hash;

		if ($deposit->save()) {
			if (intval(Input::get('confirmations')) >= Config::get('bitcoin.num_confirmations_deposit'))
			{
				Notify::alert('Money deposited!');
				$user->addToBalance($value_in_satoshi);
				echo "*ok*";
			}
		}
		else {
			Notify::alert('Unable to make external deposit!');
			App::abort(404);
		}
	}

	public function getBalance() 
	{
		$api_url = 'https://blockchain.info/merchant/' . Config::get('bitcoin.guid') . '/balance';
		$api_url .= '?password=' . urlencode(Config::get('bitcoin.password'));
		$api_url .= '&address=' . urlencode(Config::get('bitcoin.wallet_address'));
		$api_url .= '&confirmations=0';

		$response = file_get_contents($api_url);
		$object = json_decode($response);
		return array('usd' => Bitcoin::toUSD($object->balance), 'btc' => Bitcoin::toBTC($object->balance), 'satoshi' => $object->balance);
	}
}