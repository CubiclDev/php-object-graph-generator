<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator;

use Closure;
use Faker\Factory;
use Faker\Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;

class ObjectGraphGenerator
{
    private const DEFAULT_SEED = 1;

    private Generator $fakerInstance;
    private PropertyInfoExtractor $propertyInfo;
    /** @var array<string, Closure|FactoryInterface> */
    private array $registry;

    /**
     * @param array<string, Closure|FactoryInterface> $registry
     */
    public function __construct(array $registry = [])
    {
        $this->fakerInstance = Factory::create();

        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];
        $this->propertyInfo = new PropertyInfoExtractor([], $typeExtractors, [], [], []);
        $this->fakerInstance->seed(self::DEFAULT_SEED);
        $this->registry = $registry;
    }

    /**
     * @param class-string $className
     */
    public function generate(string $className): object
    {
        return $this->generateObject($className);
    }

    /**
     * @param class-string $className
     * @param array<string, Closure|FactoryInterface> $config
     */
    public function generateWithTemporaryConfig(string $className, array $config): object
    {
        $temporaryGenerator = new self(
            array_merge($this->registry, $config)
        );

        return $temporaryGenerator->generateObject($className);
    }

    /**
     * @param class-string $className
     * @throws ReflectionException
     */
    private function generateObject(string $className): object
    {
        if ($this->isInRegistry($className)) {
            return (object) $this->getFromRegistry($className);
        }

        $class = new ReflectionClass($className);
        $factoryMethod = $this->findFactoryMethod($class);

        if ($factoryMethod === null) {
            return $class->newInstance();
        }

        $arguments = array_map(
            function (ReflectionParameter $parameter) use ($className) {
                /** @var array<Type> $type */
                $type = $this->propertyInfo->getTypes($className, $parameter->getName());

                return $this->generateArgument($type[0], $className, $parameter->getName());
            },
            $factoryMethod->getParameters()
        );

        return $factoryMethod->isConstructor()
            ? $class->newInstanceArgs($arguments)
            : (object) $factoryMethod->invokeArgs(null, $arguments);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function findFactoryMethod(ReflectionClass $class): ?ReflectionMethod
    {
        try {
            return $class->getMethod('createNew');
        } catch (ReflectionException $e) {
            // Do nothing here
        }

        try {
            return $class->getMethod('create');
        } catch (ReflectionException $e) {
            return $class->getConstructor();
        }
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    private function generateArgument(Type $type, string $className, string $argumentName)
    {
        $faker = $type->isNullable() ? $this->fakerInstance->optional() : $this->fakerInstance;
        $key = sprintf('%s:%s', $className, $argumentName);

        if ($this->isInRegistry($key)) {
            return $this->getFromRegistry($key);
        }

        switch ($type->getBuiltinType()) {
            case Type::BUILTIN_TYPE_INT:
                return $faker->randomNumber(5);

            case Type::BUILTIN_TYPE_FLOAT:
                return $faker->randomFloat();

            case Type::BUILTIN_TYPE_STRING:
                return $faker->text(100);

            case Type::BUILTIN_TYPE_BOOL:
                return $faker->boolean();

            case Type::BUILTIN_TYPE_ARRAY:
                $collection = [];

                if ($type->isCollection()) {
                    $collection = array_map(
                        function () use ($argumentName, $className, $type) {
                            $collectionValueType = $type->getCollectionValueTypes();

                            return $this->generateArgument($collectionValueType[0], $className, $argumentName);
                        },
                        range(0, $faker->numberBetween(0, 10))
                    );
                }

                return $faker->passthrough($collection);

            case Type::BUILTIN_TYPE_OBJECT:
                /** @var class-string $className */
                $className = $type->getClassName();

                if ($className === 'DateTime') {
                    return $faker->dateTime();
                }

                return $faker->passthrough($this->generateObject($className));
        }
    }

    private function isInRegistry(string $key): bool
    {
        return array_key_exists($key, $this->registry);
    }

    /**
     * @param class-string|string $key
     * @return mixed
     */
    private function getFromRegistry(string $key)
    {
        return $this->registry[$key]($this, $this->fakerInstance);
    }
}
