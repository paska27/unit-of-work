<?php

namespace Isolate\UnitOfWork\Object;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;

/**
 * @api
 */
final class PropertyAccessor implements InterfacePropertyAccessor
{
    /**
     * @param $object
     * @param $propertyName
     * @return mixed
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     * 
     * @api
     */
    public function getValue($object, $propertyName)
    {
        $this->validateObject($object);
        $reflection = new \ReflectionClass($object);
        $this->validateProperty($reflection, $object, $propertyName);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param $object
     * @param $propertyName
     * @param $value
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     * 
     * @api
     */
    public function setValue($object, $propertyName, $value)
    {
        $this->validateObject($object);
        $reflection = new \ReflectionClass($object);
        $this->validateProperty($reflection, $object, $propertyName);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($object, $value);
    }

    /**
     * @param $value
     * @throws InvalidArgumentException
     */
    private function validateObject($value)
    {
        if (!is_object($value)) {
            throw new InvalidArgumentException(sprintf(
                "PropertyAccessor require object to access property, \"%s\" passed.",
                gettype($value)
            ));
        }
    }

    /**
     * @param \ReflectionClass $reflection
     * @param $object
     * @param $propertyName
     * @throws NotExistingPropertyException
     */
    private function validateProperty(\ReflectionClass $reflection, $object, $propertyName)
    {
        if (!$reflection->hasProperty($propertyName)) {
            throw new NotExistingPropertyException(sprintf(
                "Property \"%s\" does not exists in \"%s\" class.",
                $propertyName,
                get_class($object)
            ));
        }
    }
}
