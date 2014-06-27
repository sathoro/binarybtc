<?php

class Payout {

	public function user()
	{
		$this->belongsTo('User');
	}
}