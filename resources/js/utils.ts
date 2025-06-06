import { InertiaForm, router, usePage } from "@inertiajs/vue3";
import {
    AdditionalService,
    DatabaseFile,
    DatabaseImage,
    Documentable,
    DocumentForm,
    DocumentLine,
    Enum,
    Form,
    ImagePreview,
    OrderItem,
    PreOrderForm,
    PreOrderVehicle,
    ServiceVehicle,
    Statusable,
    Vehicle,
    Vehicleable,
    VehicleCalculation,
    WorkOrderTask,
} from "@/types";
import {
    computed,
    defineAsyncComponent,
    onMounted,
    onUnmounted,
    Ref,
    ref,
} from "vue";
import { setFlashMessages } from "@/globals";
import { SalesOrderStatus } from "@/enums/SalesOrderStatus";
import { ServiceOrderStatus } from "@/enums/ServiceOrderStatus";
import { WorkOrderStatus } from "@/enums/WorkOrderStatus";
import { DocumentableType } from "@/enums/DocumentableType";
import { __ } from "@/translations";
import { DocumentLineType } from "@/enums/DocumentLineType";
import { validate } from "@/validations";
import { updateStatusFormRules } from "@/rules/update-status-form-rules";
import { addWeeks, parseISO } from "date-fns";
import { format } from "date-fns";
import { CompanyType } from "@/enums/CompanyType";
import axios from "axios";

