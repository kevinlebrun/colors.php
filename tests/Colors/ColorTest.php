<?php

namespace Colors\Test;

use Colors\Color;
use Colors\InvalidArgumentException;

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

        $string = (string) color('foo')->WHITE()->bold();
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

    public function testSupportsThemes()
    {
        $color = new Color();
        $color->setTheme(
            array(
                'error' => 'red',
                'warning' => array('bg_yellow', 'white'),
                'white' => 'red',
            )
        );

        $actual = (string) $color('Error...')->error;
        $expected = (string) color('Error...')->red;
        $this->assertEquals($expected, $actual);

        $actual = (string) $color('Warning...')->warning->bold;
        $expected = (string) color('Warning...')->bg_yellow->white->bold;
        $this->assertEquals($expected, $actual);

        // no overriding existing styles
        $actual = (string) $color('foobar')->white;
        $expected = (string) color('foobar')->white;
        $this->assertEquals($expected, $actual);
    }

    public function testThrowsExceptionForInvalidThemeName()
    {
        $color = new Color();
        try {
            $color->setTheme(
                array(
                    'foo-bar' => 'red',
                )
            );
            $this->fail('must throw an InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertSame('foo-bar is not a valid style name', $e->getMessage());
        }
    }

    public function testCleansStyles()
    {
        $string = (string) color('foo')->red()->highlight('green');
        $actual = (string) color($string)->clean();
        $this->assertEquals('foo', $actual);
    }

    /**
     * @see testCleansStyles()
     */
    public function testStripsStyles()
    {
        $string = (string) color('foo')->red()->highlight('green');
        $actual = (string) color($string)->strip();
        $this->assertEquals('foo', $actual);

        $string = (string) color()->strip(color('some text')->red());
        $this->assertEquals('some text', $string);
    }

    public function testOnlyDecoratesWhenSupported()
    {
        $color = $this->getMockBuilder('Colors\Color')
            ->setMethods(array('isSupported'))
            ->getMock();

        $color->expects($this->at(0))
            ->method('isSupported')
            ->will($this->returnValue(true));

        $color->expects($this->at(1))
            ->method('isSupported')
            ->will($this->returnValue(false));

        $actual = (string) $color('foo bar')->red;
        $expected = (string) color('foo bar')->red;
        $this->assertSame($expected, $actual);

        $actual = (string) $color('foo bar')->red;
        $this->assertSame('foo bar', $actual);
    }

    public function testInterpretsStyleTags()
    {
        $color = new Color();

        $text = 'before <red>some text</red>';
        $actual = (string) $color($text)->colorize();
        $expected = 'before ' . color('some text')->red;
        $this->assertSame($expected, $actual);

        $color->setTheme(array('foo' => array('cyan', 'bold')));
        $actual = $color('<foo>some text</foo>')->colorize();
        $expected = (string) color('some text')->cyan->bold;
    }

    public function testInterpretsNestedStyleTags()
    {
        $text = '<cyan>Hello <bold>World!</bold></cyan>';
        $actual = (string) color($text)->colorize();
        $expected = (string) color('Hello ' . color('World!')->bold)->cyan;
        $this->assertSame($expected, $actual);
    }

    public function testAppliesStyleDirectlyToText()
    {
        $actual = color()->apply('blue', 'foo');
        $expected = (string) color('foo')->blue;
        $this->assertSame($expected, $actual);

        $actual = color()->white('some white text');
        $expected = (string) color('some white text')->white();
        $this->assertSame($expected, $actual);
    }

    public function testCenter()
    {
        $width = 80;
        $color = new Color();
        foreach (array('', 'hello', 'hello world!', '✩') as $text) {
            $actualWidth = mb_strlen($color($text)->center($width)->__toString(), 'UTF-8');
            $this->assertSame($width, $actualWidth);
            $actualWidth = mb_strlen($color($text)->center($width)->bg('blue')->clean()->__toString(), 'UTF-8');
            $this->assertSame($width, $actualWidth);
        }
    }

    public function testCenterMultiline()
    {
        $width = 80;
        $color = new Color();
        $text = 'hello' . PHP_EOL . '✩' . PHP_EOL . 'world';

        $actual = $color($text)->center($width)->__toString();
        foreach (explode(PHP_EOL, $actual) as $line) {
            $this->assertSame($width, mb_strlen($line, 'UTF-8'));
        }
    }

    public function testStylesAreNotAppliedWhenNotSupported()
    {
        $color = $this->getMock('colors\color', array('isSupported'));
        $color
            ->expects($this->any())
            ->method('isSupported')
            ->will($this->returnvalue(false));
        $this->assertfalse($color->issupported());

        $actual = $color->apply('blue', 'foo');
        $this->assertsame('foo', $actual);
    }

    public function testStylesAreAppliedWhenForced()
    {
        $color = $this->getMock('colors\color', array('isSupported'));
        $color
            ->expects($this->any())
            ->method('isSupported')
            ->will($this->returnvalue(false));
        $this->assertfalse($color->issupported());

        $color->setForceStyle(true);
        $this->assertTrue($color->isStyleForced());

        $actual = $color->apply('blue', 'foo');
        $expected = (string) color()->apply('blue', 'foo');
        $this->assertsame($expected, $actual);
    }
}
