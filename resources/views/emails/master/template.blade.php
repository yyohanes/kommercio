<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <!-- If you delete this meta tag, Half Life 3 will never be released.-->
    <!-- Template by marcosilva.co.uk, base on Zurb responsive templates and boiler plate, images and copy from http://www.hardgraft.com/ -->

    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>{{ isset($title)?$title:'Title' }}</title>

    <style>
        /* ------------------------------------- 		GLOBAL ------------------------------------- */
        * {
            font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
        }
        img {
            max-width:100%;
        }
        .collapse {
            padding-right:15px;
            padding:0;
        }
        body {
            -webkit-font-smoothing:antialiased;
            -webkit-text-size-adjust:none;
            width:100%!important;
            height: 100%;
        }
        /* ------------------------------------- 		ELEMENTS ------------------------------------- */
        a {
            color:#aaaaaa;
            font-size:12px;
        }
        .bt {
            padding-top:10px;
        }
        p {
            margin-bottom: 1em;
        }
        p.callout {
            padding:9px;
            font-size:12px;
        }
        p.text {
            padding-left:5px;
            font-size:12px;
        }
        p.left {
            padding:5px;
            font-size:12px;
            text-align:left;
        }
        .prod {
            margin:0;
            padding:0;
            color:#aaaaaa;
        }
        .callout a {
            font-weight:bold;
            color: #aaaaaa;
        }
        /* ------------------------------------- 		HEADER ------------------------------------- */
        table.head-wrap {
            width:100%;
        }
        .header.container table td.logo {
            padding:15px;
        }
        .header.container table td.label {
            padding:15px;
            padding-left: 0px;
        }
        /* ------------------------------------- 		BODY ------------------------------------- */
        table.body-wrap {
            width: 100%;
        }
        /* ------------------------------------- TABLE CONTENT ------------------------------------- */
        .order-table {
            margin: 20px 0;
            border-collapse: collapse;
        }

        .order-table th {
            font-weight: bold;
            text-align: left;
        }

        .order-table th, .order-table td{
            border: 1px solid #f5f5f5;
            font-size: 12px;
            padding: 8px;
        }

        .order-table tfoot td {
            background-color: #f5f5f5;
        }

        /* ------------------------------------- FOOTER------------------------------------- */
        table.footer-wrap {
            width:100%;
            background-color: #f5f5f5;
            height: 50px;
        }
        table.footer-wrap2 {
            width: 100%;
        }
        }
        /* ------------------------------------- 		TYPOGRAPHY ------------------------------------- */
        h1,h2,h3,h4,h5,h6 {
            font-family:"Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif;
            line-height:1.1;
            margin-bottom:5px;
            color:#000;
        }
        h1 small,h2 small,h3 small,h4 small,h5 small,h6 small {
            font-size:60%;
            color:#6f6f6f;
            line-height:0;
            text-transform:none;
        }
        h1 {
            font-weight:200;
            font-size:18px;
            padding:20px;
            letter-spacing:3px;
            font-weight:300;
        }
        h2 {
            font-weight:200;
            font-size:37px;
        }
        h3 {
            font-weight:500;
            font-size:27px;
        }
        h4 {
            font-weight:500;
            font-size:23px;
        }
        h5 {
            font-weight:900;
            font-size:13px;
            color:#c2a67e;
        }
        h6 {
            font-weight:900;
            font-size:14px;
            text-transform:uppercase;
            color:#444;

        }
        h7 {
            font-weight:900;
            font-size:14px;
            text-transform:uppercase;
            color:#444;
            padding:5px;
        }
        .collapse {
            margin:0!important;
        }
        p,ul {
            margin-bottom:2px;
            font-weight:normal;
            font-size:11px;
            line-height:1.6;
        }
        p.lead {
            font-size:13px;
        }
        p.last {
            margin-bottom:0px;
        }
        ul li {
            margin-left:5px;
            list-style-position: inside;
        }
        /* --------------------------------------------------- 		RESPONSIVENESS		Nuke it from orbit. ------------------------------------------------------ */
        /* Set a max-width,and make it display as block so it will automatically stretch to that width,but will also shrink down on a phone or something */
        .container {
            display:block!important;
            max-width:600px!important;
            margin:0 auto!important;
            /* makes it centered */
            clear:both!important;
        }
        /* This should also be a block element,so that it will fill 100% of the .container */
        .content {
            padding:5px;
            max-width:600px;
            margin:0 auto;
            display: block;
        }

        /* Let's make sure tables in the content area are 100% wide */
        .content table {
            width: 100%;
        }
        /* Odds and ends */
        .column {
            width:300px;
            float:left;
        }
        .column tr td {
            padding:5px;
        }
        .column-wrap {
            padding:0!important;
            margin:0 auto;
            max-width:600px!important;
        }
        .column table {
            width:100%;
        }
        .social .column {
            width:280px;
            min-width:279px;
            float:left;
        }
        .column3 {
            width:300px;
            float:left;
        }
        .column3 tr td {
            padding:1px;
        }
        .column3-wrap {
            padding:0!important;
            margin:0 auto;
            max-width:600px!important;
        }
        .column3 table {
            width:100%;
        }
        .column2 {
            width:240px;
            float:left;
        }
        .column2 tr td {
            padding:5px;
        }
        .column2-wrap {
            padding:0!important;
            margin:0 auto;
            max-width:600px!important;
        }
        .column2 table {
            width:100%;
        }
        .social .column {
            width:280px;
            min-width:279px;
            float: left;
        }
        /* Odds and ends */
        .prod {
            width:200px;
            float:left;
        }
        .prod tr td {
            padding:5px;
        }
        .prod-wrap {
            padding:0!important;
            margin:0 auto;
            max-width:600px!important;
        }
        .prod table {
            width:100%;
        }
        .prod .column {
            width:200px;
            min-width:200px;
            float: left;
        }
        /* Be sure to place a .clear element after each set of columns,just to be safe */
        .clear {
            display:block;
            clear: both;
        }
        /* ------------------------------------------- 		PHONE		For clients that support media queries.		Nothing fancy. -------------------------------------------- */
        @media only screen and (max-width:600px) {
            a[class="btn"] {
                display:block!important;
                margin-bottom:10px!important;
                background-image:none!important;
                margin-right:0!important;
            }
            div[class="column"] {
                width:auto!important;
                float:none!important;
            }
            div[class="column2"] {
                width:auto!important;
                float:none!important;
            }
            div[class="column3"] {
                width:auto!important;
                float:none!important;
            }
            table[class="top"] {
                width:auto!important;
                float:none!important;
            }
            .prod {
                width:150px;
                float:left;
            }
            table.social div[class="column"] {
                width: auto!important;
            }
        }
    </style>
