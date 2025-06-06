<style>
    @page {
        size: A4;
        margin-top: 40px !important;
        margin-bottom: 50px !important;
        /* Makes padding for the pagination and logos  */
    }

    @font-face {
        font-family: "SourceSans3";
        src: url("../storage/fonts/SourceSans3-Regular.ttf") format("truetype");
        font-weight: normal;
    }

    @font-face {
        font-family: "SourceSans3";
        src: url("../storage/fonts/SourceSans3-Bold.ttf") format("truetype");
        font-weight: bold;
    }

    @font-face {
        font-family: "Montserrat";
        src: url("../storage/fonts/Montserrat-Bold.ttf") format("truetype");
        font-weight: bold;
    }

    .source-sans3 {
        font-family: "SourceSans3", "sans-serif";
    }

    .source-sans3-medium {
        font-family: "SourceSans3", "sans-serif";
        font-weight: normal;  /* optional if it's the default weight */
    }

    .source-sans3-bold {
        font-family: "SourceSans3", "sans-serif";
        font-weight: bold;
    }

    html {
        font-family: "SourceSans3", "sans-serif";
    }

    html .heading {
        font-family: "Montserrat", "sans-serif"; !important;
        font-weight: bold;
    }

    .font-5and52 {
        font-size: 5.52pt;
    }

    .font-6 {
        font-size: 6pt;
    }

    .font-6and86 {
        font-size: 6.86pt;
    }

    .font-7 {
        font-size: 7pt;
    }

    .font-7and44 {
        font-size: 7.44pt;
    }

    .font-7and84 {
        font-size: 7.84pt;
    }

    .font-8and82 {
        font-size: 8.82pt;
    }

    .font-9 {
        font-size: 9pt;
    }

    .font-9and80 {
        font-size: 9.8pt;
    }

    .font-10 {
        font-size: 10pt;
    }

    .font-11 {
        font-size: 11pt;
    }

    .font-11and70 {
        font-size: 11.7pt;
    }

    .font-12 {
        font-size: 12pt;
    }

    .font-16 {
        font-size: 16pt;
    }

    .font-17and55 {
        font-size: 17.55pt;
    }

    .font-18 {
        font-size: 18pt;
    }

    .font-19and5 {
        font-size: 19.5pt;
    }

    .font-28 {
        font-size: 28pt;
    }

    .font-22 {
        font-size: 22pt;
    }

    .font-175and5 {
        font-size: 175.5pt;
    }

    .sticker {
        height: 100px;
        width: 235px;
    }

    .width-1 {
        width: 1%;
    }

    .page-wrapper {
    position: relative;
    min-height: 100vh; /* ensures at least a page */
    page-break-before: always; /* start on a new page if needed */
    }

    .bottom-text {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 10px;
        font-size: 10px;
    }
    .header-orange {
        background: url("../storage/images/orange.png") no-repeat center center / cover;
        width: 811px;
        height: 210px;
        margin-top: -40px !important;
        position: relative;
    }

    .header-gray-background {
        background: url("../storage/images/grey.png") no-repeat center center / cover;
        width: 811px;
        height: 223px;
        margin-top: -40px !important;
        position: relative;
    }

    .header-white {
        background: white;
        width: 811px;
        height: 221px;
        margin-top: -40px !important;
        position: relative;
    }

    .payment-image {
        padding-left: 250px;
        max-height: 125px;
        max-width: 50%;
    }

    .overlay-image {
        position: absolute;
        bottom: 0;
        right: 65px;
        max-height: 150px;
        max-width: 50%;
        z-index: 1;
    }

    .over-overlay-image {
        position: absolute;
        z-index: 2;
    }

    .header-gray-background .align-vertical-pre-order {
        position: absolute;
        top: 70px;
        left: 45px;
    }

    .header-gray-background .align-vertical-purchase-order {
        position: absolute;
        top: 60px;
        left: 45px;
    }

    .header-gray-background .align-vertical-sales-order {
        position: absolute;
        top: 62px;
        left: 45px;
    }

    .header-gray-background .align-vertical-declaration-receipt-of-goods {
        position: absolute;
        top: 70px;
        left: 45px;
    }

    .header-gray-background .align-vertical-declaration-of-vat-payment {
        position: absolute;
        top: 60px;
        left: 45px;
    }

    .header-gray-background .align-vertical-payment-information {
        position: absolute;
        top: 75px;
        left: 45px;
    }

    .header-white .align-vertical {
        position: absolute;
        top: 70px;
        left: 45px;
    }

    .header-orange .align-vertical {
        position: absolute;
        top: 60px;
        left: 45px;
    }

    .align-vertical-transport-order {
        padding-top: 70px;
        padding-left: 45px;
    }

    .vehicle-box.border-top:nth-child(1) {
        border-top: none;
    }

    footer {
        position: fixed;
        bottom: 0cm;
        left: 0cm;
        right: 0cm;
        height: 2cm;
        /** Extra personal styles **/
        background-color: #03a9f4;
        color: white;
        text-align: center;
        line-height: 1.5cm;
    }

    footer {
        position: fixed;
        bottom: 0cm;
        left: 0cm;
        right: 0cm;
        height: 2cm;
        /** Extra personal styles **/
        background-color: #03a9f4;
        color: white;
        text-align: center;
        line-height: 1.5cm;
    }

    .vehicle-box__images .image {
        width: 2.5cm;
        height: auto;
        border-radius: 0.375rem;
        /* Equivalent to rounded-md */
        object-fit: cover;
        /* Equivalent to object-cover */
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
        /* Equivalent to shadow */
    }

    .vehicle-box_quote_images .image {
        width: 154px;
        height: auto;
        border-radius: 0.375rem;
        /* Equivalent to rounded-md */
        object-fit: cover;
        /* Equivalent to object-cover */
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
        /* Equivalent to shadow */
    }

    .text-xs {
        font-size: 0.6em;
    }

    .text-s {
        font-size: 8pt;
    }

    .text-normal {
        font-size: 1em;
    }

    .text-xl {
        font-size: 1.5em;
    }

    .text-2xl {
        font-size: 3em;
    }

    .text-3xl {
        font-size: 4.5em;
    }

    .text-4xl {
        font-size: 17.5em;
    }

    .text-left {
        text-align: left;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .fw-bold {
        font-family: "SourceSans3", "sans-serif";
        font-weight: bold;
    }

    .no-wrap {
        white-space: nowrap;
    }

    .italic {
        font-style: italic;
    }

    .float-left {
        float: left;
    }

    .float-right {
        float: right;
    }

    .width-ten {
        width: 10%;
    }

    .width-12and5 {
        width: 10%;
    }

    .width-fifteen {
        width: 15%;
    }

    .width-twenty {
        width: 20%;
    }

    .width-quarter {
        width: 25%;
    }

    .width-30 {
        width: 30%;
    }

    .width-third {
        width: 33.33%;
    }

    .width-37and5 {
        width: 37.5%;
    }

    .width-half {
        width: 50%;
    }

    .width-two-thirds {
        width: 66.67%;
    }

    .width-70 {
        width: 70%;
    }

    .width-75 {
        width: 75%;
    }

    .width-80 {
        width: 80%;
    }

    .w-full {
        width: 100%;
    }

    .border {
        border: 1px solid grey;
    }

    .border-top {
        border-top: 1px solid #E3E3E3;
    }

    .border-top-black {
        border-top: 1px solid black;
    }

    .border-bottom {
        border-bottom: 1px solid #E3E3E3;
    }

    .border-bottom-black {
        border-bottom: 1px solid black;
    }

    .border-bottom-white {
        border-bottom: 1px solid #ffffff;
        width: 140px;
    }

    .m-4 {
        margin: 4em;
    }

    .m-quarter {
        margin: 0.25em;
    }

    .m-half {
        margin: 0.5em;
    }

    .mx-1 {
        margin: 0 1em;
    }

    .mx-2 {
        margin: 0 2em;
    }

    .mx-4 {
        margin: 0 4em;
    }

    .mt-0 {
        margin-top: 0;
    }

    .my-2 {
        margin: 2em 0;
    }

    .my-3 {
        margin: 3em 0;
    }

    .my-4 {
        margin: 4em 0;
    }

    .mt-half {
        margin-top: .5em;
    }
    .mt-1 {
        margin-top: 1em;
    }

    .mt-2 {
        margin-top: 2em;
    }

    .mt-3 {
        margin-top: 3em;
    }

    .mt-4 {
        margin-top: 4em;
    }

    .mb-half {
        margin-bottom: 0.5em;
    }

    .mb-1 {
        margin-bottom: 1em;
    }

    .mb-2 {
        margin-bottom: 2em;
    }

    .p-1 {
        padding: 1em;
    }

    .p-2 {
        padding: 2em;
    }

    .p-4 {
        padding: 4em;
    }

    .p-5 {
        padding: 5em;
    }

    .py-1 {
        padding: 1em 0;
    }

    .py-1half {
        padding: 1.5em 0;
    }

    .py-2 {
        padding: 2em 0;
    }

    .py-3 {
        padding: 3em 0;
    }

    .pt-half {
        padding-top: 0.5em;
    }

    .pt-1 {
        padding-top: 1em;
    }

    .pt-1and5 {
        padding-top: 1.5em;
    }

    .pt-2 {
        padding-top: 2em;
    }

    .pt-3 {
        padding-top: 3em;
    }

    .pt-4 {
        padding-top: 4em;
    }

    .pt-5 {
        padding-top: 5em;
    }

    .pb-half {
        padding-bottom: 0.5em;
    }

    .pb-1 {
        padding-bottom: 1em;
    }

    .pb-2 {
        padding-bottom: 2em;
    }

    .p-half {
        padding: 0.5em;
    }

    .p-three-quarters {
        padding: 0.75em;
    }

    .p-quarter {
        padding: 0.25em;
    }

    .px-quarter {
        padding: 0 0.25em;
    }

    .px-half {
        padding: 0 0.5em;
    }

    .p-vehicle-information {
        padding: 0.25em 0.5em;
    }

    .py-half {
        padding: 0.5em 0;
    }

    .py-quarter {
        padding: 0.25em 0;
    }

    .px-1 {
        padding: 0 1em;
    }

    .px-2 {
        padding: 0 2em;
    }

    .pl-1 {
        padding-left: 1em;
    }

    .pl-2 {
        padding-left: 2em;
    }

    .pl-12 {
        padding-left: 12em;
    }

    .pr-0 {
        padding-right: 0;
    }

    .align-top {
        vertical-align: top;
    }

    .align-right {
        text-align: right;
    }

    .sticker-div {
        position: absolute;
        top: 80%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .sticker-table {
        width: auto;
        margin-left: auto;
        margin-right: auto;
        border-collapse: collapse;
    }

    .text-white {
        color: white;
    }

    .text-price {
        color: rgb(240, 123, 58);
    }

    .bg-gray {
        background-color: #f7f7f7;
    }

    .bg-quote-options {
        background-color: #EBEBEB;
    }

    .rounded {
        border-radius: 0.375rem;
    }

    .page-break {
        page-break-after: always;
    }

    .break-inside-avoid {
        page-break-inside: avoid;
    }

    .break-inside-auto {
        break-inside: auto;
    }

    .container {
        padding: 0.5cm 1cm;
    }

    .shadow {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    }
</style>
