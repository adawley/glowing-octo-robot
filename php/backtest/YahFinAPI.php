<?php
/*
 * Copyright (C) 2009 MDBitz - Matthew John Denton - mdbitz.com
 *
 * This file is part of YahooFinanceAPI.
 *
 * YahooFinanceAPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * YahooFinanceAPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with YahooFinanceAPI.  If not, see <http://www.gnu.org/licenses/>.
 */
 
/**
 * YahooFinanceAPI
 *
 * This file contains the class YahooFinanceAPI
 *
 * @author Matthew Denton <matt@mdbitz.com>
 * @package com.mdbitz.YahooFinance
 */

/**
 * main class of the YahooFinance API
 * 
 * <code> 
 * // require the YahooFinance API core class 
 * require_once( PATH_TO_LIB . '/YahooFinanceAPI.php' );
 *	
 * // Register Auto Loader
 * spl_autoload_register(array('YahooFinanceAPI', 'autoload'));
 *
 * 	// instantiate YahooFinanceAPI
 * $api = new YahooFinanceAPI();
 *
 * // set options
 * $api->addOption("symbol");
 * $api->addOption("previousClose");
 * $api->addOption("open");
 * $api->addOption("lastTrade");
 * $api->addOption("lastTradeTime");
 * $api->addOption("change" );
 * $api->addOption("daysLow" );
 * $api->addOption("daysHigh" );
 * $api->addOption("volume" );
 *
 * // set symbols
 * $api->addSymbol("GOOG");
 * $api->addSymbol("DELL");
 *
 * // get quotes
 * $result = $api->getQuotes();
 *
 * if( $result->isSuccess() ) {
 *     $quotes = $result->data;
 *     foreach( $quotes as $quote ) {
 *         echo $quote->symbol;
 *         echo $quote->lastTrade;
 *     }
 * }
 * </code> 
 *
 * @package com.mdbitz.YahooFinance
 */
final class YahooFinanceAPI
{

    /**
     * @var string STRICT MODE
     */
    public static $STRICT = "STRICT";

    /**
     * @var string LOOSE MODE
     */
    public static $LOOSE = "LOOSE";

    /**
     * @var string Error Handling Mode
     */
    protected $_mode;

    /**
     * @var array symbols of quotes requested for
     */
    protected $_symbols = array();
 
    /**
     * @var YahooFinance_Options quote options
     */
    protected $_options = null;

    /**
     * @var string $path YahooFinance root directory
     */
    private static $_path;

    /**
     * Constructor
     * @param string $mode Error handling mode
     */
    public function __construct( $mode = "STRICT" )
    {
        $this->_options = new YahooFinance_Options();
        $this->_mode = $mode;
    }

    /**
     * magic method to return non public properties
     *
     * @see     get
     * @param   mixed $property
     * @return  mixed
     */
    public function __get( $property )
    {
        return $this->get( $property );
    }

    /**
     * Return the specified property
     *
     * @param mixed $property     The property to return
     * @return mixed
     */
    public function get( $property )
    {
        switch( $property ){
            case 'symbols':
                return $this->_symbols;
            break;
            case 'options':
                return $this->_options;
            break;
            default:
                if( $this->_mode == $STRICT ) {
                    throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
                } else {
                    return null;
                }
            break;
        }
    }

    /**
     * magic method to set non public properties
     *
     * @see    set
     * @param  mixed $property
     * @param  mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        $this->set( $property, $value );
    }

    /**
     * sets the specified property
     *
     * @param mixed $property     The property to set
     * @param mixed $value        value of property
     * @return void
     */
    public function set( $property, $value )
    {
        switch( $property ){
            case 'symbols':
                if( ! is_null( $value ) && is_array( $value ) ) {
                    $this->_symbols = $value;
                } else {
                    if( $this->_mode == YahooFinanceAPI::$STRICT ) {
                        throw new YahooFinance_Exception(sprintf('symbols property expects an array %s', get_class($this) ) );
                    }
                }
            break;
            case 'options':
                if( $value instanceof YahooFinance_Options ) {
                    $this->_options = $value;
                } else {
                    if( $this->_mode == YahooFinanceAPI::$STRICT ) {
                        throw new YahooFinance_Exception(sprintf('options property expects an object of type YahooFinance_Options %s', get_class($this) ) );
                    }
                }
            break;
            default:
                if( $this->_mode == YahooFinanceAPI::$STRICT ) {
                    throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
                }
            break;
        }
    }

