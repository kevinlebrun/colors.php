#!/usr/bin/env php
<?php
require_once './lib/Colors/Exception.php';
require_once './lib/Colors/InvalidArgumentException.php';
require_once './lib/Colors/Color.php';

$c = new \Colors\Color();

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

$text = <<<EOF
1 : <welcome>Hello <bold>World!</bold></welcome>
2 : <bye>Bye!</bye>
EOF;

echo $c($text)->colorize() . PHP_EOL;
