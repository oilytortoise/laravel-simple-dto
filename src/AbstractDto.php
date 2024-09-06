<?php

namespace Oilytortoise\LaravelSimpleDto;

use Livewire\Wireable;
use ReflectionClass;
use ReflectionProperty;

/**
 * The base DTO class. Contains all the common functionality
 * for other DTOs.
 *
 * @author Oilytortoise
 */
abstract class AbstractDto implements Wireable
{
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $reflectionPropertyType = $this->getPropertyType($key);
                $propertyIsDto = $this->propertyIsChild($key, self::class);
                $propertyIsCollection = $this->propertyIsChild($key, DtoCollection::class);

                if (is_array($value) && ($propertyIsDto || $propertyIsCollection)) {
                    $property = $reflectionPropertyType->getName();
                    $value = new $property($value);
                }

                $this->$key = $value;

            }
        }

    }

    /**
     * Get the type of property we are trying to set.
     */
    protected function getPropertyType(string $propertyName)
    {
        $reflectedProperty = new ReflectionProperty(get_class($this), $propertyName);
        $propertyType = $reflectedProperty->getType();

        return $propertyType;
    }

    /**
     * Determine whether the type of the provided property
     * is a child of the provided parent class.
     */
    protected function propertyIsChild($propertyName, $parentClassName) {
        // Use reflection to get the class
        $reflectionClass = new ReflectionClass($this);

        // Check if the property exists in the class
        if (!$reflectionClass->hasProperty($propertyName)) {
            throw new \Exception("Property '$propertyName' does not exist in the class.");
        }

        // Get the ReflectionProperty object for the property
        $property = $reflectionClass->getProperty($propertyName);

        // Get the type of the property (if any)
        $type = $property->getType();

        if ($type === null) {
            // If the property has no type, there's no strict comparison possible
            return false;
        }

        // Get the property type name
        $typeName = $type->getName();

        // Check if the typeName is a class and if it is an instance of the parent class
        if (class_exists($typeName) && is_a($typeName, $parentClassName, true)) {
            return true;
        }

        return false;
    }

    /**
     * Convert the DTO, and all nested DTOs,
     * to an array.
     */
    public function toArray(): array
    {
        $thisArray = get_object_vars($this);

        foreach ($thisArray as $key => $value) {
            if ($value instanceof AbstractDto) {
                $thisArray[$key] = $value->toArray();
                continue;
            }
            if ($value instanceof DtoCollection) {
                $thisArray[$key] = $this->collectionToArray($value);
                continue;
            }
        }

        return $thisArray;
    }

    /**
     * Convert a DtoCollection to an array.
     */
    protected function collectionToArray(DtoCollection $collection): array
    {
        $array = [];

        foreach ($collection as $item) {
            $array[] = $item->toArray();
        }

        return $array;
    }

    public function toLivewire()
    {
        return $this->toArray();
    }

    public static function fromLivewire($value)
    {
        return new static($value);
    }
}
