#!/usr/bin/php -q
<?php

function main() {
    $options = get_options();
    var_dump($options);
}

function get_options() {
    $shortopts  = "H:S:h";
    $options = getopt($shortopts);
    if(count($options) == 0 || isset($options['h']))	help();
    return $options;
}

function help() {
    $basename = str_replace(".php", "", basename($_SERVER[PHP_SELF]));
    $texto = "./$basename -H... ";
    quit($texto, 3);
}

function quit($text, $code) {
    echo $text."\n";
    exit($code);
}

main();
?>
