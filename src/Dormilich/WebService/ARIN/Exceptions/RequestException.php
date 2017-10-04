<?php

namespace Dormilich\WebService\ARIN\Exceptions;

/**
 * Indicates that a condition for making a specific API request failed. 
 */
class RequestException 
    extends     \BadMethodCallException 
    implements  ARINException 
{}
