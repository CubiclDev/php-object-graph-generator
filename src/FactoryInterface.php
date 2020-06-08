<?php

namespace Cubicl\ObjectGraphGenerator;

use Cubicl\ObjectGraphGenerator\ObjectGraphGenerator;
use Faker\Generator;

interface FactoryInterface
{
    public function __invoke(ObjectGraphGenerator $objectGraphGenerator, Generator $faker): object;
}
