<?php
namespace Isolate\UnitOfWork\Object;

interface InterfacePropertyAccessor
{
    /**
     * @param string $object
     * @param string $propertyName
     *
     * @return mixed
     */
    public function getValue($object, $propertyName);
}