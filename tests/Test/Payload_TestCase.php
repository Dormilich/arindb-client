<?php

namespace Test;

class Payload_TestCase extends \PHPUnit_Framework_TestCase
{
    private function getFilePath($name)
    {
        $file = __DIR__ . '/_fixtures/' . $name . '.xml';
        $file = str_replace('/Test/', '/payload/', $file);
        if (is_readable($file)) {
            return $file;
        }
        throw new \UnexpectedValueException("File $name.xml not found.");
    }

    public function loadXML($name, $class = 'SimpleXMLElement')
    {
        $file = __DIR__ . '/_fixtures/' . $name . '.xml';
        return simplexml_load_file($this->getFilePath($name), $class, \LIBXML_NOBLANKS);
    }

    public function loadDOM($name)
    {
        $document = new \DOMDocument;
        $document->load($this->getFilePath($name), \LIBXML_NOBLANKS);
        return $document;
    }
}
