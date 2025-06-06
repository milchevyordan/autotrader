<?php

declare(strict_types=1);

namespace App\Services\DataTable\Exports;

use App\Services\DataTable\Enums\ExportType;
use App\Services\WeekService;
use BackedEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class ExportMapper
{
    public static function resolve(ExportType $type): string
    {
        return match ($type) {
            ExportType::Csv => CsvExport::class,
        };
    }

    public static function make(ExportType $type, Collection $columns, EloquentCollection $models, Collection $dateColumns): Export
    {
        $validKeys = $columns->filter(function ($column) {
            return $column->exportable === true;
        })->keys();

        $headers = $validKeys->mapWithKeys(fn ($key) => [$key => $columns[$key]->label])->toArray();

        $rows = $models->map(function ($model) use ($dateColumns, $validKeys) {
            return $validKeys->map(function ($key) use ($dateColumns, $model) {
                $data = data_get($model, $key);
                if ($data instanceof BackedEnum) {
                    return Str::replace('_', ' ', $data->name);
                } elseif ($data instanceof Carbon) {
                    return $dateColumns[$key] ? $data->format($dateColumns[$key]->format) : $data;
                } elseif (is_array($data)) {
                    return WeekService::generateWeekInputString($data);
                }

                return $data;
            })->toArray();
        })->toArray();

        return match ($type) {
            ExportType::Csv => new CsvExport($headers, $rows),
        };
    }
}
