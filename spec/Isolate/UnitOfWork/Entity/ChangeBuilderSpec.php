<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use Isolate\UnitOfWork\Tests\Double\ProtectedEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeBuilderSpec extends ObjectBehavior
{
    function it_returns_false_when_there_is_no_difference_between_same_property_in_two_objects()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->isDifferent(new Property("firstName"), $firstObject, $secondObject)->shouldReturn(false);
    }

    function it_returns_true_when_there_is_any_difference_between_same_property_in_two_objects()
    {
        $firstObject = new EntityFake(1, "Norbert");
        $secondObject = new EntityFake(1, "Michal");

        $this->isDifferent(new Property("firstName"), $firstObject, $secondObject)->shouldReturn(true);;
    }

    function it_throws_exception_when_at_least_one_of_compared_values_is_not_an_object()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("isDifferent", [new Property("firstName"), "fakeEntity", new EntityFake()]);

        $this->shouldThrow(new InvalidArgumentException("Compared values need to be a valid objects."))
            ->during("isDifferent", [new Property("firstName"), new EntityFake(), "fakeEntity"]);
    }

    function it_throws_exception_when_compared_objects_have_different_classes()
    {
        $this->shouldThrow(new InvalidArgumentException("Compared values need to be an instances of the same class."))
            ->during("isDifferent", [new Property("firstName"), new ProtectedEntity(), new EntityFake()]);
    }

    function it_throws_exception_when_property_does_not_exists()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->shouldThrow(new NotExistingPropertyException("Property \"title\" does not exists in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" class."))
            ->during("isDifferent", [new Property("title"), $firstObject, $secondObject]);
    }

    function it_throws_exception_when_property_values_are_identical_in_both_objects()
    {
        $firstObject = $secondObject = new EntityFake(1, "Norbert", "Orzechowicz");

        $this->shouldThrow(new RuntimeException("There are no differences between objects properties."))
            ->during("buildChange", [new Property("firstName"), $firstObject, $secondObject]);
    }

    function it_throws_exception_when_property_values_are_identical_arrays()
    {
        $firstObject = new EntityFake(1);
        $firstObject->setItems([new EntityFake(5), new EntityFake(6)]);

        $secondObject = new EntityFake(1);
        $secondObject->setItems([new EntityFake(5), new EntityFake(6)]);

        $this->shouldThrow(new RuntimeException("There are no differences between objects properties."))
            ->during("buildChange", [new Property("items"), $firstObject, $secondObject]);
    }

    function it_build_change_for_different_objects()
    {
        $firstObject = new EntityFake(1, "Norbert");
        $secondObject = clone($firstObject);

        $secondObject->changeFirstName("Michal");

        $change = $this->buildChange(new Property("firstName"), $firstObject, $secondObject);
        $change->getOriginValue()->shouldReturn("Norbert");
        $change->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change");
    }
}

