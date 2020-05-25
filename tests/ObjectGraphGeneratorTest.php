<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests;

use Cubicl\ObjectGraphGenerator\ObjectGraphGenerator;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\SimpleTypesInConstructor;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\SimpleTypesInCreateMethod;
use PHPUnit\Framework\TestCase;

class ObjectGraphGeneratorTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataForTest
     *
     * @param string $class
     */
    public function itShouldGenerateObjectOfGivenType(string $class): void
    {
        $objectGraphGenerator = $this->getUnitUnderTest();
        $actual = $objectGraphGenerator->generate($class, 1);

        $this->assertInstanceOf($class, $actual);
    }

    public function dataForTest(): array
    {
        return [
            'simple types in constructor' => [SimpleTypesInConstructor::class],
            'simple types in create method' => [SimpleTypesInCreateMethod::class],
        ];
    }

    private function getUnitUnderTest(): ObjectGraphGenerator
    {
        return new ObjectGraphGenerator();
    }
}
