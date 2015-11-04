<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class represents an XML element that may only contain previously 
 * specified string values.
 */
class IntElement extends Element
{
    /**
     * @var array $options Options for the filter function.
     */
    protected $options = [];

    /**
     * Setting up the basic XML definition and the limits for the integer range. 
     * 
     * Examples:
     *  * with namespace:
     *      - new IntElement('ns:id', 'http://example.org/id');          // no limits
     *      - new IntElement('ns:id', 'http://example.org/id', 1, 9);    // min & max limit
     *      - new IntElement('ns:id', 'http://example.org/id', 1);       // only min limit
     *      - new IntElement('ns:id', 'http://example.org/id', NULL, 9); // only max limit
     *  * without namespace:
     *      - new IntElement('id');          // no limits
     *      - new IntElement('id', 1, 9);    // min & max limit
     *      - new IntElement('id', 1);       // only min limit
     *      - new IntElement('id', NULL, 9); // only max limit
     * 
     * @param string $name Tag name.
     * @param string $ns (optional) Namespace URI.
     * @param integer|NULL $min Lower boundary.
     * @param integer|NULL $max Upper boundary.
     * @return self
     * @throws LogicException Namespace prefix missing.
     */
    public function __construct($name, $ns = NULL)
    {
        $this->setNamespace((string) $name, $ns);

        $args = array_slice(func_get_args(), 1, 3);

        if ($this->namespace) {
            array_shift($args);
        }

        $this->setFilterOptions(array_slice($args, -2));
    }

    /**
     * Parse the min/max limits from the passed variables.
     * 
     * @param array $args parse first item into min_range and second into max_range.
     * @return void
     */
    private function setFilterOptions(array $args)
    {
        if (isset($args[0])) {
            $min = filter_var($args[0], \FILTER_VALIDATE_INT);
            if (is_int($min)) {
                $this->options['min_range'] = $min;
            }
        }
        if (isset($args[1])) {
            $max = filter_var($args[1], \FILTER_VALIDATE_INT);
            if (is_int($max)) {
                $this->options['max_range'] = $max;
            }
        }
    }

    /**
     * Check if a value qualifies as integer and is converted to its numeric representation.
     * 
     * @param mixed $value 
     * @return string
     * @throws Exception Value constraint violation.
     */
    protected function convert($value)
    {
        $value = parent::convert($value);
        $int = filter_var($value, \FILTER_VALIDATE_INT, ['options' => $this->options]);
        if (false !== $int) {
            return (string) $int;
        }
        $msg = 'Value "%s" is not a valid integer in the [%s] element.';
        throw new DataTypeException(sprintf($msg, $value, $this->name));
    }
}
