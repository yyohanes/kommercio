<?php
$menus = config('backend_menu');
$count = 0;
?>

<ul class="page-sidebar-menu page-header-fixed" data-keep-expanded="true" data-auto-scroll="false" data-slide-speed="200">
    <li class="sidebar-toggler-wrapper hide">
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
        <div class="sidebar-toggler"> </div>
        <!-- END SIDEBAR TOGGLER BUTTON -->
    </li>

    @foreach($menus as $menu_id => $menu)
        @include('backend.master.menu_item', ['menu' => $menu, 'depth' => 0])
    @endforeach
</ul>
