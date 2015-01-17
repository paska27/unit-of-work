<?php

namespace spec\Coduo\UnitOfWork\Command;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Tests\Double\NotPersistedEntityStub;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoveCommandSpec extends ObjectBehavior
{
    function it_has_object_that_should_be_persisted()
    {
        $object = new NotPersistedEntityStub();
        $this->beConstructedWith($object);
        $this->getObject()->shouldReturn($object);
    }

    function it_throws_exception_when_created_for_not_a_object_value()
    {
        $this->shouldThrow(new InvalidArgumentException("Remove command require object \"string\" type passed."))
            ->during("__construct", ["this is string"]);
    }
}