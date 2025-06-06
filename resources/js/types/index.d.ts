import { SupplierOrIntermediary } from "@/enums/SupplierOrIntermediary";
import { ItemType } from "@/enums/ItemType";
import { UnitType } from "@/enums/UnitType";
import { Country } from "@/enums/Country";
import { VehicleType } from "@/enums/VehicleType";
import { VehicleBody } from "@/enums/VehicleBody";
import { FuelType } from "@/enums/FuelType";
import { VehicleStatus } from "@/enums/VehicleStatus";
import { Coc } from "@/enums/Coc";
import { InteriorMaterial } from "@/enums/InteriorMaterial";
import { Transmission } from "@/enums/Transmission";
import { Panorama } from "@/enums/Panorama";
import { Headlights } from "@/enums/Headlights";
import { DigitalCockpit } from "@/enums/DigitalCockpit";
import { CruiseControl } from "@/enums/CruiseControl";
import { KeylessEntry } from "@/enums/KeylessEntry";
import { Airconditioning } from "@/enums/Airconditioning";
import { PDC } from "@/enums/PDC";
import { Camera } from "@/enums/Camera";
import { TowBar } from "@/enums/TowBar";
import { SeatHeating } from "@/enums/SeatHeating";
import { SeatMassage } from "@/enums/SeatMassage";
import { Optics } from "@/enums/Optics";
import { TintedWindows } from "@/enums/TintedWindows";
import { SportsPackage } from "@/enums/SportsPackage";
import { Currency } from "@/enums/Currency";
import { PurchaseOrderStatus } from "@/enums/PurchaseOrderStatus";
import { PreOrderStatus } from "@/enums/PreOrderStatus";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { ColorType } from "@/enums/ColorType";
import { TransportOrderStatus } from "@/enums/TransportOrderStatus";
import { ImportEuOrWorldType } from "@/enums/ImportEuOrWorldType";
import { WorkOrderTaskStatus } from "@/enums/WorkOrderTaskStatus";
import { ServiceLevelType } from "@/enums/ServiceLevelType";
import { TransportType } from "@/enums/TransportType";
import { ServiceOrderStatus } from "@/enums/ServiceOrderStatus";
import { Co2Type } from "@/enums/Co2Type";
import { DocumentStatus } from "@/enums/DocumentStatus";
import { PaymentCondition } from "@/enums/PaymentCondition";
import { CompanyAddressType } from "@/enums/CompanyAddressType";
import { Gender } from "@/enums/Gender";
import { QuoteStatus } from "@/enums/QuoteStatus";
import { TransportableType } from "@/enums/TransportableType";
import { WorkOrderType } from "@/enums/WorkOrderType";
import { NationalEuOrWorldType } from "@/enums/NationalEuOrWorldType";
import { WorkOrderStatus } from "@/enums/WorkOrderStatus";
import { OwnershipStatus } from "@/enums/OwnershipStatus";
import { ExteriorColour } from "@/enums/ExteriorColour";
import { InteriorColour } from "@/enums/InteriorColour";
import { Damage } from "@/enums/Damage";
import { ImportOrOriginType } from "@/enums/ImportOrOriginType";
import { Navigation } from "@/enums/Navigation";
import { AppConnect } from "@/enums/AppConnect";
import { SeatsElectricallyAdjustable } from "@/enums/SeatsElectricallyAdjustable";
import { SportsSeat } from "@/enums/SportsSeat";
import { DocumentableType } from "@/enums/DocumentableType";
import { VehicleStock } from "@/enums/VehicleStock";
import { Locale } from "@/enums/Locale";
import { DocumentLineType } from "@/enums/DocumentLineType";
import { Warranty } from "@/enums/Warranty";
import { SecondWheels } from "@/enums/SecondWheels";
import { Papers } from "@/enums/Papers";
import { CompanyType } from "@/enums/CompanyType";
import { Ref } from "vue";

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>
> = T & {
    auth: {
        user: User;
        company: Company;
    };
    config: {
        validation: {
            rule: {
                maxIntegerValue: number;
                maxStringLength: number;
            };

            image: {
                mimeTypes: string[];
                minImageSizeMb: number;
                maxImageSizeMb: number;
            };

            file: {
                mimeTypes: string[];
                minFileSizeMb: number;
                maxFileSizeMb: number;
            };
        };
        app_env: string;
    };
    flash: {
        status: string | null;
        success: string | null;
        errors: string | null;
        error: string | null;
    };
    lang: string;
    locale: Enum<typeof Locale>;
};

export type Enum<T> = T[keyof T];

export type Multiselect<T> = Record<string, number>;

export type Documentable =
    | PreOrderVehicle
    | Vehicle
    | ServiceVehicle
    | SalesOrder
    | ServiceOrder
    | WorkOrder;

// GlobalInputErrors
export interface GlobalInputErrors {
    failedKeys: string[];
    errorMessages: Record<string, string>;
}

interface Flash {
    errors?: string[];
    error?: string;
    warning?: string;
    success?: string;
}

export type FormMethod = {
    _method?: string;
};

export type Form = {
    [key: string]: any;
};

export interface DatabaseImage {
    id: number;
    imageable_id: number;
    imageable_type: string;
    original_name: string;
    unique_name: string;
    path: string;
    order: number;
    size: number;
    created_at: Date;
    updated_at: Date;
}

export interface DatabaseFile {
    id: number;
    fileable_id: number;
    fileable_type: string;
    original_name: string;
    unique_name: string;
    path: string;
    order: number;
    size: number;
    section?: string;
    created_at: Date;
    updated_at: Date;
}

export interface OrderFiles {
    viesFiles: DatabaseFile[];
    creditCheckFiles: DatabaseFile[];
    contractUnsignedFiles?: DatabaseFile[];
    contractSignedFiles: DatabaseFile[];
    files: DatabaseFile[];
    generatedPdf: DatabaseFile[];
}

export interface VehicleImages {
    internalImages: Array<DatabaseImage>;
    externalImages: Array<DatabaseImage>;
}

export interface VehicleFiles {
    internalFiles: Array<DatabaseFile>;
    externalFiles: Array<DatabaseFile>;
}

