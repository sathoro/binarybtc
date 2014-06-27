<?php

header('Content-type: application/json');
echo file_get_contents("https://btc-e.com/api/2/btc_usd/ticker");