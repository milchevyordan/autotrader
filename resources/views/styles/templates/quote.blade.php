<style>
    @page {
        size: A4;
        margin-bottom: 50px !important;
    }

    @font-face {
        font-family: "SourceSans3";
        src: url("/sourcesans3/SourceSans3-VariableFont_wght.ttf") format("truetype");
    }

    @font-face {
        font-family: "Montserrat";
        src: url("/Montserrat/Montserrat-VariableFont_wght.ttf") format("truetype");
    }

    html {
        font-family: "SourceSans3", "sans-serif";
        font-size: 13pt;
        line-height: 0.9;
    }

    html .heading {
        font-family: "Montserrat", "sans-serif";
        font-size: 24px;
    }

    .header {
        background: url("../storage/images/orange-background-with-dots.png") no-repeat center center / cover;
        height: 165px;
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
</style>
