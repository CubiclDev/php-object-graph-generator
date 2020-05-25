<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests\Fixture;

class SimpleTypesInConstructor
{
    private int $foo;

    public function __construct(int $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): int
    {
        return $this->foo;
    }
}