export function dateToLocaleString(date: Date | string | null | undefined) {
    if (date === null || date === undefined) {
        return;
    }

    if (date instanceof Date) {
        return date.toLocaleDateString("de-DE", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }

    return new Date(date).toLocaleDateString("de-DE", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

export const classBasename = (classPath: string): string => {
    const parts = classPath.split("\\");

    return parts[parts.length - 1];
};

export function dateForInput(date: Date | null | undefined): string | null {
    if (typeof date == "string") {
        date = new Date(date);
    }

    if (!(date instanceof Date) || isNaN(date.getTime())) {
        return null;
    }

    return `${date.getFullYear()}-${(date.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;
}

export const handleDeliveryWeekChange = (form: InertiaForm<any>) => {
    let minFrom: string | null = null;
    let maxTo: string | null = null;
    let min: string[] | null = null;
    let max: string[] | null = null;

    (Object.values(form.vehicles) as Vehicleable[]).forEach(
        (vehicle: Vehicleable) => {
            const firstFrom = vehicle.delivery_week.from
                ? vehicle.delivery_week.from[0]
                : null;
            const firstTo = vehicle.delivery_week.to
                ? vehicle.delivery_week.to[0]
                : null;

            if (
                firstFrom &&
                (!minFrom || new Date(firstFrom) < new Date(minFrom))
            ) {
                minFrom = firstFrom;
                min = vehicle.delivery_week.from;
            }

            if (firstTo && (!maxTo || new Date(firstTo) > new Date(maxTo))) {
                maxTo = firstTo;
                max = vehicle.delivery_week.to;
            }
        }
    );

    form.delivery_week = {
        from: min,
        to: max,
    };
};

export const calculatePreOrderPrices = (
    form: PreOrderForm,
    preOrderVehicleCalculation: VehicleCalculation | null
) => {
    form.total_purchase_price = multiplyPrice(
        preOrderVehicleCalculation?.total_purchase_price as string,
        form.amount_of_vehicles
    );
    form.total_purchase_price_eur = convertUnitsToCurrency(
        (convertCurrencyToUnits(form.total_purchase_price) as number) *
            form.currency_rate || 0
    );

    form.total_fee_intermediate_supplier = multiplyPrice(
        preOrderVehicleCalculation?.fee_intermediate as string,
        form.amount_of_vehicles
    );
    form.total_purchase_price_exclude_vat = multiplyPrice(
        preOrderVehicleCalculation?.net_purchase_price as string,
        form.amount_of_vehicles
    );

    form.total_vat = multiplyPrice(
        preOrderVehicleCalculation?.vat as string,
        form.amount_of_vehicles
    );
    form.total_bpm = multiplyPrice(
        preOrderVehicleCalculation?.bpm as string,
        form.amount_of_vehicles
    );
    form.total_purchase_price_include_vat = multiplyPrice(
        preOrderVehicleCalculation?.total_purchase_price as string,
        form.amount_of_vehicles
    );
};

const colorClasses = {
    Beige: "bg-amber-100",
    Blue: "bg-blue-500",
    Brown: "bg-amber-900",
    Bronze: "bg-yellow-700",
    Yellow: "bg-yellow-400",
    Grey: "bg-gray-500",
    Green: "bg-green-500",
    Red: "bg-red-500",
    Black: "bg-black",
    Silver: "bg-gray-300",
    Purple: "bg-purple-500",
    White: "bg-white",
    Orange: "bg-orange-500",
    Gold: "bg-yellow-500",
    Other: "bg-white",
};

export default colorClasses;

export function addWeeksToWeekYear(
    week: (string | Date)[] | null,
    weeksToAdd: number | null
): string[] | null {
    if (!week) {
        return null;
    }

    if (!weeksToAdd) {
        return week.map((date) =>
            date instanceof Date ? date.toISOString() : date
        );
    }

    return week.map((date) => {
        try {
            const dateString = date instanceof Date ? date.toISOString() : date;

            const parsedDate = parseISO(dateString);
            const updatedDate = addWeeks(parsedDate, weeksToAdd);

            updatedDate.setHours(updatedDate.getHours() + 4);

            return updatedDate.toISOString();
        } catch (error) {
            return ""; // Return a fallback value
        }
    });
}

export const getImage = (imagePath: string | null): string => {
    return imagePath ? `/storage/${imagePath}` : "/images/no-image.webp";
};

export const getStatusColorClassesByTaskCompletion = (
    statusEnum: any,
    currentStatus: number
) => {
    const totalSteps = statusEnum[Object.keys(statusEnum).pop()!];
    const percentageFinished = (currentStatus / totalSteps) * 100;
    let classes;
    switch (true) {
        case percentageFinished < 51:
            classes = "bg-red-100 text-red-800";
            break;
        case percentageFinished < 100:
            classes = "bg-yellow-100 text-yellow-800";
            break;
        default:
            classes = "bg-green-100 text-green-800";
            break;
    }

    return classes;
};

export const convertStatusEnum = (status: Enum<any>) => {
    return Object.entries(status)
        .filter(([name]) => isNaN(Number(name)))
        .map(([name, value]) => ({
            name,
            value: Number(value),
        }));
};

export function dateTimeToLocaleString(
    dateTime: Date | string | null
): string | undefined {
    if (dateTime === null) {
        return undefined;
    }

    let date: Date;

    if (dateTime instanceof Date) {
        date = dateTime;
    } else if (typeof dateTime === "string") {
        if (dateTime.includes("Z")) {
            date = new Date(dateTime);
        } else {
            const isoDateTime = new Date(dateTime + "Z");
            date = new Date(isoDateTime);
        }

        if (isNaN(date.getTime())) {
            console.error("Invalid date string:", dateTime);
            return undefined;
        }
    } else {
        console.error("Invalid input type:", typeof dateTime);
        return undefined;
    }

    return date.toLocaleDateString("de-DE", {
        hour: "2-digit",
        minute: "2-digit",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

/**
 * Formats a date based on UTC or local time.
 *
 * @param date The Date object to format.
 * @param dateFormat The format string (e.g., "yyyy-MM-dd HH:mm").
 * @param useUtc Boolean flag to determine whether to format in UTC or local time.
 * @returns A computed property that returns the formatted date string.
 */
export const formatDateByTimezone = (
    date: Date,
    dateFormat: string,
    useUtc: boolean = true
) =>
    computed(() => {
        const targetDate = useUtc ? new Date(date.toISOString()) : date;

        return format(targetDate, dateFormat);
    });

export function checkIsDateAfter(date: string | null, year: number): boolean {
    return date !== null && new Date(date).getFullYear() >= year;
}

export function capitalizeFirstLetter(str: string): string {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

export function pushIfNotExists<T>(arr: T[], item: T): T[] {
    if (!arr.includes(item)) {
        return [...arr, item];
    }
    return arr;
}

export function pushOrRemove<T>(arr: T[], item: T): T[] {
    const index = arr.indexOf(item);
    return index === -1 ? [...arr, item] : arr.filter((x) => x !== item);
}

export function withFlash(only: Array<string> | undefined): string[] {
    return only ? [...only, "flash"] : [];
}

export function startsWith(
    inputString: string | undefined,
    searchString: string | string[]
): boolean {
    if (typeof inputString === "undefined") {
        return false;
    }

    if (Array.isArray(searchString)) {
        // If searchString is an array, check if inputString starts with any item in the array
        return searchString.some((str) => inputString.startsWith(str));
    } else {
        // If searchString is a single string, check if inputString starts with that string
        return inputString.startsWith(searchString);
    }
}

export function replacePlaceholder(
    inputString: string,
    replacement: number,
    placeholder: string = "?id"
): string {
    return inputString.replace(placeholder, replacement.toString());
}

export function findKeyByValue(
    data: { [key: string]: number } | null | undefined,
    id: null | number
): string | undefined {
    if (data === null || data === undefined) {
        return undefined;
    }

    const foundKey = Object.keys(data).find((key) => data[key] === id);
    return foundKey || undefined;
}

export function findEnumKeyByValue(
    searchEnum: any,
    value: number | null
): string | undefined {
    if (!value) {
        return undefined;
    }

    return searchEnum[value] ?? undefined;
}

export function strToNum(number: string): number | string {
    if (number == "") {
        return "";
    }

    return typeof number === "string"
        ? parseFloat(
              number
                  .replace(/\s+/g, "") // Removes the empty space after thousands
                  .replace(/,/g, ".") // Removes the comma and sets dots
          )
        : "";
}

export function sumCurrencyValues(items: Vehicle[], extractor: (item: Vehicle) => string | number | undefined | null): string {
    return convertUnitsToCurrency(items.reduce((sum: number, item: Vehicle) => {
        const rawValue = extractor(item);

        const units = convertCurrencyToUnits(rawValue ?? "");
        const numeric = typeof units === "number" && !isNaN(units) ? units : 0;

        return sum + numeric;
    }, 0));
}

export function numberFormat(number: number | string) {
    //  Check if the user has writen the decimals with comma(,)
    const formatValue = typeof number === "string" ? strToNum(number) : number;

    //If empty string - return the empty string isntead of NaN text
    if (!formatValue) return "";

    // // de-DE if needed point for thausend separator
    return new Intl.NumberFormat("bg-BG", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(formatValue));
}

export function roundNumber(number: number): number | string {
    return number ? Math.round(Number(number)) : "";
}

export function convertCurrencyToUnits(
    currencyValue: string | number
): number | string {
    if (typeof currencyValue == "number") {
        return currencyValue;
    }

    const numericValue = strToNum(currencyValue);
    return typeof numericValue === "number"
        ? Math.floor(numericValue * 100)
        : "";
}

export function convertUnitsToCurrency(unitsValue: number | string): string {
    if (typeof unitsValue == "string") {
        return unitsValue;
    }
    return numberFormat(unitsValue / 100);
}

export const CrmCompanyType = omitEnumKeys(CompanyType, ["Base"] as Array<
    keyof typeof CompanyType
>);

export function getPriceExcludeVatAndVat(
    priceIncludeVat: string | number,
    vatPercentage?: number
): Record<any, any> {
    const priceIncludeVatUnits: number =
        typeof priceIncludeVat == "number"
            ? priceIncludeVat
            : (convertCurrencyToUnits(priceIncludeVat) as number);

    const vatUnits = ((vatPercentage ?? 0) * priceIncludeVatUnits) / 100;

    return {
        vat: convertUnitsToCurrency(vatUnits),
        price_exclude_vat: convertUnitsToCurrency(
            priceIncludeVatUnits - vatUnits
        ),
    };
}

export const updateDocumentLines = (form: InertiaForm<DocumentForm>) => {
    form.documentables.map((documentable: Documentable) => {
        const exists = form.lines.some(
            (line: DocumentLine) => line.documentable_id === documentable.id
        );
        if (exists) {
            return;
        }

        if ("calculation" in documentable) {
            form.lines.push({
                id: null!,
                documentable_id: documentable.id,
                name: replaceEnumUnderscores(
                    findEnumKeyByValue(DocumentableType, form.documentable_type)
                ),
                type: DocumentLineType.Main,
                vat_percentage: usePage().props?.auth.company.vat_percentage,
                price_exclude_vat: documentable?.calculation.sales_price_net,
                vat: documentable?.calculation.vat,
                price_include_vat: documentable?.calculation.sales_price_total,
            });

            if (documentable.calculation.is_vat) {
                return;
            }

            if (documentable?.calculation.rest_bpm_indication) {
                let prices = getPriceExcludeVatAndVat(
                    documentable?.calculation.rest_bpm_indication,
                    0
                );

                form.lines.push({
                    id: null!,
                    documentable_id: documentable.id,
                    name: __("BPM"),
                    type: DocumentLineType.Bpm,
                    vat_percentage: null,
                    price_exclude_vat: prices.price_exclude_vat,
                    vat: prices.vat,
                    price_include_vat:
                        documentable?.calculation.rest_bpm_indication,
                });
            }

            if (documentable?.calculation.leges_vat) {
                let prices = getPriceExcludeVatAndVat(
                    documentable?.calculation.leges_vat,
                    0
                );

                form.lines.push({
                    id: null!,
                    documentable_id: documentable.id,
                    name: __("RDW Identification & Leges"),
                    type: DocumentLineType.Other,
                    vat_percentage: null,
                    price_exclude_vat: prices.price_exclude_vat,
                    vat: prices.vat,
                    price_include_vat: documentable?.calculation.leges_vat,
                });
            }

            if (documentable?.calculation.recycling_fee) {
                let prices = getPriceExcludeVatAndVat(
                    documentable?.calculation.recycling_fee
                );

                form.lines.push({
                    id: null!,
                    documentable_id: documentable.id,
                    name: __("Recycling fee"),
                    type: DocumentLineType.Other,
                    vat_percentage:
                        usePage().props?.auth.company.vat_percentage,
                    price_exclude_vat: prices.price_exclude_vat,
                    vat: prices.vat,
                    price_include_vat: documentable?.calculation.recycling_fee,
                });
            }
        }

        if ("down_payment_amount" in documentable) {
            let priceIncludeVat;
            if (
                form.documentable_type ==
                DocumentableType.Sales_order_down_payment
            ) {
                priceIncludeVat = documentable.down_payment_amount;
            } else {
                let totalPrice = documentable.is_brutto
                    ? documentable.total_sales_price
                    : documentable.total_sales_price_include_vat;

                priceIncludeVat = documentable.down_payment
                    ? diffStrings(
                          totalPrice as unknown as string,
                          documentable.down_payment_amount as unknown as string
                      )
                    : totalPrice;
            }

            let vatPercentage: number =
                documentable.vat_percentage ??
                usePage().props?.auth.company.vat_percentage;

            let prices: Record<string, string> = getPriceExcludeVatAndVat(
                priceIncludeVat,
                vatPercentage
            );

            form.lines.push({
                id: null!,
                documentable_id: documentable.id,
                name:
                    "Sales Order" +
                    (form.documentable_type ==
                    DocumentableType.Sales_order_down_payment
                        ? " down payment"
                        : ""),
                type: DocumentLineType.Main,
                vat_percentage: vatPercentage,
                price_exclude_vat: prices.price_exclude_vat,
                vat: prices.vat,
                price_include_vat: priceIncludeVat,
            });
        }

        if ("order_items" in documentable) {
            documentable?.order_items?.map((orderItem: OrderItem) => {
                if (!orderItem.in_output) {
                    return;
                }

                let prices = getPriceExcludeVatAndVat(orderItem.sale_price);

                form.lines.push({
                    id: null!,
                    documentable_id: documentable.id,
                    name: orderItem.item.shortcode,
                    type: DocumentLineType.Other,
                    vat_percentage: orderItem.item.vat_percentage,
                    price_exclude_vat: prices.price_exclude_vat,
                    vat: prices.vat,
                    price_include_vat: orderItem.sale_price,
                });
            });
        }

        if (
            "order_services" in documentable &&
            Array.isArray(documentable.order_services)
        ) {
            documentable?.order_services?.map(
                (orderService: AdditionalService) => {
                    if (!orderService.in_output) {
                        return;
                    }

                    let prices = getPriceExcludeVatAndVat(
                        orderService.sale_price
                    );

                    form.lines.push({
                        id: null!,
                        documentable_id: documentable.id,
                        name: orderService.name,
                        type: DocumentLineType.Other,
                        vat_percentage:
                            usePage().props?.auth.company.vat_percentage,
                        price_exclude_vat: prices.price_exclude_vat,
                        vat: prices.vat,
                        price_include_vat: orderService.sale_price,
                    });
                }
            );
        }

        if ("tasks" in documentable) {
            documentable?.tasks?.map((task: WorkOrderTask) => {
                let prices = getPriceExcludeVatAndVat(task.actual_price);

                form.lines.push({
                    id: null!,
                    documentable_id: documentable.id,
                    name: task.name,
                    type: DocumentLineType.Other,
                    vat_percentage:
                        usePage().props?.auth.company.vat_percentage,
                    price_exclude_vat: prices.price_exclude_vat,
                    vat: prices.vat,
                    price_include_vat: task.actual_price,
                });
            });
        }
    });
};

export const calculateDocumentPrices = (form: InertiaForm<DocumentForm>) => {
    form.total_price_exclude_vat = convertUnitsToCurrency(
        form.lines.reduce(
            (sum: number, line: DocumentLine) =>
                sum + Number(convertCurrencyToUnits(line.price_exclude_vat)),
            0
        )
    );

    form.total_vat = convertUnitsToCurrency(
        form.lines.reduce(
            (sum: number, line: DocumentLine) =>
                sum + Number(convertCurrencyToUnits(line.vat)),
            0
        )
    );

    form.total_price_include_vat = convertUnitsToCurrency(
        form.lines.reduce(
            (sum: number, line: DocumentLine) =>
                sum + Number(convertCurrencyToUnits(line.price_include_vat)),
            0
        )
    );
};

export function sumStrings(...args: string[]) {
    const sum = args.reduce((sum, arg) => {
        const num = strToNum(arg);
        return typeof num === "number" && !isNaN(num) ? sum + num : sum + 0;
    }, 0);

    return numberFormat(sum);
}

export function multiplyStrings(...args: string[]) {
    const product = args.reduce((prod, arg) => {
        const num = strToNum(arg);
        return typeof num === "number" && !isNaN(num) ? prod * num : prod;
    }, 1); // Start with 1 for multiplication

    return numberFormat(product);
}

export function diffStrings(...args: string[]) {
    if (args.length < 2) {
        throw new Error("At least two arguments are required.");
    }

    const [excludeStr, ...excluders] = args;
    const excludeNum = strToNum(excludeStr);

    if (typeof excludeNum !== "number") {
        throw new Error("The first argument must be a valid number.");
    }

    const diff = excluders.reduce((sum, arg) => {
        const num = strToNum(arg);
        return typeof num === "number" && !isNaN(num) ? sum - num : sum - 0;
    }, excludeNum);

    return numberFormat(diff);
}

export function textSplit(text: string, charCount: number) {
    if (text.length > charCount) {
        return text.substring(0, charCount);
    } else {
        return text;
    }
}

type Callback = (...args: any[]) => void;

export function debounce(callback: Callback, delay = 300): Callback {
    let timer: ReturnType<typeof setTimeout> | undefined;

    return function (this: any, ...args: any[]) {
        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(() => {
            callback.apply(this, args);
        }, delay);
    };
}

export function replaceUnderscores(object: { [key: string]: any }) {
    const newObject: { [key: string]: any } = {};

    for (let key in object) {
        const newKey = key.replace(/_/g, " ");
        newObject[newKey] = object[key];
    }

    return newObject;
}

export function replaceEnumUnderscores(
    input: string | { [key: string]: number } | undefined,
    capitalize: boolean = false
) {
    if (typeof input === "string") {
        let result = input.replace(/_/g, " ");
        return capitalize
            ? result.replace(/\b\w/g, (char) => char.toUpperCase())
            : result;
    }

    if (typeof input === "undefined") {
        return "";
    }

    return Object.keys(input)
        .map((key) => {
            let result = key.replace(/_/g, " ");
            return capitalize
                ? result.replace(/\b\w/g, (char) => char.toUpperCase())
                : result;
        })
        .join(" ");
}

export const multiplyPrice = (price: string | null, vehiclesCount?: number) => {
    if (!vehiclesCount || !price) {
        return "";
    }

    return convertUnitsToCurrency(
        Number(convertCurrencyToUnits(price)) * vehiclesCount
    );
};

export function isEmpty(value: Array<any> | object | unknown): boolean {
    return Array.isArray(value)
        ? value.length === 0
        : !value || Object.keys(value as object).length === 0;
}

export function isNotEmpty(value: Array<any> | object | unknown): boolean {
    return !isEmpty(value);
}

export const useModal = (
    initialValue = false
): { showModal: Ref<boolean>; closeModal: () => void } => {
    const showModal = ref(initialValue);

    const closeModal = () => {
        showModal.value = false;
    };

    return { showModal, closeModal };
};

export function camelToKebab(str: string): string {
    return str.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
}

export function camelToTitleCase(str: string) {
    // Replace camelCase with spaces and capitalize each word
    return str
        .replace(/([a-z])([A-Z])/g, "$1 $2") // Insert a space before each uppercase letter preceded by a lowercase letter
        .replace(/^./, (match) => match.toUpperCase()) // Capitalize the first letter of the string
        .replace(/\s./g, (match) => match.toUpperCase()); // Capitalize each letter after a space
}

// Utility function to handle changes in child components
export const handleChildChanges = (
    key: string,
    value: string | number,
    form: Record<string, string | number>
): void => {
    form[key] = value;
};

String.prototype.toCapitalize = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

export const padWithZeros = (number: number, width: number): string => {
    return String(number).padStart(width, "0");
};

export const sendMailPdf = (
    resource: {
        mailable_type: string;
        mailable_id: number;
    },
    receiver: string,
    file: string,
    options?: any
) => {
    router.post(route("pdf.send_mail"), {
        resource: resource,
        receiver: receiver,
        file: file,
        options: options,
    });
};

export const changeStatus = async (
    updateStatusForm: InertiaForm<any>,
    status: number
) => {
    updateStatusForm.status = status;
    validate(updateStatusForm, updateStatusFormRules);

    return new Promise<void>((resolve, reject) => {
        updateStatusForm.post(
            route(`${updateStatusForm.route}.update-status`),
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    resolve();
                },
                onError: () => {
                    reject();
                },
            }
        );
        updateStatusForm.reset();
    });
};

export function enumToOptions(
    enumObject: any,
    capitalize: boolean = false,
    returnType: "number" | "string" = "number" // Default returning numeric values
): { name: string; value: number | string }[] {
    const options: { name: string; value: number | string }[] = [];

    for (const key in enumObject) {
        if (typeof enumObject[key] === "number" && returnType === "number") {
            options.push({
                name: capitalize ? capitalizeFirstLetter(key) : key,
                value: enumObject[key],
            });
        } else if (
            typeof enumObject[key] === "string" &&
            returnType === "string"
        ) {
            // If we want to return string keys
            options.push({
                name: capitalize ? capitalizeFirstLetter(key) : key,
                value: enumObject[key],
            });
        }
    }

    return options;
}

export const convertCollectionToMultiselectInTransportOrder = (
    item: Vehicle | PreOrderVehicle | ServiceVehicle
) => {
    let collection: any = [];

    if ("supplier" in item && item.supplier) {
        collection = (item as Vehicle | PreOrderVehicle).supplier.company
            ?.logistics_addresses;
    } else if ("sales_order" in item && item.sales_order) {
        collection = (item as unknown as Vehicle).sales_order[0]?.customer
            ?.company.logistics_addresses;
    } else if ("service_order" in item && item.service_order) {
        collection = (item as ServiceVehicle).service_order?.customer?.company
            .logistics_addresses;
    }

    return collection?.reduce((acc: any, item: any) => {
        acc[item.address] = item.id;
        return acc;
    }, {});
};

export const updateEntityData = (
    entity: Record<string, any>,
    data: [{ key: string; value: any }]
) => {
    for (const { key, value } of Object.values(data)) {
        if (!(key in entity)) {
            continue;
        }

        entity[key] = value;
    }
};

export const formatPriceOnBlur = (entity: Record<string, any>, key: string) => {
    updateEntityData(entity, [
        {
            key: key,
            value: numberFormat(entity[key]),
        },
    ]);
};

export const formatPriceOnFocus = (
    entity: Record<string, any>,
    key: string
) => {
    updateEntityData(entity, [
        {
            key: key,
            value: strToNum(entity[key]),
        },
    ]);
};

export const convertFieldsToCurrencyUnits = (
    targetForm: Form,
    formFields: string[]
): void => {
    for (const field of formFields) {
        targetForm[field] = convertCurrencyToUnits(targetForm[field] as string);
    }
};

export function convertToNumber(value: number): number {
    return value !== null ? Number(strToNum(String(value))) : value;
}

export const convertFieldsToNumber = (
    targetForm: Form,
    formFields: string[]
): void => {
    for (const field of formFields) {
        targetForm[field] = convertToNumber(targetForm[field] as number);
    }
};

export const resetOwnerId = (form: InertiaForm<any>): void => {
    if (form.owner_id != usePage().props.auth.user.id) {
        form.reset("owner_id");
    }
};

export const booleanRepresentation = (bool: boolean): string => {
    return bool ? "Yes" : "No";
};

export const convertFieldsUnitsToCurrency = (
    targetForm: Form,
    formFields: string[]
): void => {
    for (const field of formFields) {
        targetForm[field] = convertUnitsToCurrency(targetForm[field] as number);
    }
};

export const findCreatedAtStatusDate = (
    statuses: Statusable[],
    statusId: number
): string | undefined => {
    return dateTimeToLocaleString(
        statuses.find((item: any) => item.status === statusId)?.created_at ??
            null
    );
};

export const downLoadFile = (path: string) => {
    if (path) {
        location.replace(route("files.download", path as string));

        setFlashMessages({
            success: __("File downloading..."),
        });
    } else {
        setFlashMessages({
            error: __("Failed to download the file"),
        });
    }
};

export const downLoadFiles = (path: string) => {
    const link = document.createElement("a");
    link.href = route("files.download", path as string);
    link.download = "";
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setFlashMessages({
        success: "Files downloading...",
    });
};

export const downLoadMultipleFiles = async (
    files: DatabaseFile[] | DatabaseImage[] | ImagePreview[],
    fileName: string
) => {
    if (!files.length) {
        return;
    }

    try {
        const response = await axios.post(
            route("files.downloadArchived"),
            { files: files },
            {
                responseType: "blob",
            }
        );

        const url = window.URL.createObjectURL(response.data);
        const link = document.createElement("a");
        link.href = url;
        link.download = fileName.toLowerCase().replace(/\s+/g, "-") + ".zip";

        link.click();

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error("Error downloading files:", error);
    }
};

export const getBlobExtension = (blob: Blob): string => {
    const mimeType: string = blob.type;

    const mimeTypesToExtensions: { [key: string]: string } = {
        "text/csv": ".csv",
        "application/pdf": ".pdf",
        "image/jpeg": ".jpg",
        "image/png": ".png",
        "application/json": ".json",
        "text/plain": ".txt",
        "application/zip": ".zip",
        "application/msword": ".doc",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            ".docx",
        "application/vnd.ms-excel": ".xls",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            ".xlsx",
        // Add other types as needed
    };

    return mimeTypesToExtensions[mimeType] || ""; // Return the extension or an empty string if not found
};

export const downLoadAndDeleteFile = async (
    path: string,
    exportName: string
) => {
    const response = await axios.post(
        route("files.downloadAndDelete"),
        { path: path },
        {
            responseType: "blob",
        }
    );

    const extension = getBlobExtension(response.data);
    const url = window.URL.createObjectURL(response.data);
    const link = document.createElement("a");
    link.href = url;
    link.download = `${exportName}.${extension}`;

    link.click();

    window.URL.revokeObjectURL(url);

    setFlashMessages({
        success: __("File downloading..."),
    });
};

//used when records are going to database
export const formatEntityPricesToCurrencyUnits = (
    entity: Record<string, any>,
    priceKeys: string[]
) => {
    entity.forEach((item: Record<string, any>) => {
        convertFieldsToCurrencyUnits(item, priceKeys);
    });
};

//used when records are coming from database
export const formatEntityPricesUnitsToCurrency = (
    entity: Record<string, any> | any[],
    priceKeys: string[]
) => {
    if (Array.isArray(entity)) {
        entity.forEach((item: Record<string, any>) => {
            convertFieldsUnitsToCurrency(item, priceKeys);
        });
    } else if (typeof entity === "object") {
        Object.keys(entity).forEach((key) => {
            if (typeof entity[key] === "object") {
                formatEntityPricesUnitsToCurrency(entity[key], priceKeys);
            } else {
                convertFieldsUnitsToCurrency(entity, priceKeys);
            }
        });
    }
};

export const findLastFileStartingWith = (
    files: DatabaseFile[] | undefined,
    name: string,
    limit?: number
): DatabaseFile | DatabaseFile[] | undefined => {
    if (!limit) {
        return files
            ?.slice()
            .reverse()
            .find((file) => file.unique_name.startsWith(name));
    }

    return files
        ?.slice()
        .reverse()
        .filter((file) => file.unique_name.startsWith(name))
        .slice(0, limit);
};

export const isSameType = <T>(obj: unknown): obj is T => {
    if (obj === null || typeof obj !== "object") {
        return false;
    }

    const requiredProperties: Array<keyof T> = Object.keys(obj) as Array<
        keyof T
    >;

    return requiredProperties.every((prop) => prop in obj);
};

export const iconComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    Euro: defineAsyncComponent(() => import("@/icons/currency/Euro.vue")),
    US_Dollar: defineAsyncComponent(
        () => import("@/icons/currency/UsDollar.vue")
    ),
    Pound_Sterling: defineAsyncComponent(
        () => import("@/icons/currency/PoundSterling.vue")
    ),
    Australian_Dollar: defineAsyncComponent(
        () => import("@/icons/currency/AustralianDollar.vue")
    ),
    Yen: defineAsyncComponent(() => import("@/icons/currency/Yen.vue")),
    Zloty: defineAsyncComponent(() => import("@/icons/currency/Zloty.vue")),
};

export const transportableTypeComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    Vehicles: defineAsyncComponent(
        () => import("@/components/html/VehicleLinks.vue")
    ),
    Pre_order_vehicles: defineAsyncComponent(
        () => import("@/components/html/PreOrderVehicleLinks.vue")
    ),
    Service_vehicles: defineAsyncComponent(
        () => import("@/components/html/ServiceVehicleLinks.vue")
    ),
};

export const workOrderTypeComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    Vehicle: defineAsyncComponent(
        () => import("@/components/html/VehicleLinks.vue")
    ),
    Service_vehicle: defineAsyncComponent(
        () => import("@/components/html/ServiceVehicleLinks.vue")
    ),
};

export const documentableTypeComponentMap: Record<
    string,
    ReturnType<typeof defineAsyncComponent>
> = {
    Pre_order_vehicle: defineAsyncComponent(
        () => import("@/components/html/PreOrderVehicleLinks.vue")
    ),
    Vehicle: defineAsyncComponent(
        () => import("@/components/html/VehicleLinks.vue")
    ),
    Service_vehicle: defineAsyncComponent(
        () => import("@/components/html/ServiceVehicleLinks.vue")
    ),
    Sales_order_down_payment: defineAsyncComponent(
        () => import("@/components/html/SalesOrderLinks.vue")
    ),
    Sales_order: defineAsyncComponent(
        () => import("@/components/html/SalesOrderLinks.vue")
    ),
    Service_order: defineAsyncComponent(
        () => import("@/components/html/ServiceOrderLinks.vue")
    ),
    Work_order: defineAsyncComponent(
        () => import("@/components/html/WorkOrderLinks.vue")
    ),
};

export const documentableTypeStatusEnumMap: Record<string, Enum<any>> = {
    Sales_order_down_payment: SalesOrderStatus,
    Sales_order: SalesOrderStatus,
    Service_order: ServiceOrderStatus,
    Work_order: WorkOrderStatus,
};

export function omitEnumKeys<T extends object>(
    enumObj: T,
    keysToOmit: (keyof T)[]
): Partial<T> {
    // Filter out the specified keys, and ensure we're only working with the keys (not reverse mapping)
    return Object.fromEntries(
        Object.entries(enumObj).filter(
            ([key, value]) =>
                isNaN(Number(key)) && !keysToOmit.includes(key as keyof T)
        )
    ) as Partial<T>;
}

export function useScroll(scrollThreshold = 100) {
    const isScrolled = ref(false);

    const handleScroll = () => {
        isScrolled.value = window.scrollY > scrollThreshold;
    };

    onMounted(() => {
        window.addEventListener("scroll", handleScroll);
    });

    onUnmounted(() => {
        window.removeEventListener("scroll", handleScroll);
    });

    return {
        isScrolled,
    };
}
