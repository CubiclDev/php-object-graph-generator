<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests\Fixture;

class SelfReferencingObjects
{
    /** @var SelfReferencingObjects|null */
    private $reference;

    /** @var int */
    private $number;

    public function __construct(?SelfReferencingObjects $reference, int $number)
    {
        $this->reference = $reference;
        $this->number    = $number;
    }

    public function getReference(): ?SelfReferencingObjects
    {
        return $this->reference;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

}
