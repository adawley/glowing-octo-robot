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
        $report = new StrategyReport($this->orders);
        return $report;
    }

}

abstract class StockStrategy
    extends Strategy
{

    protected $model;

    function __construct()
    {
        parent::__construct();
        $this->model = new MarketDataModelImpl();
    }

    protected function setData($symbol, $columns = '*')
    {
        parent::__construct();     
        $cols = '';
        foreach ($columns as $column){
            $cols .= YahFin_Field::name($column).',';
        }
        
        $this->model->setData($symbol, 0, trim($cols, ','));
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

    protected function inPosition()
    {
        $order = $this->currentOrder();
        return $order->isOrdered();
    }

}

class StrategyReport
{

    /**
     * Net Profit.
     * @var float
     */
    public $netProfit;

    /**
     * Total profit of winning trades.
     * @var float 
     */
    public $winningTradeProfit;

    /**
     * Total loss of losing trades.
     * @var float
     */
    public $losingTradeLoss;

    /**
     * Average profit of a winning trade.
     * @var float
     */
    public $averageWinningTradeProfit;

    /**
     * Average loss of a losing trade.
     * @var float
     */
    public $averageLosingTradeLoss;

    /**
     * Total number of closed trades.
     * @var int
     */
    public $totalTradeCount;

    /**
     * Total number of winning trades (profit > 0).
     * @var float
     */
    public $winningTradeCount;

    /**
     * Total number of losing trades (profit < 0).
     * @var int 
     */
    public $losingTradeCount;

    /**
     * Initial portfolio value.
     * @var float
     */
    public $portfolioValue;

    /**
     * Net return percent.
     * @var float
     */
    public $returnPercent;

    /**
     * Maximum portfolio drawdown.
     * @var float
     */
    public $maxDrawdown;

    /**
     * All ordered and closed orders.
     * @var array
     */
    public $orderLog;

    /**
     * 
     * @param Order $orders
     */
    function __construct(&$orders)
    {
        $this->netProfit                 = 0.0;
        $this->winningTradeProfit        = 0.0;
        $this->losingTradeLoss           = 0.0;
        $this->averageWinningTradeProfit = 0.0;
        $this->averageLosingTradeLoss    = 0.0;
        $this->totalTradeCount           = 0;
        $this->winningTradeCount         = 0;
        $this->losingTradeCount          = 0;
        $this->portfolioValue            = 10000.0;
        $this->returnPercent             = 0.0;
        $this->maxDrawdown               = 0.0;
        $this->orderLog                  = '';

        $this->initialize($orders);
    }

    function __toString()
    {
        $out = '';
        $out .= "Portfolio:\t$" . $this->numfmt($this->portfolioValue) . "\n";
        $out .= "Net Profit:\t$" . $this->numfmt($this->netProfit) . "\n";
        $out .= "Return:\t\t" . $this->numfmt($this->returnPercent) . "%" . "\n";
        $out .= "Max Drawdown:\t$" . $this->numfmt($this->maxDrawdown) . "\n";
        $out .= "Trades:\t" . $this->totalTradeCount . "\n";
        $out .= "Winning: " . $this->winningTradeCount . "\tLosing:  " . $this->losingTradeCount . "\n";
        $out .= "Net P/L: $" . $this->numfmt($this->winningTradeProfit) . "\tNet P/L: $" . $this->numfmt($this->losingTradeLoss) . "\n";
        $out .= "Avg P/L: $" . $this->numfmt($this->averageWinningTradeProfit) . "\tAvg P/L: $" . $this->numfmt($this->averageLosingTradeLoss) . "\n";

        return $out;
    }

    /**
     * 
     * @param Order $orders
     */
    private function initialize(&$orders)
    {
        foreach ($orders as $order) {
            if ($order->isClosed()) {
                $profit = $order->profit();

                // net profit
                $this->netProfit += $profit;

                // max drawdown %
                if ($this->netProfit < $this->maxDrawdown) {
                    $this->maxDrawdown = $this->netProfit;
                }

                // total # of trades
                $this->totalTradeCount++;

                // winning trades
                if ($profit > 0) {

                    // # winning trades
                    $this->winningTradeCount++;

                    // net profit of winning trades
                    $this->winningTradeProfit += $profit;
                }

                // losing trades
                if ($profit < 0) {

                    // # losing trades
                    $this->losingTradeCount++;

                    // net loss of losing trades
                    $this->losingTradeLoss -= $profit;
                }

                $this->orderLog .= $order . " PNL: {$profit}\n";
            }

            if ($order->isOrdered()) {
                $this->orderLog .= $order . "\n";
            }
        }

        // return%
        if ($this->netProfit != 0) {
            $this->returnPercent = ($this->netProfit / $this->portfolioValue) * 100.0;
        }

        // average profit of winnign trades
        if ($this->winningTradeCount != 0) {
            $this->averageWinningTradeProfit = $this->winningTradeProfit / $this->winningTradeCount;
        }

        // average loss of losing trades
        if ($this->losingTradeCount != 0) {
            $this->averageLosingTradeLoss = $this->losingTradeLoss / $this->losingTradeCount;
        }
    }

    public function numfmt($number)
    {
        if (is_float($number + 1)) {
            return number_format($number, 2);
        } else {
            return $number;
        }
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
        $count = $this->model->count();
        for ($i = 0; $i < $count; $i++) {
            if (isset($this->bbands[0][$i])) {
                $close = $this->model->getField($i, YahFin_Field::_CLOSE);
                $date  = $this->model->getField($i, YahFin_Field::DATE);
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

    private $symbols, $reports;

    function __construct($symbols)
    {
        parent::__construct();
        $this->symbols = $symbols;
    }

    function test()
    {
        $columns = array(YahFin_Field::DATE,  YahFin_Field::_CLOSE);
        foreach ($this->symbols as $symbol) {
            $startTime = microtime(true);
            $this->setData($symbol, $columns);
            $dataTime = microtime(true);
            $rsi = $this->model->rsi(YahFin_Field::_CLOSE, 14);
            $configTime = microtime(true);
            $count     = $this->model->count();
            for ($i = 0; $i < $count; $i++) {
                if (isset($rsi[$i])) {                    
                    if ($rsi[$i] < 30 && !$this->inPosition()) {
                        $this->buyOrder($i, YahFin_Field::_CLOSE);
                    } elseif ($rsi[$i] > 50 && $this->inPosition()) {
                        $this->sellOrder($i, YahFin_Field::_CLOSE);
                    }
                }
            }
            $processTime = microtime(true);
            $report = parent::report();
            $endTime = microtime(true);
            $report->dataTime = $dataTime - $startTime;
            $report->configTime = $configTime - $dataTime;
            $report->processTime = $processTime - $configTime;
            $report->totalTime = $endTime - $startTime;
            $this->reports[$symbol] = $report;
        }
        
        
    }
    
    function report()
    {
        return $this->reports;
    }

}
?>

