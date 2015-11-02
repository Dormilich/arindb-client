<?php

namespace Dormilich\WebService\ARIN\Exceptions;

/**
 * Used for indicating a non-existing key in a collection.
 */
class NotFoundException 
    extends     \OutOfBoundsException
    implements  ARINException 
{}
