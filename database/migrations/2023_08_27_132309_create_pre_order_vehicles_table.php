<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pre_order_vehicles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table->foreignId('make_id')
                ->references('id')
                ->on('makes');

            $table->foreignId('vehicle_model_id')
                ->references('id')
                ->on('vehicle_models');

            $table->foreignId('variant_id')
                ->nullable()
                ->references('id')
                ->on('variants')
                ->nullOnDelete();

            $table->foreignId('engine_id')
                ->references('id')
                ->on('engines');

            $table->foreignId('supplier_company_id')
                ->nullable()
                ->references('id')
                ->on('companies');

            $table->foreignId('supplier_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table->unsignedTinyInteger('type');
            $table->unsignedTinyInteger('body')->nullable();
            $table->unsignedTinyInteger('fuel');
            $table->unsignedTinyInteger('vehicle_status')->nullable();
            $table->unsignedTinyInteger('current_registration')->nullable();
            $table->unsignedTinyInteger('interior_material')->nullable();
            $table->unsignedTinyInteger('transmission');
            $table->unsignedTinyInteger('specific_exterior_color')->nullable();
            $table->unsignedTinyInteger('specific_interior_color')->nullable();
            $table->unsignedTinyInteger('panorama')->nullable();
            $table->unsignedTinyInteger('headlights')->nullable();
            $table->unsignedTinyInteger('digital_cockpit')->nullable();
            $table->unsignedTinyInteger('cruise_control')->nullable();
            $table->unsignedTinyInteger('keyless_entry')->nullable();
            $table->unsignedTinyInteger('air_conditioning')->nullable();
            $table->unsignedTinyInteger('pdc')->nullable();
            $table->unsignedTinyInteger('second_wheels')->nullable();
            $table->unsignedTinyInteger('camera')->nullable();
            $table->unsignedTinyInteger('tow_bar')->nullable();
            $table->unsignedTinyInteger('seat_heating')->nullable();
            $table->unsignedTinyInteger('seat_massage')->nullable();
            $table->unsignedTinyInteger('optics')->nullable();
            $table->unsignedTinyInteger('tinted_windows')->nullable();
            $table->unsignedTinyInteger('sports_package')->nullable();
            $table->unsignedTinyInteger('color_type')->nullable();
            $table->unsignedTinyInteger('warranty')->nullable();
            $table->unsignedTinyInteger('navigation')->nullable();
            $table->unsignedTinyInteger('app_connect')->nullable();
            $table->unsignedTinyInteger('seats_electrically_adjustable')->nullable();
            $table->unsignedTinyInteger('sports_seat')->nullable();

            $table->string('komm_number')->nullable();
            $table->string('vehicle_model_free_text')->nullable();
            $table->string('variant_free_text')->nullable();
            $table->string('engine_free_text')->nullable();
            $table->string('factory_name_color')->nullable();
            $table->string('factory_name_interior')->nullable();
            $table->string('transmission_free_text', 550)->nullable();
            $table->string('vehicle_reference')->nullable();
            $table->string('configuration_number')->nullable();
            $table->string('image_path')->nullable();
            $table->string('warranty_free_text')->nullable();
            $table->string('navigation_free_text')->nullable();
            $table->string('app_connect_free_text')->nullable();
            $table->string('panorama_free_text')->nullable();
            $table->string('headlights_free_text')->nullable();
            $table->string('digital_cockpit_free_text')->nullable();
            $table->string('cruise_control_free_text')->nullable();
            $table->string('keyless_entry_free_text')->nullable();
            $table->string('air_conditioning_free_text')->nullable();
            $table->string('pdc_free_text')->nullable();
            $table->string('second_wheels_free_text')->nullable();
            $table->string('camera_free_text')->nullable();
            $table->string('tow_bar_free_text')->nullable();
            $table->string('sports_seat_free_text')->nullable();
            $table->string('seats_electrically_adjustable_free_text')->nullable();
            $table->string('seat_heating_free_text')->nullable();
            $table->string('seat_massage_free_text')->nullable();
            $table->string('optics_free_text')->nullable();
            $table->string('tinted_windows_free_text')->nullable();
            $table->string('sports_package_free_text')->nullable();
            $table->string('highlight_1')->nullable();
            $table->string('highlight_2')->nullable();
            $table->string('highlight_3')->nullable();
            $table->string('highlight_4')->nullable();
            $table->string('highlight_5')->nullable();
            $table->string('highlight_6')->nullable();
            $table->string('production_weeks')->nullable();
            $table->string('expected_delivery_weeks')->nullable();

            $table->integer('kilometers')->nullable();
            $table->unsignedTinyInteger('expected_leadtime_for_delivery_from')->nullable();
            $table->unsignedTinyInteger('expected_leadtime_for_delivery_to')->nullable();

            $table->unsignedTinyInteger('registration_weeks_from')->nullable();
            $table->unsignedTinyInteger('registration_weeks_to')->nullable();

            $table->text('option')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_order_vehicles');
    }
};
