@extends('layout')

@section('content')
	<div class="container" id="main">
		<div class="row">
			<div class="col-md-4">
				<h2>Withdraw Funds</h2>

				<ul>
					<li>Minimum Withdrawal: <strong>{{ Bitcoin::toBTC(Config::get('bitcoin.minimum_withdrawal')) }} BTC</strong>
					<li>Withdrawal Fee: <strong>{{ Bitcoin::toBTC(Config::get('bitcoin.withdrawal_fee')) }} BTC</strong>
				</ul>

				@if (Session::has('error'))
					<div class="errors">{{ Session::get('error') }}</div>
				@endif
				@if (Session::has('success'))
					<div class="success">{{ Session::get('success') }}</div>
				@endif
				{{ Form::open(array('url' => 'withdraw/withdraw', 'class' => 'withdraw-form')) }}
					<div class="form-group">
						<div data-input="address" class="errors"></div>
						{{ Form::text('address', Input::old('address'), array('class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'BTC Address')) }}
					</div>
					<div class="form-group">
						<div data-input="amount" class="errors"></div>
						{{ Form::text('amount', Input::old('amount'), array('class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'Amount')) }}
					</div>
					<button type="submit" class="btn btn-primary">Make Withdraw</button>
				{{ Form::close() }}
			</div>
		</div>
	</div>
@stop