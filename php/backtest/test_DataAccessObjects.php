<?php

/**
 * test_DataAccessObjects
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
include_once 'DataAccessObjects.php';

/**
 * test_DataAccessObjects
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  
 * @author   Rob Dawley <>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     http://
 */
class test_DataAccessObjects
{

    private $yfs, $yfh;
    
    function __construct()
    {
        $this->yfs   = new YahFin_Sqlite();
        $this->yfh = new YahFin_Historical();
    }

    function test_yfh_get()
    {
        $this->yfh->get('spy');
    }

    function test_yfs_insert()
    {
        $data = $this->yfh->get('spy');
        $this->yfs->insert('spy', $data);
    }

    function test_yfs_select()
    {
        $result = $this->yfs->select('spy', 250);
        var_dump($this->yfs->fetchObject($result));
    }
    
    function test_yfs_shoudlUpdate(){
        $this->yfs->shouldUpdate('spy');
    }
    
    function test_yfs_setLastUpdate(){
        $this->yfs->setLastUpdate('spy');
    }

}

$a = new test_DataAccessObjects();
//$a->test_insert();
//$a->test_select();
//$a->test_shoudlUpdate();
$a->test_setLastUpdate();
?>
