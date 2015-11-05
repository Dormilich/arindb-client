<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Boolean;
use Dormilich\WebService\ARIN\Elements\Integer;
use Dormilich\WebService\ARIN\Lists\ArrayElement;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Dormilich\WebService\ARIN\Lists\Group;
use Dormilich\WebService\ARIN\Lists\NamedGroup;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;

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

        $group = new NamedGroup('test', 'foo');
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Lists\ArrayElement', $group);

        $group = new ObjectGroup('test', 'Element');
        $this->assertInstanceOf(
            'Dormilich\WebService\ARIN\Lists\ArrayElement', $group);
    }

    // MultiLine

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

    // Group

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

    public function testGroupCount()
    {
        $g = new Group('test');

        // empty 
        $this->assertCount(0, $g);

        // not empty, but not defined
        $g[] = new Element('foo');
        $this->assertCount(1, $g);

        // not empty and defined
        $g[0]->setValue('bar');
        $this->assertCount(1, $g);
    }

    public function invalidGroupValueProvider()
    {
        return [
            ['foo'], [12345], [3.14157], [true], [false], [null], [new stdClass], 
        ];
    }

    /**
     * @dataProvider invalidGroupValueProvider
     * @expectedException Dormilich\WebService\ARIN\Exceptions\DataTypeException
     */
    public function testGroupDoesNotAcceptPrimitives($value)
    {
        $g = new Group('test');
        $g->addValue($value);
    }

    public function testGetElementFromGroupByName()
    {
        $g = new Group('test');

        $a = new Element('foo');
        $a->setValue('first');
        $b = new Boolean('foo');
        $b->setValue(1);
        $c = new Integer('bar');
        $c->setValue(11);

        $g->addValue($a)->addValue($b)->addValue($c);

        $this->assertSame($a, $g->fetch('foo'));
        $this->assertSame($c, $g->fetch('bar'));
        $this->assertNull($g->fetch('quux'));

        $this->assertSame($a, $g['foo']);
        $this->assertSame($c, $g['bar']);
        $this->assertNull($g['quux']);

        $this->assertSame([$a, $b], $g->filter('foo'));
    }

    // NamedGroup

    public function testNamedGroupAcceptsAnyClassWithValidName()
    {
        $g = new NamedGroup('test', ['foo', 'bar']);

        // shove in a bunch of elements...
        $g[] = new Element('foo');
        $g[] = new Boolean('foo');
        $g[] = new Group('bar');

        $this->assertCount(3, $g);
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
     */
    public function testNamedGroupRejectsClassWithInvalidName()
    {
        $g = new NamedGroup('test', ['foo', 'bar']);
        $g[] = new Element('quux');
    }

    /**
     * @expectedException LogicException
     */
    public function testNamedGroupRejectsInvalidName()
    {
        $g = new NamedGroup('test', new stdClass);
    }

    // ObjectGroup

    /**
     * @expectedException LogicException
     */
    public function testObjectGroupRejectsInvalidClassName()
    {
        $g = new ObjectGroup('test', 'Foo');
    }

    public function testObjectGroupAcceptsAnyValidClass()
    {
        $g = new ObjectGroup('test', 'Element');

        // shove in a bunch of elements...
        $g[] = new Element('foo');
        $g[] = new Boolean('foo');
        $g[] = new Integer('bar');

        $this->assertCount(3, $g);
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
     */
    public function testObjectGroupRejectsInvalidClass()
    {
        $g = new ObjectGroup('test', 'Payload');
        $g[] = new Group('quux');
    }
}
