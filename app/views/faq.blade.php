@extends('layout')

@section('content')
  <div class="container" id="main">
    <h1>Frequently Asked Questions</h1>
    <div class="panel-group" id="accordion">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-8">
              How do I place a bet?
            </a>
          </h4>
        </div>
        <div id="collapse-0-8" class="panel-collapse collapse">
          <div class="panel-body">
            <p>First you will need to create an account. After registering and logging in, go to the 'Deposit' page to view the unique public address associated with your account. Send your bitcoin to that address and after 3 confirmations (usually less than one hour) your balance will be updated.</p>

            <p>Once the funds are in your account, visit the home page to view the available bets or create your own.</p>
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-0">
              Is betting anonymous?
            </a>
          </h4>
        </div>
        <div id="collapse-0-0" class="panel-collapse collapse">
          <div class="panel-body">
            Yes, betting is completely anonymous. Your email will never be publicly displayed on the website and other users can't track your bets. In addition, we will never share your email address with any third-parties.
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-6">
              What is the 'cross-bet amount'?
            </a>
          </h4>
        </div>
        <div id="collapse-0-6" class="panel-collapse collapse">
          <div class="panel-body">
            The cross-bet is the amount another person must bet against you. If you win the bet, it is how much you will win. For bets that are close to the current price the cross-bet amount should be very similar or equal to your own bet amount. This is because you are not taking more risk relative to the other person. If, for example, you bet that the price will be $100 higher in one hour then the cross-bet amount should be higher because that is a higher risk for you (ie: statistically, it is more likely the price will not move $100 in your chosen direction within one hour).
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-5">
              How much can I bet?
            </a>
          </h4>
        </div>
        <div id="collapse-0-5" class="panel-collapse collapse">
          <div class="panel-body">
            The minimum bet amount is {{ Bitcoin::toBTC(Config::get('bitcoin.minimum_bet')) }} BTC. For now there is a maximum bet limit of {{ Bitcoin::toBTC(Config::get('bitcoin.maximum_bet')) }} BTC.
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-4">
              Can I cancel a bet?
            </a>
          </h4>
        </div>
        <div id="collapse-0-4" class="panel-collapse collapse">
          <div class="panel-body">
            As long as the bet has not already been accepted, yes. You may go to your 'My Bets' page and click the 'Delete' button. This will cancel the bet and you will immediately receive the bet amount back into your balance, ready to be withdrawn or used to make another bet.
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-1">
              What fees are involved?
            </a>
          </h4>
        </div>
        <div id="collapse-0-1" class="panel-collapse collapse">
          <div class="panel-body">
            <ul>
              <li>When you win a bet there is a 2% fee taken from the winnings.
              <li>When you withdraw your bitcoin to an external wallet there is a {{ Bitcoin::toBTC(Config::get('bitcoin.withdrawal_fee')) }} BTC fee.
              <li>There is no fee to deposit your bitcoin.
            </ul>
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-7">
              When a bet is over, when are the winnings distributed?
            </a>
          </h4>
        </div>
        <div id="collapse-0-7" class="panel-collapse collapse">
          <div class="panel-body">
            Immediately. If you win a bet then the second it is over your account balance will reflect your winnings. If you lose a bet your balance will not change since the bet amount has already been deducted from your account.
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-3">
              Where is the pricing data from?
            </a>
          </h4>
        </div>
        <div id="collapse-0-3" class="panel-collapse collapse">
          <div class="panel-body">
            The pricing data is calculated every minute using a volume weighted average from two major exchanges. Since it is necessary for the data to be consistent and readily available we did not want to source the data from more than two exchanges in order to reduce downtime. We feel that using a volume-weighted approach will be a good long term solution.
          </div>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse-0-2">
              What security measures have you taken to protect user's data?
            </a>
          </h4>
        </div>
        <div id="collapse-0-2" class="panel-collapse collapse">
          <div class="panel-body">
            The site is secured with SSL encryption, all passwords are salted and hashed before being stored in the database, and we use CSRF tokens to validate every POST request made to our servers. Furthermore, SSH access to the server has been disabled and remote MySQL access is disabled. If you find a vulnerability please disclose it responsibly using the contact form above and we will reward you.
          </div>
        </div>
      </div>
    </div>
  </div>
@stop