export interface SalesOrderFiles {
    viesFiles: DatabaseFile[];
    creditCheckFiles: DatabaseFile[];
    contractFiles: DatabaseFile[];
    contractSignedFiles: DatabaseFile[];
    files: DatabaseFile[];
    generatedPdf: Array<DatabaseFile>;
}

export interface TransportOrderFiles {
    transportInvoiceFiles: DatabaseFile[];
    cmrWaybillFiles: DatabaseFile[];
    files: DatabaseFile[];
    generatedPickupAuthorizationPdf: DatabaseFile[];
    generatedTransportRequestOrTransportOrderPdf: DatabaseFile[];
    generatedStickervelPdf: DatabaseFile[];
}

export interface RadioToggleInput {
    name: string;
    value: boolean;
}

export interface SelectInput {
    name: string;
    value: number | number[];
}

export interface ValueUpdate {
    key: string;
    value: string | number | null;
}

export interface UpdateFormValue<T> {
    key: keyof T;
    value: T[keyof T];
}

export interface DashboardBoxBuilder {
    count: number;
    redFlagsCount: number;
}
export interface DashboardBox {
    name: string;
    description: string;
    slug: string;
    boxBuilder: DashboardBoxBuilder;
}

export interface User {
    readonly id: number;
    creator: User;
    prefix: string | null;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
    image_path: string;
    mobile: string;
    other_phone: string | null;
    gender: Enum<typeof Gender>;
    language: Enum<typeof Country>;
    email_verified_at: string;
    company_id: number;
    company: Company;
    images: DatabaseImage;
    id_card_expiry_date: Date;
    roles: string[];
    permissions: string[];
    change_logs: ChangeLog[];
    pendingOwnershipsCount: number | string;
    notificationsCount: number | string;
    readonly created_at: Date;
    readonly updated_at: Date;
    pre_orders?: PreOrder[];
    pre_orders_count?: number;
    purchase_orders?: PurchaseOrder[];
    purchase_orders_count?: number;
    sales_orders?: SalesOrder[];
    sales_orders_count?: number;
    service_orders?: ServiceOrder[];
    service_orders_count?: number;
    work_orders?: WorkOrder[];
    work_orders_count?: number;
    transport_orders?: TransportOrder[];
    transport_orders_count?: number;
    service_levels?: ServiceLevel[];
    service_levels_count?: number;
    items?: Item[];
    items_count?: number;
    documents?: Document[];
    documents_count?: number;
    vehicles?: Vehicle[];
    vehicles_count?: number;
    pre_order_vehicles?: PreOrderVehicle[];
    pre_order_vehicles_count?: number;
    service_vehicles?: ServiceVehicle[];
    service_vehicles_count?: number;
    makes?: Make[];
    makes_count?: number;
    vehicle_models?: VehicleModel[];
    vehicle_models_count?: number;
    vehicle_groups?: VehicleGroup[];
    vehicle_groups_count?: number;
    engines?: Engine[];
    engines_count?: number;
    variants?: Variant[];
    variants_count?: number;
    companies?: Company[];
    companies_count?: number;
    company_groups?: CompanyGroup[];
    company_groups_count?: number;
    users?: User[];
    users_count?: number;
    created_user_groups?: UserGroup[];
    created_user_groups_count?: number;
    quotes?: Quote[];
    quotes_count?: number;
}

export interface UserForm extends Omit<User, "id", "creator">, Form {
    roles: number[];
}

export interface CrmUser extends User {
    id_card_files: DatabaseFile;
}

export interface CrmUserForm extends Omit<UserForm, "id_card_files">, Form {
    id_card_files: File[];
}

export interface UserGroup {
    id: number;
    name: string;
    users?: User[];
    creator: User;
}

export interface UserGroupForm
    extends Omit<UserGroup, "creator", "users">,
        Form {
    userIds: number[];
}

export interface Make {
    readonly id: number;
    creator: User;
    name: string;
    readonly created_at: Date;
}

export interface MakeForm extends Omit<Make, "creator", "created_at">, Form {}

export interface Variant extends Form {
    readonly id: number;
    name: string;
    make: Make;
    user: User;
}

export interface VariantForm extends Omit<Variant, "make", "user">, Form {
    make_id: number;
}

export interface CompanyAddress {
    id?: number;
    type: Enum<typeof CompanyAddressType>;
    address: string;
    remarks?: string;
}

export interface CompanyPdfAssets {
    logo: DatabaseImage[];
    pdf_signature_image: DatabaseImage[];
    pdf_header_pre_purchase_sales_order_image: DatabaseImage[];
    pdf_header_documents_image: DatabaseImage[];
    pdf_header_quote_transport_and_declaration_image: DatabaseImage[];
    pdf_sticker_image: DatabaseImage[];
    pdf_footer_image: DatabaseImage[];
}

export interface CompanyPdfAssetsFormData {
    logo: File[];
    pdf_signature_image: File[];
    pdf_header_pre_purchase_sales_order_image: File[];
    pdf_header_documents_image: File[];
    pdf_header_quote_transport_and_declaration_image: File[];
    pdf_sticker_image: File[];
    pdf_footer_image: File[];
}

export interface Company {
    id: number;
    type: Enum<typeof CompanyType>;
    creator: User;
    company_group_id: number | null;
    main_user_id: number | null;
    main_user: User;
    billing_contact_id: number | null;
    billing_contact: User;
    logistics_contact_id: number | null;
    delivery_contact: User;
    default_currency: Enum<typeof Currency>;
    vat_percentage: number;
    country: Enum<typeof Country>;
    name: string;
    number: string | null;
    number_addition: string | null;
    postal_code: string;
    city: string;
    address: string;
    province: string | null;
    street: string | null;
    address_number: string | null;
    address_number_addition: string | null;
    vat_number: string | null;
    purchase_type: Enum<typeof NationalEuOrWorldType>;
    locale: Enum<typeof Locale>;
    company_number_accounting_system: string | null;
    debtor_number_accounting_system: string | null;
    creditor_number_accounting_system: string | null;
    website: string | null;
    email: string;
    phone: string;
    iban: string | null;
    swift_or_bic: string | null;
    bank_name: string | null;
    kvk_number: string | null;
    image_path: string;
    billing_remarks: string | null;
    logistics_times: string | null;
    pdf_footer_text: string | null;
    logistics_remarks: string | null;
    addresses?: CompanyAddress[];
    logistics_addresses?: CompanyAddress[];
    change_logs: ChangeLog[];
    readonly created_at: Date;
    logo?: string;
}

