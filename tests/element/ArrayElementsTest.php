<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\ArrayElement;
use Dormilich\WebService\ARIN\Lists\Group;
use Dormilich\WebService\ARIN\Lists\MultiLine;

class ArrayElementsTest extends PHPUnit_Framework_TestCase
{
    public function testElementsInheritElement()
    {
        // this class changes only the toDOM() method
        $line = new MultiLine('test');
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Lists\ArrayElement', $line);

        $group = new Group('test');
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Lists\ArrayElement', $group);
    }

    public function testMultilineSetValues()
    {
        $m = new MultiLine('test');

        $m->setValue(1);
        $this->assertSame(['1'], $m->getValue());

        $m->setValue(2);
        $this->assertSame(['2'], $m->getValue());
    }

    public function testMultilineAddValues()
    {
        $m = new MultiLine('test');

        $m->addValue(1);
        $this->assertSame(['1'], $m->getValue());

        $m->addValue(2);
        $this->assertSame(['1', '2'], $m->getValue());
    }

    public function testMultilineIsDefined()
    {
        $m = new MultiLine('test');

        $this->assertFalse($m->isDefined());

        $m->setValue(1);
        $this->assertTrue($m->isDefined());
    }

    public function testMultilineArrayIsset()
    {
        $m = new MultiLine('test');

        $this->assertFalse(isset($m[0]));

        $m->setValue(1);
        $this->assertTrue(isset($m[0]));

        $this->assertFalse(isset($m['x']));
    }

    public function testMultilineArrayGet()
    {
        $m = new MultiLine('test');

        $this->assertNull($m[0]);

        $m->setValue(1);
        $this->assertSame('1', $m[0]);
    }

    public function testMultilineArraySet()
    {
        $m = new MultiLine('test');

        $m[0] = 1;
        $this->assertSame('1', $m[0]);

        $m[10] = 2;
        $this->assertSame('2', $m[1]);
        $this->assertSame(['1', '2'], $m->getValue());
    }

    public function testMultilineArrayPush()
    {
        $m = new MultiLine('test');

        $m[] = 'a';
        $m[] = 'b';
        $m[] = 'c';

        $this->assertSame(['a', 'b', 'c'], $m->getValue());
        $this->assertCount(3, $m);
    }

    public function testMultilineArrayUnset()
    {
        $m = new MultiLine('test');

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
        $g = new Group('test');

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
        $g = new Group('test');
        $g->addValue($value);
    }
}
