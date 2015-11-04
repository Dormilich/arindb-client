<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class accepts any serialisable object(s) as its content that match the 
 * predefined classes. 
 */
class ObjectGroup extends Group
{
	/**
	 * @var array $classes Allowed class names of the members.
	 */
	protected $classes = [];

	/**
	 * Set the base name of the group element and the required class names of 
	 * its collection members.
	 * 
	 * @param string $name Tag name.
	 * @return self
	 */
	public function __construct($name, array $classes)
	{
		parent::__construct($name);

		foreach ($classes as $class) {
			if (!$this->resolveClass((string) $class)) {
				throw new \LogicException("Class name $class not found.");
			}
		}
	}

	/**
	 * Check (partial) class or interface name if it exists 1) in the global namespace 
	 * 2) in the ARIN namespace 3) in one of its sub folders. A class name may be 
	 * given ass fully qualified name (Dormilich\WebService\ARIN\Lists\Collection), 
	 * as partial qualified name (Payloads\Message), or as class name (Element).
	 * 
	 * @param string $className Class name.
	 * @return boolean TRUE if at least one matching class was found.
	 */
	protected function resolveClass($className)
	{
		if (class_exists($className)) {
			$this->classes[] = $className;
			return true;
		}

		$nsList = explode('\\', __NAMESPACE__);
		array_pop($nsList);
		$baseNS = implode('\\', $nsList) . '\\';

		$nameList = ['', 'Payloads\\', 'Elements\\', 'Lists\\'];
		$nameList = array_map(function ($category) use ($baseNS, $className) {
			return $baseNS . $category . $className;
		}, $nameList);

		// return FALSE if the class name could be found nowhere
		return array_reduce($nameList, function ($carry, $class) {
			if (class_exists($class)) {
				$this->classes[] = $class;
				return true;
			}
			return $carry or false;
		}, false);
	}

	/**
	 * Check if the value’s class is supported.
	 * 
	 * @param mixed $value 
	 * @return XMLHandler
	 * @throws Exception Value is not serialisable.
	 */
	protected function convert($value)
	{
		// no matter which classes are allowed, it must implement XMLHandler
		$value = parent::convert($value);
		// check if it's one of the allowed classes
		if ($this->supports($value)) {
			return $value;
		}
		$msg = 'Value of type %s is not a valid object for the [%s] element.';
		$type = is_object($value) ? get_class($value) : gettype($value);
		throw new DataTypeException(sprintf($msg, $type, $this->name));
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
			return $value instanceof $class ? true : ($carry or false);
		}, false);
	}
}
