<?php

namespace Dormilich\WebService\ARIN\Exceptions;

/**
 * Used for indicating invalid data types while processing. Usually due to 
 * invalid input (e.g. setting an array where a string is expected).
 */
class RequestException 
    extends     \BadMethodCallException 
    implements  ARINException 
{}
