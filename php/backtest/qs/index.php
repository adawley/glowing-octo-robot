<?php
include '../YahFinAPI.php';

$api = new YahooFinanceAPI();

$api->addOption("symbol");
$api->addOption("lastTrade");
$api->addOption("lastTradeTime");

$objs = array();

$count = strlen($_GET['s']);

class StockParse
{
    public $count;
    public $current;
    
    function __construct() {
        $this->count = 0;
        $this->current = 0;
        $this->add();
    }
    
    function add()
    {
        $this->{'s'.$this->count} = '';
        $this->{'m'.$this->count} = '';
        $this->current = $this->count;
        $this->count++;
    }
    
    function &s()
    {
        return $this->{'s'.$this->current};
    }
    
    function &m()
    {
        return $this->{'m'.$this->current};
    }
    
}

$tobj = new StockParse();
$buf = &$tobj->s();

for ($i = 0; $i < $count; $i++) {
    $c = $_GET['s'][$i];

    if ($c == ',') {
        $objs[] = $tobj;
        $tobj = new StockParse();
        $buf = &$tobj->s();
    } elseif ($c == '*') {
        $buf = &$tobj->m();
    } elseif ($c == '-') {
        $tobj->add();
        $buf = &$tobj->s();
    } else {
        $buf .= $c;
    }
}

$objs[] = $tobj;

//foreach (preg_split('/,/', $_GET['s']) as $s) {
//    $tobj         = new stdClass();
//    $tobj->symbol = $s;
//    $tobj->count  = 0;
//    $count        = &$tobj->count;
//
//    // see if there is a pair
//    if (count($pair = preg_split('/-/', $s)) > 0) {
//        foreach ($pair as $p) {
//            // see if there are multipliers
//            if (count($mult = preg_split('/\*/', $s)) > 0) {
//                foreach ($mult as $m) {
//                    $tobj->{'s' . $count} = $m;
//                }
//            }
//            $count++;
//        }
//    }
//    $api->addSymbol($s);
//
//    $objs[] = $tobj;
//}
//$result = $api->getQuotes();
//$out    = '';
//if ($result->isSuccess()) {
//    $quotes = $result->data;
//    foreach ($quotes as $quote) {
//        $out .= $quote->symbol . ' ' . $quote->lastTrade . ' ' . $quote->lastTradeTime . '<br>';
//    }
//}

if (isset($_GET['v'])):
    //echo $out;
    echo "<pre>";
    print_r($objs);

else:
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <link rel="stylesheet" href="css/jquery-ui-1.9.0.custom.min.css" />
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
            <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
            <script src="jquery.tablesorter.min.js"></script>
            <script type="text/javascript">
                $(document).ready(function(){ 
                    setInterval(function(){
                        $('#loadable').load('?s=spy,dia,goog&v=n');
                    }, 30000);                
                });
            </script>
        </head>
        <body>
            <div id="loadable">
                <?= $out ?>
            </div>
        </body>
    </html>
<?php endif; ?>