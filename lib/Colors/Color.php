<?php

namespace Colors;

class Color
{

    const FORMAT_PATTERN = '#<([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)>(.*?)</\\1?>#s';
    // http://www.php.net/manual/en/functions.user-defined.php
    const STYLE_NAME_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    protected $initial = '';
    protected $wrapped = '';
    protected $styles = array(
        // styles
        // italic and blink may not work depending of your terminal
        'bold'      => "\033[1m%s\033[0m",
        'dark'      => "\033[2m%s\033[0m",
        'italic'    => "\033[3m%s\033[0m",
        'underline' => "\033[4m%s\033[0m",
        'blink'     => "\033[5m%s\033[0m",
        'reverse'   => "\033[7m%s\033[0m",
        'concealed' => "\033[8m%s\033[0m",
        // foreground colors
        'black'     => "\033[30m%s\033[0m",
        'red'       => "\033[31m%s\033[0m",
        'green'     => "\033[32m%s\033[0m",
        'yellow'    => "\033[33m%s\033[0m",
        'blue'      => "\033[34m%s\033[0m",
        'magenta'   => "\033[35m%s\033[0m",
        'cyan'      => "\033[36m%s\033[0m",
        'white'     => "\033[37m%s\033[0m",
        // background colors
        'bg_black'   => "\033[40m%s\033[0m",
        'bg_red'     => "\033[41m%s\033[0m",
        'bg_green'   => "\033[42m%s\033[0m",
        'bg_yellow'  => "\033[43m%s\033[0m",
        'bg_blue'    => "\033[44m%s\033[0m",
        'bg_magenta' => "\033[45m%s\033[0m",
        'bg_cyan'    => "\033[46m%s\033[0m",
        'bg_white'   => "\033[47m%s\033[0m",
    );
    protected $theme = array();
    protected $isStyleForced = false;

    public function __construct($string = '')
    {
        $this->setInternalState($string);
    }

    public function __invoke($string)
    {
        return $this->setInternalState($string);
    }

    public function __call($method, $args)
    {
        if (count($args) >= 1) {
            return $this->apply($method, $args[0]);
        }

        return $this->apply($method);
    }

    public function __get($name)
    {
        return $this->apply($name);
    }

    public function __toString()
    {
        return $this->wrapped;
    }

    public function setForceStyle($force)
    {
        $this->isStyleForced = (bool) $force;
    }

    public function isStyleForced()
    {
        return $this->isStyleForced;
    }

    /**
     * https://github.com/symfony/Console/blob/master/Output/StreamOutput.php#L93-112
     */
    public function isSupported()
    {
        // @codeCoverageIgnoreStart
        if (DIRECTORY_SEPARATOR == '\\') {
            return false !== getenv('ANSICON');
        }

        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
        // @codeCoverageIgnoreEnd
    }

    protected function setInternalState($string)
    {
        $this->initial = $this->wrapped = (string) $string;
        return $this;
    }

    protected function stylize($style, $text)
    {
        if (!$this->shouldStylize()) {
            return $text;
        }

        $style = strtolower($style);

        if (array_key_exists($style, $this->styles)) {

            $text = sprintf($this->styles[$style], $text);

        } elseif (array_key_exists($style, $this->theme)) {

            $styles = $this->theme[$style];
            if (!is_array($styles)) {
                $styles = array($styles);
            }

            foreach ($styles as $styl) {
                $text = $this->stylize($styl, $text);
            }

        } else {
            throw new InvalidArgumentException("Invalid style $style");
        }

        return $text;
    }

    protected function shouldStylize()
    {
        return $this->isStyleForced() || $this->isSupported();
    }

    public function apply($style, $text = null)
    {
        if ($text === null) {
            $this->wrapped = $this->stylize($style, $this->wrapped);
            return $this;
        }

        return $this->stylize($style, $text);
    }

    public function fg($color, $text = null)
    {
        return $this->apply($color, $text);
    }

    public function bg($color, $text = null)
    {
        return $this->apply('bg_' . $color, $text);
    }

    public function highlight($color, $text = null)
    {
        return $this->bg($color, $text);
    }

    public function reset()
    {
        $this->wrapped = $this->initial;
        return $this;
    }

    public function center($width = 80, $text = null)
    {
        if ($text === null) {
            $text = $this->wrapped;
        }

        $centered = '';
        foreach (explode(PHP_EOL, $text) as $line) {
            $line = trim($line);
            $lineWidth = strlen($line) - mb_strlen($line, 'UTF-8') + $width;
            $centered .= str_pad($line, $lineWidth, ' ', STR_PAD_BOTH) . PHP_EOL;
        }

        $this->setInternalState(trim($centered, PHP_EOL));
        return $this;
    }

    protected function stripColors($text)
    {
        return preg_replace("/\033\[\d+m/", '', $text);
    }

    public function clean($text = null)
    {
        if ($text === null) {
            $this->wrapped = $this->stripColors($this->wrapped);
            return $this;
        }

        return $this->stripColors($text);
    }

    public function strip($text = null)
    {
        return $this->clean($text);
    }

    public function isAValidStyleName($name)
    {
        return preg_match(self::STYLE_NAME_PATTERN, $name);
    }

    public function setTheme(array $theme)
    {
        foreach ($theme as $name => $styles) {
            if (!$this->isAValidStyleName($name)) {
                throw new InvalidArgumentException("$name is not a valid style name");
            }
        }

        $this->theme = $theme;
        return $this;
    }

    protected function colorizeText($text)
    {
        return preg_replace_callback(self::FORMAT_PATTERN, array($this, 'replaceStyle'), $text);
    }

    /**
     * https://github.com/symfony/Console/blob/master/Formatter/OutputFormatter.php#L124-162
     */
    public function colorize($text = null)
    {
        if ($text === null) {
            $this->wrapped = $this->colorizeText($this->wrapped);
            return $this;
        }

        return $this->colorizeText($text);
    }

    protected function replaceStyle($matches)
    {
        return $this->apply($matches[1], $this->colorize($matches[2]));
    }
}
