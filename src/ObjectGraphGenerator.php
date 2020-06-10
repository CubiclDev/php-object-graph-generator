<?php

declare(strict_types=1);

namespace Cubicl\ObjectGraphGenerator;

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

    /** @var Generator */
    private $fakerInstance;

    /** @var PropertyInfoExtractor */
    private $propertyInfo;

    /** @var array */
    private $registry;

    /** @var array */
    private $temporaryRegistry = [];

    public function __construct(array $registry = [])
    {
        $this->fakerInstance = Factory::create();

        $phpDocExtractor     = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $typeExtractors      = [$phpDocExtractor, $reflectionExtractor];
        $this->propertyInfo  = new PropertyInfoExtractor([], $typeExtractors, [], [], []);
        $this->fakerInstance->seed(self::DEFAULT_SEED);
        $this->registry = $registry;
    }

    public function generate(string $className): object
    {
        return $this->generateObject($className);
    }

    public function generateWithTemporaryConfig(string $className, array $config): object
    {
        $this->temporaryRegistry = $config;
        $object = $this->generateObject($className);
        $this->temporaryRegistry = [];

        return $object;
    }

    /**
     * @param string $className
     *
     * @return mixed|object
     * @throws ReflectionException
     */
    private function generateObject(string $className)
    {
        if ($this->isInRegistry($className)) {
            return $this->getFromRegistry($className);
        }

        $class         = new ReflectionClass($className);
        $factoryMethod = $this->findFactoryMethod($class);

        if ($factoryMethod === null) {
            return $class->newInstance();
        }

        $arguments = array_map(
            function (ReflectionParameter $parameter) use ($className) {
                $type = $this->propertyInfo->getTypes($className, $parameter->getName());

                return $this->generateArgument($type[0], $className, $parameter->getName());
            },
            $factoryMethod->getParameters()
        );

        return $factoryMethod->isConstructor()
            ? $class->newInstanceArgs($arguments)
            : $factoryMethod->invokeArgs(null, $arguments);
    }

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
     * @param Type   $type
     *
     * @param string $className
     * @param string $argumentName
     *
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
                            return $this->generateArgument($type->getCollectionValueType(), $className, $argumentName);
                        },
                        range(0, $faker->numberBetween(0, 10))
                    );
                }
                return $faker->passthrough($collection);
            case Type::BUILTIN_TYPE_OBJECT:
                if ($type->getClassName() === 'DateTime') {
                    return $faker->dateTime();
                }
                return $faker->passthrough($this->generateObject($type->getClassName()));
        }
    }

    private function isInRegistry(string $key): bool
    {
        return array_key_exists($key, $this->temporaryRegistry) || array_key_exists($key, $this->registry);
    }

    private function getFromRegistry(string $key)
    {
        if (isset($this->temporaryRegistry[$key])) {
            return $this->temporaryRegistry[$key]($this, $this->fakerInstance);
        }
        return $this->registry[$key]($this, $this->fakerInstance);
    }
}
