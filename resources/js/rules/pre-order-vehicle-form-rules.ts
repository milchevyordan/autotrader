export const preOrderVehicleFormRules = {
    make_id: {
        required: true,
        type: "number",
    },
    variant_id: {
        required: false,
        type: "number",
    },
    vehicle_model_free_text: {
        required: false,
        type: "string",
    },
    variant_free_text: {
        required: false,
        type: "string",
    },
    engine_id: {
        required: true,
        type: "number",
    },
    engine_free_text: {
        required: false,
        type: "string",
    },
    type: {
        required: true,
        type: "number",
    },
    vehicle_model_id: {
        required: true,
        type: "number",
    },
    supplier_company_id: {
        required: false,
        type: "number",
    },
    supplier_id: {
        required: false,
        type: "number",
    },
    body: {
        required: false,
        type: "number",
    },
    fuel: {
        required: true,
        type: "number",
    },
    vehicle_status: {
        required: false,
        type: "number",
    },
    current_registration: {
        required: false,
        type: "number",
    },
    interior_material: {
        required: false,
        type: "number",
    },
    transmission: {
        required: true,
        type: "number",
    },
    specific_exterior_color: {
        required: false,
        type: "number",
    },
    specific_interior_color: {
        required: false,
        type: "number",
    },
    panorama: {
        required: false,
        type: "number",
    },
    headlights: {
        required: false,
        type: "number",
    },
    digital_cockpit: {
        required: false,
        type: "number",
    },
    cruise_control: {
        required: false,
        type: "number",
    },
    keyless_entry: {
        required: false,
        type: "number",
    },
    air_conditioning: {
        required: false,
        type: "number",
    },
    pdc: {
        required: false,
        type: "number",
    },
    second_wheels: {
        required: false,
        type: "number",
    },
    camera: {
        required: false,
        type: "number",
    },
    tow_bar: {
        required: false,
        type: "number",
    },
    seat_heating: {
        required: false,
        type: "number",
    },
    seat_massage: {
        required: false,
        type: "number",
    },
    optics: {
        required: false,
        type: "number",
    },
    tinted_windows: {
        required: false,
        type: "number",
    },
    sports_package: {
        required: false,
        type: "number",
    },
    color_type: {
        required: false,
        type: "number",
    },
    warranty: {
        required: false,
        type: "number",
    },
    navigation: {
        required: false,
        type: "number",
    },
    sports_seat: {
        required: false,
        type: "number",
    },
    seats_electrically_adjustable: {
        required: false,
        type: "number",
    },
    app_connect: {
        required: false,
        type: "number",
    },
    warranty_free_text: {
        required: false,
        type: "string",
    },
    navigation_free_text: {
        required: false,
        type: "string",
    },
    app_connect_free_text: {
        required: false,
        type: "string",
    },
    panorama_free_text: {
        required: false,
        type: "string",
    },
    headlights_free_text: {
        required: false,
        type: "string",
    },
    digital_cockpit_free_text: {
        required: false,
        type: "string",
    },
    cruise_control_free_text: {
        required: false,
        type: "string",
    },
    keyless_entry_free_text: {
        required: false,
        type: "string",
    },
    air_conditioning_free_text: {
        required: false,
        type: "string",
    },
    pdc_free_text: {
        required: false,
        type: "string",
    },
    second_wheels_free_text: {
        required: false,
        type: "string",
    },
    camera_free_text: {
        required: false,
        type: "string",
    },
    tow_bar_free_text: {
        required: false,
        type: "string",
    },
    sports_seat_free_text: {
        required: false,
        type: "string",
    },
    seats_electrically_adjustable_free_text: {
        required: false,
        type: "string",
    },
    seat_heating_free_text: {
        required: false,
        type: "string",
    },
    seat_massage_free_text: {
        required: false,
        type: "string",
    },
    optics_free_text: {
        required: false,
        type: "string",
    },
    tinted_windows_free_text: {
        required: false,
        type: "string",
    },
    sports_package_free_text: {
        required: false,
        type: "string",
    },
    highlight_1: {
        required: false,
        type: "string",
    },
    highlight_2: {
        required: false,
        type: "string",
    },
    highlight_3: {
        required: false,
        type: "string",
    },
    highlight_4: {
        required: false,
        type: "string",
    },
    highlight_5: {
        required: false,
        type: "string",
    },
    highlight_6: {
        required: false,
        type: "string",
    },
    komm_number: {
        required: false,
        type: "number",
    },
    factory_name_color: {
        required: false,
        type: "string",
    },
    factory_name_interior: {
        required: false,
        type: "string",
    },
    transmission_free_text: {
        required: false,
        type: "string",
        maxLength: 550,
    },
    vehicle_reference: {
        required: false,
        type: "string",
    },
    configuration_number: {
        required: false,
        type: "string",
    },
    kilometers: {
        required: false,
        type: "number",
        min: 0
    },
    production_weeks: {
        complex: true,
        type: "week_range",
    },
    expected_delivery_weeks: {
        complex: true,
        type: "week_range",
    },
    expected_leadtime_for_delivery_from: {
        required: false,
        type: "number",
        min: 0,
        max: 255
    },
    expected_leadtime_for_delivery_to: {
        required: false,
        type: "number",
        min: 0,
        max: 255
    },
    registration_weeks_from: {
        required: false,
        type: "number",
        min: 0,
        max: 255
    },
    registration_weeks_to: {
        required: false,
        type: "number",
        min: 0,
        max: 255
    },
    currency_exchange_rate: {
        required: false,
        type: "number",
        min: 0
    },
    option: {
        required: false,
        type: "text",
    },
    is_vat: {
        required: true,
        type: "boolean",
    },
    is_locked: {
        required: true,
        type: "boolean",
    },
    intermediate: {
        required: true,
        type: "boolean",
    },
    original_currency: {
        required: false,
        type: "number",
    },
    selling_price_supplier: {
        required: false,
        type: "string",
    },
    sell_price_currency_euro: {
        required: false,
        type: "string",
    },
    vat_percentage: {
        required: false,
        type: "number",
    },
    net_purchase_price: {
        required: false,
        type: "string",
    },
    fee_intermediate: {
        required: false,
        type: "string",
        with: "intermediate",
    },
    total_purchase_price: {
        required: false,
        type: "string",
    },
    costs_of_damages: {
        required: false,
        type: "string",
    },
    transport_inbound: {
        required: false,
        type: "string",
    },
    transport_outbound: {
        required: false,
        type: "string",
    },
    costs_of_taxation: {
        required: false,
        type: "string",
    },
    recycling_fee: {
        required: false,
        type: "string",
    },
    sales_margin: {
        required: false,
        type: "string",
    },
    total_costs_with_fee: {
        required: false,
        type: "string",
    },
    sales_price_net: {
        required: false,
        type: "string",
    },
    vat: {
        required: false,
        type: "string",
    },
    sales_price_incl_vat_or_margin: {
        required: false,
        type: "string",
    },
    rest_bpm_indication: {
        required: false,
        type: "string",
    },
    leges_vat: {
        required: false,
        type: "string",
    },
    sales_price_total: {
        required: false,
        type: "string",
    },
    gross_bpm: {
        required: false,
        type: "string",
    },
    bpm_percent: {
        required: false,
        type: "string",
    },
    bpm: {
        required: false,
        type: "string",
    },
    internal_remarks: {
        complex: true,
    },
};
