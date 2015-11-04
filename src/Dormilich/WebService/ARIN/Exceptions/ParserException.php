<?php

namespace Dormilich\WebService\ARIN\Exceptions;

/**
 * Thrown when there is a critical problem parsing XML.
 */
class ParserException 
    extends     \RuntimeException 
    implements  ARINException 
{}