export interface CompanyForm
    extends Omit<
            Company,
            "creator",
            "main_user",
            "billing_contact",
            "delivery_contact"
        >,
        Form {}

export interface CrmCompany extends Company {
    kvk_expiry_date: Date;
    vat_expiry_date: Date;
    kvk_files: DatabaseFile[];
    vat_files: DatabaseFile[];
}

export interface CrmCompanyForm
    extends Omit<
            CrmCompany,
            "creator",
            "main_user",
            "billing_contact",
            "delivery_contact",
            "kvk_files",
            "vat_files"
        >,
        Form {
    kvk_files: File[];
    vat_files: File[];
}

export interface Workflow {
    readonly id: number;
    vehicleableType: string;
    vehicle: Vehicle | ServiceVehicle;
    finished_steps_management?: WorkflowFinishedStep[];
}

export interface WorkOrder {
    readonly id: number;
    creator_id: number;
    creator: User;
    vehicleable_id: number;
    type: Enum<typeof WorkOrderType>;
    workflow: Workflow;
    total_price: number;
    status: Enum<typeof WorkOrderStatus>;
    internal_remarks: InternalRemark[];
    tasks: WorkOrderTask[];
    files: DatabaseFile[];
    statuses: Statusable[];
    readonly created_at: Date;
    readonly deleted_at: Date;
}

export type Workorderable = Vehicle | ServiceVehicle;

export interface WorkOrderForm
    extends Omit<WorkOrder, "internal_remarks", "creator">,
        Form {}

export interface WorkOrderTask {
    id: number;
    creator_id: number;
    creator: User;
    assigned_to_id: number;
    assigned_to: User;
    work_order_id: number;
    work_order: WorkOrder;
    total_price: number;
    name: string;
    description: string;
    type: Enum<typeof WorkOrderTaskStatus>;
    status: Enum<typeof WorkOrderTaskStatus>;
    estimated_price: number;
    actual_price: number;
    planned_date: Date;
    completed_at: Date;
    readonly created_at: Date;
    images: DatabaseImage[];
    files: DatabaseFile[];
}

export interface WorkOrderTaskForm
    extends Omit<
            WorkOrderTask,
            "internal_remarks",
            "images",
            "files",
            "creator"
        >,
        Form {
    images: File[];
    files: File[];
}

export interface VehicleOptions {
    option: string | null;
    damage_description: string | null;
    expected_date_of_availability_from_supplier: WeekPicker;
    expected_leadtime_for_delivery_from: number;
    expected_leadtime_for_delivery_to: number;
    first_registration_nl: Date | null;
    registration_nl: Date | null;
    registration_date_approval: Date | null;
    last_name_registration: Date | null;
    first_name_registration_nl: Date | null;
    registration_valid_until: Date | null;
}

export interface VehicleFactoryOptions {
    factory_name_interior: string | null;
    panorama: Enum<typeof Panorama> | null;
    headlights: Enum<typeof Headlights> | null;
    digital_cockpit: Enum<typeof DigitalCockpit> | null;
    cruise_control: Enum<typeof CruiseControl> | null;
    keyless_entry: Enum<typeof KeylessEntry> | null;
    air_conditioning: Enum<typeof Airconditioning> | null;
    pdc: Enum<typeof PDC> | null;
    second_wheels: Enum<typeof SecondWheels> | null;
    camera: Enum<typeof Camera> | null;
    tow_bar: Enum<typeof TowBar> | null;
    seat_heating: Enum<typeof SeatHeating> | null;
    seat_massage: Enum<typeof SeatMassage> | null;
    optics: Enum<typeof Optics> | null;
    tinted_windows: Enum<typeof TintedWindows> | null;
    sports_package: Enum<typeof SportsPackage> | null;
    warranty: Enum<typeof Warranty> | null;
    navigation: Enum<typeof Navigation> | null;
    sports_seat: Enum<typeof SportsSeat> | null;
    seats_electrically_adjustable: Enum<
        typeof SeatsElectricallyAdjustable
    > | null;
    app_connect: Enum<typeof AppConnect> | null;
    color_type: Enum<typeof ColorType> | null;
    warranty_free_text: string | null;
    navigation_free_text: string | null;
    app_connect_free_text: string | null;
    panorama_free_text: string | null;
    headlights_free_text: string | null;
    digital_cockpit_free_text: string | null;
    cruise_control_free_text: string | null;
    keyless_entry_free_text: string | null;
    air_conditioning_free_text: string | null;
    pdc_free_text: string | null;
    second_wheels_free_text: string | null;
    camera_free_text: string | null;
    tow_bar_free_text: string | null;
    sports_seat_free_text: string | null;
    seats_electrically_adjustable_free_text: string | null;
    seat_heating_free_text: string | null;
    seat_massage_free_text: string | null;
    optics_free_text: string | null;
    tinted_windows_free_text: string | null;
    sports_package_free_text: string | null;
    highlight_1: string | null;
    highlight_2: string | null;
    highlight_3: string | null;
    highlight_4: string | null;
    highlight_5: string | null;
    highlight_6: string | null;
}

export interface VehicleOrders {
    pre_order: PreOrder[];
    purchase_order: PurchaseOrder[];
    sales_order: SalesOrder[];
    service_order: ServiceOrder;
    transport_orders: TransportOrder[];
    work_order: WorkOrder;
    documents: Document[];
    quotes: Quote[];
}

export interface VehicleOptionsData {
    make: Multiselect<Make>;
    engine?: Multiselect<Engine>;
    variant?: Multiselect<Variant>;
    vehicleModel?: Multiselect<VehicleModel>;
    vehicleGroup?: Multiselect<VehicleGroup>;
    bpmValues?: any;
}

