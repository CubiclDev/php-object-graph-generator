<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests\Fixture;

class CircularReferenceObjectA
{
    /** @var CircularReferenceObjectB|null */
    private $objectB;

    public function getObjectB(): ?CircularReferenceObjectB
    {
        return $this->objectB;
    }

    public function setObjectB(?CircularReferenceObjectB $objectB): void
    {
        $this->objectB = $objectB;
    }
}
