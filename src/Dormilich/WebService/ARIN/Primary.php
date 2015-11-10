<?php

namespace Dormilich\WebService\ARIN;

/**
 * Denotes a primary payload that 
 *  1) can be used for a REST request
 *  2) contains a primary lookup key (handle)
 *  3) is subject to at least one of the CRUD operations without external data
 * 
 * Payloads that fulfill these conditions are Customer, Delegation, Net, Org, 
 * and Poc.
 */
interface Primary
{
    /**
     * Get the primary (lookup) key of the payload. If the key is not defined 
     * NULL is returned (as is the default value of an empty element).
     * 
     * @return string
     */
    public function getHandle();
}
