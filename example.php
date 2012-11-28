#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Colors\Color;

$c = new Color();

// highlight('green') === bg('green') === bg_green()
// white() === fg('white')
echo $c('Hello World!')->white()->bold()->highlight('green') . PHP_EOL;

// using some magic
echo $c('Hello World!')->white->bold->bg_green . PHP_EOL;

// create your own theme
$c->setTheme(
    array(
        'welcome' => array('white', 'bg_green'),
        'bye' => 'blue',
    )
);

echo $c('Hello World!')->welcome->bold . PHP_EOL;
echo $c('Bye!')->bye . PHP_EOL;

// use style tags
$text = <<<EOF
1 : <welcome>Hello <bold>World!</bold></welcome>
2 : <bye>Bye!</bye>
EOF;

echo $c($text)->colorize() . PHP_EOL;

// center text
$text = 'hello' . PHP_EOL . '✩' . PHP_EOL . 'world';
echo $c($text)->center() . PHP_EOL;

// use standard API
$message = $c->apply('bold', $c->white('Hello World!'));
echo $message . PHP_EOL;
echo $c->clean($message) . PHP_EOL;
