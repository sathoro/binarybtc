<?php

class Bet extends Eloquent {

    private static $_time_formats = array(
    	// array(1, 'second', 1),           
        array(60, 'minute', 60),
        array(3600, 'hour', 3600),
        array(86400, 'day', 86400),
        array(604800, 'week', 604800),
    );

	public function user()
	{
		$this->belongsTo('User', 'created_by_user_id');
	}

    public function scopeAvailable($query)
    {
        $user_id = Auth::check() ? Auth::user()->id : -1;
        return $query->where('expired', 0)->whereNull('accepted_by_user_id')->where('deleted', 0)->whereNotIn('created_by_user_id', array($user_id));
    }

    public function handleWinner() 
    {
        if ($this->winner_paid) return false;

        $btc_price = Bitcoin::toUSD();

        if ($this->type == 'above') {
            $winner = ($btc_price > $this->target_price) ? $this->created_by_user_id : $this->accepted_by_user_id;
        }
        else if ($this->type == 'under') {
            $winner = ($btc_price < $this->target_price) ? $this->created_by_user_id : $this->accepted_by_user_id;
        }

        $this->winner_user_id = $winner;
        $winnings = (1 - Config::get('bitcoin.winnings_fee')) * (intval($this->bet_amount) + intval($this->cross_bet_amount));
        $winner_model = User::where('id', $winner)->first();
        $winner_model->addToBalance($winnings);
        Notify::alert("User $winner paid " . Bitcoin::toBTC($winnings) . " BTC");
        $this->winner_paid = 1;
        $this->save();
        return true;
    }

    public static function checkExpired() 
    {
        $expired = Bet::available()->where('expires_at', '<=', date('Y-m-d H:i:s'))->whereNull('accepted_by_user_id')->lists('id');
        if (count($expired) > 0) Bet::whereIn('id', $expired)->update(array('expired' => 1));

        $finished = Bet::where('expires_at', '<=', date('Y-m-d H:i:s'))->whereNotNull('accepted_by_user_id')->where('winner_paid', 0)->get();
        foreach($finished as $bet)
        {
            $bet->handleWinner();
        }
    }

    public function delete()
    {
        $this->update(array('deleted' => 1, 'price_when_deleted' => $btc_price, 'deleted_at' => time()));
    }

    public function getCanBeDeletedAttribute() {
        return $this->canBeDeleted();
    }

    public function canBeDeleted() {
        return !$this->deleted && !$this->expired && is_null($this->accepted_by_user_id);
    }

    public function getIsOwnerAttribute() {
        return $this->created_by_user_id == Auth::user()->id;
    }

    public function getIsExpiredAttribute() {
        return strtotime($this->expires_at) - time() < 1;
    }

	public function getReadableExpirationAttribute() {
		$difference = strtotime($this->expires_at) - time();
        if ($difference > 1){
            $message = array();

            foreach (array_reverse(self::$_time_formats) as $key => $format) {
                if ($difference > $format[0]) {
                    $count = floor($difference / $format[2]);
                    $plural = ($count != 1) ? 's' : '';
                    $message[] = "$count {$format[1]}$plural";
                    $message[] = ', ';
                    $difference -= $format[0] * $count;
                }
            }

            if (count($message) >= 4) $message[count($message) - 3] = ' and ';
            return rtrim(implode('', $message), ', ');
        }
        else {
            return false;
        }
	}
}