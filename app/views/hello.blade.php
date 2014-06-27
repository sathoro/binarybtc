@extends('layout')

@section('content')
  @if (!Auth::check())
    <div class="jumbotron">
      <div class="container">
        <h1>Binary Bets</h1>
        <p>Take advantage of bitcoin's volatility by betting on price swings.</p>
        <p><button class="btn btn-primary btn-lg create-new-bet" data-toggle="modal" data-target="#new-bet-modal" role="button">Create New Bet &raquo;</button></p>
      </div>
    </div>
  @else
    <div id="priceChart" class="large"></div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-8">
        @if (Auth::check())
          <button style="margin-top:-10px;margin-bottom:20px" class="btn btn-primary btn-lg create-new-bet" data-toggle="modal" data-target="#new-bet-modal" role="button">Create New Bet &raquo;</button>
        @endif
        <div class="panel panel-default">
          <div class="panel-heading">
            <h1 class="panel-title" style="font-size:26px">Available Bets</h1>
          </div>
          <div class="panel-body">
            @if (count($bets) > 0)
              <p>Listed below are bets that you can make against other people. When you find a favorable amount, price, and timeframe just click <strong>Bet</strong> to receive more information. If you would like to create a bet instead, use the button above.</p>

              @foreach($bets as $bet)
                @if ($bet->type = ($bet->type == "above" ? "under" : "above")) @endif
                <div class="trade" data-opts='{"bet_id": "{{ $bet->id }}", "direction": "{{ $bet->type }}", "price": "{{ $bet->target_price }}", "duration": "{{ $bet->readableExpiration }}", "winnings": "{{ Bitcoin::toBTC($bet->bet_amount) }}", "amount": "{{ Bitcoin::toBTC($bet->cross_bet_amount) }}", "total": "{{ Bitcoin::toBTC($bet->cross_bet_amount) + Bitcoin::toBTC($bet->bet_amount) }}", "change": ""}'>
                  <div class="trade-heading">
                    <span class="text-{{ $bet->type == "under" ? "red" : "green" }}">{{ ucfirst($bet->type) }}</span> <strong>${{ $bet->target_price }}</strong> in {{ $bet->readableExpiration }}
                  </div>
                  <div class="trade-body">
                    <p>
                      <strong>Potential Winnings</strong>: {{ Bitcoin::toBTC($bet->bet_amount) }} BTC <br>
                      <strong>Bet Amount</strong>: {{ Bitcoin::toBTC($bet->cross_bet_amount) }} BTC
                    </p>
                    <p><button class="btn btn-default bet-btn" data-toggle="modal" data-target="#bet-modal">Bet &raquo;</button></p>
                  </div>
                </div>
              @endforeach

              <div class="text-center">
                {{ $bets->links() }}
              </div>
            @else
              No bets available, create one above.
            @endif
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Current Price</h3>
          </div>
          <div class="panel-body">
            <h3 style="margin:0 auto;display:block"><span id="latest_price">{{ number_format($btc_price, 4) }}</span> USD / BTC</h3>
            <small>updated <span data-js="last-updated" data-value="{{ $updated_at }}" data-time="{{ time() }}">{{ time() - $updated_at }}</span> seconds ago</small>
          </div>
        </div>

        @if (!Auth::check())
          <!-- <div id="priceChart" class="small"></div> -->
        @endif
      </div>
    </div>
  </div>

  <div class="modal fade" id="bet-modal" tabindex="-1" role="dialog" aria-labelledby="bet-modal-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="bet-modal-label">Place Your Bet</h4>
        </div>
        <div class="modal-body">
          <p class="errors">
            @if (!Auth::check())
              You must be logged in to place any bets.
            @endif
          </p>
          <p>You are betting that the price of bitcoin will be <span data-js="bet-direction"></span> <strong>$<span data-js="bet-price"></span></strong> <!--(<span data-js="bet-change"></span>) -->in <span data-js="bet-duration"></span>.
          <p class="h4" style="line-height:25px">
            <strong>Potential Winnings:</strong> <span data-js="bet-winnings"></span> BTC<br>
            <strong>Bet Amount:</strong> <span data-js="bet-amount"></span> BTC
          </p>

          <p>By clicking <strong>Place Bet</strong> you are debiting your account <strong><span data-js="bet-amount"></span> BTC</strong>. If you win the bet your account will be credited <strong><span data-js="bet-total"></span> BTC</strong> minus a 2% fee.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary accept-bet" {{ !Auth::check() ? 'disabled' : '' }}>Place Bet</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <!-- <button type="button" class="btn btn-primary"></button> -->
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade bs-modal-lg" id="new-bet-modal" tabindex="-1" role="dialog" aria-labelledby="bet-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="bet-modal-label">Create a Bet</h4>
        </div>
        <div class="modal-body">
          <div class="bet-options">
            <p class="errors">
              @if (!Auth::check())
                You must be logged in to place any bets.
              @endif
            </p>
            <small style="display:block;max-width:800px;margin-bottom:12px"><strong>Note:</strong> You may cancel your bet at anytime before another person accepts the bet. After the bet has been accepted you cannot cancel the bet. No fees are incurred by canceling a bet.</small>

            <div data-input="bet_amount" class="errors"></div>
            <div style="margin-bottom:10px">
              <span class="h5" style="font-weight:bold;width:130px;display:inline-block;margin-right:15px">I want to bet</span> 
              <input name="bet_amount" style="display:inline-block;width:100px;margin-right:10px" type="text" class="form-control" value=".2">BTC
            </div>

            <div data-input="type" class="errors"></div>
            <div data-input="target_price" class="errors"></div>
            <span class="h5" style="font-weight:bold;width:130px;display:inline-block;margin-right:15px">the price will be</span>
            <div class="btn-group" name="type">
              <button type="button" class="btn btn-success active" val="above">Above</button>
              <button type="button" class="btn btn-danger" val="under">Under</button>
            </div>
            <input name="target_price" style="display:inline-block;width:auto" type="text" class="form-control" value="{{ number_format($btc_price, 2) }}">

            <p style="margin-bottom:15px"></p>

            <div data-input="expiration" class="errors"></div>
            <span class="h5" style="font-weight:bold;width:130px;display:inline-block;margin-right:15px">in</span>
            <div class="btn-group" name="expiration">
              <button type="button" class="btn btn-default" val="1800">30 mins</button>
              <button type="button" class="btn btn-default" val="3600">1 hour</button>
              <button type="button" class="btn btn-default active" val="14400">4 hours</button>
              <button type="button" class="btn btn-default" val="28800">8 hours</button>
              <button type="button" class="btn btn-default" val="43200">12 hours</button>
              <button type="button" class="btn btn-default" val="86400">24 hours</button>
              <button type="button" class="btn btn-default" val="604800">7 days</button>
            </div>

            <p style="margin-bottom:15px"></p>

            <small style="display:block;max-width:400px;margin-bottom:12px">The cross-bet is the amount another person must bet against you. If you set this too high, nobody will accept the bet. If you set it too low, then you get a smaller reward. You should set this higher for bets that are further away from the current price due to the extra risk you are taking, and lower for bets that are close to the current price.</small>
            <div data-input="cross_bet_amount" class="errors"></div>
            <span class="h5" style="font-weight:bold;width:130px;display:inline-block;margin-right:15px">Cross-Bet Amount</span> 
            <input name="cross_bet_amount" style="display:inline-block;width:100px;margin-right:10px" type="text" class="form-control" value=".2"> BTC
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary place-bet" {{ !Auth::check() ? 'disabled' : '' }}>Place Bet</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <!-- <button type="button" class="btn btn-primary"></button> -->
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade bs-modal-sm register-modal" id="register-modal" tabindex="-1" role="dialog" aria-labelledby="bet-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="bet-modal-label">Create New Account</h4>
        </div>
        {{ Form::open(array('url'=>'users/register', 'class' => 'registration-form')) }}
          <div class="modal-body">
            <div data-input="email" class="errors"></div>
            <div class="input-group">
              <span class="input-group-addon">@</span>
              <input type="text" name="email" class="form-control" placeholder="Email">
            </div>

            <div data-input="password" class="errors"></div>
            <div class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
              <input type="password" name="password" class="form-control" placeholder="Password">
            </div>

            <div data-input="password_confirmation" class="errors"></div>
            <div class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
              <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
            </div>

            <small>Your email <strong>will not</strong> be shared with any third-parties, used for marketing purposes, or published anywhere on the website.</small>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary">Register</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        {{ Form::close() }}
      </div>
    </div>
  </div>
