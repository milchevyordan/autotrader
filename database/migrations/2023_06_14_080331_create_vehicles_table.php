<?php

declare(strict_types=1);

use App\Enums\VehicleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('creator_id')
                ->references('id')
                ->on('users');

            $table->foreignId('make_id')
                ->nullable()
                ->references('id')
                ->on('makes');

            $table->foreignId('vehicle_model_id')
                ->nullable()
                ->references('id')
                ->on('vehicle_models');

            $table->foreignId('variant_id')
                ->nullable()
                ->references('id')
                ->on('variants')
                ->nullOnDelete();

            $table->foreignId('engine_id')
                ->nullable()
                ->references('id')
                ->on('engines');

            $table->foreignId('vehicle_group_id')
                ->nullable()
                ->references('id')
                ->on('vehicle_groups')
                ->nullOnDelete();

            $table->foreignId('supplier_company_id')
                ->nullable()
                ->references('id')
                ->on('companies');

            $table->foreignId('supplier_id')
                ->nullable()
                ->references('id')
                ->on('users');

            $table->unsignedTinyInteger('type')->nullable();
            $table->unsignedTinyInteger('body')->nullable();
            $table->unsignedTinyInteger('fuel')->nullable();
            $table->unsignedTinyInteger('vehicle_status')->nullable()->default(VehicleStatus::Used->value);
            $table->unsignedTinyInteger('stock')->nullable();
            $table->unsignedTinyInteger('current_registration')->nullable();
            $table->unsignedTinyInteger('coc')->nullable();
            $table->unsignedTinyInteger('interior_material')->nullable();
            $table->unsignedTinyInteger('transmission')->nullable();
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

            $table->boolean('is_ready_to_be_sold')->default(false);
            $table->boolean('purchase_repaired_damage')->default(false);

            $table->integer('hp')->index()->nullable();
            $table->integer('kilometers')->nullable();
            $table->integer('kw')->index()->nullable();
            $table->integer('co2_wltp')->nullable()->index();
            $table->integer('co2_nedc')->nullable()->index();
            $table->integer('supplier_given_damages')->nullable();
            $table->integer('gross_bpm_new')->nullable();
            $table->integer('rest_bpm_as_per_table')->nullable();
            $table->integer('calculation_bpm_in_so')->nullable();
            $table->integer('bpm_declared')->nullable();
            $table->integer('gross_bpm_recalculated_based_on_declaration')->nullable();
            $table->integer('gross_bpm_on_registration')->nullable();
            $table->integer('rest_bpm_to_date')->nullable();
            $table->integer('invoice_bpm')->nullable();
            $table->integer('bpm_post_change_amount')->nullable();
            $table->unsignedTinyInteger('depreciation_percentage')->nullable();

            $table->string('vehicle_model_free_text')->nullable();
            $table->string('variant_free_text')->nullable();
            $table->string('engine_free_text')->nullable();
            $table->string('factory_name_color')->nullable();
            $table->string('factory_name_interior')->nullable();
            $table->string('transmission_free_text', 550)->nullable();
            $table->text('advert_link')->nullable();
            $table->string('vehicle_reference')->nullable();
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

            $table->string('nl_registration_number')->nullable();
            $table->string('supplier_reference_number')->nullable();
            $table->string('identification_code', 1281)->nullable();
            $table->string('expected_date_of_availability_from_supplier')->nullable();

            $table->string('vin', 17)->nullable()->index();
            $table->text('option')->nullable();
            $table->text('damage_description')->nullable();

            $table->unsignedTinyInteger('expected_leadtime_for_delivery_from')->nullable();
            $table->unsignedTinyInteger('expected_leadtime_for_delivery_to')->nullable();

            $table->date('first_registration_date')->nullable();
            $table->date('first_registration_nl')->nullable();
            $table->date('registration_nl')->nullable();
            $table->date('registration_date_approval')->nullable();
            $table->date('last_name_registration')->nullable();
            $table->date('first_name_registration_nl')->nullable();
            $table->date('registration_valid_until')->nullable();

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
        Schema::dropIfExists('vehicles');
    }
};
