#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Colors\Color;

$c = new Color();

echo 'System colors:' . PHP_EOL;

for ($i = 0; $i < 16; $i++) {
    echo $c->apply('bg_color[' . $i . ']', '  ');
}

echo PHP_EOL . PHP_EOL . 'Color cube, 6x6x6:' . PHP_EOL;

for ($g = 0; $g < 6; $g++) {
    for ($r = 0; $r < 6; $r++) {
        for ($b = 0; $b < 6; $b++) {
            $color = 16 + ($r * 36) + ($g * 6) + $b;
            echo $c('  ')->bg('color[' . $color . ']');
        }
        echo ' ';
    }
    echo PHP_EOL;
}

echo PHP_EOL . 'Grayscale ramp:' . PHP_EOL;

for ($i = 232; $i < 256; $i++) {
    echo $c('  ')->bg('color[' . $i . ']');
}
