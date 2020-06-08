<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests\Fixture;

class CircularReferenceObjectB
{
    /** @var CircularReferenceObjectA|null */
    private $objectA;

    public function getObjectA(): ?CircularReferenceObjectA
    {
        return $this->objectA;
    }

    public function setObjectA(CircularReferenceObjectA $objectA): void
    {
        $this->objectA = $objectA;
    }


}
