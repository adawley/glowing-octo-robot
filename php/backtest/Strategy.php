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

    /**
     * Strategy constructor
     */
    function __construct()
    {
        $this->orders = array(new Order());
        $this->currentOrder = 0;
        $this->orderCount   = 0;
    }

    /**
     * Add an order.
     * 
     * @param Order $order The order to add.
     * 
     * @return int
     */
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

    /**
     * Get the total profit from the closed orders.
     * 
     * @return float
     */
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
     * @param array $symbols Array of symbols.
     * 
     * @return StrategyReport
     */
    public function report($symbols)
    {
        $report = new StrategyReport($this->orders, $symbols);
        return $report;
    }

}

/**
 * StockStrategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
abstract class StockStrategy
    extends Strategy
{

    /**
     *
     * @var MarketDataModelImpl 
     */
    protected $model;

    /**
     * StockStrategy constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->model = new MarketDataModelImpl();
    }

    /**
     * Set data.
     * 
     * @param string $symbol Symbol to use.
     * @param array $columns Columns to use.
     */
    protected function setData($symbol, $columns)
    {
        parent::__construct();

        $this->model->setData($symbol, 0, $columns);
    }

    /**
     * Add a buy order.
     * 
     * @param int $index
     * @param float $price
     */
    protected function buyOrder($index, $price)
    {
        $day   = $this->model->getDay($index);
        $order = new Order();
        $order->buy($day->date, $price);
        $this->addOrder($order);
    }

    /**
     * Add a sell order.
     * 
     * @param int $index
     * @param float $price
     */
    protected function sellOrder($index, $price)
    {
        $day   = $this->model->getDay($index);
        $order = $this->currentOrder();
        $order->sell($day->date, $price);
    }

    /**
     * Is the current order active.
     * 
     * @return bool
     */
    protected function inPosition()
    {
        $order = $this->currentOrder();
        return $order->isOrdered();
    }

}

/**
 * StrategyReport
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
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
     * Average duration of a trade.
     * @var int
     */
    public $averageDuration;

    /**
     * Symbols used in report.
     * @var array
     */
    private $symbols;

    /**
     * StrategyReport constructor.
     * 
     * @param Order $orders
     */
    function __construct(&$orders, $symbols)
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
        $this->averageDuration           = 0;
        $this->symbols                   = $symbols;

        $this->initialize($orders);
    }

    /**
     * 
     * @return string
     */
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
        $out .= "Avg. Duration (days): " . $this->numfmt($this->averageDuration) . "\n";

        return $out . print_r($this->userData, true);
    }

    /**
     * Get the symbols used in this report.
     * 
     * @return array
     */
    public function getSymbols()
    {
        return $this->symbols;
    }

    /**
     * Initialize all of the orders.
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

                // duration
                $this->averageDuration += $order->duration();

                // log the order
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


        if ($this->totalTradeCount != 0) {

            // winning trade probability
            $this->winningTradeProbability = $this->winningTradeCount / $this->totalTradeCount;

            // expectancy
            $pLoss            = $this->losingTradeCount / $this->totalTradeCount;
            $this->expectancy = ($this->winningTradeProbability * $this->averageWinningTradeProfit) - ($pLoss * $this->averageLosingTradeLoss);

            // average duration
            $this->averageDuration = $this->averageDuration / $this->totalTradeCount;
        }

        //TODO: 10 trade rolling expectancy?
    }

    /**
     * Number format helper to reduce decimal places.
     * 
     * @param string $member The class member to format.
     * 
     * @return string
     */
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