@stop

@section('bottom')
  @if (Auth::check())
    <script src="{{ url('dist/js/amcharts.js') }}"></script>
    <script src="{{ url('dist/js/serial.js') }}"></script>
    <script src="{{ url('dist/js/dark.js') }}"></script>
    <script src="{{ url('dist/js/amstock.js') }}"></script>

    <script>
      "use strict";

      if ($("#priceChart").length !== 0) {
        $.get("/chart_data", function(json) {
          window.chart = AmCharts.makeChart("priceChart", {
              type: "stock",
              theme: "dark",
              pathToImages: "http://binbtc.com/assets/img/",
              dataDateFormat: "YYYY-MM-DD HH:NN:SS",
              panels: [{
                  showCategoryAxis: false,
                  title: "Value",
                  percentHeight: 70,

                  stockGraphs: [{
                    id: "g1",
                    valueField: "value",
                    type: "smoothedLine",
                    lineThickness: 2,
                    bullet: "round"
                  }],
                  stockLegend: {
                    valueTextRegular: " ",
                    markerType: "none"
                  }
                }
              ],
              chartScrollbarSettings: {
                graph: "g1",
                usePeriod: "10mm",
                position: "top"
              },
              categoryAxesSettings: {
                minPeriod: "mm"
              },
              exportConfig: {
                  menuRight: '20px',
                  menuBottom: '50px',
                  menuItems: [{
                      icon: '',
                      format: 'png'
                  }]
              },
              periodSelector: {
                position: "bottom",
                dateFormat: "YYYY-MM-DD JJ:NN",
                inputFieldWidth: 150,
                periods: [{
                  period: "hh",
                  count: 1,
                  label: "1 hour",
                  selected: true

                }, {
                  period: "hh",
                  count: 2,
                  label: "2 hours"
                }, {
                  period: "hh",
                  count: 5,
                  label: "5 hour"
                }, {
                  period: "hh",
                  count: 12,
                  label: "12 hours"
                }, {
                  period: "MAX",
                  label: "MAX"
                }]
              },
              dataSets: [{
                color: "#b0de09",
                fieldMappings: [{
                  fromField: "value",
                  toField: "value"
                }],

                dataProvider: json,
                categoryField: "date"
              }],
          });
        });
      }
    </script>
  @endif
@stop