export interface VehicleBase {
    readonly id: number;
    creator: User;
    make: Make;
    vehicle_model: VehicleModel;
    variant: Variant;
    engine: Engine;
    engine_id: number;
    engine_free_text: string | null;
    type: Enum<typeof VehicleType>;
    body: Enum<typeof VehicleBody> | null;
    fuel: Enum<typeof FuelType>;
    variant_id: number | null;
    vehicle_model_free_text: string | null;
    variant_free_text: string | null;
    current_registration: Enum<typeof Country> | null;
    interior_material: Enum<typeof InteriorMaterial> | null;
    transmission: Enum<typeof Transmission>;
    transmission_free_text: string | null;
    specific_exterior_color: Enum<typeof ExteriorColour> | null;
    supplier_company_id: number;
    supplier_id: number;
    created_at: Date;
}

export interface Vehicle
    extends VehicleBase,
        VehicleOrders,
        VehicleOptions,
        VehicleCalculation,
        VehicleFactoryOptions,
        VehicleImages,
        VehicleFiles {
    is_ready_to_be_sold: boolean;
    purchase_repaired_damage: boolean;
    vehicleGroup?: Multiselect<VehicleGroup>;
    owner_id: number;
    ownership: Ownership;
    first_registration_date: Date | null;
    workflow: Workflow;
    supplier_company?: Company;
    supplier: User;
    calculation: VehicleCalculation;
    vehicle_group: VehicleGroup;
    coc: Enum<typeof Coc> | null;
    kilometers: number | null;
    hp: number | null;
    kw: number | null;
    vin: string | null;
    co2_nedc: number | null;
    co2_wltp: number | null;
    factory_name_color: string | null;
    vehicle_status: Enum<typeof VehicleStatus> | null;
    stock: Enum<typeof VehicleStock> | null;
    advert_link: string | null;
    vehicle_reference: string | null;
    image_path: string | null;
    specific_interior_color: Enum<typeof InteriorColour>;
    supplier_reference_number: string | null;
    supplier_given_damages: number | string | null;
    gross_bpm_new: number | string | null;
    rest_bpm_as_per_table: number | string | null;
    calculation_bpm_in_so: number | string | null;
    bpm_declared: number | string | null;
    gross_bpm_recalculated_based_on_declaration: number | string | null;
    gross_bpm_on_registration: number | string | null;
    rest_bpm_to_date: number | string | null;
    invoice_bpm: number | string | null;
    bpm_post_change_amount: number | string | null;
    depreciation_percentage: number | null;
    nl_registration_number: string | null;
    identification_code: string | null;
    user_follows?: User[];
    change_logs: ChangeLog[];
    internal_remarks: InternalRemark[];
    pivot: Vehicleable;
    make_id: number;
    vehicle_model_id: number;
    vehicle_group_id: number;
    readonly created_at: Date;
    readonly updated_at: Date;
    readonly deleted_at: Date;
}

export interface Vehicleable {
    vehicle_id: number;
    vehicleable_id: number;
    vehicleable_type: string;
    delivery_week: WeekPicker;
}

export interface WeekPicker {
    from: string[] | null;
    to: string[] | null;
}

export interface VehicleForm
    extends Omit<
            Vehicle,
            "make",
            "model",
            "variant",
            "engine",
            "vehicle_group",
            "creator"
        >,
        Form,
        FormMethod {}

export interface PreOrderVehicle
    extends VehicleBase,
        VehicleCalculation,
        VehicleFactoryOptions,
        VehicleImages,
        VehicleFiles {
    supplier_company?: Company;
    supplier: User;
    calculation: VehicleCalculation;
    vehicle_status: Enum<typeof VehicleStatus> | null;
    vehicleModel?: Enum<typeof VehicleModel>;
    komm_number: string | null;
    factory_name_color: string | null;
    image_path: string | null;
    vehicle_reference: string | null;
    specific_interior_color: null | int;
    configuration_number: string | null;
    kilometers: number | null;
    production_weeks: WeekPicker;
    expected_delivery_weeks: WeekPicker;
    expected_leadtime_for_delivery_from: number | null;
    expected_leadtime_for_delivery_to: number | null;
    registration_weeks_from: number | null;
    registration_weeks_to: number | null;
    option: string | null;
    pre_order?: PreOrder;
    transport_orders?: TransportOrder[];
    internal_remarks: InternalRemark[];
    documents?: Document[];
    readonly created_at: Date;
    readonly deleted_at: Date;
}

export interface PreOrderVehicleForm
    extends Omit<
            PreOrderVehicle,
            "make",
            "model",
            "variant",
            "engine",
            "creator"
        >,
        Form,
        FormMethod {
    make_id: number;
    vehicle_model_id: number;
}

export interface VehicleCalculation {
    is_vat: boolean;
    is_locked: boolean;
    intermediate: boolean;
    original_currency: Enum<typeof Currency> | null;
    selling_price_supplier: number | string | null;
    sell_price_currency_euro: number | string | null;
    vat_percentage: number | null;
    net_purchase_price: number | string | null;
    fee_intermediate: number | string | null;
    total_purchase_price: number | string | null;
    costs_of_damages: number | string | null;
    transport_inbound: number | string | null;
    transport_outbound: number | string | null;
    costs_of_taxation: number | string | null;
    recycling_fee: number | string | null;
    purchase_cost_items_services: number | string | null;
    sale_price_net_including_services_and_products: number | string | null;
    sale_price_services_and_products: number | string | null;
    discount: number | string | null;
    sales_margin: number | string | null;
    total_costs_with_fee: number | string | null;
    sales_price_net: number | string | null;
    vat: number | string | null;
    sales_price_incl_vat_or_margin: number | string | null;
    rest_bpm_indication: number | string | null;
    leges_vat: number | string | null;
    sales_price_total: number | string | null;
    gross_bpm: number | string | null;
    bpm_percent: number | string | null;
    bpm: number | string | null;
    currency_exchange_rate: number | null;
    bpmValues?: Bpm;
}

