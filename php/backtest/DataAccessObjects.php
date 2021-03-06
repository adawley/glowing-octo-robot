<?php

/**
 * DataAccessObjects
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */

/**
 * CSVParserBase
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Michael Crosby <michael@crosbymichael.com>
 * @license  http://crosbymichael.com CC Attribution-NonCommercial
 * @link     https://github.com/crosbymichael
 */
abstract class CSVParserBase
{

    protected $MaxLenght        = 0;
    protected $Separator        = '';
    public $HeaderArray      = null;
    public $IsFirstRowHeader = false;

    function __construct($maxLenght = 1000, $separator = ',')
    {
        $this->MaxLenght = $maxLenght;
        $this->Separator = $separator;
    }

    public function Parse($csvData)
    {
        $array = null;

        if (($fh = fopen($csvData, "r"))) {

            while (($data = fgetcsv($fh, $this->MaxLenght, $this->Separator))) {
                $array[] = $data;
            }

            fclose($fh);
        } else {
            throw new Exception("Cannot parse data", 1);
        }
        return $this->ProcessArray($array);
    }

    protected abstract function ProcessArray($array);

    protected function StripString($contents)
    {
        return preg_replace('/\s+/', '', $contents);
    }

}

/**
 * CSVParserFactory
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Michael Crosby <michael@crosbymichael.com>
 * @license  http://crosbymichael.com CC Attribution-NonCommercial
 * @link     https://github.com/crosbymichael
 */
class CSVParserFactory
{

    public static function Create($type, $maxLenght = 1000, $separator = ',')
    {
        switch ($type) {
            case 'json':
                return new CSVToJsonParser($maxLenght, $separator);
            case 'object':
                return new CSVToObjectParser($maxLenght, $separator);
            case 'xml':
                return new CSVToXmlParser($maxLenght, $separator);
        }
    }

}

/**
 * CSVToJsonParser
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Michael Crosby <michael@crosbymichael.com>
 * @license  http://crosbymichael.com CC Attribution-NonCommercial
 * @link     https://github.com/crosbymichael
 */
class CSVToJsonParser
    extends CSVParserBase
{

    protected function ProcessArray($array)
    {

        if ($this->IsFirstRowHeader) {
            $this->HeaderArray = $array[0];
            $array             = $this->ToAssocativeArray($array);
        }

        return json_encode($array);
    }

    private function ToAssocativeArray($array)
    {

        $columnCount = count($array[0]);
        $temp        = array();

        for ($i = 1; $i < count($array); $i++) {
            $item = array();

            for ($n = 0; $n < $columnCount; $n++) {
                $columnName        = $this->HeaderArray[$n];
                $item[$columnName] = $array[$i][$n];
            }

            $temp[] = $item;
            unset($item);
        }
        return $temp;
    }

}

/**
 * CSVToObjectParser
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class CSVToObjectParser
    extends CSVParserBase
{

    protected function ProcessArray($array)
    {
        if ($this->IsFirstRowHeader) {
            $this->HeaderArray = array_shift($array);
            $array             = $this->ToObjectArray($array);
        }

        return $array;
    }

    private function ToObjectArray(&$array)
    {
        $result = array();
        $rowCount    = count($array);
        $columnCount = count($this->HeaderArray);
        for ($i = 0; $i < $rowCount; $i++) {
            $clazz = new stdClass();
            for ($j = 0; $j < $columnCount; $j++) {
                $clazz->{$this->HeaderArray[$j]} = $array[$i][$j];
            }
            $result[]                        = $clazz;
            unset($clazz);
        }
        return $result;
    }

}

/**
 * CSVToXmlParser
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Michael Crosby <michael@crosbymichael.com>
 * @license  http://crosbymichael.com CC Attribution-NonCommercial
 * @link     https://github.com/crosbymichael
 */
class CSVToXmlParser
    extends CSVParserBase
{

    public $ItemName = 'item';

    protected function ProcessArray($array)
    {
        $startingIndex = 0;
        $document      = '';

        if ($this->IsFirstRowHeader) {
            $startingIndex     = 1;
            $this->HeaderArray = $array[0];
        }

        $columnCount = count($array[0]);

        $document = "<?xml version=\"1.0\" ?>\n";
        $document .= "<root>\n";

        for ($i = $startingIndex; $i < count($array); $i++) {
            $document .= $this->GetItemName();

            for ($n = 0; $n < $columnCount; $n++) {
                $columnName = $this->GetColumnName($n);

                $document .= sprintf(
                    "\t\t<%s>%s</%s>\n", $columnName, $array[$i][$n], $columnName);
            }

            $document .= $this->GetItemName(true);
        }

        $document .= "</root>";

        return $document;
    }

    private function GetColumnName($index)
    {
        $name = 'column' . $index;

        if ($this->HeaderArray != null &&
            count($this->HeaderArray) > 0) {
            $name = $this->StripString(
                $this->HeaderArray[$index]);
        }
        return $name;
    }

    private function GetItemName($close = false)
    {
        $format = ($close) ? "\t</%s>\n" : "\t<%s>\n";
        return sprintf($format, $this->ItemName);
    }

}

