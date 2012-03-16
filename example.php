#!/usr/bin/env php
<?php

require_once './lib/Colors/Color.php';

use Colors\Color;

function c($string = '')
{
    return new Colors\Color($string);
}

$color = new Color();
echo $color('Some bold red text')->red->bold . PHP_EOL;
echo c('Some reversed blue text')->blue->reverse . PHP_EOL;
echo c('Some underlined text')->underline . PHP_EOL;

$color->setTheme(
    array(
        'error' => 'red',
        'warning' => array('bg_yellow', 'white'),
    )
);

echo $color('Error...')->error . PHP_EOL;
echo $color('Warning...')->warning->bold . PHP_EOL;