export interface Bpm {
    type_id: number;
    fuel_type: Enum<typeof FuelType>;
    year: number;
    co2_min: number;
    co2_max: number | null;
    sum_amount: number;
    multiply_amount: number;
    min_co2_to_add_diesel_tax: number | null;
    additional_diesel_tax: number | null;
}

export interface VehicleGroup {
    id: number;
    name: string;
    vehicles: Vehicle[];
    creator: User;
    readonly created_at: Date;
}

export interface VehicleGroupForm
    extends Omit<VehicleGroup, "creator", "created_at">,
        Form {}

export interface CompanyGroup {
    id: number;
    name: string;
    companies?: Company[];
    creator: User;
    readonly created_at: Date;
}

export interface CompanyGroupForm
    extends Omit<CompanyGroup, "creator", "created_at">,
        Form {}

export interface Engine extends Form {
    readonly id: number;
    name: string;
    make: VehicleMake;
    fuel: Enum<typeof FuelType>;
    user: User;
    readonly created_at: Date;
}

export interface EngineForm extends Omit<Engine, "make", "user">, Form {
    make_id: number;
}

export interface Item {
    id: number;
    creator: User;
    unit_type: Enum<typeof UnitType>;
    type: Enum<typeof ItemType>;
    is_vat: boolean;
    vat_percentage: number;
    shortcode: string;
    description: string;
    purchase_price: number;
    sale_price: number;
    pivot?: {
        service_level_id: number;
        item_id: number;
        in_output: boolean;
    };
    in_output?: boolean;
    item?: Item;
    readonly created_at: Date;
}

export interface ItemForm extends Omit<Item, "creator", "created_at">, Form {}

export interface OrderItem {
    id: number;
    type: number;
    shortcode: string;
    description: string;
    in_output: boolean;
    purchase_price: string;
    sale_price: string;
    item: Item;
    shouldBeAdded?: boolean;
}

export interface Setting {
    id: number;
    company: Company;
    costs_of_damages: number;
    transport_inbound: number;
    transport_outbound: number;
    costs_of_taxation: number;
    recycling_fee: number;
    sales_margin: number;
    leges_vat: number;
}

export interface SettingForm extends Omit<Setting, "company">, Form {}

export interface VehicleModel extends Form {
    readonly id: number;
    name: string;
    make: Make;
    creator: User;
}

export interface VehicleModelForm
    extends Omit<VehicleModel, "make", "creator">,
        Form {
    make_id: number;
}

export interface PurchaseOrder extends OrderFiles {
    readonly id: number;
    creator: User;
    owner: User;
    owner_id: number;
    supplier_company_id: number;
    supplier_id: number;
    intermediary_company_id: number;
    intermediary_id: number;
    purchaser_id: number;
    supplier_company: Company;
    intermediary_company: Company;
    supplier: User;
    intermediary: User;
    purchaser: User;
    status: Enum<typeof PurchaseOrderStatus>;
    currency_po: Enum<typeof Currency>;
    type: Enum<typeof NationalEuOrWorldType>;
    vat_percentage: number;
    down_payment_amount: number;
    total_payment_amount: number;
    sales_restriction: string;
    contact_notes: string;
    transport_included: boolean;
    vat_deposit: boolean;
    vat_deposit_amount: number;
    document_from_type: Enum<typeof SupplierOrIntermediary>;
    papers: Enum<typeof Papers>;
    payment_condition: Enum<typeof PaymentCondition>;
    payment_condition_free_text: string | null;
    down_payment: boolean;
    total_purchase_price: number;
    total_purchase_price_eur: number;
    total_fee_intermediate_supplier: number;
    total_purchase_price_exclude_vat: number;
    total_transport: number;
    total_vat: number;
    total_bpm: number;
    total_purchase_price_include_vat: number;
    currency_rate: number;
    internal_remarks: InternalRemark[];
    statuses: Statusable[];
    mails: Mail[];
    vehicleIds: number[];
    readonly created_at: Date;
}

export interface PurchaseOrderForm
    extends Omit<
            PurchaseOrder,
            "owner",
            "supplier",
            "intermediary",
            "purchaser",
            "creator"
        >,
        Form,
        FormMethod {}

export interface PreOrder extends OrderFiles {
    readonly id: number;
    creator: User;
    supplier_company: Company;
    supplier: User;
    intermediary_company: Company;
    intermediary: User;
    purchaser: User;
    pre_order_vehicle_id: number;
    status: Enum<typeof PreOrderStatus>;
    type: Enum<typeof ImportEuOrWorldType>;
    document_from_type: Enum<typeof SupplierOrIntermediary>;
    currency_po: Enum<typeof Currency>;
    vat_percentage: number;
    down_payment_amount: number;
    contact_notes: string;
    transport_included: boolean;
    vat_deposit: boolean;
    amount_of_vehicles: number;
    down_payment: boolean;
    total_purchase_price: number;
    total_purchase_price_eur: number;
    total_fee_intermediate_supplier: number;
    total_purchase_price_exclude_vat: number;
    total_vat: number;
    total_bpm: number;
    total_purchase_price_include_vat: number;
    currency_rate: number;
    statuses: Statusable[];
    change_logs: ChangeLog[];
    mails: Mail[];
    internal_remarks: InternalRemark[];
    pre_order_vehicle?: PreOrderVehicle;
    readonly created_at: Date;
}

export interface PreOrderForm
    extends Omit<PreOrder, "supplier", "intermediary", "purchaser", "creator">,
        Form,
        FormMethod {
    supplier_company_id: number;
    supplier_id: number;
    intermediary_company_id: number;
    intermediary_id: number;
    purchaser_id: number;
}

export interface UpdateStatusForm extends Form, FormMethod {
    _method: string;
    id: number;
    status: number;
    locale?: Enum<typeof Locale>;
    route: string;
}