/**
 * Finviz_Field
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class Finviz_Field
{

    const NUMBER        = 'No.';
    const TICKER        = 'Ticker';
    const COMPANY       = 'Company';
    const SECTOR        = 'Sector';
    const INDUSTRY      = 'Industry';
    const COUNTRY       = 'Country';
    const MARKET_CAP    = 'Market Cap';
    const PE            = 'P/E';
    const BETA          = 'Beta';
    const ATR           = 'Average True Range';
    const SMA20         = '20-Day Simple Moving Average';
    const SMA50         = '50-Day Simple Moving Average';
    const SMA200        = '200-Day Simple Moving Average';
    const RSI14         = 'Relative Strength Index (14)';
    const AVG_VOLUME    = 'Average Volume';
    const PRICE         = 'Price';
    const CHANGE        = 'Change';
    const VOLUME        = 'Volume';
    const EARNINGS_DATE = 'Earnings Date';

}

/**
 * Finviz_Helper
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class Finviz_Helper
{

    /**
     * Finviz address
     * @var string
     */
    private $address = 'http://finviz.com/export.ashx?v=152&f=$$FILTERS$$&c=0,1,2,3,4,5,6,7,48,49,52,53,54,59,63,65,66,67,68';

    /**
     * Get the parsed finviz data.
     * 
     * @param array $filters Finviz search filters to use (i.e. idx_sp500)
     * @param array $columns Finviz columns to get (Finviz_Field)
     * 
     * @return array
     */
    function get($filters, $columns = NULL)
    {
        $filters = implode(',', $filters);
        $address = str_replace('$$FILTERS$$', $filters, $this->address);
        if (!is_null($columns)) {
            $address .= ',' . implode(',', $columns); // appends the columns
        }
        $csvParser                   = CSVParserFactory::Create("object");
        $csvParser->IsFirstRowHeader = true;
        return $csvParser->Parse($address);
    }

}

/**
 * MarketDataGateway
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class MarketDataGateway
{

    /**
     *
     * @var YahFin_Sqlite 
     */
    private $_db;

    /**
     *
     * @var YahFin_Historical 
     */
    private $_yfh;

    /**
     * MarketDataGateway constructor
     */
    function __construct()
    {
        $this->_db  = new YahFin_Sqlite();
        $this->_yfh = new YahFin_Historical();
    }

    /**
     * Get historical data.  First checks database to see if data is current. If
     * it is not then it goes out an fetches new data, and inserts it into the 
     * database.  If the data is recent it returns the database data.
     * 
     * @param string $symbol
     * @param int $days
     * @param array $columns An array of columns to grab.
     * @return type
     */
    function get($symbol, $days, $columns)
    {
        if ($this->_db->shouldUpdate($symbol)) {
            $data = $this->_yfh->get($symbol);
            $this->_db->insert($symbol, $data);
            unset($data);
        }

        return $this->_db->select($symbol, $days, $columns);
    }

    /**
     * Return the result set as an object array.
     * 
     * @param SQLite3Result $result
     * 
     * @return array
     */
    function resultAsObjectArray($result)
    {
        $rtn = array();
        while ($row = Sqlite_Helper::fetchObject($result)) {
            $rtn[] = $row;
        }
        return $rtn;
    }

}

/**
 * Sqlite_Helper
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class Sqlite_Helper
{

    /**
     * Fetches a result row as an object.
     * 
     * @param SQLite3Result $result The result set.
     * @param object $objectType Specific object to return as.
     * 
     * @return object
     */
    public static function fetchObject($sqlite3result, $objectType = NULL)
    {
        $array = $sqlite3result->fetchArray();

        if ($array === false) {
            return false;
        }

        if (is_null($objectType)) {
            $object = new stdClass();
        } else {
            // does not call this class' constructor 
            $object = unserialize(sprintf('O:%d:"%s":0:{}', strlen($objectType), $objectType));
        }

        $reflector = new ReflectionObject($object);
        for ($i = 0; $i < $sqlite3result->numColumns(); $i++) {
            $name  = $sqlite3result->columnName($i);
            $value = $array[$name];

            try
            {
                $attribute = $reflector->getProperty($name);

                $attribute->setAccessible(TRUE);
                $attribute->setValue($object, $value);
            } catch (ReflectionException $e)
            {
                $object->$name = $value;
            }
        }

        return $object;
    }

}

