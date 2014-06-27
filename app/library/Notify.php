<?php

class Notify {

	public static function alert($msg)
	{
		Mail::queue('emails.alert', array('msg' => $msg, 'debug' => self::getDebugInfo()), function($message)
		{
			$message->to(Config::get('app.email'))->subject('Alert!');
		});
	}

	public static function getDebugInfo()
	{
		$output = "";
		$output .= print_r($_SERVER, true);
		$output .= print_r($_POST, true);
		$output .= print_r($_GET, true);
		return $output;
	}
}