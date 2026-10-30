<?php

$text = 'Δ';
// CONVERT GREEK CHARACTERS
$pattern = array('/del/','/lambda/');
$replacement = array('Δ','λ');
$outtext = preg_replace($pattern,$replacement,$text);
echo 'Replacement: ' . $outtext  . "\n";

?>
