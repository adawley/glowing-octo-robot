<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');
include "Strategy.php";

$symbol = $_GET['s'];


if (strcmp($symbol, 'djia') == 0) {
    $symbol = array('AA', 'AXP', 'BA', 'BAC', 'CAT', 'CSCO', 'CVX', 'DD', 'DIS', 'GE', 'HD'
        , 'HPQ', 'IBM', 'INTC', 'JNJ', 'JPM', 'KFT', 'KO', 'MCD', 'MMM', 'MRK', 'MSFT', 'PFE'
        , 'PG', 'T', 'TRV', 'UTX', 'VZ', 'WMT', 'XOM');
} else {
    $symbol = preg_split('/,/', $symbol);
}

function getStrategy($s)
{
    $result = null;
    switch ($_GET['f']):
        case 'rsi':
            $result = new RsiStrategy($s);
            break;
        case 'bollinger':
            $result = new BollingerStrategy($s);
            break;
        case '357':
            $result = new ThreeFiveSevenStrategy($s);
            break;
        default :
            throw new Exception("Strategy not found.");
    endswitch;

    return $result;
}

echo '<pre>';
foreach ($symbol as $s) {
    echo "\n";
    echo ':::', $s, ':::',"\n";
    $stgy = getStrategy($s);
    $stgy->test();
    echo $stgy->report(), "\n";
}

?>