export interface SalesOrder extends SalesOrderFiles, ServiceLevelDefaults {
    readonly id: number;
    readonly number: string;
    creator: User;
    owner: User;
    owner_id: number;
    status: Enum<typeof SalesOrderStatus>;
    customer_company?: Company;
    customer?: User;
    seller: User;
    customer_company_id: number;
    customer_id: number;
    seller_id: number;
    service_level_id: number;
    reference: string;
    currency: Enum<typeof Currency>;
    down_payment: boolean;
    down_payment_amount: number;
    vat_deposit: boolean;
    vat_percentage: number;
    total_vehicles_purchase_price: number;
    total_costs: number;
    total_sales_price_service_items: number;
    total_sales_margin: number;
    total_sales_price: number;
    is_brutto: boolean;
    calculation_on_sales_order: boolean;
    total_fee_intermediate_supplier: number;
    total_sales_price_exclude_vat: number;
    total_sales_excl_vat_with_items: number;
    total_vat: number;
    total_bpm: number;
    total_sales_price_include_vat: number;
    total_registration_fees: number;
    currency_rate: number;
    additional_info_conditions: string;
    delivery_week: WeekPicker;
    order_items: OrderItem[];
    internal_remarks: InternalRemark[];
    statuses: Statusable[];
    mails: Mail[];
    quote?: Quote;
    vehicles?: Vehicle[];
    documents?: Document;
    readonly created_at: Date;
    readonly deleted_at: Date;
}

export interface SalesOrderForm
    extends Omit<SalesOrder, "customer", "seller", "creator", "order_items">,
        Form,
        FormMethod {
    additional_services: AdditionalService[];
    items: OrderItem[];
}

export interface TransportOrder extends TransportOrderFiles {
    readonly id: number;
    creator: User;
    owner: User;
    owner_id: number;
    status: Enum<typeof TransportOrderStatus>;
    transport_company_use: boolean;
    transport_company: Company;
    transporter: User;
    transport_type: Enum<typeof TransportType>;
    vehicle_type: Enum<typeof TransportableType>;
    pick_up_company_id: number;
    pick_up_location_id: number;
    pick_up_location_free_text: string;
    pick_up_notes: string;
    pick_up_after_date: Datimestampte;
    delivery_company_id: number;
    delivery_location_id: number;
    delivery_location_free_text: string;
    delivery_notes: string;
    deliver_before_date: Date;
    planned_delivery_date: Date;
    total_transport_price: number;
    vehicleIds?: number[];
    internal_remarks?: InternalRemark[];
    statuses: Statusable[];
    pick_up_location?: CompanyAddress;
    delivery_location?: CompanyAddress;
    readonly created_at: Date;
}

export interface TransportOrderForm
    extends Omit<
            TransportOrder,
            "customer",
            "purchaser",
            "creator",
            "transport_company"
        >,
        Form,
        FormMethod {
    transport_company_id: number;
    transporter_id: number;
    transportables: TransportableData[];
}

export interface Document {
    readonly id: number;
    creator: User;
    owner: User;
    owner_id: number;
    customer_company_id: number;
    customer_id: number;
    customer_company: Company;
    customer: User;
    status: Enum<typeof DocumentStatus>;
    documentable_type: Enum<typeof DocumentableType>;
    paid_at: Date;
    payment_condition: Enum<typeof PaymentCondition>;
    payment_condition_free_text: string | null;
    number: string;
    notes: string;
    total_price_exclude_vat: number;
    total_vat: number;
    total_price_include_vat: number;
    date: Date;
    lines: DocumentLine[];
    change_logs: ChangeLog[];
    documentables: Documentable[];
    internal_remarks: InternalRemark[];
    files: DatabaseFile[];
    statuses: Statusable[];
    mails: Mail[];
    pre_order_vehicles?: PreOrderVehicle[];
    vehicles?: Vehicle[];
    service_vehicles?: ServiceVehicle[];
    sales_orders?: SalesOrder[];
    service_orders?: ServiceOrder[];
    readonly created_at: Date;
}

export interface DocumentForm
    extends Omit<Document, "customer", "creator">,
        Form,
        FormMethod {
    documentableIds: number[];
}

export interface DocumentLine {
    id: number;
    document_id?: number;
    name: string;
    type: Enum<typeof DocumentLineType>;
    vat_percentage: number;
    price_exclude_vat: number;
    vat: number;
    price_include_vat: number;
    documentable_id: number;
}

export interface ChangeLog {
    id: string;
    creator_id: number;
    creator: User;
    changeable_type: string;
    changeable_id: number;
    change: string;
    created_at: string;
}

interface ChangeLogsChange {
    old: string;
    new: string;
}

export interface TransportableData {
    transport_order_id?: number;
    transportable_type?: string;
    transportable_id: number;
    location_id: number;
    location_free_text: string;
    price: null | number;
}

export interface ServiceVehicle {
    readonly id: number;
    vehicle_type: Enum<typeof VehicleType>;
    creator: User;
    make: Make;
    vehicle_model: VehicleModel;
    variant: Variant;
    current_registration: Enum<typeof Country>;
    co2_type: Enum<typeof Co2Type>;
    co2_value: number;
    kilometers: number;
    vin: string;
    first_registration_date: Date;
    workflow?: Workflow;
    transport_orders?: TransportOrder[];
    service_order?: ServiceOrder;
    work_order?: WorkOrder;
    pivot: Vehicleable;
    documents?: Document[];
    readonly created_at: Date;
    readonly deleted_at: Date;
}

export interface ServiceVehicleForm
    extends Omit<ServiceVehicle>,
        Form,
        FormMethod {
    make_id: number;
    vehicle_model_id: number;
    variant_id: number;
}

export interface ServiceOrder extends ServiceLevelDefaults {
    readonly id: number;
    status: Enum<typeof ServiceOrderStatus>;
    service_vehicle?: ServiceVehicle;
    order_items?: OrderItem[];
    service_vehicle_id: number;
    customer_company_id: number;
    customer_id: number;
    seller_id: number;
    service_level_id: number;
    customer_company: Company;
    creator: User;
    customer?: User;
    seller: User;
    service_level: ServiceLevel;
    readonly created_at: Date;
    vehicleDocuments: DatabaseFile;
    statuses: Statusable[];
    images: DatabaseImage[];
    files: DatabaseFile[];
    internal_remarks: InternalRemark[];
    readonly deleted_at: Date;
}

