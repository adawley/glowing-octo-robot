<?php

/**
 * Order
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
 * Order
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class Order
{

    protected
        $status,
        $purchase,
        $sale;

    /**
     * 
     */
    function __construct()
    {
        $this->status   = OrderStatus::NOT_PLACED;
        $this->purchase = new stdClass();
        $this->sale     = new stdClass();
    }

    function buy($date, $price)
    {
        $this->purchase->date  = $date;
        $this->purchase->price = $price;
        $this->status          = OrderStatus::ORDERED;
    }

    function isClosed()
    {
        return ($this->status == OrderStatus::CLOSED);
    }

    function isOrdered()
    {
        return ($this->status == OrderStatus::ORDERED);
    }

    private function preview($arg)
    {
        return $arg->date . "@" . $arg->price;
    }

    function profit()
    {
        if ($this->status == OrderStatus::CLOSED) {
            return $this->sale->price - $this->purchase->price;
        } else {
            return false;
        }
    }

    function sell($date, $price)
    {
        $this->sale->date  = $date;
        $this->sale->price = $price;
        $this->status      = OrderStatus::CLOSED;
    }

    function __toString()
    {
        switch ($this->status):
            case OrderStatus::CLOSED:
                $profit = $this->sale->price - $this->purchase->price;
                $bto    = "BTO:" . $this->preview($this->purchase) . "\t";
                return $bto."STC:" . $this->preview($this->sale) . "\t" . $profit;
            case OrderStatus::NOT_PLACED:
                return "Order not placed yet";
            case OrderStatus::ORDERED:
                return "BTO:" . $this->preview($this->purchase) . "\t";
        endswitch;
    }

}

/**
 * OrderStatus
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
final class OrderStatus
{

    const NOT_PLACED = 0;
    const ORDERED    = 1;
    const CLOSED     = 2;

    public static function name($OrderStatus)
    {
        switch ($OrderStatus):
            case OrderStatus::CLOSED:
                return 'Closed';
            case OrderStatus::NOT_PLACED:
                return 'Not Placed';
            case OrderStatus::ORDERED:
                return 'Ordered';
        endswitch;
    }

}

?>
