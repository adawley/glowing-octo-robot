<?php

/**
 * MarketDataModel
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
require_once 'DataAccessObjects.php';

/**
 * MarketDataModel
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
abstract class MarketDataModel
{

    protected $dao, $data;

    function __construct()
    {
        $this->dao = new MarketDataGateway();
    }

    /**
     * Get bollinger bands
     * @param YahFin_Field $source The price type to use.
     * @param int $period Number of period
     * @param int $dev Band deviations
     * @return array
     */
    public function bbands($source, $period, $dev)
    {
        return trader_bbands($this->getPrices($source), $period, $dev, $dev, TRADER_MA_TYPE_SMA);
    }

    /**
     * Number of days
     * 
     * @return int
     */
    function count()
    {
        return count($this->data);
    }
    
    function filterField(&$item, $key, $field)
    {        
        $item = $item->{$field};
    }

    /**
     * Get price array for a single day.
     * 
     * @param int $key Day (location in array).
     * 
     * @return array
     */
    function getDay($key)
    {
        return $this->data[$key];
    }

    /**
     * Get the field for a specific day.
     * 
     * @param int $key Day (location in array).
     * @param YahFin_Field $field The price to use.
     * 
     * @return mixed
     */
    function getField($key, $field)
    {
        $name = YahFin_Field::name($field);
        return $this->data[$key]->{$name};        
    }
    
    /**
     * 
     * @param int $index Data index
     * @param int $days Days back.
     * @param YahFin_Field $field Specific field, if null all are returned.
     */
    function getBackPeriod($index, $days, $field = NULL)
    {
        $offset = 0;
        $length = 0;
        
        if($index >= $days){        
            $offset = ($index-$days) + 1;
            $length = $days;
        }else{
            $offset = 0;
            $length = $index;
        }
        
        $data = array_slice($this->data, $offset, $length, true);
        
        if(is_null($field)){
            return $data;
        }else {
            array_walk($data, array($this,'filterField'), YahFin_Field::name($field));
            return $data;
        }
    }

    /**
     * Get all the field in the data set.
     * 
     * @param YahFin_Field $field Field to use.
     * @param int $offset If offset is non-negative, the sequence will start at 
     * that offset in the array. If offset is negative, the sequence will start 
     * that far from the end of the array.
     * @param int $length If length is given and is positive, then the sequence
     *  will have up to that many elements in it. If the array is shorter than 
     * the length, then only the available array elements will be present. If 
     * length is given and is negative then the sequence will stop that many 
     * elements from the end of the array. If it is omitted, then the sequence 
     * will have everything from offset up until the end of the array.
     * 
     * @return array
     */
    public function getPrices($field, $offset = 0, $length = NULL)
    {
        $data =  array_slice($this->data, $offset, $length, true);
        array_walk($data, array($this,'filterField'), YahFin_Field::name($field));        
        return $data;
        
    }

    public function highest($index, $days, $field = YahFin_Field::HIGH)
    {
        $data = $this->getBackPeriod($index, $days, $field);
        $count = count($data);
                
        if($count > 1){
            $result = trader_max($data, $count);
            return array_pop($result);
        }else{
            return array_pop($data);
        }
    }
    
    public function lowest($index, $days, $field = YahFin_Field::LOW)
    {
        $data = $this->getBackPeriod($index, $days, $field);
        $count = count($data);
                
        if($count > 1){
            $result = trader_min($data, $count);
            return array_pop($result);
        }else{
            return array_pop($data);
        }
    }
        
    /**
     * Relative Strength Index
     * 
     * @param YahFin_Field $source Price to use.
     * @param int $period Number of period.
     * 
     * @return array
     */
    public function rsi($source, $period = 14)
    {
        return trader_rsi($this->getPrices($source), $period);
    }

    /**
     * Set data.
     * 
     * @param string $symbol Symbol to use.
     * @param int $days Number of days back.
     * @param string $columns Comma seperated list of columns to use.
     */
    public function setData($symbol, $days, $columns = '*')
    {        
        $result     = $this->dao->get($symbol, $days, $columns);
        $this->data = array_reverse($this->dao->resultAsObjectArray($result));
    }

    /**
     * Simple Moving Average
     * 
     * @param YahFin_Field $source The price to use.
     * @param int $period Number of period.
     * 
     * @return array
     */
    public function sma($source, $period = 20)
    {
        return trader_sma($this->getPrices($source), $period);
    }

}

class MarketDataModelImpl
    extends MarketDataModel
{

    function __construct($symbol = null)
    {
        parent::__construct();
        if (!is_null($symbol)) {
            $this->setData($symbol, 0);
        }
    }

}

?>