    /**
     * add Symbol to list of quotes to be obtained
     *
     * @param string $symbol Symbol to be added
     * @return void
     */
    public function addSymbol( $symbol )
    {
        if( ! is_null( $symbol ) && ! in_array( $symbol, $this->_symbols ) ){
            $this->_symbols[] = $symbol;
        }
    }

    /**
     * remove symbol from list to obtain quotes for
     *
     * @param string $symbol Symbol to be removed
     * @return void
     */
    public function removeSymbol( $symbol )
    {
        if( ! is_null( $symbol ) ) {
            $key = array_search( $symbol, $this->_symbols );
            if( $key != NULL || $key !== FALSE ) {
                unset( $this->_symbols );
            }
        }
    }

    /**
     * add Option to information to be returned
     *
     * @param string $option Quote property
     * @return void
     */
    public function addOption( $option )
    {
        if( ! is_null( $option ) && array_key_exists( $option, YahooFinance_Options::$_SpecialTags ) ){
            $this->_options->set( $option, true );
        } else {
            if( $this->_mode == YahooFinanceAPI::$STRICT ) {
                throw new YahooFinance_Exception(sprintf('Unknown option %s::%s', get_class($this), $option));
            }
        }
    }

    /**
     * remove Option from information to be returned
     *
     * @param string $option Quote property
     * @return void
     */
    public function removeOption( $option )
    {
        if( ! is_null( $option ) && array_key_exists( $option, YahooFinance_Options::$_SpecialTags ) ) {
            if( $this->_options->get( $option ) === true ) {
                $this->_options->set( $option, false );
            }
        }
    }

    /**
     * resets all symbols and options
     *
     * @return void
     */
    public function clear()
    {
        $this->_options = new YahooFinance_Options();
        $this->_symbols = array();
    }

    /**
     * gets Quotes for symbols with set options
     *
     * @return void
     */
    public function getQuotes()
    {
        $url = "http://download.finance.yahoo.com/d/quotes.csv?s=" . implode( "+", $this->_symbols ) . "&f=" .$this->_options->toParamString() . "&e=.csv";
        
        if(function_exists('curl_exec')) {
            return $this->curlRequest( $url );
        } else {
            return $this->fopenRequest( $url );
        }
    }

    /**
     * perform curl request of specified url
     *
     * @param url Request Server Location
     * @return YahooFinance_Result
     */
    private function curlRequest( $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec( $ch );
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = $this->processResponse( $resp );
        return new YahooFinance_Result( $code, $data );
    }

    /**
     * perform fopen request of specified url
     *
     * @param url Request Server Location
     * @return YahooFinance_Result
     */
    private function fopenRequest( $url )
    {
        $http_options = array('method'=>'GET','timeout'=>3);
        $context = stream_context_create(array('http'=>$http_options));
        $resp = @file_get_contents($url, null, $context);
        $data = null;
        $code = 400;
        if( $resp !== false ) {
            $code = 200;
            $data = $this->processResponse( $resp );
        }
        return new YahooFinance_Result( $code, $data );
    }

    /**
     * process response
     *
     * @param response Response Data
     * @return array
     */
    private function processResponse( $response )
    {
        $quotes = $this->_options->parseResult( $response );
        return $quotes;
    }

    /**
     * simple autoload function
     * returns true if the class was loaded, otherwise false
     *
     * @param string $classname
     * @return boolean
     */
    public static function autoload($className)
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
           return false;
        }

        $class = self::getPath() . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (file_exists($class)) {
            require $class;
            return true;
        }

        return false;
    }

    /**
     * Get the root path to Yahoo Finance API
     *
     * @return string
     */
    public static function getPath()
    {
        if ( ! self::$_path) {
            self::$_path = dirname(__FILE__);
        }

        return self::$_path;
    }

}

/**
 * Exception
 *
 * This file contains the class YahooFinance_Exception
 *
 * @author Matthew Denton <matt@mdbitz.com>
 * @package com.mdbitz.YahooFinance
 */

