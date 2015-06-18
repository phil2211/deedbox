<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap(array('config'));


$index = Zend_Search_Lucene::open('/var/www/docindex');


$subquery = Zend_Search_Lucene_Search_QueryParser::parse(file_get_contents('/tmp/search.txt'));
$query = new Zend_Search_Lucene_Search_Query_Boolean();
$query->addSubquery($subquery, true);

$hits = $index->find($query);



//$test = $index->getSimilarity();
//$out = $test->tf(100);
foreach ($hits as $hit)
    var_dump($hit->score);





