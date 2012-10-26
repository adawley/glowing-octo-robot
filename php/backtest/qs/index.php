<?php
include '../YahFinAPI.php';

class SymbolParse
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

$objs = array();
$count = strlen($_GET['s']);
$tobj = new SymbolParse();
$buf = &$tobj->s();

for ($i = 0; $i < $count; $i++) {
    $c = $_GET['s'][$i];

    if ($c == ',') {
        $objs[] = $tobj;
        $tobj = new SymbolParse();
        $buf = &$tobj->s();
    } elseif ($c == '*') {
        $buf = &$tobj->m();
    } elseif ($c == '-') {
        $tobj->add();
        $buf = &$tobj->s();
    } else {
        $buf .= strtoupper($c);
    }
}

$objs[] = $tobj;

$api = new YahooFinanceAPI();
$api->addOption("symbol");
$api->addOption("lastTrade");
$api->addOption("lastTradeTime");

foreach($objs as $obj){
    for($i=0; $i<$obj->count;$i++){
        // add symbol to fin queue
        $api->addSymbol($obj->{'s'.$i});
        
        // set multiplier to 1 if not set.
        $mult = (float)(strcmp($obj->{'m'.$i},'') == 0 ? '1' : $obj->{'m'.$i});
        
        // set object to a negative multiplier if we are on index 1 or greater        
         $obj->{'m'.$i} = ($i > 0) ? $mult*-1 : $mult;
    }
}

$result = $api->getQuotes();

$results = array();
if ($result->isSuccess()) {
    foreach($result->data as $row){
        $results[$row->symbol]['lastTrade'] = (float) $row->lastTrade;
        $results[$row->symbol]['lastTradeTime'] = (float) $row->lastTradeTime;
    }
} else{
    echo "<br>Api fetch failed.<br>";
}
    

//if (isset($_GET['v'])):
if(true):
    echo json_encode($objs);
    echo json_encode($results);
    echo '<br><br>';
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
            <div id="master_data"> 
                <table id="master_table">
                    <tr>
                        <td class="spy"></td>
                        <td class="dia"></td>
                    </tr>
                </table>
            </div>                
        </body>
    </html>
<?php endif; ?>