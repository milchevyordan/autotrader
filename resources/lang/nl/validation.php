<?php

declare(strict_types=1);

return [
    'accepted'        => 'Het veld :attribute moet geaccepteerd worden.',
    'accepted_if'     => 'Het veld :attribute moet geaccepteerd worden wanneer :other :value is.',
    'active_url'      => 'Het veld :attribute moet een geldige URL zijn.',
    'after'           => 'Het veld :attribute moet een datum zijn na :date.',
    'after_or_equal'  => 'Het veld :attribute moet een datum zijn na of gelijk aan :date.',
    'alpha'           => 'Het veld :attribute mag alleen letters bevatten.',
    'alpha_dash'      => 'Het veld :attribute mag alleen letters, cijfers, streepjes en underscores bevatten.',
    'alpha_num'       => 'Het veld :attribute mag alleen letters en cijfers bevatten.',
    'array'           => 'Het veld :attribute moet een array zijn.',
    'ascii'           => 'Het veld :attribute mag alleen enkelbyte alfanumerieke tekens en symbolen bevatten.',
    'before'          => 'Het veld :attribute moet een datum zijn voor :date.',
    'before_or_equal' => 'Het veld :attribute moet een datum zijn voor of gelijk aan :date.',
    'between'         => [
        'array'   => 'Het :attribute moet minimaal één cijfer bevatten, samen met letters, symbolen en een combinatie van hoofd- en kleine letters.',
        'file'    => 'Het :attribute moet minimaal één cijfer bevatten, samen met letters, symbolen en een combinatie van hoofd- en kleine letters.',
        'numeric' => 'Het :attribute moet minimaal één cijfer bevatten, samen met letters, symbolen en een combinatie van hoofd- en kleine letters.',
        'string'  => 'Het :attribute moet minimaal één cijfer bevatten, samen met letters, symbolen en een combinatie van hoofd- en kleine letters.',
    ],
    'boolean'           => 'Het veld :attribute moet waar of onwaar zijn.',
    'confirmed'         => 'De bevestiging van het veld :attribute komt niet overeen.',
    'current_password'  => 'Het wachtwoord is onjuist.',
    'date'              => 'Het veld :attribute moet een geldige datum zijn.',
    'date_equals'       => 'Het veld :attribute moet een datum zijn gelijk aan :date.',
    'date_format'       => 'Het veld :attribute moet overeenkomen met het formaat :format.',
    'decimal'           => 'Het veld :attribute moet :decimal decimalen bevatten.',
    'declined'          => 'Het veld :attribute moet afgewezen worden.',
    'declined_if'       => 'Het veld :attribute moet afgewezen worden wanneer :other :value is.',
    'different'         => 'Het veld :attribute en :other moeten verschillend zijn.',
    'digits'            => 'Het veld :attribute moet :digits cijfers bevatten.',
    'digits_between'    => 'Het veld :attribute moet tussen :min en :max cijfers bevatten.',
    'dimensions'        => 'Het veld :attribute heeft ongeldige afmetingen voor een afbeelding.',
    'distinct'          => 'Het veld :attribute heeft een dubbele waarde.',
    'doesnt_end_with'   => 'Het veld :attribute mag niet eindigen met één van de volgende waarden: :values.',
    'doesnt_start_with' => 'Het veld :attribute mag niet beginnen met één van de volgende waarden: :values.',
    'email'             => 'Het veld :attribute moet een geldig e-mailadres zijn.',
    'ends_with'         => 'Het veld :attribute moet eindigen met één van de volgende waarden: :values.',
    'enum'              => 'De geselecteerde :attribute is ongeldig.',
    'exists'            => 'De geselecteerde :attribute is ongeldig.',
    'file'              => 'Het veld :attribute moet een bestand zijn.',
    'filled'            => 'Het veld :attribute moet een waarde bevatten.',
    'gt'                => [
        'array'   => 'Het veld :attribute moet meer dan :value items bevatten.',
        'file'    => 'Het veld :attribute moet groter zijn dan :value kilobytes.',
        'numeric' => 'Het veld :attribute moet groter zijn dan :value.',
        'string'  => 'Het veld :attribute moet groter zijn dan :value tekens.',
    ],
    'gte' => [
        'array'   => 'Het veld :attribute moet :value items of meer bevatten.',
        'file'    => 'Het veld :attribute moet groter zijn dan of gelijk aan :value kilobytes.',
        'numeric' => 'Het veld :attribute moet groter zijn dan of gelijk aan :value.',
        'string'  => 'Het veld :attribute moet groter zijn dan of gelijk aan :value tekens.',
    ],
    'image'     => 'Het veld :attribute moet een afbeelding zijn.',
    'in'        => 'De geselecteerde :attribute is ongeldig.',
    'in_array'  => 'Het veld :attribute moet voorkomen in :other.',
    'integer'   => 'Het veld :attribute moet een geheel getal zijn.',
    'ip'        => 'Het veld :attribute moet een geldig IP-adres zijn.',
    'ipv4'      => 'Het veld :attribute moet een geldig IPv4-adres zijn.',
    'ipv6'      => 'Het veld :attribute moet een geldig IPv6-adres zijn.',
    'json'      => 'Het veld :attribute moet een geldige JSON-string zijn.',
    'lowercase' => 'Het veld :attribute moet in kleine letters zijn.',
    'lt'        => [
        'array'   => 'Het veld :attribute moet minder dan :value items bevatten.',
        'file'    => 'Het veld :attribute moet kleiner zijn dan :value kilobytes.',
        'numeric' => 'Het veld :attribute moet kleiner zijn dan :value.',
        'string'  => 'Het veld :attribute moet kleiner zijn dan :value tekens.',
    ],
    'lte' => [
        'array'   => 'Het veld :attribute mag niet meer dan :value items bevatten.',
        'file'    => 'Het veld :attribute moet kleiner zijn dan of gelijk aan :value kilobytes.',
        'numeric' => 'Het veld :attribute moet kleiner zijn dan of gelijk aan :value.',
        'string'  => 'Het veld :attribute moet kleiner zijn dan of gelijk aan :value tekens.',
    ],
    'mac_address' => 'Het veld :attribute moet een geldig MAC-adres zijn.',
    'max'         => [
        'array'   => 'Het veld :attribute mag niet meer dan :max items bevatten.',
        'file'    => 'Het veld :attribute mag niet groter zijn dan :max kilobytes.',
        'numeric' => 'Het veld :attribute mag niet groter zijn dan :max.',
        'string'  => 'Het veld :attribute mag niet groter zijn dan :max tekens.',
    ],
    'max_digits' => 'Het veld :attribute mag niet meer dan :max cijfers bevatten.',
    'mimes'      => 'Het veld :attribute moet een bestand zijn van het type: :values.',
    'mimetypes'  => 'Het veld :attribute moet een bestand zijn van het type: :values.',
    'min'        => [
        'array'   => 'Het veld :attribute moet ten minste :min items bevatten.',
        'file'    => 'Het veld :attribute moet ten minste :min kilobytes zijn.',
        'numeric' => 'Het veld :attribute moet ten minste :min zijn.',
        'string'  => 'Het veld :attribute moet ten minste :min tekens zijn.',
    ],
    'min_digits'       => 'Het veld :attribute moet ten minste :min cijfers bevatten.',
    'missing'          => 'Het veld :attribute moet ontbreken.',
    'missing_if'       => 'Het veld :attribute moet ontbreken als :other :value is.',
    'missing_unless'   => 'Het veld :attribute moet ontbreken tenzij :other :value is.',
    'missing_with'     => 'Het veld :attribute moet ontbreken als :values aanwezig is.',
    'missing_with_all' => 'Het veld :attribute moet ontbreken als :values aanwezig zijn.',
    'multiple_of'      => 'Het veld :attribute moet een veelvoud zijn van :value.',
    'not_in'           => 'De geselecteerde :attribute is ongeldig.',
    'not_regex'        => 'Het formaat van het veld :attribute is ongeldig.',
    'numeric'          => 'Het veld :attribute moet een getal zijn.',
    'password'         => [
        'letters'       => 'Het veld :attribute moet minstens één letter bevatten.',
        'mixed'         => 'Het veld :attribute moet minstens één hoofdletter en één kleine letter bevatten.',
        'numbers'       => 'Het veld :attribute moet minstens één cijfer bevatten.',
        'symbols'       => 'Het veld :attribute moet minstens één symbool bevatten.',
        'uncompromised' => 'De opgegeven :attribute is verschenen in een datalek. Kies a.u.b. een andere :attribute.',
    ],
    'present'              => 'Het veld :attribute moet aanwezig zijn.',
    'prohibited'           => 'Het veld :attribute is verboden.',
    'prohibited_if'        => 'Het veld :attribute is verboden als :other :value is.',
    'prohibited_unless'    => 'Het veld :attribute is verboden tenzij :other zich in :values bevindt.',
    'prohibits'            => 'Het veld :attribute verbiedt dat :other aanwezig is.',
    'regex'                => 'Het formaat van het veld :attribute is ongeldig.',
    'required'             => 'Het veld :attribute is verplicht.',
    'required_array_keys'  => 'Het veld :attribute moet invoeren bevatten voor: :values.',
    'required_if'          => 'Het veld :attribute is verplicht als :other :value is.',
    'required_if_accepted' => 'Het veld :attribute is verplicht als :other geaccepteerd is.',
    'required_unless'      => 'Het veld :attribute is verplicht tenzij :other zich in :values bevindt.',
    'required_with'        => 'Het veld :attribute is verplicht als :values aanwezig is.',
    'required_with_all'    => 'Het veld :attribute is verplicht als :values aanwezig zijn.',
    'required_without'     => 'Het veld :attribute is verplicht als :values niet aanwezig is.',
    'required_without_all' => 'Het veld :attribute is verplicht als geen van de :values aanwezig zijn.',
    'same'                 => 'Het veld :attribute en :other moeten overeenkomen.',
    'size'                 => [
        'array'   => 'Het veld :attribute moet :size items bevatten.',
        'file'    => 'Het veld :attribute moet :size kilobytes zijn.',
        'numeric' => 'Het veld :attribute moet :size zijn.',
        'string'  => 'Het veld :attribute moet :size tekens zijn.',
    ],
    'starts_with'                                                    => 'Het veld :attribute moet beginnen met één van de volgende waarden: :values.',
    'string'                                                         => 'Het veld :attribute moet een tekenreeks zijn.',
    'timezone'                                                       => 'Het veld :attribute moet een geldige tijdzone zijn.',
    'unique'                                                         => 'Het veld :attribute is al in gebruik.',
    'uploaded'                                                       => 'Het uploaden van het veld :attribute is mislukt.',
    'url'                                                            => 'Het formaat van het veld :attribute is ongeldig.',
    'uuid'                                                           => 'Het veld :attribute moet een geldige UUID zijn.',
    'Not selected any vehicles, need to select at least one Vehicle' => 'Er zijn geen voertuigen geselecteerd. U moet minimaal één voertuig selecteren.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
];
