<?php

declare(strict_types=1);

namespace App\Services;

use App\Casts\Price;
use App\Models\ChangeLog;
use App\Support\CurrencyHelper;
use App\Support\EnumHelper;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class ChangeLoggerService
{
    /**
     * Context model.
     *
     * @var Model
     */
    private Model $model;

    /**
     * Relations that need to be included in the change log.
     *
     * @var array
     */
    private array $relations;

    /**
     * Fields to be included if no provided, all fields are going to be watched for changes.
     *
     * @var array
     */
    private array $fields;

    /**
     * Changes as an array.
     *
     * @var array
     */
    private array $changeLogs = [];

    /**
     * Default omitted fields that do not need to be logged.
     *
     * @var array|string[]
     */
    private array $omittedFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'real_name'];

    /**
     * Convert model casts to appropriate format.
     *
     * @param  Model      $model
     * @param  array      $diff
     * @return null|array
     */
    private function convertCasts(Model $model, array $diff): null|array
    {
        if (empty($diff)) {
            return null;
        }
        $casts = $model->getCasts();

        $convertedDiff = [];
        foreach ($diff as $differanceKey => $differanceAttributes) {
            foreach ($differanceAttributes as $attributeKey => $attribute) {
                $attributeKeyReadable = $this->convertToReadableFormat($attributeKey);
                if (isset($casts[$attributeKey])) {
                    if (str_starts_with($casts[$attributeKey], 'App\Enums')) {
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['old'] = EnumHelper::getEnumName($casts[$attributeKey], $attribute['old']);
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['new'] = EnumHelper::getEnumName($casts[$attributeKey], $attribute['new']);
                    } elseif (str_starts_with($casts[$attributeKey], 'datetime')) {
                        $old = $attribute['old'] !== null ? date('d.m.Y', strtotime($attribute['old'])) : null;
                        $new = $attribute['new'] !== null ? date('d.m.Y', strtotime($attribute['new'])) : null;
                        if ($old == $new) {
                            continue;
                        }
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['old'] = $old;
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['new'] = $new;
                    } elseif ('boolean' == $casts[$attributeKey]) {
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['old'] = $attribute['old'] ? 'Yes' : 'No';
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['new'] = $attribute['new'] ? 'Yes' : 'No';
                    } elseif ('main' == $differanceKey && Price::class == $casts[$attributeKey]) {
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['old'] = CurrencyHelper::convertUnitsToCurrency($attribute['old']);
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['new'] = CurrencyHelper::convertUnitsToCurrency($attribute['new']);
                    } else {
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['old'] = $attribute['old'];
                        $convertedDiff[$differanceKey][$attributeKeyReadable]['new'] = $attribute['new'];
                    }
                } elseif ('pivot' == $attributeKey) {
                    if ($model instanceof \Spatie\Permission\Models\Permission) {
                        continue;
                    }

                    $convertedDiff[$differanceKey]['In Output']['old'] = isset($attribute['old']['in_output']) ? ($attribute['old']['in_output'] ? 'Yes' : 'No') : null;
                    $convertedDiff[$differanceKey]['In Output']['new'] = isset($attribute['new']['in_output']) ? ($attribute['new']['in_output'] ? 'Yes' : 'No') : null;
                } else {
                    $convertedDiff[$differanceKey][$attributeKeyReadable]['old'] = $attribute['old'];
                    $convertedDiff[$differanceKey][$attributeKeyReadable]['new'] = $attribute['new'];
                }
            }
        }

        return $convertedDiff;
    }

    /**
     * Get the value of model.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set the value of model.
     *
     * @param  Model $model
     * @return self
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the value of relations.
     *
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Set the value of relations.
     *
     * @param  array $relations
     * @return self
     */
    public function setRelations(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Get the value of fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Set the value of fields.
     *
     * @param  array $fields
     * @return self
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Update changeLogs variable.
     *
     * @param  string $key
     * @param  array  $changeLogs
     * @return void
     */
    private function addChangeLogs(string $key, array $changeLogs): void
    {
        $currentKeyLogs = $this->getChangeLogs()[$key] ?? [];

        $this->changeLogs[$key] = [...$currentKeyLogs, ...$changeLogs];
    }

    /**
     * Get the value of changeLogs.
     *
     * @return array
     */
    private function getChangeLogs(): array
    {
        return $this->changeLogs;
    }

    /**
     * Get the value of changeLogs.
     *
     * @return array
     */
    private function getOmittedFields(): array
    {
        return $this->omittedFields;
    }

    /**
     * Get model as key name.
     *
     * @param  Model  $model
     * @return string
     */
    private function getModelPropName(Model $model): string
    {
        $reflector = new ReflectionClass($model);

        return lcfirst($reflector->getShortName());
    }

    /**
     * Retrieves the attributes of a given model and filters them
     * based on the fields specified. If no fields are specified, all attributes
     * are returned.
     *
     * @param  Model $model
     * @return array
     */
    private function getModelAttributes(Model $model): array
    {
        $allAttributes = $model->getAttributes();
        $fields = $this->getFields();

        return ! empty($fields)
            ? array_intersect_key($allAttributes, array_flip($fields))
            : $allAttributes;
    }

    /**
     * Create a new ChangeLoggerService instance.
     *
     * @param  Model      $model
     * @param  null|array $relations
     * @param  null|array $fields
     * @return void
     */
    public function __construct(Model $model, ?array $relations = [], ?array $fields = [])
    {
        $this->setRelations($relations);
        $this->setFields($fields);
        $this->addChangeLogs($this->getModelPropName($model), $this->getModelAttributes($model));

        foreach ($relations as $relation) {
            $relationDataArray = $model->{$relation}()
                ->get()
                ->toArray();

            $this->addChangeLogs($relation, $relationDataArray);
        }
    }

    /**
     * Compare and retrieve changed fields and save changes to database.
     *
     * @param  Model $model
     * @return $this
     */
    public function logChanges(Model $model): static
    {
        $changeLogs = $this->getChangeLogs();

        $modelStartAttributes = $changeLogs[$this->getModelPropName($model)];
        $currentModelAttributes = $this->getModelAttributes($model);
        $modelDifferences = $this->recursiveArrayDiff($model, $modelStartAttributes, $currentModelAttributes);
        $relationDifferences = [];
        foreach ($this->getRelations() as $relation) {
            $relationStartAttributes = $changeLogs[$relation];
            $currentRelationAttributes = $model->{$relation}()->get()->toArray();
            $relationDiff = $this->recursiveArrayDiff($model->{$relation}()->getRelated(), $relationStartAttributes, $currentRelationAttributes, $relation);

            if ($relationDiff) {
                $relationDifferences = array_merge($relationDifferences, $relationDiff);
            }
        }

        if (! $modelDifferences && empty($relationDifferences)) {
            return $this;
        }

        ChangeLog::create([
            'creator_id'      => auth()->id(),
            'changeable_id'   => $model->id,
            'changeable_type' => $model::class,
            'change'          => json_encode(array_merge($modelDifferences ?? [], $relationDifferences)),
        ]);

        return $this;
    }

    /**
     * Update the omitted fields
     *
     * @param array $fields
     * @return $this
     */
    public function setOmittedFields(array $fields): static
    {
        $this->omittedFields = array_merge($this->omittedFields, $fields);

        return $this;
    }

    /**
     * Return array of changes.
     *
     * @param  Model       $model
     * @param  array       $array1
     * @param  array       $array2
     * @param  null|string $relation
     * @return null|array
     */
    public function recursiveArrayDiff(Model $model, array $array1, array $array2, ?string $relation = null): ?array
    {
        $omittedFields = array_merge($model->omittedInChangeLog ?? [], $this->getOmittedFields());

        $diff = [];

        // Index elements of array1 and array2 by their ID
        $indexed1 = $this->indexArrayById($array1, $relation);
        $indexed2 = $this->indexArrayById($array2, $relation);

        // Find IDs present in array2 but not in array1
        $newIds = array_diff_key($indexed2, $indexed1);

        // Find IDs present in array1 but not in array2
        $removedIds = array_diff_key($indexed1, $indexed2);

        // Compare values of common IDs
        foreach ($indexed1 as $id => $item1) {
            if (! isset($indexed2[$id])) {
                continue; // ID not present in array2
            }

            $item2 = $indexed2[$id];

            $itemDiff = [];

            // Compare fields of the item
            foreach ($item1 as $field => $value1) {
                if (! array_key_exists($field, $item2) || in_array($field, $omittedFields, true)) {
                    continue; // Field not present in item2
                }

                $value2 = $item2[$field];

                if ($value1 != $value2) {
                    // Field value has changed
                    $itemDiff[$field] = ['old' => $value1, 'new' => $value2];
                }
            }

            // If there are differences in the item, include them in the overall diff
            if (! empty($itemDiff)) {
                $diff[$item1['real_name']] = $itemDiff;
            }
        }

        // Include differences for new IDs
        foreach ($newIds as $item2) {
            foreach ($item2 as $field => $value) {
                if ($value && ! in_array($field, $omittedFields, true)) {
                    $diff[$item2['real_name']][$field] = ['old' => null, 'new' => $value];
                }
            }
        }

        // Include differences for removed IDs
        foreach ($removedIds as $item1) {
            foreach ($item1 as $field => $value) {
                if ($value && ! in_array($field, $omittedFields, true)) {
                    $diff[$item1['real_name']][$field] = ['old' => $value, 'new' => null];
                }
            }
        }

        return $this->convertCasts($model, $diff);
    }

    /**
     * Index given array by id.
     *
     * @param        $array
     * @param        $relation
     * @return array
     */
    private function indexArrayById($array, $relation): array
    {
        $indexed = [];
        if ($relation) {
            $relationName = ucfirst($relation);
            foreach ($array as $key => $item) {
                $indexed[$key] = $item;
                $indexed[$key]['real_name'] = $relationName.' '.$key + 1;
            }
        } else {
            $indexed['main'] = $array;
            $indexed['main']['real_name'] = 'main';
        }

        return $indexed;
    }

    private function convertToReadableFormat($input): string
    {
        // Split the input string by underscores
        $words = explode('_', $input);

        // Capitalize the first letter of each word and join them with spaces
        return ucwords(implode(' ', $words));
    }
}
