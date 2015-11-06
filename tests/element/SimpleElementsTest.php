<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Boolean;
use Dormilich\WebService\ARIN\Elements\Integer;
use Dormilich\WebService\ARIN\Elements\IP;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Elements\RegExp;
use Dormilich\WebService\ARIN\Elements\Selection;
use Test\Stringer;

class SimpleElementsTest extends PHPUnit_Framework_TestCase
{
	public function testElementsInheritElement()
	{
		$bool = new Boolean('test');
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $bool);

		$length = new Integer('test');
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $length);

		$length = new IP('test');
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $length);

		$length = new LengthElement('test', 1);
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $length);

		$fixed = new RegExp('test', '/./');
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $fixed);

		$fixed = new Selection('test', [1]);
		$this->assertInstanceOf(
			'Dormilich\WebService\ARIN\Elements\Element', $fixed);
	}

	// Element

	public function testElementWithValidationCallback()
	{
		$elem = new Element('test', NULL, 'ctype_xdigit');
		$elem->setValue(md5('validation callback test'));

		$this->assertTrue($elem->isValid());
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testElementWithValidationCallbackAndInvalidValueFails()
	{
		$elem = new Element('test', NULL, 'ctype_xdigit');
		$elem->setValue('validation callback test');

		$this->assertTrue($elem->isValid());
	}

	// Boolean

	public function boolValueProvider()
	{
		return [
			[true, true],   [false, false], 
			[1, true],      [0, false], 
			['true', true], ['false', false], 
			['on', true],   ['off', false], 
			['', false], 
			// NULL resets the content!
			[NULL, NULL], 
		];
	}

	/**
	 * @dataProvider boolValueProvider
	 */
	public function testBooleanAcceptsAnyInput($value, $test)
	{
		$bool = new Boolean('test');
		$bool->setValue($value);

		$this->assertSame($test, $bool->getValue());
	}

	// Selection

	public function testSelectionAllowsDefinedValues()
	{
		$fixed = new Selection('test', ['foo', 'bar']);

		$fixed->setValue('foo');
		$this->assertSame('foo', $fixed->getValue());

		$fixed->setValue('bar');
		$this->assertSame('bar', $fixed->getValue());
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testSelectionRejectsUndefinedValue()
	{
		$fixed = new Selection('test', ['foo', 'bar']);

		$fixed->setValue('quux');
	}

	public function testSelectionConvertsInputToString()
	{
		$fixed = new Selection('test', ['foo', 'bar']);
		$bar = new Stringer('bar');

		$fixed->setValue($bar);
		$this->assertSame('bar', $fixed->getValue());
	}

	public function testSelectionConvertsDefinitionsToString()	
	{
		// main use is on numbers but their test is less explicit
		$fixed = new Selection('test', [new Stringer('bar')]);

		$fixed->setValue('bar');
		$this->assertSame('bar', $fixed->getValue());
	}

	public function testSelectionWithNamespace()
	{
		$fixed = new Selection('ex:test', 'http://example.org/ex', ['foo', 'bar']);

		$this->assertSame('test', $fixed->getName());

		$fixed->setValue('foo');
		$this->assertSame('foo', $fixed->getValue());
	}

	/**
	 * @expectedException LogicException
	 */
	public function testSelectionRequiresDefinitions()
	{
		$fixed = new Selection('ex:test', 'http://example.org/ex');
	}

	/**
	 * @expectedException LogicException
	 */
	public function testSelectionWithNamespaceRequiresPrefix()
	{
		$fixed = new Selection('test', 'http://example.org/ex', []);
	}

	// Length

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
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
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

	public function testLengthElementWithNamespace()
	{
		$length = new LengthElement('ex:test', 'http://example.org/ex', 3);

		$this->assertSame('test', $length->getName());

		$length->setValue('foo');
		$this->assertSame('foo', $length->getValue());
	}

	/**
	 * @expectedException LogicException
	 */
	public function testLengthElementWithNamespaceRequiresPrefix()
	{
		$length = new LengthElement('test', 'http://example.org/ex');
	}

	// Integer

	public function testIntegerAcceptsNumericInput()
	{
		$int = new Integer('test');

		$int->setValue(17);
		$this->assertSame(17, $int->getValue());

		$int->setValue(-18);
		$this->assertSame(-18, $int->getValue());

		$int->setValue('9');
		$this->assertSame(9, $int->getValue());

		$int->setValue(' -5 ');
		$this->assertSame(-5, $int->getValue());

		$int->setValue(new Stringer(13));
		$this->assertSame(13, $int->getValue());

		$int->setValue(true);
		$this->assertSame(1, $int->getValue());
	}

	public function integerRangeAndValueProvider()
	{
		return [
			[-1, 1, 0], [NULL, 5, -3], [8, NULL, 64367], 
			// limits are used in the correct order!
			[1, -1, 0], 
		];
	}

	/**
	 * @dataProvider integerRangeAndValueProvider
	 */
	public function testIntegerAcceptsInputInRange($min, $max, $value)
	{
		$int = new Integer('test', $min, $max);
		$int->setValue($value);

		$this->assertTrue($int->isValid());
	}

	public function invalidIntegerInputProvider()
	{
		return [
			[new stdClass], ['foo'], [3.147], [false], 
		];
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\DataTypeException
	 * @dataProvider invalidIntegerInputProvider
	 */
	public function testIntegerRejectsInvalidInput($value)
	{
		$int = new Integer('test');

		$int->setValue($value);
	}

	public function integerRangeAndInvalidValueProvider()
	{
		return [
			[-1, 1, 18], [NULL, 5, 7], [8, NULL, 3], 
		];
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 * @dataProvider integerRangeAndInvalidValueProvider
	 */
	public function testIntegerRejectsInputOutOfRange($min, $max, $value)
	{
		$int = new Integer('test', $min, $max);

		$int->setValue($value);
	}

	public function testIntegerWithNamespace()
	{
		$int = new Integer('ex:test', 'http://example.org/ex');
		$this->assertSame('test', $int->getName());
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testIntegerWithNamespaceAndMin()
	{
		$int = new Integer('ex:test', 'http://example.org/ex', -1);
		$int->setValue(-2);
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testIntegerWithNamespaceAndMax()
	{
		$int = new Integer('ex:test', 'http://example.org/ex', NULL, 1);
		$int->setValue(2);
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 * @dataProvider integerRangeAndInvalidValueProvider
	 */
	public function testIntegerWithNamespaceAndMinMax($min, $max, $value)
	{
		$int = new Integer('ex:test', 'http://example.org/ex', $min, $max);
		$int->setValue($value);
	}

	/**
	 * @expectedException LogicException
	 */
	public function testIntegerWithNamespaceRequiresPrefix()
	{
		$fixed = new Selection('test', 'http://example.org/ex');
	}

	// IP

	public function IPProvider()
	{
		return [
			[NULL, '1a7:0815::1',     '1a7:0815::1'], 
			[NULL, '127.0.0.1',       '127.0.0.1'], 
			[NULL, '192.168.001.010', '192.168.001.010'], 
			[IP::PADDED,   '127.0.0.1',       '127.000.000.001'], 
			[IP::UNPADDED, '192.168.001.010', '192.168.1.10'], 
		];
	}

	/**
     * @dataProvider IPProvider
	 */
	public function testIPwithValidAddresses($flag, $value, $expected)
	{
		$ip = new IP('test', $flag);

		$ip->setValue($value);
		$this->assertSame($expected, $ip->getValue());
	}

	public function testIPwithNamespace()
	{
		$ip = new IP('ex:test', 'http://example.org/ex');
		$this->assertSame('test', $ip->getName());
	}

	public function testIPwithNamespaceAndFlag()
	{
		$ip = new IP('ex:test', 'http://example.org/ex', IP::PADDED);

		$ip->setValue('192.168.1.10');
		$this->assertSame('192.168.001.010', $ip->getValue());

		$ip = new IP('ex:test', 'http://example.org/ex', IP::UNPADDED);

		$ip->setValue('127.000.000.001');
		$this->assertSame('127.0.0.1', $ip->getValue());
	}

	/**
	 * @expectedException LogicException
	 */
	public function testIPwithNamespaceRequiresPrefix()
	{
		$ip = new IP('test', 'http://example.org/ex');
	}

	public function testIPwithPaddingFlagOnSettingValue()
	{
		$ip = new IP('test', IP::PADDED);

		$ip->setValue('192.168.17.2', IP::UNPADDED);
		$this->assertSame('192.168.17.2', $ip->getValue());
	}

	// RegExp

	public function testRegExpWithValidInput()
	{
		$re = new RegExp('test', '/\w+/');
		$re->setValue('foo');

		$this->assertTrue($re->isValid());
	}

	/**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testRegExpWithInvalidInput()
	{
		$re = new RegExp('test', '/\w+/');
		$re->setValue('...');
	}

	/**
	 * @expectedException LogicException
	 */
	public function testRegExpWithInvalidPattern()
	{
		$re = new RegExp('test', 'foo');
	}
}
