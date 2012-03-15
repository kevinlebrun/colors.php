<?php

namespace Colors;

function color($string = '')
{
    return new Color($string);
}

class ColorsTest extends \PHPUnit_Framework_TestCase
{

    public function testConvertsAsString()
    {
        $color = color('foo');
        $this->assertSame('foo', (string) $color);

        $color('bar');
        $this->assertSame('bar', (string) $color);
    }

    public function testDecoratesString()
    {
        $string = (string) color('foo')->red();
        $this->assertSame("\033[31mfoo\033[0m", $string);

        $string = (string) color('foo')->white()->bold();
        $this->assertSame("\033[1m\033[37mfoo\033[0m\033[0m", $string);
    }

    public function testThrowsExceptionForInvalidStyle()
    {
        try {
            color('foo bar')->foo();
            $this->fail('Must throw an InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
            $this->assertInstanceOf('Colors\InvalidArgumentException', $e);
            $this->assertEquals('Invalid style foo', $e->getMessage());
        }
    }

    public function testHasShortcutDecorators()
    {
        $string = 'Hello World!';

        $actual = (string) color($string)->fg('blue');
        $expected = (string) color($string)->blue();
        $this->assertSame($expected, $actual);

        $actual = (string) color($string)->bg('blue');
        $expected = (string) color($string)->bg_blue();
        $this->assertSame($expected, $actual);

        $actual = (string) color($string)->highlight('blue');
        $expected = (string) color($string)->bg('blue');
        $this->assertSame($expected, $actual);

        $actual = (string) color($string)->blue;
        $expected = (string) color($string)->blue();
        $this->assertSame($expected, $actual);
    }

    public function testResetsDecoration()
    {
        $string = (string) color('foo')->blue()->reset();
        $this->assertSame('foo', $string);
    }

    public function testHasDecorationInterceptor()
    {
        $passedThrough = false;

        $self = $this;
        $string = (string) color('foo')->bg_blue->tap(
            function ($string) use ($self, &$passedThrough) {
                $passedThrough = true;
                $self->assertSame("\033[44mfoo\033[0m", $string);
            }
        )->reset()->green();
        $this->assertSame("\033[32mfoo\033[0m", $string);

        if (!$passedThrough) {
            $this->fail('Not intercepted');
        }
    }

    public function testThrowsAnExceptionForInvalidInterceptor()
    {
        try {
            color('foo')->tap('not a callback');
            $this->fail('Must throw an InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Invalid parameter; must be callable', $e->getMessage());
        }
    }

}
