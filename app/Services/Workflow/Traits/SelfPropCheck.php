<?php

declare(strict_types=1);

namespace App\Services\Workflow\Traits;

use App\Services\Workflow\Exceptions\PropEmptyException;
use App\Services\Workflow\Exceptions\PropNotFoundException;
use Illuminate\Support\Collection;

/**
 * Trait providing methods for self property checks.
 */
trait SelfPropCheck
{
    /**
     * Get the value of a nested property within the current object.
     *
     * @param  string[]                 $propNames Array of property names representing the hierarchy of properties
     * @return mixed                    The value of the specified nested property
     */
    public function getSelfProp(array $propNames): mixed
    {
        $currentObject = $this;

        try {
            foreach ($propNames as $propName) {
                $this->validatePropertyExists($currentObject, $propName);
                $this->validateNotEmptyValue($currentObject, $propName);

                $currentObject = $currentObject->{$propName};
            }

        } catch (PropNotFoundException $e) {
            return null;
        } catch (PropEmptyException $e) {
            return null;
        }

        return $currentObject;
    }

    /**
     * Check if property exists and it's value is not empty.
     *
     * @param  array $propNames
     * @return bool
     */
    public function hasFilledSelfProp(array $propNames): bool
    {

        return (bool) $this->getSelfProp($propNames);

    }

    /**
     * Validate that a property exists within the current object.
     *
     * @param  object                   $object   The object to check
     * @param  string                   $propName The property name
     * @throws InvalidArgumentException If the property does not exist
     */
    private function validatePropertyExists(object $object, string $propName): void
    {
        if (! isset($object->{$propName})) {
            throw new PropNotFoundException("Property '{$propName}' does not exist");
        }
    }

    /**
     * Validate that a property is not an empty collection within the current object.
     *
     * @param  object                   $object   The object to check
     * @param  string                   $propName The property name
     * @throws InvalidArgumentException If the property is an empty collection
     */
    private function validateNotEmptyValue(object $object, string $propName): void
    {
        $propertyValue = $object->{$propName};

        if ($propertyValue instanceof Collection && $propertyValue->isEmpty() || empty($propertyValue)) {
            throw new PropEmptyException("Property '{$propName}' is an empty collection or null/empty");
        }
    }

    /**
     * Check if the specified property of the current object matches the expected value.
     *
     * @param  string[] $propNames     Array of property names representing the hierarchy of properties
     * @param  mixed    $expectedValue The expected value of the property
     * @param  bool     $strictCheck   If true, use strict comparison (===); otherwise, use loose comparison (==)
     * @return bool     Returns true if the property matches the expected value, false otherwise
     */
    protected function compareSelfProp(array $propNames, mixed $expectedValue, bool $strictCheck = false): bool
    {
        try {
            $currentObject = $this->getSelfProp($propNames);
        } catch (PropNotFoundException $ex) {
            return false; // Not equal, because the prop does not exist
        } catch (PropEmptyException $ex) {
            return false; // Not equal, because the prop IS empty
        }

        return $strictCheck ? $currentObject === $expectedValue : $currentObject == $expectedValue;
    }
}
