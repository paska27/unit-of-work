<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Definition\Repository;
use Isolate\UnitOfWork\Entity\Property\PHPUnitValueComparer;
use Isolate\UnitOfWork\Entity\Property\ValueComparer;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;

/**
 * @api
 */
class Comparer
{
    /**
     * @var ValueComparer
     */
    private $propertyValueComparer;

    /**
     * @var Repository
     */
    private $definitions;

    /**
     * @param Repository $definitions
     * @param null|ValueComparer $valueComparer
     */
    public function __construct(Repository $definitions, ValueComparer $valueComparer = null)
    {
        $this->propertyValueComparer = $valueComparer ? $valueComparer : new PHPUnitValueComparer();
        $this->definitions = $definitions;
    }

    /**
     * @param $firstEntity
     * @param $secondEntity
     * @return bool
     * @throws InvalidArgumentException
     * 
     * @api
     */
    public function areEqual($firstEntity, $secondEntity)
    {
        $entityDefinition = $this->definitions->getDefinition($firstEntity);

        if (!$entityDefinition->fitsFor($secondEntity)) {
            throw new InvalidArgumentException("You can't compare entities of different type.");
        }

        foreach ($entityDefinition->getObservedProperties() as $property) {
            if ($this->propertyValueComparer->hasDifferentValue($property, $firstEntity, $secondEntity)) {
                return false;
            }
        }

        return true;
    }
}
