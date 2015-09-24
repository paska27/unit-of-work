<?php

namespace Isolate\UnitOfWork\Object;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

class InMemoryRegistry implements Registry
{
    /**
     * @var SnapshotMaker
     */
    private $snapshotMaker;

    /**
     * @var PropertyCloner
     */
    private $recoveryPoint;

    /**
     * @var array
     */
    private $objects;

    /**
     * @var array
     */
    private $snapshots;

    /**
     * @var array
     */
    private $removed;

    /**
     * @param SnapshotMaker $snapshotMaker
     * @param PropertyCloner $recoveryPoint
     */
    public function __construct(SnapshotMaker $snapshotMaker, PropertyCloner $recoveryPoint)
    {
        $this->snapshotMaker = $snapshotMaker;
        $this->recoveryPoint = $recoveryPoint;
        $this->objects = [];
        $this->snapshots = [];
        $this->removed = [];
    }

    /**
     * {@inheritdoc}
     */
    public function isRegistered($object)
    {
        return array_key_exists($this->getId($object), $this->objects);
    }

    /**
     * {@inheritdoc}
     */
    public function register($object)
    {
        $this->objects[$this->getId($object)] = $object;
        $this->snapshots[$this->getId($object)] = $this->snapshotMaker->makeSnapshotOf($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getSnapshot($object)
    {
        return $this->snapshots[$this->getId($object)];
    }

    /**
     * {@inheritdoc}
     */
    public function makeNewSnapshots()
    {
        foreach ($this->objects as $id => $entity) {
            $this->makeNewObjectSnapshot($entity);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function makeNewObjectSnapshot($object)
    {
        $this->snapshots[$this->getId($object)] = $this->snapshotMaker->makeSnapshotOf($object);
    }

    /**
     * {@inheritdoc}
     */
    public function isRemoved($object)
    {
        return array_key_exists($this->getId($object), $this->removed);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        if (!$this->isRegistered($object)) {
            $this->register($object);
        }

        $this->removed[$this->getId($object)] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanRemoved()
    {
        foreach ($this->removed as $id => $object) {
            unset($this->snapshots[$id]);
            unset($this->objects[$id]);
        }

        $this->removed = [];
    }

    /**
     * {@inheritDoc}
     */
    public function cleanRemovedObject($object)
    {
        $id = $this->getId($object);
        if (empty($id)) {
            throw new InvalidArgumentException("Object wasn't set as removed");
        }

        unset($this->snapshots[$id], $this->objects[$id], $this->removed[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return array_values($this->objects);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->removed = [];

        foreach ($this->snapshots as $id => $objectSnapshot) {
            $this->recoveryPoint->cloneProperties($this->objects[$id], $objectSnapshot);
        }
    }

    /**
     * @param $entity
     * @return string
     */
    private function getId($entity)
    {
        return spl_object_hash($entity);
    }
}
