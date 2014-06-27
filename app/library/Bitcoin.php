<?php

class Bitcoin {

	public static function toSatoshi($btc)
	{
		return $btc * 100000000;
	}

	public static function toBTC($satoshi)
	{
		return $satoshi / 100000000;
	}

	public static function toUSD($amount = 100000000, $satoshi = true)
	{
		$USDPerBTC = Cache::get('bitcoin_price');
		
		return $satoshi ? $USDPerBTC * Bitcoin::toBTC($amount) : $USDPerBTC * $amount;
	}

	public static function getLatestPrice()
	{
		ini_set('default_socket_timeout', 10);
		$opts = array('http' =>
			array(
				'method'  => 'GET',
				'timeout' => 10
			)
		);

		$context  = stream_context_create($opts);
		$btc_e = json_decode(@file_get_contents('https://btc-e.com/api/2/btc_usd/ticker', false, $context), true);
		if (is_null($btc_e))
		{
			return false;
		}

		$btc_e_volume = floatval($btc_e['ticker']['vol_cur']);

		$bitstamp = json_decode(@file_get_contents('https://www.bitstamp.net/api/ticker', false, $context), true);
		if (is_null($bitstamp))
		{
			return false;
		}

		$bitstamp_volume = floatval($bitstamp['volume']);

		$total_volume = $btc_e_volume + $bitstamp_volume;
		$weighted_price = ($btc_e_volume / $total_volume) * $btc_e['ticker']['last'] +
						  ($bitstamp_volume / $total_volume) * $bitstamp['last'];

		DB::table('price_hist')->insert(array('price' => $weighted_price, 'created_at' => date('Y-m-d H:i:s')));

		Cache::forever('bitcoin_price', $weighted_price);
	}

	public static function createAddress()
	{
		$callback_url = url(Config::get('bitcoin.callback'));
		$callback_url .= '?secret=' . Config::get('bitcoin.secret');

		$api_url = 'https://blockchain.info/api/receive';
		$api_url .= '?method=create&address=' . Config::get('bitcoin.wallet_address') . '&callback=' . urlencode($callback_url);

		$response = file_get_contents($api_url);

		$object = json_decode($response);
		return $object->input_address;
	}
	
	public static function checkAddress($address)
	{
	    $origbase58 = $address;
	    $dec = "0";

	    for ($i = 0; $i < strlen($address); $i++)
	    {
	        $dec = bcadd(bcmul($dec,"58",0),strpos("123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz",substr($address,$i,1)),0);
	    }

	    $address = "";

	    while (bccomp($dec,0) == 1)
	    {
	        $dv = bcdiv($dec,"16",0);
	        $rem = (integer)bcmod($dec,"16");
	        $dec = $dv;
	        $address = $address.substr("0123456789ABCDEF",$rem,1);
	    }

	    $address = strrev($address);

	    for ($i = 0; $i < strlen($origbase58) && substr($origbase58,$i,1) == "1"; $i++)
	    {
	        $address = "00".$address;
	    }

	    if (strlen($address)%2 != 0)
	    {
	        $address = "0".$address;
	    }

	    if (strlen($address) != 50)
	    {
	        return false;
	    }

	    if (hexdec(substr($address,0,2)) > 0)
	    {
	        return false;
	    }

	    return substr(strtoupper(hash("sha256",hash("sha256",pack("H*",substr($address,0,strlen($address)-8)),true))),0,8) == substr($address,strlen($address)-8);
	}
}