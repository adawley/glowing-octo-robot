<?php

/**
 * Description of yahfin_sqlite
 *
 * @author rob
 */
class yahfin_sqlite {

	private $_db;

	function __construct() {
		$this->_db = new SQLite3("new.db");
	}

	function createTable($symbol) {
		$symbol = strtoupper($symbol);
		$query = "create table if not exists '{$symbol}' 
			(
			date DATE primary key asc
			, open FLOAT
			, high FLOAT
			, low FLOAT
			, close FLOAT
			, volume BIGINT
			, adjclose FLOAT)";
		$this->_db->exec($query);
	}

	/**
	 * 
	 */
	function insert($symbol, $data) {
		$chunks = array_chunk($data, 400);
		foreach ($chunks as $chunk) {
			$row = array_shift($chunk);
			$symbol = strtoupper($symbol);
			$this->createTable($symbol);
			$adjclose = 'Adj Close';
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

			$this->_db->exec($query);
		}
	}

}

class SymbolData {

	public $date = '';
	public $open = 0;
	public $high = 0;
	public $low = 0;
	public $close = 0;
	public $volume = 0;
	public $adjclose = 0;

}

class YahooFinance_Historical {

	function get($symbol) {
		$day = date('j');
		$mth = date('n') - 1;
		$yer = date('Y');
		$address = "http://ichart.finance.yahoo.com/table.csv?s={$symbol}&d={$mth}&e={$day}&f={$yer}&g=d&a=0&b=1&c=2000&ignore=.csv";
		$jsonParser = CSVParserFactory::Create("json");
		$jsonParser->IsFirstRowHeader = true;
		return json_decode($jsonParser->Parse($address));
	}

}

class CSVParserFactory {

	public static function Create($type, $maxLenght = 1000, $separator = ',') {
		switch ($type) {
			case 'json':
				return new CSVToJsonParser($maxLenght, $separator);
			case 'xml':
				return new CSVToXmlParser($maxLenght, $separator);
		}
	}

}

class CSVToJsonParser extends CSVParserBase {

	protected function ProcessArray($array) {

		if ($this->IsFirstRowHeader) {
			$this->HeaderArray = $array[0];
			$array = $this->ToAssocativeArray($array);
		}

		return json_encode($array);
	}

	private function ToAssocativeArray($array) {

		$columnCount = count($array[0]);
		$temp = array();

		for ($i = 1; $i < count($array); $i++) {
			$item = array();

			for ($n = 0; $n < $columnCount; $n++) {
				$columnName = $this->HeaderArray[$n];
				$item[$columnName] = $array[$i][$n];
			}

			$temp[] = $item;
			$item = null;
		}
		return $temp;
	}

}

abstract class CSVParserBase {

	protected $MaxLenght = 0;
	protected $Separator = '';
	public $HeaderArray = null;
	public $IsFirstRowHeader = false;

	function __construct($maxLenght = 1000, $separator = ',') {
		$this->MaxLenght = $maxLenght;
		$this->Separator = $separator;
	}

	public function Parse($csvData) {
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

	protected function StripString($contents) {
		return preg_replace(
										'/\s+/', '', $contents);
		;
	}

}

//$a = new yahfin_sqlite();
//$a->createTable('spy');
$yfh = new YahooFinance_Historical();
$data = $yfh->get('spy');
$db = new yahfin_sqlite();
$db->insert('spy', $data);
?>