/**
 * StrategyResult
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class StrategyResult
{

    /**
     *
     * @var string 
     */
    private $strategy;

    /**
     *
     * @var string 
     */
    private $date;

    /**
     *
     * @var string
     */
    private $symbol;

    /**
     *
     * @var float
     */
    public $probability_win;

    /**
     *
     * @var float
     */
    public $expectancy;

    /**
     *
     * @var mixed
     */
    public $uservalue;

    /**
     * StrategyResult constructor
     * 
     * @param string $strategy
     * @param string $symbol
     */
    function __construct($strategy, $symbol)
    {
        $this->strategy        = $strategy;
        $this->date            = strftime('%Y-%m-%d');
        $this->symbol          = $symbol;
        $this->probability_win = 0;
        $this->expectancy      = 0;
        $this->uservalue       = '';
    }

    /**
     * The insert query for the StrategyResults table.
     * 
     * @return string
     */
    function insertQuery()
    {
        $userValue = Sqlite3::escapeString($this->uservalue);
        $query     = "insert or replace into 'StrategyResults' 
            (strategy, date, symbol, probability_win, expectancy, uservalue )
            values 
            ('{$this->strategy}','{$this->date}','{$this->symbol}'
                ,{$this->probability_win},{$this->expectancy}
                    ,'{$userValue}') ";
        return $query;
    }

}

/**
 * StrategyResultsController
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class StrategyResultsController
{

    /**
     * SQLite Database Connection
     * 
     * @var connection
     */
    private $_db;

    /**
     * StrategyResultsController constructor
     */
    function __construct()
    {
        $this->_db = new SQLite3("db/new.sqlite");
        $this->createTable();
    }

    /**
     * Create the StrategyResults table.
     * 
     * @throws ErrorException
     */
    private function createTable()
    {
        $query = "create table if not exists 'StrategyResults' 
			( strategy TEXT
			, date DATE
			, symbol TEXT
            , probability_win FLOAT
            , expectancy FLOAT
            , uservalue TEXT
            , primary key (strategy, date, symbol))";
        if (!$this->_db->exec($query)) {
            throw new ErrorException("Could not create StrategyResults table.");
        }
    }

    /**
     * Insert into table.
     * 
     * @param StrategyResult $strategyResult
     */
    function insert($strategyResult)
    {
        if ($strategyResult instanceof StrategyResult) {
            $this->_db->exec($strategyResult->insertQuery());
        } else {
            throw new InvalidArgumentException("Argument must be of type StrategyResult");
        }
    }

    /**
     * Select from table.
     * 
     * @param string $strategy
     * @param string $date
     * @param string $symbol
     * 
     * @return SQLite3Result
     */
    function select($strategy, $date = NULL, $symbol = NULL)
    {
        $date   = (is_null($date)) ? '' : "and date = '" . $date . "'";
        $symbol = (is_null($symbol)) ? '' : " and symbol = '" . $symbol . "'";
        $query  = "
            select * 
            from 'StrategyResults' 
            where strategy = '{$strategy}'
            {$date}
            {$symbol}";
        return $this->_db->query($query);
    }

}

/**
 * YahFin_Field
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
final class YahFin_Field
{

    const DATE           = 0;
    const OPEN           = 1;
    const HIGH           = 2;
    const LOW            = 3;
    const CLOSE          = 4;
    const VOLUME         = 5;
    const ADJUSTED_CLOSE = 6;

    /**
     * default closing price to use
     */
    const _CLOSE = 6;

    /**
     * Get the nice name of the field.
     * 
     * @param YahFin_Field $var
     * 
     * @return string
     */
    public static function name($var)
    {
        switch ($var) {
            case YahFin_Field::DATE:
                return 'date';
            case YahFin_Field::OPEN:
                return 'open';
            case YahFin_Field::HIGH:
                return 'high';
            case YahFin_Field::LOW:
                return 'low';
            case YahFin_Field::CLOSE:
                return 'close';
            case YahFin_Field::VOLUME:
                return 'volume';
            case YahFin_Field::ADJUSTED_CLOSE:
                return 'adjclose';
        }
    }

}

/**
 * YahFin_Historical
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class YahFin_Historical
{

    /**
     * Retrieve the historical data from yahoo finanace.
     * 
     * @param string $symbol
     * 
     * @return array
     */
    function get($symbol)
    {
        $day                         = date('j');
        $mth                         = date('n') - 1;
        $yer                         = date('Y');
        $address                     = "http://ichart.finance.yahoo.com/table.csv?s={$symbol}&d={$mth}&e={$day}&f={$yer}&g=d&a=0&b=1&c=2006&ignore=.csv";
        $csvParser                   = CSVParserFactory::Create("object");
        $csvParser->IsFirstRowHeader = true;
        return $csvParser->Parse($address);
    }

}

