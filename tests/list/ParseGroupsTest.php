<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Dormilich\WebService\ARIN\Lists\Group;

class ParseGroupsTest extends PHPUnit_Framework_TestCase
{
	public function testParseMultiLine()
	{
		$string  = '<comment><line number="1">foo</line><line number="2">bar</line>';
		$string .= '<line number="3">baz</line><line number="4">quux</line></comment>';
		$xml = simplexml_load_string($string);

		$elem = new MultiLine('comment');
		$elem->parse($xml);

		$this->assertSame(['foo', 'bar', 'baz', 'quux'], $elem->getValue());
	}

	public function testParseAndReserialiseMultiLine()
	{
		$string  = '<comment><line number="1">foo</line><line number="2">bar</line>';
		$string .= '<line number="3">baz</line><line number="4">quux</line></comment>';
		$xml = simplexml_load_string($string);

		$elem = new MultiLine('comment');
		$elem->parse($xml);

        $doc = new DOMDocument;
        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame($string, $xml);
	}

	public function testParseGroup()
	{
		$string  = '<list><item>1</item><item>abc</item>';
		$string .= '<test><item name="bar"/></test></list>';
		$xml = simplexml_load_string($string);

		$group = new Group('list');
		$group->parse($xml);

		$this->assertCount(3, $group);

		$this->assertSame('1', $group[0]->getValue());
		$this->assertSame('item', $group[0]->getName());

		$this->assertSame('abc', $group[1]->getValue());
		$this->assertSame('item', $group[1]->getName());

		$this->assertCount(1, $group[2]);
		$this->assertSame('test', $group[2]->getName());
		$this->assertSame('item', $group[2][0]->getName());
		$this->assertSame('bar', $group[2][0]->name);
		$this->assertFalse($group[2][0]->isDefined());
	}
}
