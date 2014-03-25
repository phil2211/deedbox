<?php
$text = file_get_contents('/tmp/test.txt');

$text = 'Lausanne, 12. Januar 2012';
$matches = array();

//$tmp = preg_match("/([a-z]*,)([\\n ])([0-3][0-9][\\.-][01][0-9][\\.-][12]?[0-9]?[0-9]{2})([ \\n])/uis", $text, $matches);
$tmp = preg_match("/([a-z]*, )([0-3][0-9][\\.-] (Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember) [12]?[0-9]?[0-9]{2})/uis", $text, $matches);

var_dump($tmp);

var_dump($matches);

$translate = array('Januar'=>'01.', 'Februar'=>'02.','März'=>'03.','April'=>'04.','Mai'=>'05.','Juni'=>'06.','Juli'=>'07.','August'=>'08.','September'=>'09.','Oktober'=>'10.','November'=>'11.','Dezember'=>'12.');

$matches[2] = strtr($matches[2], $translate);
$matches[2] = str_replace(' ', '', $matches[2]);

var_dump($matches);

$tmp = strtotime($matches[2]);

var_dump($tmp);

$tmp = date('d.M.Y', $tmp);

var_dump($tmp);
