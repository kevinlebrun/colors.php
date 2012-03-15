#!/usr/bin/env php
<?php

require_once './lib/Colors/Colors.php';

use Colors\Color;

function c($string = '')
{
    return new Colors\Color($string);
}

$color = new Color();
echo $color('Some bold red text')->red->bold . PHP_EOL;
echo c('Some reversed blue text')->blue->reverse . PHP_EOL;
echo c('Some underlined text')->underline . PHP_EOL;
