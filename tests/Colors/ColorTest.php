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

}
