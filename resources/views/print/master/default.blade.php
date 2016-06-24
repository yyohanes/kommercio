<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('page_title')</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('backend/assets/template/global/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/styles/print.css') }}" rel="stylesheet">

    <link href="{{ asset('backend/assets/template/global/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/template/global/plugins/simple-line-icons/simple-line-icons.min.css') }}" rel="stylesheet" type="text/css" />

    @section('head_styles')
        <style>
            @page{
                size: A4 portrait;
            }
        </style>
    @show
</head>

<body>

<div id="wrapper">
    @yield('content')
</div>
<!-- /#wrapper -->

@section('bottom_scripts')
    <script src="{{ asset('backend/assets/template/global/plugins/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script>
        window.print();
    </script>
@show
</body>

</html>