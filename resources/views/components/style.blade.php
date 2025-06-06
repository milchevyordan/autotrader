<style>
    .invoice-box {
        margin: auto;
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }

    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }

    .image {
        border-radius: 0.25rem 0.25rem 0.5rem 0.5rem;
        -o-object-fit: cover;
        object-fit: cover;
        width: 12rem;
        min-width: 150px;
        height: 173px;
    }

    .invoice-box table td {
        padding: 5px;
    }

    .invoice-box table tr.top table td,
    .invoice-box table tr.top td table {
        padding-bottom: 20px;
    }

    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }

    .invoice-box table tr.heading {
        line-height: 30px;
        page-break-after: avoid;
    }

    .invoice-box table {
        padding-bottom: 40px;
    }

    .pb-40 {
        padding-bottom: 40px !important;
    }

    .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
        padding: 5px 10px;
    }

    .invoice-box table tr.information td img {
        max-width: 100%;
        height: auto;
        object-fit: contain;
        page-break-inside:avoid;
        /*page-break-after:always;*/
    }

    .vehicle-title {
        display: inline-block;
        font-size: 24px;
        font-weight: bold;
        color: red;
        text-align: center;
        padding: 10px;
        border: 2px solid red;
        border-radius: 10px;
        box-sizing: border-box;
    }

    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.item td {
        border-bottom: 1px solid #eee;
    }

    .invoice-box table tr.item.last td {
        border-bottom: none;
    }

    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }

    .thead {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .invoice-box table tr.thead > td {
        padding-bottom: 5px !important;
    }

    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }

        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }

    /** RTL **/
    .invoice-box.rtl {
        direction: rtl;
        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }

    .invoice-box.rtl table {
        text-align: right;
    }

    .invoice-box.rtl table tr td:nth-child(2) {
        text-align: left;
    }
</style>