/**
 * ThreeFiveSevenStrategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class ThreeFiveSevenStrategy
    extends StockStrategy
{

    /**
     * 3 period sma
     * @var array 
     */
    private $sma3;

    /**
     * 5 period sma
     * @var array 
     */
    private $sma5;

    /**
     * 5 period sma
     * @var array 
     */
    private $sma7;

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
     * YahFin default field.
     * @var YahFin_Field
     */
    private $field;

    /**
     * ThreeFiveSevenStrategy constructor
     * 
     * @param array $symbols
     */
    function __construct($symbols)
    {
        parent::__construct();
        $this->field   = YahFin_Field::_CLOSE;
        $this->symbols = $symbols;
    }

    /**
     * Check if the current index is a buy.
     * 
     * @param int $index
     * 
     * @return mixed False if not a buy, or price to buy it at.
     */
    function buyTrigger($index)
    {
        $close = $this->model->getField($index, $this->field);
        if ($close < $this->sma3[$index] // the close is below the sma3
            && $close < $this->sma5[$index] // and the close is below the sma5
            && $close < $this->sma7[$index] // and the close is below the sma7
        ) {
            return $close;
        }
        return false;
    }

    /**
     * Get the reports array.
     * 
     * @return array
     */
    function reports()
    {
        return $this->reports;
    }

    /**
     * Check if the current index is a sell.
     * 
     * @param int $index
     * 
     * @return boolean False if not a sell, or price to sell it at.
     */
    function sellTrigger($index)
    {
        $close = $this->model->getField($index, $this->field);
        if ($close > $this->sma3[$index]   // the close is above the 3sma
            || $close > $this->sma5[$index] // or the close is above the 5sma
            || $close > $this->sma7[$index] // or the close is above the 7sma
        ) {
            return $close;
        }
        return false;
    }

    /**
     * Test the strategy and generate reports.
     */
    function test()
    {
        $columns = array(YahFin_Field::DATE, $this->field);
        foreach ($this->symbols as $symbol) {
            $this->setData($symbol, $columns);
            $this->sma3 = $this->model->sma($this->field, 3);
            $this->sma5 = $this->model->sma($this->field, 5);
            $this->sma7 = $this->model->sma($this->field, 7);
            $count      = $this->model->count();
            $keys       = array_keys($this->sma7);
            $start      = array_shift($keys);
            for ($i = $start; $i < $count; $i++) {
                if ($this->inPosition()) {
                    $val = $this->sellTrigger($i);
                    if ($val !== false) {
                        $this->sellOrder($i, $val);
                    }
                } else {
                    $val = $this->buyTrigger($i);
                    if ($val !== false) {
                        $this->buyOrder($i, $val);
                    }
                }
            }
            $report = parent::report(array($symbol));

            $last3                        = array_slice($this->sma3, -1);
            $last5                        = array_slice($this->sma5, -1);
            $last7                        = array_slice($this->sma7, -1);
            $report->userData             = new stdClass();
            $report->userData->last       = $last3[0] . ',' . $last5[0] . ',' . $last7[0];
            $report->userData->buyTrigger = ($this->buyTrigger(array_pop($keys)) !== false && $report->expectancy > 0) ? 'Yes' : '-';

            $this->reports[$symbol] = $report;
        }
    }

}

/**
 * BollingerStrategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class BollingerStrategy
    extends StockStrategy
{

    /**
     * All bands
     * @var array
     */
    private $bbands;

    /**
     * The upper band.
     * @var array
     */
    private $upper;

    /**
     * The mean band.
     * @var array
     */
    private $mean;

    /**
     * The lower band.
     * @var array
     */
    private $lower;

    /**
     * Symbols
     * @var array
     */
    private $symbols;

    /**
     * BollingerStrategy constructor.
     * 
     * @param array $symbols symbols to use.
     */
    function __construct($symbols)
    {
        parent::__construct();
        $this->symbols = $symbols;
        $this->bbands  = $this->model->bbands(YahFin_Field::_CLOSE, 20, 2);
        $this->upper   = &$this->bbands[0];
        $this->mean    = &$this->bbands[1];
        $this->lower   = &$this->bbands[2];
    }

    /**
     * Test the strategy and generate reports.
     */
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
            $report = parent::report(array($symbol));

            $lastRsi                      = array_slice($rsi, -1);
            $report->userData             = new stdClass();
            $report->userData->last       = $lastRsi[0];
            $report->userData->buyTrigger = ($this->buyTrigger($lastRsi[0]) && $report->expectancy > 0) ? 'Yes' : '-';

            $this->reports[$symbol] = $report;
        }
    }

}

/**
 * RsiStrategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
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
     *
     * @var array
     */
    private $rsi;

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

    /**
     * Add a report to the reports array and update the strategy results table.
     * 
     * @param StrategyReport $report
     */
    function addReport($report)
    {
        $symbols                    = $report->getSymbols();
        $this->reports[$symbols[0]] = $report;
        $rslts                      = new StrategyResultsController();
        $rslt                       = new StrategyResult('RSI', $symbols[0]);
        $rslt->uservalue            = json_encode($report);
        $rslts->insert($rslt);
    }

    /**
     * Check if the current index is a buy.
     * 
     * @param int $index
     * 
     * @return mixed False if not a buy. Buy price if true.
     */
    function buyTrigger($index)
    {
        if ($this->rsi[$index] < 30) {
            return $this->model->getField($index, YahFin_Field::_CLOSE);
        }

        return false;
    }

    /**
     * Get the reports array.
     * 
     * @return array
     */
    function reports()
    {
        return $this->reports;
    }

    /**
     * Search for symbols (candidates)
     * 
     * @return array
     */
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

    /**
     * Check if the current index is a sell.
     * 
     * @param int $index
     * 
     * @return mixed False if not a sell.  Sell price if true.
     */
    function sellTrigger($index)
    {
        if ($this->rsi[$index] > 50) {
            return $this->model->getField($index, YahFin_Field::_CLOSE);
        }

        return false;
    }

    /**
     * Test the strategy and generate reports.
     */
    function test()
    {
        $columns = array(YahFin_Field::DATE, YahFin_Field::_CLOSE);
        foreach ($this->symbols as $symbol) {
            $this->setData($symbol, $columns);
            $this->rsi = $this->model->rsi(YahFin_Field::_CLOSE, 14);
            $count     = $this->model->count();
            $keys      = array_keys($this->rsi);
            $start     = array_shift($keys);
            for ($i = $start; $i < $count; $i++) {
                if ($this->inPosition()) {
                    $var = $this->sellTrigger($i);
                    if ($var !== false) {
                        $this->sellOrder($i, $var);
                    }
                } else {
                    $var = $this->buyTrigger($i);
                    if ($var !== false) {
                        $this->buyOrder($i, $var);
                    }
                }
            }
            $report = parent::report(array($symbol));

            $lastRsi                      = array_pop($keys);
            $report->userData             = new stdClass();
            $report->userData->last       = $this->rsi[$lastRsi];
            $report->userData->buyTrigger =
                ($this->buyTrigger($lastRsi) !== false
                && $report->expectancy > 0) ? 'Yes' : '-';

            $this->addReport($report);
        }
    }

}