/**
 * YahooFinance_Exception defines the exception
 * thrown by the YahooFinanceAPI.
 * @package com.mdbitz.YahooFinance
 */
class YahooFinance_Exception extends Exception
{

}

/**
 * Options
 *
 * This file contains the class YahooFinance_Options
 *
 * @author Matthew Denton <matt@mdbitz.com>
 * @package com.mdbitz.YahooFinance
 */

/**
 * YahooFinance_Options defines the options requested via the API, as well
 * as handlers to parse resulting data to Quote option.
 *
 * @package com.mdbitz.YahooFinance
 */
class YahooFinance_Options
{

    /**
     * @var array option variables
     */
    public static $_SpecialTags = array(
                              "ask" => "a",
                              "averageDailyVolume" => "a2",
                              "askSize" => "a5",
                              "bid" => "b",
                              "askRealTime" => "b2",
                              "bidRealTime" => "b3",
                              "bookValue" => "b4",
                              "bidSize" => "b6",
                              "changeAndPercentChange" => "c",
                              "change" => "c1",
                              "commision" => "c3",
                              "changeRealTime" => "c6",
                              "afterHoursChangeRealTime" => "c8",
                              "dividendPerShare" => "d",
                              "lastTradeDate" => "d1",
                              "tradeDate" => "d2",
                              "earningsPerShare" => "e",
                              "errorIndication" => "e1",
                              "EPSEstimateCurrentYear" => "e7",
                              "EPSEstimateNextYear" => "e8",
                              "EPSEstimateNextQuarter" => "e9",
                              "floatShares" => "f6",
                              "daysLow" => "g",
                              "daysHigh" => "h",
                              "52WeekLow" => "j",
                              "52WeekHigh" => "k",
                              "holdingsGainPercent" => "g1",
                              "annualizedGain" => "g3",
                              "holdingsGain" => "g4",
                              "holdingsGainPercentRealTime" => "g5",
                              "holdingsGainRealTime" => "g6",
                              "moreInfo" => "i",
                              "orderBookRealTime" => "i5",
                              "marketCapitalization" => "j1",
                              "marketCapitalizationRealTime" => "j3",
                              "EBITDA" => "j4",
                              "changeFrom52WeekLow" => "j5",
                              "percentChangeFrom52WeekLow" => "j6",
                              "lastTradeWithTimeRealTime" => "k1",
                              "changePercentRealTime" => "k2",
                              "lastTradeSize" => "k3",
                              "changeFrom52WeekHigh" => "k4",
                              "percentChangeFrom52WeekHigh" => "k5",
                              "lastTradeWithTime" => "l",
                              "lastTrade" => "l1",
                              "highLimit" => "l2",
                              "lowLimit" => "l3",
                              "daysRange" => "m",
                              "daysRangeRealTime" => "m2",
                              "50DayMovingAverage" => "m3",
                              "200DayMovingAverage" => "m4",
                              "changeFrom200DayMovingAverage" => "m5",
                              "percentChangeFrom200DayMovingAverage" => "m6",
                              "changeFrom50DayMovingAverage" => "m7",
                              "percentChangeFrom50DayMovingAverage" => "m8",
                              "name" => "n",
                              "notes" => "n4",
                              "open" => "o",
                              "previousClose" => "p",
                              "pricePaid" => "p1",
                              "changeInPercent" => "c2",
                              "pricePerSales" => "p5",
                              "pricePerBook" => "p6",
                              "exDividendDate" => "q",
                              "priceEarningsRatio" => "r",
                              "dividendPayDate" => "r1",
                              "priceEarningsRatioRealTime" => "r2",
                              "PEGRatio" => "r5",
                              "pricePerEPSEstimateCurrentYear" => "r6",
                              "pricePerEPSEstimateNextYear" => "r7",
                              "symbol" => "s",
                              "sharesOwned" => "s1",
                              "shortRatio" => "s7",
                              "lastTradeTime" => "t1",
                              "tradeLinks" => "t6",
                              "tickerTrend" => "t7",
                              "1YrTargetPrice" => "t8",
                              "volume" => "v",
                              "holdingsValue" => "v1",
                              "holdingsValueRealTime" => "v7",
                              "52WeekRange" => "w",
                              "daysValueChange" => "w1",
                              "daysValueChangeRealTime" => "w4",
                              "stockExchange" => "x",
                              "dividendYeild" => "y"
                              );

