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
    

    function __construct($symbol)
    {
        $this->dao  = new MarketDataGateway();
        $this->setData($symbol, 0);
    }

    public function bbands($source, $period, $dev)
    {
        return trader_bbands($this->getPrices($source), $period, $dev, $dev, TRADER_MA_TYPE_SMA);
    }

    function count(){
        return count($this->data);
    }
    
    function getDay($key)
    {
        return $this->data[$key];
    }

    function getField($key, $field)
    {
        $name = YahFin_Field::name($field);
        return $this->data[$key]->{$name};
    }

    public function getPrices($field)
    {
        $data = array();
        $name  = YahFin_Field::name($field);
        $count = count($this->data);
        for ($i = 0; $i < $count; $i++) {
            $data[] = $this->data[$i]->{$name};
        }
        return $data;
    }

    public function rsi($source, $period = 14)
    {
        return trader_rsi($this->getPrices($source), $period);
    }

    public function setData($symbol, $days)
    {
        $result = $this->dao->get($symbol, $days);
        $this->data = array_reverse($this->dao->resultAsObjectArray($result));
    }
    
    public function sma($source, $period = 20)
    {
        return trader_sma($this->getPrices($source), $period);
    }

}

class MarketDataModelImpl
    extends MarketDataModel
{

    function __construct($symbol)
    {
        parent::__construct($symbol);
    }
}

?>
