<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');


if (!isset($_GET['s']) && !isset($_GET['f'])) {
    die("s and f not set.");
}
$startTime = microtime(true);

include "Strategy.php";


$symbol = $_GET['s'];
$func   = $_GET['f'];
$stgy   = null;
$search = false;

if (strcmp($symbol, 'djia') == 0) {
    $symbol = array('AA', 'AXP', 'BA', 'BAC', 'CAT', 'CSCO', 'CVX', 'DD', 'DIS', 'GE', 'HD'
        , 'HPQ', 'IBM', 'INTC', 'JNJ', 'JPM', 'KFT', 'KO', 'MCD', 'MMM', 'MRK', 'MSFT', 'PFE'
        , 'PG', 'T', 'TRV', 'UTX', 'VZ', 'WMT', 'XOM');
} elseif (strcmp($symbol, 'search') == 0) {
    $search = true;
    $symbol = NULL;
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
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            body{
                font: 10px verdana;
            }
            img#dialog-img{
                float: left;
            }
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
                cursor: pointer;
            }
            table th.blank{
                border: none;
                background-color: transparent;
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
            tbody tr{
                cursor: pointer;                
            }
            tbody tr:hover{
                background-color: lightblue
            }
        </style>
        <link rel="stylesheet" href="css/jquery-ui-1.9.0.custom.min.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
        <script src="jquery.tablesorter.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){ 
                $("#dataTable").tablesorter(); 
                
                $( "#dialog" ).dialog({
                    autoOpen: false,
                    height: 350, //550
                    width: 960,//1300,
                    modal: true
                });
            });
            
            function showDialog(arg){                
                var addr = "http://chart.finance.yahoo.com/z?t=1y&q=b&l=on&z=m&p=s,v,b&a=r14&lang=en-US&region=US&s="+arg.id;
                $("#dialog-img").attr("src",addr);
                $( "#dialog-data" ).html("<pre>"+reportData[arg.id].orderLog+"</pre>");
                $( "#dialog" ).dialog( "open" );
            }
        </script>
        <script type="text/javascript">
            var reportData = <?= json_encode($reports) ?>
        </script>
    </head>
    <body>
        <table id="dataTable" class="tablesorter">
            <thead>
                <tr>
                    <th class="blank"></th>
                    <th colspan="4">Total</th>
                    <th colspan="3">Winning</th>
                    <th colspan="3">Losing</th>
                    <th colspan="4">Stats</th>
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
                    <th>Prob. Win</th>
                    <th>Expectancy</th>
                    <th>Calc</th>
                    <th>Buy?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $s => $rept): ?>                    
                    <tr id="<?= $s ?>" onclick="showDialog(this);">
                        <td class="symbol"  ><?= $s ?></td>
                        <td class="total"><?= $rept->numfmt('netProfit') ?> </td>
                        <td class="total"><?= $rept->numfmt('returnPercent') ?> </td>
                        <td class="total"><?= $rept->numfmt('maxDrawdown') ?> </td>
                        <td class="total"><?= $rept->totalTradeCount ?> </td>
                        <td class="win"><?= $rept->winningTradeCount ?> </td>
                        <td class="win"><?= $rept->numfmt('winningTradeProfit') ?> </td>
                        <td class="win"><?= $rept->numfmt('averageWinningTradeProfit') ?> </td>
                        <td class="loss"><?= $rept->losingTradeCount ?> </td>                        
                        <td class="loss"><?= $rept->numfmt('losingTradeLoss') ?> </td>                        
                        <td class="loss"><?= $rept->numfmt('averageLosingTradeLoss') ?> </td>
                        <td ><?= ($rept->numfmt('winningTradeProbability')*100).'%' ?> </td>
                        <td ><?= $rept->numfmt('expectancy') ?> </td>
                        <td ><?= $rept->userData->last ?> </td>
                        <td ><?= $rept->userData->buyTrigger ?> </td>

                    </tr>    
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= "<br>Calculated in " . (microtime(true) - $startTime) . " seconds." ?>
        <div id="dialog">
            <img src="" id="dialog-img"/>
            <div id="dialog-data"></div>
        </div>
    </body>
</html>
