<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator\Tests;

use Cubicl\ObjectGraphGenerator\ObjectGraphGenerator;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\CircularReferenceObjectA;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\Factory\SelfReferencingObjectsFactory;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\SelfReferencingObjects;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\SimpleTypesInConstructor;
use Cubicl\ObjectGraphGenerator\Tests\Fixture\SimpleTypesInCreateMethod;
use PHPUnit\Framework\TestCase;

class ObjectGraphGeneratorTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataForTest
     * @param class-string $class
     */
    public function itShouldGenerateObjectOfGivenType(string $class): void
    {
        $objectGraphGenerator = $this->getUnitUnderTest();
        $actual = $objectGraphGenerator->generate($class);

        $this->assertInstanceOf($class, $actual);
    }

    /**
     * @test
     */
    public function itShouldUseTemporaryConfigBeforeRegistry(): void
    {
        $objectGraphGenerator = $this->getUnitUnderTest();
        /** @var SimpleTypesInConstructor $actual */
        $actual = $objectGraphGenerator->generateWithTemporaryConfig(SimpleTypesInConstructor::class, [
            'Cubicl\ObjectGraphGenerator\Tests\Fixture\SimpleTypesInConstructor:bar' => fn () => true,
        ]);

        $this->assertTrue($actual->isBar());
    }

    /**
     * @return array<string,array<class-string>>
     */
    public function dataForTest(): array
    {
        return [
            'simple types in constructor' => [SimpleTypesInConstructor::class],
            'simple types in create method' => [SimpleTypesInCreateMethod::class],
            'self referencing objects' => [SelfReferencingObjects::class],
            'circular referencing objects' => [CircularReferenceObjectA::class],
        ];
    }

    private function getUnitUnderTest(): ObjectGraphGenerator
    {
        return new ObjectGraphGenerator([
            SelfReferencingObjects::class => new SelfReferencingObjectsFactory(),
            'Cubicl\ObjectGraphGenerator\Tests\Fixture\SimpleTypesInConstructor:bar' => fn() => false,
        ]);
    }
}
