<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator;

use Faker\Generator;

interface FactoryInterface
{
    public function __invoke(ObjectGraphGenerator $objectGraphGenerator, Generator $faker): object;
}
