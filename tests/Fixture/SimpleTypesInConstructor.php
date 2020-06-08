<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests\Fixture;

class SimpleTypesInConstructor
{
    private int $foo;
    private bool $bar;

    public function __construct(int $foo, bool $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFoo(): int
    {
        return $this->foo;
    }

    public function isBar(): bool
    {
        return $this->bar;
    }
}
