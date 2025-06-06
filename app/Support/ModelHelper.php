<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class ModelHelper
{
    /**
     * Return, for example, 'Pre Order #1' by passed model and id.
     *
     * @param  Model  $model
     * @return string
     */
    public static function getModelNameWithId(Model $model): string
    {
        $modelName = preg_replace('/([a-z])([A-Z])/', '$1 $2', class_basename($model));

        return $modelName.' #'.$model->id;
    }

    /**
     * Return edit route by provided model and id.
     *
     * @param  Model  $model
     * @return string
     */
    public static function getEditRoute(Model $model): string
    {
        $mainPart = str_replace('_', '-', $model->getTable());

        return route($mainPart.'.edit', $model->id);
    }

    /**
     * Returns the vehicle attributes.
     *
     * @return array
     */
    public static function getVehicleAttributes(): array
    {
        return [
            ['attribute' => 'warranty', 'attribute_free_text' => 'warranty_free_text', 'label' => __('Warranty')],
            ['attribute' => 'navigation', 'attribute_free_text' => 'navigation_free_text', 'label' => __('Navigation')],
            ['attribute' => 'app_connect', 'attribute_free_text' => 'app_connect_free_text', 'label' => __('App connect')],
            ['attribute' => 'panorama', 'attribute_free_text' => 'panorama_free_text', 'label' => __('Panorama')],
            ['attribute' => 'headlights', 'attribute_free_text' => 'headlights_free_text', 'label' => __('Headlights')],
            ['attribute' => 'digital_cockpit', 'attribute_free_text' => 'digital_cockpit_free_text', 'label' => __('Digital Cockpit')],
            ['attribute' => 'cruise_control', 'attribute_free_text' => 'cruise_control_free_text', 'label' => __('Cruise Control')],
            ['attribute' => 'keyless_entry', 'attribute_free_text' => 'keyless_entry_free_text', 'label' => __('Keyless entry')],
            ['attribute' => 'air_conditioning', 'attribute_free_text' => 'air_conditioning_free_text', 'label' => __('Air conditioning')],
            ['attribute' => 'pdc', 'attribute_free_text' => 'pdc_free_text', 'label' => 'PDC'],
            ['attribute' => 'second_wheels', 'attribute_free_text' => 'second_wheels_free_text', 'label' => __('Second wheels')],
            ['attribute' => 'camera', 'attribute_free_text' => 'camera_free_text', 'label' => __('Camera')],
            ['attribute' => 'tow_bar', 'attribute_free_text' => 'tow_bar_free_text', 'label' => __('Tow bar')],
            ['attribute' => 'sports_seat', 'attribute_free_text' => 'sports_seat_free_text', 'label' => __('Sports seat')],
            ['attribute' => 'seats_electrically_adjustable', 'attribute_free_text' => 'seats_electrically_adjustable_free_text', 'label' => __('Seats electrically adjustable')],
            ['attribute' => 'seat_heating', 'attribute_free_text' => 'seat_heating_free_text', 'label' => __('Seat heating')],
            ['attribute' => 'seat_massage', 'attribute_free_text' => 'seat_massage_free_text', 'label' => __('Seat massage')],
            ['attribute' => 'optics', 'attribute_free_text' => 'optics_free_text', 'label' => __('Optics')],
            ['attribute' => 'tinted_windows', 'attribute_free_text' => 'tinted_windows_free_text', 'label' => __('Tinted windows')],
            ['attribute' => 'sports_package', 'attribute_free_text' => 'sports_package_free_text', 'label' => __('Sports package')],
        ];
    }
}