export interface ServiceOrderForm
    extends Omit<
            ServiceOrder,
            "service_vehicle",
            "customer",
            "seller",
            "service_level",
            "creator",
            "vehicle_type",
            "make",
            "vehicleModel",
            "variant",
            "current_registration",
            "co2_type",
            "images",
            "files"
        >,
        Form,
        FormMethod {
    service_vehicle_id: number;
    service_level_id: number;
    items: OrderItem[];
    additional_services: AdditionalService[];
    images: File[];
    files: File[];
}

export interface AdditionalService {
    readonly id?: number;
    name: string;
    sale_price: string;
    purchase_price: string;
    in_output: boolean;
    is_service_level: boolean;
}

export interface ServiceLevel extends ServiceLevelDefaults {
    readonly id: number;
    creator: User;
    name: string;
    type: Enum<typeof ServiceLevelType>;
    rdw_company_number: string;
    login_autotelex: string;
    api_japie: string;
    bidder_name_autobid: string;
    bidder_number_autobid: string;
    api_vwe: string;
    items: Item[];
    additional_services: AdditionalService[];
    change_logs: ChangeLog[];
    companies: Company[];
    sales_orders: SalesOrder[];
    sales_orders_count: number;
    service_orders: ServiceOrder[];
    service_orders_count: number;
    quotes: Quote[];
    quotes_count: number;
    readonly created_at: Date;
}

export interface ServiceLevelDefaults {
    damage: Enum<typeof Damage>;
    payment_condition: Enum<typeof PaymentCondition>;
    payment_condition_free_text: string | null;
    discount: number | string | null;
    discount_in_output: boolean;
    transport_included: boolean;
    type_of_sale?: Enum<typeof ImportOrOriginType>;
    type_of_service?: Enum<typeof ImportOrOriginType>;
}

export interface CompanyDefaults {
    purchase_type: Enum<typeof NationalEuOrWorldType>;
    default_currency: Enum<typeof Currency>;
}

export interface DatabaseRecord {
    id: number;
    name: string;
}

export interface ImagePreview {
    id?: number;
    name: string;
    size: number;
    imageData?: string;
}

export interface ServiceLevelForm
    extends Omit<
            ServiceLevel,
            "items",
            "additional_service_items",
            "companies",
            "creator",
            "created_at"
        >,
        Form,
        FormMethod {
    items: number[];
    additional_services: AdditionalService[];
    companies: Array<number>;
}

export interface InternalRemark {
    readonly id: number;
    note: string;
    created_at: Date;
    creator: User;
    users: User[];
    replies: InternalRemarkReply[];
}

export interface InternalRemarkReply {
    readonly id: number;
    reply: string;
    created_at: Date;
    creator: User;
}

export interface InternalRemarkReplyForm extends Form, FormMethod {}

export interface QuoteInvitation {
    id: number;
    status: number;
    quote: Quote;
    quote_id: number;
    customer_ids: number[];
    user_group_id: number;
    customer: User;
    created_at: Date;
}

export interface QuoteInvitationForm {
    quote_id: number;
    customer_ids: number[];
    user_group_id: number;
    locale: Enum<typeof Locale>;
}

export interface Quote extends ServiceLevelDefaults {
    vehicles: Vehicle[];
    id: number;
    owner_id: number;
    type: Enum<typeof ImportEuOrWorldType>;
    creator: User;
    customer?: User;
    customer_company?: Company;
    customer_company_id: number;
    seller?: User;
    seller_id: number;
    reservation_customer: User;
    reservation_customer_id: number;
    reservation_until: string;
    customer_id: number;
    service_level_id: number;
    sales_order_id: number;
    status: Enum<typeof QuoteStatus>;
    name: string;
    delivery_week: WeekPicker;
    down_payment: boolean;
    down_payment_amount: number;
    vat_percentage: number;
    vat_deposit: boolean;
    additional_info_conditions: string;
    email_text: string;
    total_vehicles_purchase_price: number;
    total_costs: number;
    total_sales_price_service_items: number;
    total_sales_margin: number;
    total_fee_intermediate_supplier: number;
    total_sales_price_exclude_vat: number;
    total_sales_price_include_vat: number;
    total_vat: number;
    total_bpm: number;
    is_brutto: boolean;
    calculation_on_quote: boolean;
    total_quote_price_exclude_vat: number;
    total_registration_fees: number;
    total_quote_price: number;
    readonly created_at: Date;
    readonly updated_at: Date;
    files: DatabaseFile[];
    statuses: Statusable[];
    order_services: AdditionalService[];
    order_items: OrderItem[];
    quote_invitations: QuoteInvitation[];
    service_level?: ServiceLevel;
    mails: Mail[];
    sales_order?: SalesOrder;
    internal_remarks: InternalRemark[];
}

export interface QuoteForm
    extends Omit<Quote, "customer", "vehicles">,
        Form,
        FormMethod {
    customer_id?: number;
}

export interface Option {
    name: string;
    value: string | number | null;
}

export interface Mail {
    id: number;
    creator_id: number;
    creator: User;
    receivable_id: number;
    receivable_type: string;
    receivable: User | Company;
    mail_content_id: number;
    subject: string;
    attached_file_unique_name: string;
    content: MailContent;
    created_at: Date;
}

export interface Statusable {
    status: number;
    statusable_id: number;
    statusable_type: string;
    created_at: Date;
}

export interface MailContent {
    id: number;
    content: string;
    created_at: Date;
}