/**
 * YahFin_Sqlite
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class YahFin_Sqlite
{

    /**
     * SQLite Database Connection
     * 
     * @var connection
     */
    private $_db;

    /**
     * YahFin_Sqlite constructor
     */
    function __construct()
    {
        $this->_db = new SQLite3("db/new.sqlite");
    }

    /**
     * Attempts to create a new table named $symbol.
     *  
     * @param string $symbol The name of the table.
     * 
     * @return string The name of the table on success
     * @throws ErrorException If the table could not be created.
     */
    private function createTable($symbol)
    {
        $symbol = strtoupper($symbol);
        $query  = "create table if not exists '{$symbol}' 
			(
			date DATE primary key asc
			, open FLOAT
			, high FLOAT
			, low FLOAT
			, close FLOAT
			, volume BIGINT
			, adjclose FLOAT)";
        if ($this->_db->exec($query)) {
            return $symbol;
        } else {
            throw new ErrorException("Could not create {$symbol} table.");
        }
    }

    /**
     * Inserts an array of rows into the database.  It verifies the table is 
     * created first.
     * 
     * @param string $symbol Table where the data is going to be inserted.
     * @param array $data Multidimentional array of row data.
     */
    function insert($symbol, $data)
    {
        // get the table name from createTable.
        $symbol = $this->createTable($symbol);

        // access variable due to space.
        $adjclose = 'Adj Close';

        // was anything inserted?
        $updated = false;

        // break the array into 400 row chunks due to a processing limitation 
        // in sqlite
        $chunks = array_chunk($data, 400);

        foreach ($chunks as $chunk) {
            // get the first row for the initial insert statement.
            $row = array_shift($chunk);

            $query = "insert or replace into '{$symbol}' ";
            $query .= " select '{$row->Date}' as 'date', ";
            $query .= " {$row->Open} as 'open', ";
            $query .= " {$row->High} as 'high', ";
            $query .= " {$row->Low} as 'low', ";
            $query .= " {$row->Close} as 'close', ";
            $query .= " {$row->Volume} as 'volume', ";
            $query .= " {$row->{$adjclose}} as 'adjclose'\n ";


            foreach ($chunk as $row) {
                $query .= "union select '{$row->Date}',{$row->Open},{$row->High},{$row->Low},{$row->Close},{$row->Volume},{$row->{$adjclose}}\n";
            }

            $updated = $this->_db->exec($query);
        }

        // Trigger the last_update timestamp.
        if ($updated) {
            $this->setLastUpdate($symbol);
        }
    }

    /**
     * Retreive data from the database.
     * 
     * @param string $symbol The symbol for the data.
     * @param int $days The number of days back. 0 for all data.
     * @param array $columns An array of columns to retrieve.
     * 
     * @return SQLite3Result 
     */
    function select($symbol, $days, $columns)
    {
        $symbol = strtoupper($symbol);
        $limit  = '';
        if ($days != 0) {
            $limit = "LIMIT {$days}";
        }

        $cols = array();
        foreach ($columns as $column) {
            $cols[]  = YahFin_Field::name($column);
        }
        $columns = implode(',', $cols);

        $query = "SELECT {$columns}
            FROM {$symbol}
            ORDER BY date desc
            {$limit}";
        return $this->_db->query($query);
    }

    /**
     * Sets the last_update column in TABLE_STATS to the current timestamp.
     * 
     * @param string $symbol Table that is being updated.
     * 
     * @return boolean  True on success; False on failure.
     */
    function setLastUpdate($symbol)
    {
        $symbol = strtoupper($symbol);
        $time   = time();
        $query  = "INSERT OR REPLACE INTO TABLE_STATS ('symbol','last_update') VALUES ('{$symbol}',{$time})";
        return $this->_db->exec($query);
    }

    /**
     * Compares todays date to the last_updated column in TABLE_STATS for 
     * $symbol.  If the table was last updated today, it will return FALSE.
     * 
     * @param string $symbol The table to test for.
     * 
     * @return boolean False if it was updated today.
     */
    function shouldUpdate($symbol)
    {
        $symbol      = strtoupper($symbol);
        $query       = "select last_update from TABLE_STATS where symbol = '{$symbol}'";
        $last_update = date('Y m d', $this->_db->querySingle($query));
        $today       = date('Y m d', time());

        // dont update if it was already updated today.
        if (strcmp($last_update, $today) == 0) {
            return false;
        } else {
            return true;
        }
    }

}

?>
