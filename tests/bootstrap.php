<?php

define( 'YII_ENABLE_EXCEPTION_HANDLER', false );
define( 'YII_ENABLE_ERROR_HANDLER', false );

$_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once( '/usr/local/yii/framework/yii.php' );
require_once( dirname(__FILE__) . '/TestApplication.php' );
require_once( 'PHPUnit/Framework/TestCase.php' );

class CTestCase extends PHPUnit_Framework_TestCase
{
}

class CActiveRecordTestCase extends CTestCase
{
}