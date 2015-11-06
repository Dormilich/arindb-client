<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\Exceptions\ConstraintException;

/**
 * This class accepts any serialisable object(s) as its content that match the 
 * predefined classes. 
 */
class ObjectGroup extends Group
{
	/**
	 * @var array $classes Allowed class names of the members.
	 */
	protected $classList = [];

	/**
	 * Set the base name of the group element and the required class names of 
	 * its collection members.
	 * 
	 * @param string $name Tag name.
	 * @param string|array(string) $classList List of allowed classes in the collection.
	 * @return self
	 * @throws LogicException Class definition empty.
	 * @throws LogicException Class name not found.
	 */
	public function __construct($name, $classList)
	{
		parent::__construct($name);

		$classList = (array) $classList;

		if (count($classList) === 0) {
			throw new \LogicException('Allowed classes list must not be empty.');
		}

		foreach ($classList as $class) {
			if (!$this->resolveClass((string) $class)) {
				throw new \LogicException("Class name $class not found.");
			}
		}
	}

	/**
	 * Check class name if it is a payload or a generic element. This 
	 * restriction is necessary for the XML parser to decide which object to 
	 * instantiate (only payloads have a unique name).
	 * 
	 * @param string $className Class name.
	 * @return boolean TRUE if a matching class was found.
	 */
	protected function resolveClass($className)
	{
		$baseNS = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\'));

		if ($className === 'Element') {
			$this->classes[] = $baseNS . '\\Elements\\Element';
			return true;
		}

		$payload = $baseNS . '\\Payloads\\' . $className;

		if (class_exists($payload)) {
			$this->classes[] = $payload;
			return true;
		}

		return false;
	}

	/**
	 * Check if the value’s class is supported.
	 * 
	 * @param mixed $value 
	 * @return XMLHandler
	 * @throws ConstraintException Value is not serialisable.
	 */
	protected function convert($value)
	{
		// no matter which classes are allowed, it must implement XMLHandler
		$value = parent::convert($value);
		// check if it’s one of the allowed classes
		if ($this->supports($value)) {
			return $value;
		}
		$msg = 'Value of type %s is not a valid object for the [%s] element.';
		$type = is_object($value) ? get_class($value) : gettype($value);
		throw new ConstraintException(sprintf($msg, $type, $this->getName()));
	}

	/**
	 * Check if the value’s class is supported.
	 * 
	 * @param object $value 
	 * @return boolean
	 */
	public function supports($value)
	{
		return array_reduce($this->classes, function ($carry, $class) use ($value) {
			return $value instanceof $class ?: ($carry or false);
		}, false);
	}
}
