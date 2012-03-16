<?php

namespace Colors;

class Color
{

    protected $_initial = '';
    protected $_wrapped = '';
    protected $_styles = array(
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
    protected $_theme = array();

    public function __construct($string = '')
    {
        $this->_setInternalState($string);
    }

    public function __invoke($string)
    {
        return $this->_setInternalState($string);
    }

    public function __call($method, $args)
    {
        return $this->_stylize($method);
    }

    public function __get($name)
    {
        return $this->_stylize($name);
    }

    public function __toString()
    {
        return $this->_wrapped;
    }

    protected function _setInternalState($string)
    {
        $this->_initial = $this->_wrapped = (string) $string;
        return $this;
    }

    protected function _stylize($style)
    {
        if (array_key_exists($style, $this->_styles)) {

            $this->_wrapped = sprintf($this->_styles[$style], $this->_wrapped);

        } else if (array_key_exists($style, $this->_theme)) {

            $styles = $this->_theme[$style];
            if (!is_array($styles)) {
                $styles = array($styles);
            }

            foreach ($styles as $styl) {
                $this->_stylize($styl);
            }

        } else {
            throw new InvalidArgumentException("Invalid style $style");
        }

        return $this;
    }

    public function fg($color)
    {
        return $this->_stylize($color);
    }

    public function bg($color)
    {
        return $this->_stylize('bg_' . $color);
    }

    public function highlight($color)
    {
        return $this->bg($color);
    }

    public function reset()
    {
        $this->_wrapped = $this->_initial;
        return $this;
    }

    public function clean()
    {
        $this->_wrapped = preg_replace("/\033\[\d+m/", '', $this->_wrapped);
        return $this;
    }

    public function setTheme(array $theme)
    {
        $this->_theme = $theme;
        return $this;
    }

}
