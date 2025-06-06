export const vehicleFormRules = {
    owner_id: {
        required: true,
        type: "number",
    },
    make_id: {
        required: false,
        type: "number",
    },
    vehicle_model_id: {
        required: false,
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
        required: false,
        type: "number",
    },
    engine_free_text: {
        required: false,
        type: "string",
    },
    vehicle_group_id: {
        required: false,
        type: "number",
    },
    type: {
        required: false,
        type: "number",
    },
    body: {
        required: false,
        type: "number",
    },
    fuel: {
        required: false,
        type: "number",
    },
    vehicle_status: {
        required: false,
        type: "number",
    },
    stock: {
        required: false,
        type: "number",
    },
    current_registration: {
        required: false,
        type: "number",
    },
    coc: {
        required: false,
        type: "number",
    },
    interior_material: {
        required: false,
        type: "number",
    },
    transmission: {
        required: false,
        type: "number",
    },
    transmission_free_text: {
        required: false,
        type: "string",
        maxLength: 550,
    },
    specific_exterior_color: {
        required: false,
        type: "number",
    },
    factory_name_color: {
        required: false,
        type: "string",
    },
    specific_interior_color: {
        required: false,
        type: "number",
    },
    factory_name_interior: {
        required: false,
        type: "string",
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
    hp: {
        required: false,
        type: "number",
        min: 0
    },
    kilometers: {
        required: false,
        type: "number",
        min: 0
    },
    currency_exchange_rate: {
        required: false,
        type: "number",
        min: 0
    },
    kw: {
        required: false,
        type: "number",
        min: 0
    },
    advert_link: {
        required: false,
        type: "text",
    },
    vehicle_reference: {
        required: false,
        type: "string",
    },
    co2_wltp: {
        required: false,
        type: "number",
        min: 0
    },
    co2_nedc: {
        required: false,
        type: "number",
        min: 0
    },
    is_ready_to_be_sold: {
        required: true,
        type: "boolean",
    },
    purchase_repaired_damage: {
        required: true,
        type: "boolean",
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
        type: "price",
    },
    sell_price_currency_euro: {
        required: false,
        type: "price",
    },
    vat_percentage: {
        required: false,
        type: "number",
    },
    net_purchase_price: {
        required: false,
        type: "price",
        min: 0
    },
    fee_intermediate: {
        required: false,
        type: "price",
        with: "intermediate"
    },
    total_purchase_price: {
        required: false,
        type: "price",
        min: 0
    },
    costs_of_damages: {
        required: false,
        type: "price",
        min: 0
    },
    transport_inbound: {
        required: false,
        type: "price",
        min: 0
    },
    transport_outbound: {
        required: false,
        type: "price",
        min: 0
    },
    costs_of_taxation: {
        required: false,
        type: "price",
        min: 0
    },
    recycling_fee: {
        required: false,
        type: "price",
        min: 0
    },
    sales_margin: {
        required: false,
        type: "price",
    },
    total_costs_with_fee: {
        required: false,
        type: "price",
    },
    sales_price_net: {
        required: false,
        type: "price",
    },
    vat: {
        required: false,
        type: "price",
    },
    sales_price_incl_vat_or_margin: {
        required: false,
        type: "price",
    },
    rest_bpm_indication: {
        required: false,
        type: "price",
    },
    leges_vat: {
        required: false,
        type: "price",
    },
    sales_price_total: {
        required: false,
        type: "price",
    },
    gross_bpm: {
        required: false,
        type: "price",
    },
    bpm_percent: {
        required: false,
        type: "price",
    },
    bpm: {
        required: false,
        type: "price",
    },
    nl_registration_number: {
        required: false,
        minLength: 2,
        type: "string",
    },
    supplier_company_id: {
        required: false,
        type: "number",
    },
    supplier_id: {
        required: false,
        type: "number",
    },
    supplier_reference_number: {
        required: false,
        type: "string",
        minLength: 2,
    },
    supplier_given_damages: {
        required: false,
        type: "price",
    },
    gross_bpm_new: {
        required: false,
        type: "price",
    },
    rest_bpm_as_per_table: {
        required: false,
        type: "price",
    },
    calculation_bpm_in_so: {
        required: false,
        type: "price",
    },
    bpm_declared: {
        required: false,
        type: "price",
    },
    gross_bpm_recalculated_based_on_declaration: {
        required: false,
        type: "price",
    },
    gross_bpm_on_registration: {
        required: false,
        type: "price",
    },
    rest_bpm_to_date: {
        required: false,
        type: "price",
    },
    invoice_bpm: {
        required: false,
        type: "price",
    },
    bpm_post_change_amount: {
        required: false,
        type: "price",
    },
    depreciation_percentage: {
        required: false,
        type: "number",
        min: 0,
        max: 100
    },
    vin: {
        required: false,
        type: "string",
        minLength: 17,
        maxLength: 17,
    },
    option: {
        required: false,
        type: "text",
    },
    color_type: {
        required: false,
        type: "number",
    },
    damage: {
        required: false,
        type: "text",
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
    expected_date_of_availability_from_supplier: {
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
    first_registration_date: {
        required: false,
        type: "date",
    },
    first_registration_nl: {
        required: false,
        type: "date",
    },
    registration_nl: {
        required: false,
        type: "date",
    },
    registration_date_approval: {
        required: false,
        type: "date",
    },
    last_name_registration: {
        required: false,
        type: "string",
    },
    first_name_registration_nl: {
        required: false,
        type: "date",
    },
    registration_valid_until: {
        required: false,
        type: "date",
    },
    internal_remarks: {
        complex: true,
    },
};
