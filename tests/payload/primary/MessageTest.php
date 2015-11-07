<?php

use Dormilich\WebService\ARIN\Payloads\Message;
use Dormilich\WebService\ARIN\Payloads\Attachment;
use Test\Payload_TestCase;

class MessageTest extends Payload_TestCase
{
    public function testSerialise()
    {
        $payload = new Message;
        $file = new Attachment;

        $payload['subject'] = 'SUBJECT';
        $payload['text'] = 'Line 1';
        $payload['category'] = 'NONE';

        $file['data'] = 'DATA';
        $file['filename'] = 'FILENAME';

        $payload['attachments'][] = $file;

        $this->assertTrue($payload->isValid());

        $actual = $payload->toXML(NULL);
        $expected = $this->loadDOM('message-request');

        $this->assertSame($expected->saveXML(), $actual->saveXML());
    }

    public function testParseXML()
    {
        $payload = new Message;
        $payload->parse($this->loadXML('message-response'));

        $this->assertSame([
            'messageId'   => 'MESSAGEID',
            'createdDate' => 'Tue Feb 28 17:41:17 EST 2012',
            'subject'     => 'SUBJECT',
            'text'        => ['Line 1'],
            'category'    => 'NONE',
            'attachments' => [],
            'attachmentReferences' => [[
                'attachmentFilename' => 'ATTACHMENTFILENAME',
                'attachmentId'       => 'ATTACHMENTID',
            ]],
        ], $payload->getValue());

        $this->assertFalse($payload->isValid());
    }
}
