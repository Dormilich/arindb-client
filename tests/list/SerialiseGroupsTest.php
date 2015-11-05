<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Dormilich\WebService\ARIN\Lists\Group;

class SerialiseGroupsTest extends PHPUnit_Framework_TestCase
{

    public function testSerialiseMultiLine()
    {
        $doc = new DOMDocument;
        $elem = new MultiLine('comment');
        $elem
            ->addValue('foo')
            ->addValue('bar')
            ->addValue('baz')
            ->addValue('quux')
        ;

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $string  = '<comment>';
        $string .=   '<line number="1">foo</line>';
        $string .=   '<line number="2">bar</line>';
        $string .=   '<line number="3">baz</line>';
        $string .=   '<line number="4">quux</line>';
        $string .= '</comment>';

        $this->assertSame($string, $xml);
    }

    public function testSerialiseGroup()
    {
        $doc = new DOMDocument;
        $item = new Element('item');
        $test = new Group('test');
        $group = new Group('list');

        $item->setValue(1);
        $group->addValue($item);

        $item2 = clone $item;
        $item2->setValue('abc');
        $group->addValue($item2);

        $item3 = clone $item;
        $item3->name = 'bar';
        $test->addValue($item3);
        $group->addValue($test);

        $node = $group->toDOM($doc);
        $xml = $doc->saveXML($node);

        // indentation is for better readability
        $string  = '<list>';
        $string .=   '<item>1</item>';
        $string .=   '<item>abc</item>';
        $string .=   '<test>';
        $string .=     '<item name="bar"/>';
        $string .=   '</test>';
        $string .= '</list>';

        $this->assertSame($string, $xml);
    }
}