/**
 * TMTTStrategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class TMTTStrategy
    extends StockStrategy
{

    /**
     *
     * @var float
     */
    private $stopLoss;

    /**
     *
     * @var float
     */
    private $sellAt;

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
     * TMTTStrategy constructor
     * @param array $symbols
     */
    function __construct($symbols)
    {
        parent::__construct();
        $this->symbols = $symbols;
    }

    /**
     * Place a sell bracket then the buy order.
     * 
     * @param int $index
     * @param float $price
     */
    function buyOrder($index, $price)
    {
        $this->setSellBracket($index);
        parent::buyOrder($index, $price);
    }

    /**
     * Check if the current index is a buy.
     * 
     * @param int $index
     * 
     * @return mixed False if not a buy. Buy price if true.
     */
    function buyTrigger($index)
    {
        // (3 day) pullback
        $close1 = $this->model->getField($index - 1, YahFin_Field::_CLOSE);
        $close2 = $this->model->getField($index - 2, YahFin_Field::_CLOSE);

        if ($close1 < $close2) {
            // todays high > yesterdays high
            $yhigh = $this->model->getField($index - 1, YahFin_Field::HIGH);
            $thigh = $this->model->getField($index, YahFin_Field::HIGH);

            if ($thigh > $yhigh) {
                return $yhigh;
            }
        }

        return false;
    }

    /**
     * Get the reports array.
     * 
     * @return array
     */
    function reports()
    {
        return $this->reports;
    }

    /**
     * Set the sell bracket order for when there is a buy trigger.
     * 
     * @param int $index
     */
    function setSellBracket($index)
    {
        // highest high in the last 10 days
        $this->sellAt = $this->model->highest($index, 3);

        // lowest low in the last 3 days
        $this->stopLoss = $this->model->lowest($index, 3);
    }

    /**
     * Check if the current index is a sell.
     * 
     * @param int $index
     * 
     * @return mixed False if not a sell.  Sell price if true.
     */
    function sellTrigger($index)
    {
        $day = $this->model->getDay($index);
        if ($day->low <= $this->stopLoss) {
            return $this->stopLoss;
        } elseif ($day->high >= $this->sellAt) {
            return $this->sellAt;
        }
        return false;
    }

    /**
     * Test the strategy and generate reports.
     */
    function test()
    {
        $columns = array(YahFin_Field::DATE, YahFin_Field::_CLOSE, YahFin_Field::HIGH, YahFin_Field::LOW);
        foreach ($this->symbols as $symbol) {
            $this->setData($symbol, $columns);
            $count = $this->model->count();
            $start = 2; // because we are looking for index - 2
            for ($i = $start; $i < $count; $i++) {
                if ($this->inPosition()) {
                    $var = $this->sellTrigger($i);
                    if ($var !== false) {
                        $this->sellOrder($i, $var);
                    }
                } else {
                    $var = $this->buyTrigger($i);
                    if ($var !== false) {
                        $this->buyOrder($i, $var);
                    }
                }
            }
            $report = parent::report();

            $report->userData             = new stdClass();
            $report->userData->last       = 0;
            $report->userData->buyTrigger = (
                $this->buyTrigger($count - 1) !== false
                && $report->expectancy > 0) ? 'Yes' : '-';

            $this->reports[$symbol] = $report;
        }
    }

}

/**
 * RoboStrategy
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class RoboStrategy
    extends StockStrategy
{

    function buyZone()
    {
        // rsi buy trigger = true
    }

    function buyTrigger()
    {
        // TMTT buy trigger = true
    }

}
?>