    /**
     * @var array Quote Options
     */    
    protected $_options = array();

    /**
     * Constructor initializes options
     *
     * @param array $options quote options
     */
    public function __construct( Array $options = null)
    {   
        if( ! is_null( $options ) ) {
            foreach( $options as $key => $value ) {
                $this->set( $key, $value );
            }
        }
    }

    /**
     * magic method to return non public properties
     *
     * @see     get
     * @param   mixed $property
     * @return  mixed
     */
    public function __get( $property )
    {
        return $this->get( $property );
    }

    /**
     * Return the specified property
     *
     * @param mixed $property     The property to return
     * @return mixed
     */
    public function get( $property )
    {
        if( array_key_exists( $property, YahooFinance_Options::$_SpecialTags ) ) {
            if( array_key_exists( $property, $this->_options ) ){
                return $this->_values[$property];
            } else {
                return null;
            }
        } else {
            throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
        }
    }

    /**
     * magic method to set non public properties
     *
     * @see    set
     * @param  mixed $property
     * @param  mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        $this->set( $property, $value );
    }

    /**
     * sets the specified property
     *
     * @param mixed $property     The property to set
     * @param mixed $value        value of property
     * @return void
     */
    public function set( $property, $value )
    {
        if( array_key_exists( $property, YahooFinance_Options::$_SpecialTags ) ){
            if( is_bool( $value ) ){
                $this->_options[$property] = $value;
            } else {
                throw new YahooFinance_Exception(sprintf("Property needs to be set to a boolean [true,false] %s::%s", get_class($this), $property));
            }
        } else {
            throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
        }
    }

    /**
     * generated paramater string of user requested options.
     *
     * @return string
     */
    public function toParamString()
    {
        $paramStr = "";
        foreach( $this->_options as $key => $value ){
            if( $value ) {
                $paramStr .= YahooFinance_Options::$_SpecialTags[$key];
            }
        }
        return $paramStr;
    }

    /**
     * converts returned data into quote objects
     *
     * @param string $csvStrInput response data
     * @return array Quote Objects
     */
    public function parseResult( $csvStrInput )
    {
        $results = array();
        $csvStrs = preg_split("[\n|\r]", $csvStrInput);
        foreach( $csvStrs as $csvStr ){
            if( !empty( $csvStr ) ) {
                $csvArray = explode(",", $csvStr );
                $propPos = 0;
                $quote = new YahooFinance_Quote();
                foreach( $this->_options as $key => $value ) {
                    $propVal = $csvArray[$propPos];
                    $propVal = trim( $propVal );
                    if( 0 === strpos($propVal, "\"") ) {
                        $propVal = substr( $propVal, 1, strlen( $propVal ) - 2 );
                    }
                    //$propVal = substr( $propVal, 0 , strlen( $propVal) - 1 );
                    $quote->set( $key, $propVal );
                    $propPos ++;
                }
                $results[] = $quote;
            }
        }
        return $results;
    }

}

/**
 * Result
 *
 * This file contains the class YahooFinance_Result
 *
 * @author Matthew Denton <matt@mdbitz.com>
 * @package com.mdbitz.YahooFinance
 */

/**
 * YahooFinance_Result defines the result object returned by a request
 * made by the YahooFinanceAPI.
 * 
 * <b>Properties</b>
 * <ul>
 *   <li>code</li>
 *   <li>data</li>
 * </ul>
 *
 * @package com.mdbitz.YahooFinance
 */
class YahooFinance_Result
{

    /**
     * @var string response code
     */
    protected $_code = null;

    /**
     * @var array response data
     */
    protected $_data = null;

    /**
     * Constructor initializes {@link $_code} {@link $_data}
     *
     * @param string $code response code
     * @param array $data array of Quote Objects
     */
    public function __construct( $code = null, $data = null)
    {
        $this->_code = $code;
        $this->_data = $data;
    }

