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

    /**
     *
     * @var OrderStatus 
     */
    protected $status;

    /**
     *
     * @var OrderSide 
     */
    protected $purchase;

    /**
     *
     * @var OrderSide
     */
    protected $sale;

    /**
     * Order constructor
     */
    function __construct()
    {
        $this->status   = OrderStatus::NOT_PLACED;
        $this->purchase = new OrderSide();
        $this->sale     = new OrderSide();
    }

    /**
     * Place a buy order.
     * 
     * @param string $date
     * @param float $price
     */
    function buy($date, $price)
    {
        $this->purchase->date($date);
        $this->purchase->price($price);
        $this->status = OrderStatus::ORDERED;
    }

    /**
     * Get the duration of this order.
     * 
     * @return float
     */
    function duration()
    {
        $result = FALSE;

        if ($this->status == OrderStatus::CLOSED || $this->status == OrderStatus::ORDERED) {
            $tmp    = $this->purchase->date()->diff($this->sale->date());
            $result = $tmp->days;
        }

        return $result;
    }

    /**
     * Is this order closed?
     * 
     * @return bool
     */
    function isClosed()
    {
        return ($this->status == OrderStatus::CLOSED);
    }

    /**
     * Is this order ordered?
     * 
     * @return bool
     */
    function isOrdered()
    {
        return ($this->status == OrderStatus::ORDERED);
    }

    /**
     * The profit of this order.
     * 
     * @return float 
     */
    function profit()
    {
        if ($this->status == OrderStatus::CLOSED) {
            return $this->sale->price() - $this->purchase->price();
        } else {
            return false;
        }
    }

    /**
     * Get the purchase of this order.
     * 
     * @return OrderSide
     */
    function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * Get the sale of this order.
     * 
     * @return OrderSide
     */
    function getSale()
    {
        return $this->sale;
    }

    /**
     * Place a sell order.
     * 
     * @param string $date
     * @param float $price
     */
    function sell($date, $price)
    {
        $this->sale->date($date);
        $this->sale->price($price);
        $this->status = OrderStatus::CLOSED;
    }

    /**
     * 
     * @return string
     */
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

    /**
     *
     * @var DateTime
     */
    private $date;

    /**
     *
     * @var float
     */
    private $price;

    /**
     * OrderSide constructor.
     */
    function __construct()
    {
        $this->date  = '';
        $this->price = -1;
    }

    /**
     * Get or set the date.
     * 
     * @param DateTime $date
     * 
     * @return DateTime If no argument is given, the current date is given.
     * 
     * @throws InvalidArgumentException
     */
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

    /**
     * Get or set the price.
     * 
     * @param float $price
     * 
     * @return float
     */
    function price($price = NULL)
    {
        if (is_null($price)) {
            return $this->price;
        } else {
            $this->price = $price;
        }
    }

    /**
     * 
     * @return string
     */
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
    /**
     * The order is not placed
     */
    const NOT_PLACED = 0;

    /**
     * The order is ordered.
     */
    const ORDERED = 1;

    /**
     * The order is closed.
     */
    const CLOSED = 2;

    /**
     * Get the name association of the given order status
     * 
     * @param OrderStatus $OrderStatus
     * 
     * @return string
     */
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
