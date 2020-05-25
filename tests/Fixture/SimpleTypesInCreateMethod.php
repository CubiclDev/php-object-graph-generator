<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests\Fixture;

class SimpleTypesInCreateMethod
{
    private int $foo;

    public static function create(int $foo): self
    {
        $self = new self();
        $self->foo = $foo;

        return $self;
    }

    public function getFoo(): int
    {
        return $this->foo;
    }
}
