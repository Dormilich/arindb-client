<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\ArrayElement;
use Dormilich\WebService\ARIN\Elements\GroupElement;
use Dormilich\WebService\ARIN\Elements\MultilineElement;

class ArrayElementsTest extends PHPUnit_Framework_TestCase
{
    public function testElementsInheritElement()
    {
        // this class changes only the toDOM() method
        $line = new MultilineElement('test');
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Elements\Element', $line);
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Elements\ArrayElement', $line);

        $group = new GroupElement('test');
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Elements\Element', $group);
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Elements\ArrayElement', $group);
    }

    public function testMultilineSetValues()
    {
        $m = new MultilineElement('test');

        $m->setValue(1);
        $this->assertSame(['1'], $m->getValue());

        $m->setValue(2);
        $this->assertSame(['2'], $m->getValue());
    }

    public function testMultilineAddValues()
    {
        $m = new MultilineElement('test');

        $m->addValue(1);
        $this->assertSame(['1'], $m->getValue());

        $m->addValue(2);
        $this->assertSame(['1', '2'], $m->getValue());
    }

    public function testMultilineIsDefined()
    {
        $m = new MultilineElement('test');

        $this->assertFalse($m->isDefined());

        $m->setValue(1);
        $this->assertTrue($m->isDefined());
    }

    public function testMultilineArrayIsset()
    {
        $m = new MultilineElement('test');

        $this->assertFalse(isset($m[0]));

        $m->setValue(1);
        $this->assertTrue(isset($m[0]));

        $this->assertFalse(isset($m['x']));
    }

    public function testMultilineArrayGet()
    {
        $m = new MultilineElement('test');

        $this->assertNull($m[0]);

        $m->setValue(1);
        $this->assertSame('1', $m[0]);
    }

    public function testMultilineArraySet()
    {
        $m = new MultilineElement('test');

        $m[0] = 1;
        $this->assertSame('1', $m[0]);

        $m[10] = 2;
        $this->assertSame('2', $m[1]);
        $this->assertSame(['1', '2'], $m->getValue());
    }

    public function testMultilineArrayPush()
    {
        $m = new MultilineElement('test');

        $m[] = 'a';
        $m[] = 'b';
        $m[] = 'c';

        $this->assertSame(['a', 'b', 'c'], $m->getValue());
    }

    public function testMultilineArrayUnset()
    {
        $m = new MultilineElement('test');

        $m[] = 'a';
        $m[] = 'b';
        $m[] = 'c';

        unset($m[1]);
        $this->assertSame(['a', 'c'], $m->getValue());

        unset($m[3]);
        $this->assertSame(['a', 'c'], $m->getValue());
    }

    // let’s have some fun with elements

    public function testGroupIsDefined()
    {
        $g = new GroupElement('test');

        // empty => false
        $this->assertFalse($g->isDefined());

        // not empty, but the contained element is empty
        $g[] = new Element('foo');
        $this->assertFalse($g->isDefined());

        // array access rocks...
        $g[0]->setValue('bar');
        $this->assertTrue($g->isDefined());
    }

    public function scalarValueProvider()
    {
        return [
            ['foo'], [12345], [3.14157], [true], [false], [null]
        ];
    }

    /**
     * @dataProvider scalarValueProvider
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ARINException
     * @expectedException Dormilich\WebService\ARIN\Exceptions\DataTypeException
     */
    public function testGroupDoesNotAcceptPrimitives($value)
    {
        $g = new GroupElement('test');
        $g->addValue($value);
    }
}
