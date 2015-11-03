<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\BoolElement;
use Dormilich\WebService\ARIN\Elements\FixedElement;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Test\Stringer;

class SimpleElementsTest extends PHPUnit_Framework_TestCase
{
	public function testElementsInheritElement()
	{
		$bool = new BoolElement('test');
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $bool);

		$fixed = new FixedElement('test', []);
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $fixed);

		$length = new LengthElement('test', 1);
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $length);
	}

	public function boolValueProvider()
	{
		return [
			[true, true], [false, false], 
			[1, true], [0, false], 
			['true', true], ['false', false], 
			['foo', false], ['', false], 
			[new stdClass, false], [null, false], 
		];
	}

	/**
	 * @dataProvider boolValueProvider
	 */
	public function testBoolElementAcceptsAnyInput($value, $test)
	{
		$bool = new BoolElement('test');
		$bool->setValue($value);

		$this->assertSame($test, $bool->getValue());
	}

	public function testFixedElementAllowsDefinedValues()
	{
		$fixed = new FixedElement('test', ['foo', 'bar']);

		$fixed->setValue('foo');
		$this->assertSame('foo', $fixed->getValue());

		$fixed->setValue('bar');
		$this->assertSame('bar', $fixed->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ARINException
     * @expectedException Dormilich\WebService\ARIN\Exceptions\DataTypeException
	 */
	public function testFixedElementRejectsUndefinedValue()
	{
		$fixed = new FixedElement('test', ['foo', 'bar']);

		$fixed->setValue('quux');
	}

	public function testFixedElementConvertsInputToString()
	{
		$fixed = new FixedElement('test', ['foo', 'bar']);
		$bar = new Stringer('bar');

		$fixed->setValue($bar);
		$this->assertSame('bar', $fixed->getValue());
	}

	public function testFixedElementConvertsDefinitionsToString()	
	{
		// main use is on numbers but their test is less explicit
		$fixed = new FixedElement('test', [new Stringer('bar')]);

		$fixed->setValue('bar');
		$this->assertSame('bar', $fixed->getValue());
	}

	public function testLengthElementAcceptsInputWithStringLength()
	{
		$length = new LengthElement('test', 3);

		$length->setValue('foo');
		$this->assertSame('foo', $length->getValue());

		$length->setValue(123);
		$this->assertSame('123', $length->getValue());
	}

	public function invalidLengthInputProvider()
	{
		return [
			[0], [12345], ['foo']
		];
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ARINException
     * @expectedException Dormilich\WebService\ARIN\Exceptions\DataTypeException
	 * @dataProvider invalidLengthInputProvider
	 */
	public function testLengthElementRejectsInvalidInput($value)
	{
		$length = new LengthElement('test', 2);

		$length->setValue($value);
	}

	public function invalidLengthProvider()
	{
		return [
			[0], [-2], [null], ['foo']
		];
	}

	/**
	 * @dataProvider invalidLengthProvider
	 */
	public function testLengthElementDefaultsToLength1($value)
	{
		$length = new LengthElement('test', $value);

		$length->setValue('x');
		$this->assertSame('x', $length->getValue());
	}
}
