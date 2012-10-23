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
        $this->purchase = new OrderSide();
        $this->sale     = new OrderSide();
    }

    function buy($date, $price)
    {
        $this->purchase->date($date);
        $this->purchase->price($price);
        $this->status = OrderStatus::ORDERED;
    }

    function duration()
    {
        $result = FALSE;

        if ($this->status == OrderStatus::CLOSED || $this->status == OrderStatus::ORDERED) {
            $tmp = $this->purchase->date()->diff($this->sale->date());
            $result = $tmp->days;
        }

        return $result;
    }

    function isClosed()
    {
        return ($this->status == OrderStatus::CLOSED);
    }

    function isOrdered()
    {
        return ($this->status == OrderStatus::ORDERED);
    }

    function profit()
    {
        if ($this->status == OrderStatus::CLOSED) {
            return $this->sale->price() - $this->purchase->price();
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @return OrderSide
     */
    function getPurchase(){
        return $this->purchase;
    }

    /**
     * 
     * @return OrderSide
     */
    function getSale(){
        return $this->sale;
    }
    
    function sell($date, $price)
    {
        $this->sale->date($date);
        $this->sale->price($price);
        $this->status = OrderStatus::CLOSED;
    }

    function __toString()
    {
        switch ($this->status):
            case OrderStatus::CLOSED:
                $profit = $this->sale->price() - $this->purchase->price();
                $bto    = "BTO:" . $this->purchase . "\t";
                return $bto . "STC:" . $this->sale . "\tPNL:" . $profit;
            case OrderStatus::NOT_PLACED:
                return "Order not placed yet";
            case OrderStatus::ORDERED:
                return "BTO:" . $this->purchase . "\t";
        endswitch;
    }

}

/**
 * OrderSide
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class OrderSide
{

    private $date;
    private $price;

    function __construct()
    {
        $this->date  = '';
        $this->price = -1;
    }

    function date($date = NULL)
    {
        if (is_null($date)) {
            return $this->date;
        } elseif (is_string($date)) {
            $this->date = new DateTime($date);
        } elseif ($date instanceof DateTime) {
            $this->date = $date;
        } else {
            throw new InvalidArgumentException("Must be string or DateTime.");
        }
    }

    function price($price = NULL)
    {
        if (is_null($price)) {
            return $this->price;
        } else {
            $this->price = $price;
        }
    }

    function __toString()
    {
        return $this->date->format('Y-m-d') . "@" . $this->price();
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
