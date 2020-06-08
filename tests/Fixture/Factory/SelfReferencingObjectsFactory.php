<?php

namespace Cubicl\ObjectGraphGenerator\Tests\Fixture\Factory;

use Cubicl\ObjectGraphGenerator\FactoryInterface;
use Cubicl\ObjectGraphGenerator\ObjectGraphGenerator;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\SelfReferencingObjects;
use Faker\Generator;

class SelfReferencingObjectsFactory implements FactoryInterface
{
    public function __invoke(ObjectGraphGenerator $objectGraphGenerator, Generator $faker): SelfReferencingObjects
    {
        return new SelfReferencingObjects(null, 1);
    }
}