</head>
<body bgcolor="#FFFFFF" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
<body bgcolor="#FFFFFF" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
<img editable="true" />
<!------------------------------------ ---- HEADER -------------------------- ------------------------------------->
<table class="head-wrap" bgcolor="#f5f5f5">
    <tr>
        <td>
        </td>
        <td class="header container">
            <div class="content">
                <table bgcolor="#f5f5f5" class="">
                    <tr>
                        <td>
                            @if(file_exists(public_path('project/assets/images/email-logo.png')))
                                <img alt="{{ config('project.client_name', config('kommercio.default_name')) }}" src="{{ asset('project/assets/images/email-logo.png') }}" />
                            @else
                                <h3 class="collapse">{{ config('project.client_name', config('kommercio.default_name')) }}</h3>
                            @endif
                        </td>
                        <td align="right">
                            <h6 class="collapse">{{ config('project.client_subtitle', config('kommercio.default_subtitle')) }}</h6>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
        </td>
    </tr>
</table>
<!------------------------------------ ---- BODY -------------------------- ------------------------------------->
<table class="body-wrap">
    <tr>
        <td>
        </td>
        <td class="container" bgcolor="#FFFFFF">
            @yield('content')
        </td>
        <td>
        </td>
    </tr>
</table>
<!-- FOOTER -->
<table class="footer-wrap">
    <tr>
        <td>
        </td>
        <td class="container">
            <!-- content -->
            <div class="content">
                <table>
                    <tr>
                        <td align="center">
                            <p>
                                <a href="#">Terms</a> | <a href="#">Privacy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- /content -->
        </td>
        <td>
        </td>
    </tr>
</table>
<!-- /FOOTER -->
</body>
</html>