@extends('layout')

@section('content')
	<div class="container" id="main">
		<div class="row">
			<h2>Your Bets</h2>
			@if (count($bets) > 0)
				<table class="table table-condensed">
					<thead>
						<tr>
							<th>Expires In</th>
							<th>Direction</th>
							<th>Price</th>
							<th>Bet Amount</th>
							<th>Cross Bet Amount</th>
							<th>Win / Loss</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($bets as $bet)
							<tr>
								<td>{{ !$bet->isExpired ? $bet->readableExpiration : "Expired" }}</td>
								<td>
									@if ($bet->isOwner)
										<span class="{{ $bet->type == "above" ? "text-green" : "text-red" }}">{{ ucfirst($bet->type) }}</span>
									@else
										<span class="{{ $bet->type == "under" ? "text-green" : "text-red" }}">
											@if ($bet->type = ($bet->type == "above" ? "under" : "above")) @endif
											{{ ucfirst($bet->type) }}
										</span>
									@endif
								</td>
								<td>{{ $bet->target_price }}</td>
								<td>{{ Bitcoin::toBTC($bet->bet_amount) }} BTC</td>
								<td>{{ Bitcoin::toBTC($bet->cross_bet_amount) }} BTC</td>
								<td>
									@if (!is_null($bet->winner_user_id))
										@if ($bet->winner_user_id == Auth::user()->id)
											<span class="text-green">{{ Bitcoin::toBTC($bet->cross_bet_amount) }} BTC</span>
										@else
											<span class="text-red">-{{ Bitcoin::toBTC($bet->bet_amount) }} BTC</span>
										@endif
									@else
										n/a
									@endif
								</td>
								<td>
									@if ($bet->isExpired)
										@if ($bet->winner_user_id == Auth::user()->id)
											<span class="text-green">Won!</span>
										@else
											<span class="text-red">Lost</span>
										@endif
									@else
										@if ($bet->deleted)
											<span class="text-red">Deleted</span>
										@elseif ($bet->expired)
											<span class="text-red">Expired</span>
										@elseif (!is_null($bet->accepted_by_user_id))
											<span class="text-green">Accepted</span>
										@else 
											<span class="text-green">Not Yet Accepted</span>
										@endif
									@endif
								</td>
								<td>
									@if ($bet->canBeDeleted)
										<button type="submit" class="btn btn-danger btn-sm delete-bet" data-bet-id="{{ $bet->id }}">Delete</button>
									@endif
								</td>
						@endforeach
					</tbody>
				</table>
			@else
				You haven't placed any bets yet.
			@endif
		</div>
	</div>
@stop