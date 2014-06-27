<?php

class Withdraw extends Eloquent {

	public function user()
	{
		$this->belongsTo('User');
	}
}