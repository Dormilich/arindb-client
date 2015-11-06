<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Test\Stringer;

class ElementTest extends PHPUnit_Framework_TestCase
{
	public function testElementImplementsInterfaces()
	{
		$elem = new Element('test');

		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\ElementInterface', $elem);
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\XMLHandler', $elem);
	}

	public function testElementHasCorrectTagNameWithoutNamespace()
	{
		$foo = new Element('foo');
		$this->assertSame('foo', $foo->getName());

		$bar = new Element('abc:bar');
		$this->assertSame('bar', $bar->getName());
	}

	/**
	 * @expectedException LogicException
	 */
	public function testNamespaceMustBeURI()
	{
		new Element('foo:bar', 'foo-bar-quux');
	}

	public function testElementHasCorrectTagNameUsingNamespace()
	{
		$foo = new Element('foo:bar', 'http://example.org/foo');
		$this->assertSame('bar', $foo->getName());
	}

	/**
	 * @expectedException LogicException
	 */
	public function testNamespaceRequiresPrefix()
	{
		new Element('bar', 'http://example.org/foo');
	}

	public function scalarValueProvider()
	{
		return [
			['foo'], [12345], [3.14157], [true], [false], 
		];
	}

	/**
	 * @dataProvider scalarValueProvider
	 */
	public function testElementHasOnlyStringValues($value)
	{
		$elem = new Element('test');
		$elem->setValue($value);

		$this->assertSame((string) $value, $elem->getValue());
	}

	public function testElementsAcceptsStringObject()
	{
		$elem = new Element('test');
		$obj = new Stringer('foo');
		$elem->setValue($obj);

		$this->assertSame('foo', $elem->getValue());
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\DataTypeException
	 */
	public function testElementThrowsExceptionOnNonStringifiableObject()
	{
		$elem = new Element('test');
		$elem->setValue(new stdClass);
	}

	public function testElementSetValueVariants()
	{
		$elem = new Element('test');

		$elem->setValue('foo');
		$this->assertSame('foo', $elem->getValue());

		$elem->setValue('bar');
		$this->assertSame('bar', $elem->getValue());

		$elem->addValue('foo');
		$this->assertSame('foo', $elem->getValue());

		$elem->addValue('bar');
		$this->assertSame('bar', $elem->getValue());
	}

	public function testElementUnsetValue()
	{
		$elem = new Element('test');

		$this->assertNull($elem->getValue());

		$elem->setValue('foo');
		$this->assertSame('foo', $elem->getValue());

		$elem->setValue(null);
		$this->assertNull($elem->getValue());
	}

	public function testElementValidity()
	{
		$elem = new Element('test');

		// starts with undefined
		$this->assertFalse($elem->isValid());

		// define value
		$elem->setValue(1);
		$this->assertTrue($elem->isValid());

		// unset value
		$elem->setValue(null);
		$this->assertFalse($elem->isValid());
	}

	public function testAttributesLeavesValueUntouched()
	{
		$elem = new Element('test');

		$this->assertNull($elem->foo);

		$elem->foo = 'foo';

		$this->assertSame('foo', $elem->foo);
		$this->assertFalse($elem->isValid());

		unset($elem->foo);

		$this->assertNull($elem->foo);

		// unknown attribute does not issue an error
		unset($elem->bar);
	}
}
