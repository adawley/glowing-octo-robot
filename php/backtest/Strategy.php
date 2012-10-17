<?php

/**
 * Strategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
require_once 'MarketDataModel.php';
require_once 'Order.php';

/**
 * Strategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
abstract class Strategy
{

    /**
     *
     * @var Order 
     */
    private $orders;
    
     /**
      *
      * @var int
      */
    private $currentOrder;
    
    /**
     *
     * @var int
     */
    private $orderCount;

    function __construct()
    {
        $this->orders = array(new Order());
        $this->currentOrder = 0;
        $this->orderCount   = 0;
    }

    protected function addOrder(&$order)
    {
        $this->orders[$this->orderCount] = $order;
        $this->currentOrder              = $this->orderCount;
        $this->orderCount++;

        return $this->currentOrder;
    }

    /**
     * Get the most recent order.
     * 
     * @return Order
     */
    protected function currentOrder()
    {
        return $this->orders[$this->currentOrder];
    }

    protected function totalProfit()
    {
        $total = 0;
        foreach ($this->orders as $order) {
            if ($order->isClosed()) {
                $total += $order->profit();
            }
        }        
        return $total;
    }
    
    public function report()
    {
        $total = 0;
        $out = '';
        foreach ($this->orders as $order) {
            if ($order->isClosed() || $order->isOrdered()) {
                $total += $order->profit();
                $out .= $order."\n";
            }
        }        
        $out .= "Total P/L:\t".$total;
        return $out;
    }

}

abstract class StockStrategy
    extends Strategy
{

    protected $model, $marketData;

    function __construct($symbol)
    {
        parent::__construct();
        $this->model = new MarketDataModelImpl($symbol);
    }

    protected function buyOrder($key, $field)
    {
        $day   = $this->model->getDay($key);
        $name  = YahFin_Field::name($field);
        $order = new Order();
        $order->buy($day->date, $day->{$name});
        $this->addOrder($order);
    }

    protected function sellOrder($key, $field)
    {
        $day   = $this->model->getDay($key);
        $name  = YahFin_Field::name($field);
        $order = $this->currentOrder();
        $order->sell($day->date, $day->{$name});        
    }
    
    protected function inPosition(){
        $order = $this->currentOrder();
        return $order->isOrdered();
    }

}



class ThreeFiveSevenStrategy
    extends StockStrategy
{

    private $sma3,
        $sma5,
        $sma7;

    function __construct($symbol)
    {
        parent::__construct($symbol);
        $this->sma3 = $this->model->sma(YahFin_Field::_CLOSE, 3);
        $this->sma5 = $this->model->sma(YahFin_Field::_CLOSE, 5);
        $this->sma7 = $this->model->sma(YahFin_Field::_CLOSE, 7);
    }

    function test()
    {
        $count = $this->model->count();
        for ($i = 0; $i < $count; $i++) {
            if (isset($this->sma3[$i]) && isset($this->sma5[$i]) && isset($this->sma7[$i])) {
                $close = $this->model->getField($i, YahFin_Field::_CLOSE);

                if ($close < $this->sma3[$i] // the close is below the sma3
                    && $close < $this->sma5[$i] // and the close is below the sma5
                    && $close < $this->sma7[$i] // and the close is below the sma7
                    && !$this->inPosition()) { // and we are not in a position
                    $this->buyOrder($i, YahFin_Field::_CLOSE);
                } elseif (
                    ($close > $this->sma3[$i]   // the close is above the 3sma
                    || $close > $this->sma5[$i] // or the close is above the 5sma
                    || $close > $this->sma7[$i]) // or the close is above the 7sma
                    && $this->inPosition()) {// and we are in a position
                    $this->sellOrder($i, YahFin_Field::_CLOSE);
                }
            }
        }
        echo $this->report();
    }

}

class BollingerStrategy
    extends StockStrategy
{

    private $bbands, $upper, $mean, $lower;

    function __construct($symbol)
    {
        parent::__construct($symbol);
        $this->bbands = $this->model->bbands(YahFin_Field::_CLOSE, 20, 2);
        $this->upper  = &$this->bbands[0];
        $this->mean   = &$this->bbands[1];
        $this->lower  = &$this->bbands[2];
    }

    function test()
    {
        echo "<pre>";
        $count = $this->model->count();
        for ($i = 0; $i < $count; $i++) {
            if (isset($this->bbands[0][$i])) {
                $close = $this->model->getField($i, YahFin_Field::_CLOSE);
                $date = $this->model->getField($i, YahFin_Field::DATE);
                if ($close > $this->upper[$i]) {
                    echo "Breakout: " . $date . "\n";
                }
                if ($close < $this->lower[$i]) {
                    echo "Breakdown: " . $date . "\n";
                }
            }
        }
    }

}

class RsiStrategy
    extends StockStrategy
{

    private $rsi;

    function __construct($symbol)
    {
        parent::__construct($symbol);
        $this->rsi = $this->model->rsi(YahFin_Field::_CLOSE, 14);        
    }

    function test()
    {        
        $count = $this->model->count();
        for ($i = 0; $i < $count; $i++) {
            if (isset($this->rsi[$i])) {
                $rsi = $this->rsi[$i];                
                if ($rsi < 30 && ! $this->inPosition()) {
                    $this->buyOrder($i, YahFin_Field::_CLOSE);
                } elseif ($rsi > 50 && $this->inPosition()) {
                    $this->sellOrder($i, YahFin_Field::_CLOSE);
                }
            }
        }     
    }

}



?>
