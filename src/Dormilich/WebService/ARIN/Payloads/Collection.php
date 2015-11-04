<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Lists\ObjectGroup;

/**
 * This payload is used as a container, to store multiple payloads and return 
 * them back to the customer. This list payload will act as a wrapper for 
 * ticket searching, the setting of phones on POCs, etc. 
 */
class Collection extends ObjectGroup
{
    public function __construct()
    {
        parent::__construct('collection', 'Payloads\\Payload');
    }
}