    /**
     * magic method to return non public properties
     *
     * @see     get
     * @param   mixed $property
     * @return  mixed
     */
    public function __get( $property )
    {
        return $this->get( $property);
    }

    /**
     * Return the specified property
     *
     * @param mixed $property     The property to return
     * @return mixed
     */
    public function get( $property )
    {
        switch( $property ){
            case 'code':
                return $this->_code;
            break;
            case 'data':
                return $this->_data;
            break;
            default:
                throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
            break;
        }
    }

    /**
     * magic method to set non public properties
     *
     * @see    set
     * @param  mixed $property
     * @param  mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        $this->set( $property, $value );
    }

    /**
     * sets the specified property
     *
     * @param mixed $property     The property to set
     * @param mixed $value        value of property
     * @return void
     */
    public function set( $property, $value )
    {
        switch( $property ){
            case 'code':
                $this->_code = $value;
            break;
            case 'data':
                $this->_data = $value;
            break;
            default:
                throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
            break;
        }
    }

    /**
     * returns a JSON representation of this Result
     *
     * @return string
     */
    public function toJSON( )
    {
    
        $json = "{ \"code\" : \"$this->code\", \"data\" : [ ";
        foreach( $this->_data as $quote ) {
            $json .= $quote->toJSON() . ", ";
        }
        $json = substr( $json, 0, strrpos($json, ",") );
        $json .= " ] }";
        return $json;
    }

    /**
     * returns an XML representation of this Result
     *
     * @return string
     */
    public function toXML( )
    {
    
        $xml = "<result><code>$this->code</code><data>";
        foreach( $this->_data as $quote ) {
            $xml .= $quote->toXML();
        }
        $xml .= "</data></result>";
        return $xml;
    }

	/**
     * is request successfull
     * @return boolean
     */
    public function isSuccess() 
    {
        if( "2" == substr( $this->_code, 0, 1 ) ) {
            return true;
        } else {
            return false;
        }
    }

}

/**
 * Quote
 *
 * This file contains the class YahooFinance_Quote
 *
 * @author Matthew Denton <matt@mdbitz.com>
 * @package com.mdbitz.YahooFinance
 */

/**
 * YahooFinance_Quote defines the resulting Quote returned for an
 * individual symbol.
 *
 * @package com.mdbitz.YahooFinance
 */
class YahooFinance_Quote
{

    /**
     * @var array quote values
     */
    protected $_values = array();

    /**
     * Constructor
     *
     */
    public function __construct( )
    {

    }

    /**
     * magic method to return non public properties
     *
     * @see     get
     * @param   mixed $property
     * @return  mixed
     */
    public function __get( $property)
    {
        return $this->get( $property);
    }

    /**
     * Return the specified property
     *
     * @param mixed $property     The property to return
     * @return mixed
     */
    public function get( $property)
    {
        if( array_key_exists( $property, YahooFinance_Options::$_SpecialTags ) ){
            if( array_key_exists( $property, $this->_values ) ){
                return $this->_values[$property];
            } else {
                return null;
            }
        } else {
            throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
        }
    }

    /**
     * magic method to set non public properties
     *
     * @see    set
     * @param  mixed $property
     * @param  mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        $this->set( $property, $value );
    }

    /**
     * sets the specified property
     *
     * @param mixed $property     The property to set
     * @param mixed $value        value of property
     * @return void
     */
    public function set( $property, $value )
    {
        if( array_key_exists( $property, YahooFinance_Options::$_SpecialTags ) ) {
            $this->_values[$property] = $value;
        } else {
            throw new YahooFinance_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
        }
    }

    /**
     * returns a JSON representation of this Quote
     *
     * @return String
     */
    public function toJSON( )
    {
        $json = "{ ";
        foreach( $this->_values as $key => $value ) {
            $json .= "\"$key\" : \"$value\", ";
        }
        $json = substr( $json, 0, strrpos($json, ",") );
        $json .= " }";
        return $json;
    }

    /**
     * returns an XML representation of this Quote
     *
     * @return String
     */
    public function toXML( )
    {
        $xml = "<quote>";
        foreach( $this->_values as $key => $value ) {
            $xml .= "<$key>$value</$key>";
        }
        $xml .= "</quote>";
        return $xml;
    }

}
