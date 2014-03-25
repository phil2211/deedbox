<?php
$text1 = 'Hallo Philip und Meike auf der Welt';
$text2 = 'Hallo Philip und Maike in der Welt';

$sim = similar_text($text1, $text2, $percent);

var_dump($sim, $percent, strlen($text1), 100/strlen($text2)*$sim);

