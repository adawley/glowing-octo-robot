<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');


if (!isset($_GET['s']) && !isset($_GET['f'])) {
    die("s and f not set.");
}
$startTime = microtime(true);

include "Strategy.php";

$includeTime = microtime(true) - $startTime;

$symbol = $_GET['s'];
$func = $_GET['f'];
$stgy = null;

if (strcmp($symbol, 'djia') == 0) {
    $symbol = array('AA', 'AXP', 'BA', 'BAC', 'CAT', 'CSCO', 'CVX', 'DD', 'DIS', 'GE', 'HD'
        , 'HPQ', 'IBM', 'INTC', 'JNJ', 'JPM', 'KFT', 'KO', 'MCD', 'MMM', 'MRK', 'MSFT', 'PFE'
        , 'PG', 'T', 'TRV', 'UTX', 'VZ', 'WMT', 'XOM');
} else {
    $symbol = preg_split('/,/', $symbol);
}

switch ($func):
    case 'rsi':
        $stgy = new RsiStrategy($symbol);
        break;
    case 'bollinger':
        $stgy = new BollingerStrategy($symbol);
        break;
    case '357':
        $stgy = new ThreeFiveSevenStrategy($symbol);
        break;
    default :
        throw new Exception("Strategy not found.");
endswitch;

$stgy->test();
$reports = $stgy->report();
$processTime = microtime(true) - $startTime;

?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            table{
                border-collapse: collapse;
            }            
            table td, table th{
                padding: 0 1em 0 1em;
                border: 1px solid black;
                text-align: center;
            }
            table th{
                background-color: lightgray;
            }
            table td.total{
                background-color: #00A6C7;
            }
            table td.win{
                background-color: lightgreen
            }
            table td.loss{
                background-color: lightcoral;
            }
        </style>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
        <script src="jquery.tablesorter.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){ 
                $("#dataTable").tablesorter(); 
            });
        </script>
    </head>
    <body>
        <table id="dataTable" class="tablesorter">
            <thead>
                <tr>
                    <th > </th>
                    <th colspan="4">Total</th>
                    <th colspan="3">Winning</th>
                    <th colspan="3">Losing</th>
                </tr>
                <tr>
                    <th class="total">Symbol</th>
                    <th class="total">Net Profit</th>
                    <th class="total">Return</th>
                    <th class="total">Max Drawdown</th>
                    <th class="total">Trades</th>
                    <th class="win">Trades</th>
                    <th class="win">Net P/L</th>
                    <th class="win">Avg P/L</th>
                    <th class="loss">Trades</th>
                    <th class="loss">Net P/L</th>
                    <th class="loss">Avg P/L</th>
                </tr>
                <tr>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $s => $rept): ?>                    
                    <tr>
                        <td ><?= $s ?></td>
                        <td class="total"><?= $rept->netProfit ?> </td>
                        <td class="total"><?= $rept->returnPercent ?> </td>
                        <td class="total"><?= $rept->maxDrawdown ?> </td>
                        <td class="total"><?= $rept->totalTradeCount ?> </td>
                        <td class="win"><?= $rept->winningTradeCount ?> </td>
                        <td class="win"><?= $rept->winningTradeProfit ?> </td>
                        <td class="win"><?= $rept->averageWinningTradeProfit ?> </td>
                        <td class="loss"><?= $rept->losingTradeCount ?> </td>                        
                        <td class="loss"><?= $rept->losingTradeLoss ?> </td>                        
                        <td class="loss"><?= $rept->averageLosingTradeLoss ?> </td>
                        <td ><?= $rept->dataTime ?> </td>
                        <td ><?= $rept->configTime ?> </td>
                        <td ><?= $rept->processTime ?> </td>
                        <td ><?= $rept->totalTime ?> </td>                      
                        
                    </tr>    
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= "Inc: ".$includeTime."<br>ProcessTime: ".$processTime."<br>Total Time: ".(microtime(true) - $startTime) ?>
    </body>
</html>