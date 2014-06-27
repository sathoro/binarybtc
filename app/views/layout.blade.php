<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Easy and anonymous binary betting platform for bitcoin!">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link rel="shortcut icon" href="{{ url('assets/img/bitcoin-logo.png') }}">

    <title>BinaryBTC | Bitcoin Binary Trades</title>

    <link href="http://fonts.googleapis.com/css?family=Ubuntu|Open+Sans:400,600" rel="stylesheet">
    <link href="{{ url('dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ url('assets/css/style.css') }}" rel="stylesheet">

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    @yield('top')
  </head>

  <body {{ Request::is('/') ? "id='home-page'" : "id='inner-page'" }}>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <img class="logo" src="{{ url('assets/img/bitcoin-logo.png') }}">
          <a class="navbar-brand" href="{{ url('') }}">BinaryBTC</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left">
            <li><a href="{{ url('faq') }}">FAQ</a></li>
            <li><a href="#" data-toggle="modal" data-target="#contact-modal">Contact</a></li>
          </ul>
          @if (Auth::check())
            <ul class="nav navbar-nav navbar-right">
              <li><a href="/users/bets">My Bets</a></li>
              <li><a href="/users/deposit">Deposit</a></li>
              <li><a href="/users/withdraw">Withdraw</a></li>
              <li><a href="/logout">Logout</a></li>
            </ul>
            <div class="navbar-text navbar-right text-white" style="margin-right: 20px">
              Balance: {{ Auth::user()->balance }} BTC
            </div>
          @else
            {{ Form::open(array('url'=>'users/login', 'class' => 'login-form navbar-form navbar-right')) }}
              <div class="form-group">
                <input type="text" name="email" placeholder="Email" class="form-control" autocomplete="off">
              </div>
              <div class="form-group">
                <input type="password" name="password" placeholder="Password" class="form-control" autocomplete="off">
              </div>
              <button type="submit" class="btn btn-success">Sign in</button>
              <button class="btn btn-primary" data-toggle="modal" data-target="#register-modal" type="button">Register</button>
            {{ Form::close() }}
          @endif
          @if (!Request::is('/'))
            <div class="navbar-text navbar-right text-white" style="margin-right: 20px">
              Current Price: ${{ number_format($btc_price, 2) }}
            </div>
          @endif
        </div>
      </div>
    </div>

    @yield('content')

    <hr>
    <footer>
      <div class="container">
        <p>&copy; BinBTC 2014 <a href="{{ url('') }}">Terms of Service</a> <a href="{{ url('') }}">Contact</a></p>
      </div>
    </footer>

    <div class="modal fade bs-modal-sm contact-modal" id="contact-modal" tabindex="-1" role="dialog" aria-labelledby="bet-modal-label" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="bet-modal-label">Contact Form</h4>
          </div>
          {{ Form::open(array('url'=>'contact/submit', 'class' => 'contact-form')) }}
            <div class="modal-body">
              <div data-input="email" class="errors"></div>
              <div class="input-group">
                <span class="input-group-addon">@</span>
                <input type="text" name="email" class="form-control" placeholder="Email" value="{{ Auth::check() ? Auth::user()->email : "" }}">
              </div>

              <textarea name="message" class="form-control" rows="3" placeholder="Message" style="margin-top:15px"></textarea>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" type="submit">Submit</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          {{ Form::close() }}
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="{{ url('dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ url('dist/js/qrcode.js') }}"></script>

    <script>
      $(function() {
        "use strict";

        $.ajaxSetup({
          cache: false,
          headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $('.register-modal').on('show.bs.modal', function() {
          var $this = $(this);
          setTimeout(function() {
            $this.find('input[type=text]:eq(0)').focus();
          }, 700);
        });

        $('.contact-form').submit(function() {
          $.post($(this).attr('action'), $(this).serialize());
          $(this).find('.modal-body').html('Message sent!');
          $(this).find('button[type=submit]').prop('disabled', true);
          return false;
        });

        function handle_errors(errors, form) {
          try {
            if (typeof errors != 'object') errors = $.parseJSON(errors);
            $.each(errors, function(i, e) {
              $(form).find('div[data-input=' + i + ']').eq(0).text(e);
            });
          }
          catch(e) {
            $(form).find('.errors').eq(0).text(errors);
          }
        }

        function clear_errors(form) {
          $(form).find('.errors').text('');
        }

        $('.registration-form').submit(function(e) {
          var $this = $(this);
          clear_errors($this);
          e.preventDefault();
          $.post($this.attr('action'), $this.serialize(), function(data) {
            if (data['success'] === true) {
              location.reload(true);
            }
            else {
              handle_errors(data['errors'], $this);
            }
          });
        });

        $('.login-form').submit(function(e) {
          var $this = $(this);
          clear_errors($this);
          e.preventDefault();
          $.post($this.attr('action'), $this.serialize(), function(data) {
            if (data['success'] === true) {
              location.reload(true);
            }
            else {
              alert('Invalid login.');
            }
          });
        });

        $('.bet-btn').click(function() {
          var $modal = $('#bet-modal'),
              $json = $(this).closest('.trade').data('opts'),
              $direction = $modal.find('[data-js=bet-direction]');

          $modal.find('.address-container').hide();

          $.each($json, function(i, e) {
            $modal.find('[data-js=bet-' + i + ']').text(e);
          });

          $modal.find('.accept-bet').data('bet-id', $json['bet_id']);

          if ($direction.text() == "under") {
            $direction.addClass('text-red');
            $modal.find('[data-js=bet-change]').addClass('text-red');
          }
          else {
            $direction.addClass('text-green');
            $modal.find('[data-js=bet-change]').addClass('text-green');
          }
        });

        $('.accept-bet').click(function() {
          var $modal = $(this).closest('.modal-content'),
              data = {bet_id: $(this).data('bet-id')};

          clear_errors($modal);

          $.post('/bet/crossbet', data, function(data) {
            if (data['success'] === true) {
              window.location = "/users/bets";
            }
            else {
              handle_errors(data['errors'], $modal);
            }
          });
        });

        $('.btn-group button').click(function() {
          $(this).parent().children().removeClass('active');
          $(this).addClass('active');
        });

        $('.place-bet').click(function() {
          var $modal = $(this).closest('.modal-content'),
              data = {};

          data.type = $modal.find('[name=type]').children('.active:eq(0)').attr('val');
          data.expiration = $modal.find('[name=expiration]').children('.active:eq(0)').attr('val');
          data.bet_amount = $modal.find('[name=bet_amount]').val();
          data.cross_bet_amount = $modal.find('[name=cross_bet_amount]').val();
          data.target_price = $modal.find('[name=target_price]').val();

          clear_errors($modal);

          $.post('/bet/create', data, function(data) {
            if (data['success'] === true) {
              window.location = "/users/bets";
            }
            else {
              handle_errors(data['errors'], $modal);
            }
          });
        });

        $('.delete-bet').click(function() {
          var bet_id = $(this).data('bet-id');

          $.post('/bet/delete', {bet_id: bet_id}, function(data) {
            if (data['success'] === true) {
              window.location = "/users/bets";
            }
            else {
              alert('Bet couldn\'t be deleted at this time!');
            }
          });
        });

        var $make_qr_code = $('.make-qr-code');
        if ($make_qr_code.length !== 0) {
          var qrCode = qrcode(3, 'M');
          var text = $make_qr_code.text();
          text = text.replace(/^[\s\u3000]+|[\s\u3000]+$/g, '');
          qrCode.addData(text);
          qrCode.make();
          $make_qr_code.parent().children('.qr-address').html(qrCode.createImgTag(5));
        }

        var $price_last_updated = $('[data-js=last-updated]'),
            refresh_try = 0;
        if ($price_last_updated.length !== 0) {
          setInterval(function() {
            var time = $price_last_updated.data('time') + 1,
                diff = time - $price_last_updated.data('value');
            $price_last_updated.data('time', time);
            $price_last_updated.text(time - $price_last_updated.data('value'));
            if (diff > 64 && refresh_try++ % 5 == 0) {
              $.get('/latest_price', function(data) {
                if (data['time'] > $price_last_updated.data('value')) {
                  $price_last_updated.data('value', data['time']);
                  $price_last_updated.text(time - data['time']);
                  $("#latest_price").text(data['price']);

                  if (window.chart !== undefined) {
                    var $selected = $('.amChartsButtonSelected');
                    window.chart.dataSets[0]['dataProvider'].push({'value': data['price'], 'date': data['date']});
                    window.chart.validateData();
                    if ($selected.length !== 0) $selected.click();
                  }
                }
              });
            }
          }, 1000);
        }
      });
    </script>

    @yield('bottom')
  </body>
</html>