interface WorkflowStepChecks {
    hasAddedWorkOrderTasks: boolean;
    hasArrangedExportRdw: boolean;
    hasBeenNotifiedBySupplierForPickupDate: boolean;
    hasBpmReturned: boolean;
    hasCalculatedAndProcessedProvision: boolean;
    hasChosenValuationOrBpm: boolean;
    hasCreatedVehicleInvoice: boolean;
    hasDecidedInternalOrExternalTransport: boolean;
    hasDefinedPickupDate: boolean;
    hasDefinedPickupLocation: boolean;
    hasDeliverLicenseNumberAndCodesOrPapers: boolean;
    hasDeterminedBpmAndUploadedBpmDeclaration: boolean;
    hasEstimatedDeliveryDate: boolean;
    hasEstimatedPickupDate: boolean;
    hasExecutedValuation: boolean;
    hasFilledRegistrationNumberAndCodes: boolean;
    hasFirstRegistrationDate: boolean;
    hasInformedSupplierForPickupDate: boolean;
    hasMarkedCompletedWorkOrder: boolean;
    hasMarkedCompletedWorkOrderTasks: boolean;
    hasOrArrangedForeignRegistration: boolean;
    hasPassedSampleInspection: boolean;
    hasPhotosTaken: boolean;
    hasPlannedSampleInspection: boolean;
    hasPlannedValuation: boolean;
    hasPreparedBankPayment: boolean;
    hasProcessBpmLicense: boolean;
    hasRdwApproval: boolean;
    hasRdwIdentification: boolean;
    hasReadyForInvoice: boolean;
    hasReceivedForeignVehicleDocuments: boolean;
    hasReceivedInvoicePayment: boolean;
    hasReceivedLicencePlateAndRegistrationCard: boolean;
    hasReceivedNlVehicleDocuments: boolean;
    hasReceivedOriginalDocuments: boolean;
    hasReceivedProForm: boolean;
    hasReceivedServicePayment: boolean;
    hasReceivedVatDeposit: boolean;
    hasReceivedVehicle: boolean;
    hasReclamationsDone: boolean;
    hasRegisteredClient: boolean;
    hasSampleInspectionApproval: boolean;
    hasSentAllDocumentsAndBpmReturnForm: boolean;
    hasSentBpmInvoice: boolean;
    hasSentCmr: boolean;
    hasSentInvoiceToCustomer: boolean;
    hasSentPaymentConfirmation: boolean;
    hasSentPickupAuth: boolean;
    hasSentTransportAndAuth: boolean;
    hasSentTransportOutboundOrder: boolean;
    hasSentVehicleInvoice: boolean;
    hasServiceInvoice: boolean;
    hasTransportCompanyConfirmation: boolean;
    hasTransportOrderInbound: boolean;
    hasTransportOrderOutbound: boolean;
    hasTransportOutboundDelivered: boolean;
    hasTransportOutboundPickedUp: boolean;
    hasUploadedBpmApproval: boolean;
    hasUploadedBpmDeclaration: boolean;
    hasUploadedFinalInvoice: boolean;
    hasUploadedNlRegistration: boolean;
    hasUploadedVehicleIntakeForm: boolean;
    hasVatDepositDocument: boolean;
    hasVinNumber: boolean;
}

export interface WorkflowFinishedStep {
    readonly id?: number;
    workflow_id: int;
    class_name: string;
    additional_value?: string;
    finished_at: Date;
    files?: DatabaseFile[];
    images?: DatabaseImage[];
}

export interface WorkflowFinishedStepForm
    extends Omit<WorkflowFinishedStep, "finished_at", "files">,
        Form {
    files?: File[];
    email_subject?: string;
    email_recipient?: string;
    week?: WeekPicker;
}

interface WorkflowComponent {
    name: string;
    isCompleted: boolean;
}

interface WorkflowRedFlag {
    name: string;
    description: string;
    isTriggered: boolean;
}
export interface WorkflowStep extends WorkflowComponent {
    modalComponentName: string;
    className: string;
    isDisabled: boolean;
    hasQuickDateFinish: boolean;
    componentData: any;
    finishedStepAdditionalValue: string;
    summary?: string;
    dateFinished?: Date;
    emailRecipient?: string;
    emailTemplateText?: string;
    emailSubject?: string;
    url: string;
    files: DatabaseFile[];
    images: DatabaseImage[];
    redFlag?: WorkflowRedFlag;
}

export interface WorkflowStatus extends WorkflowComponent {
    summary?: string;
    steps: WorkflowStep[];
}

export interface WorkflowSubprocess extends WorkflowComponent {
    vueComponentIcon: string;
    isCompleted: boolean;
    statuses: WorkflowStatus[];
}

export interface WorkflowProcess extends WorkflowComponent {
    subprocesses: WorkflowSubprocess[];
}

export interface Workflow {
    readonly id: number;
    process: WorkflowProcess;
    vehicle: Vehicle;
    workOrder?: WorkOrder;
    documents?: Document[];
    files?: VehicleFiles & { stepFiles: DatabaseFile[] };
    images?: VehicleImages;
}

export interface EmailTemplate {
    creator: User;
    id: number;
    name: string;
    text_top?: string;
    text_bottom?: string;
    updated_at: Date | null;
    created_at: Date;
}

export interface EmailTemplateForm
    extends Omit<EmailTemplate, "id", "creator">,
        Form {}

export interface ReCacheForm extends Form {}

export type WorkFilterTab = "allRecords" | "redFlags";

export interface Ownership<T = any> {
    id: number;
    creator_id: number;
    user_id: number;
    ownable_type: string;
    ownable_id: number;
    creator: User;
    user: User;
    ownable: T;
    status: Enum<typeof OwnershipStatus>;
}

export interface OwnerProps {
    mainCompanyUsers: Multiselect<User>;
    pendingOwnerships?: Ownership[];
}

export interface OwnershipForm extends Omit<Ownership>, Form, FormMethod {}

interface Notification {
    readonly id: any;
    creator: User;
    read_at: Date | null;
    data: {
        user_name: string;
        model_name: string;
        model_id: number;
    };
}

export interface Permission {
    readonly id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at?: string;
}

export interface Role {
    permissions: Permission[];
    readonly id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at?: string;
    change_logs: ChangeLog[];
}

export interface RoleForm
    extends Omit<RoleData, "id" | "permissions" | "created_at" | "updated_at"> {
    permissions: number[];
    id?: number;
    _method?: "PUT" | "PATCH";
}

export interface Rule {
    required?: boolean;
    type?: string;
    with?: string;
    without?: string;
    minLength?: number;
    maxLength?: number;
    min?: number | string | Ref<string>;
    max?: number | string | Ref<string>;
    [key: string]: any; // Allow for additional properties in a nested rule
}

export type Rules = Record<string, Rule>;
