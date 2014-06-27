@extends('layout')

@section('content')
	<div class="container" id="main">
		<div class="row">
			<div class="col-md-6">
				<h2>Make Deposit</h2>

				<ul>
					<li>Deposit your BTC to the address below and after <strong>three confirmations</strong> it will be credited to your account balance.
					<li>Minimum deposit of <strong>{{ Bitcoin::toBTC(Config::get('bitcoin.minimum_deposit')) }} BTC</strong>
					<li>There is no fee to deposit.
					<li>You will receive an email when your funds are deposited.
					<li>This address is unique for you, and will not change.
				</ul>

				<div>
					<div class="qr-address text-center"></div>
					<h3 style="margin:0 auto 20px 0" class="text-address make-qr-code">{{ Auth::user()->deposit_btc_address }}</h3>
				</div>
		</div>
	</div>
@stop