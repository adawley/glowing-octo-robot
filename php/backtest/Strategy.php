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

    /**
     * Returns a report of the current strategy.
     * 
     * @return \StrategyReport
     */
    public function report()
    {
        $report = new StrategyReport($this->orders);
        return $report;
    }

}

abstract class StockStrategy
    extends Strategy
{

    /**
     *
     * @var MarketDataModelImpl 
     */
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
        foreach ($columns as $column) {
            $cols .= YahFin_Field::name($column) . ',';
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
     * The average profit per dollar at risk.
     * @var float 
     */
    public $expectancy;

    /**
     * User defined data.
     * @var mixed 
     */
    public $userData;

    /**
     * Likleyhood of a winning trade.
     * @var float
     */
    public $winningTradeProbability;

    /**
     * StrategyReport constructor.
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
        $this->expectancy                = 0;
        $this->winningTradeProbability   = 0;

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
        $out .= "Expectancy: " . $this->numfmt($this->expectancy) . "\n";
        $out .= "Winning Trade Pro.: " . $this->numfmt($this->winningTradeProbability) . "\n";

        return $out . print_r($this->userData, true);
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
								
                //TODO: (avg) duration

                $this->orderLog .= $order . "\n";
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

        // winning trade probability
        if ($this->totalTradeCount != 0) {
            $this->winningTradeProbability = $this->winningTradeCount / $this->totalTradeCount;
        }

        // expectancy
        $pLoss            = $this->losingTradeCount / $this->totalTradeCount;
        $this->expectancy = ($this->winningTradeProbability * $this->averageWinningTradeProfit) - ($pLoss * $this->averageLosingTradeLoss);

        //TODO: 10 trade rolling expectancy?
    }

    public function numfmt($member)
    {
        $number = $this->{$member};
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

    /**
     * Symbols to process.
     * @var array
     */
    private $symbols;

    /**
     * Results from test.
     * @var array
     */
    private $reports;
    private $field;

    function __construct($symbols)
    {
        parent::__construct();
        $this->field = YahFin_Field::_CLOSE;
        $this->symbols = $symbols;
    }

    function buyTrigger($key)
    {
        $close = $this->model->getField($key, $this->field);
        return ($close < $this->sma3[$key] // the close is below the sma3
            && $close < $this->sma5[$key] // and the close is below the sma5
            && $close < $this->sma7[$key] // and the close is below the sma7
            );
    }

    function report()
    {
        return $this->reports;
    }
    
    function sellTrigger($key)
    {
        $close = $this->model->getField($key, $this->field);
        return ($close > $this->sma3[$key]   // the close is above the 3sma
            || $close > $this->sma5[$key] // or the close is above the 5sma
            || $close > $this->sma7[$key] // or the close is above the 7sma
            );
    }

    function test()
    {
        $columns = array(YahFin_Field::DATE, $this->field);
        foreach ($this->symbols as $symbol) {
            $this->setData($symbol, $columns);
            $this->sma3 = $this->model->sma($this->field, 3);
            $this->sma5 = $this->model->sma($this->field, 5);
            $this->sma7 = $this->model->sma($this->field, 7);
            $count      = $this->model->count();
            for ($i = 0; $i < $count; $i++) {
                if (isset($this->sma3[$i]) && isset($this->sma5[$i]) && isset($this->sma7[$i])) {
                    if ($this->buyTrigger($i) && !$this->inPosition()) {
                        $this->buyOrder($i, $this->field);
                    } elseif ($this->sellTrigger($i) && $this->inPosition()) {// and we are in a position
                        $this->sellOrder($i, $this->field);
                    }
                }
            }
            $report = parent::report();

            $last3                      = array_slice($this->sma3, -1);
            $last5                      = array_slice($this->sma5, -1);
            $last7                      = array_slice($this->sma7, -1);
            $report->userData             = new stdClass();
            $report->userData->last       = $last3[0].','.$last5[0].','.$last7[0];
            $report->userData->buyTrigger = ($this->buyTrigger($count-1) && $report->expectancy > 0) ? 'Yes' : '-';

            $this->reports[$symbol] = $report;
        }
    }

}

class BollingerStrategy
    extends StockStrategy
{

    private $bbands, $upper, $mean, $lower;

    function __construct($symbol)
    {
        parent::__construct();
        $this->bbands = $this->model->bbands(YahFin_Field::_CLOSE, 20, 2);
        $this->upper  = &$this->bbands[0];
        $this->mean   = &$this->bbands[1];
        $this->lower  = &$this->bbands[2];
    }

    function test()
    {
        $columns = array(YahFin_Field::DATE, YahFin_Field::_CLOSE);
        foreach ($this->symbols as $symbol) {
            $this->setData($symbol, $columns);
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
            $report = parent::report();

            $lastRsi                      = array_slice($rsi, -1);
            $report->userData             = new stdClass();
            $report->userData->last       = $lastRsi[0];
            $report->userData->buyTrigger = ($this->buyTrigger($lastRsi[0]) && $report->expectancy > 0) ? 'Yes' : '-';

            $this->reports[$symbol] = $report;
        }
    }

}

class RsiStrategy
    extends StockStrategy
{

    /**
     * Symbols to process.
     * @var array
     */
    private $symbols;

    /**
     * Results from test.
     * @var array
     */
    private $reports;

    /**
     * RsiStrategy constructor
     * 
     * @param array $symbols Array of symbols to test, or null to scan for 
     * symbols to use.
     */
    function __construct($symbols)
    {
        parent::__construct();
        if (is_null($symbols)) {
            $this->symbols = $this->scanner();
        } else {
            $this->symbols = $symbols;
        }
    }

    function buyTrigger($rsi)
    {
        return ($rsi < 30);
    }

    function scanner()
    {
        $finviz  = new Finviz_Helper();
        $filters = array('idx_sp500', 'ta_rsi_os30');
        $scan   = $finviz->get($filters);
        $result = array();
        foreach ($scan as $row) {
            $result[] = $row->{Finviz_Field::TICKER};
        }
        return $result;
    }

    function sellTrigger($rsi)
    {
        return ($rsi > 50);
    }

    function test()
    {
        $columns = array(YahFin_Field::DATE, YahFin_Field::_CLOSE);
        foreach ($this->symbols as $symbol) {
            $this->setData($symbol, $columns);
            $rsi   = $this->model->rsi(YahFin_Field::_CLOSE, 14);
            $count = $this->model->count();
            for ($i = 0; $i < $count; $i++) {
                if (isset($rsi[$i])) {
                    if ($this->buyTrigger($rsi[$i]) && !$this->inPosition()) {
                        $this->buyOrder($i, YahFin_Field::_CLOSE);
                    } elseif ($this->sellTrigger($rsi[$i]) && $this->inPosition()) {
                        $this->sellOrder($i, YahFin_Field::_CLOSE);
                    }
                }
            }
            $report = parent::report();

            $lastRsi                      = array_slice($rsi, -1);
            $report->userData             = new stdClass();
            $report->userData->last       = $lastRsi[0];
            $report->userData->buyTrigger = ($this->buyTrigger($lastRsi[0]) && $report->expectancy > 0) ? 'Yes' : '-';

            $this->reports[$symbol] = $report;
        }
    }

    function report()
    {
        return $this->reports;
    }

}

class TMTTStrategy
    extends StockStrategy
{

    function buyTrigger($index)
    {
        // 3 day pullback
        // close > yesterdays high
        // stop below yesterdays low
    }

    function sellTrigger($index)
    {
        // price reaches highest point of 3 day pullback
    }
}


class RoboStrategy
	extends StockStrategy
{
	function buyZone(){
		// rsi buy trigger = true
	}
	function buyTrigger(){
		// TMTT buy trigger = true
	}
}

?>

