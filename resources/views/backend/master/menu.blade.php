<ul class="page-sidebar-menu page-header-fixed" data-keep-expanded="true" data-auto-scroll="true" data-slide-speed="200">
    <li class="sidebar-toggler-wrapper hide">
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
        <div class="sidebar-toggler"> </div>
        <!-- END SIDEBAR TOGGLER BUTTON -->
    </li>

    <li class="nav-item start open {{ NavigationHelper::activeClass('dashboard')?'active':'' }}">
        <a href="{{ route('backend.dashboard') }}" class="nav-link ">
            <i class="icon-home"></i>
            <span class="title">Dashboard</span>
        </a>
    </li>

    @can('access', ['view_product', 'view_product_category', 'view_product_attribute', 'view_product_feature', 'view_manufacturer'])
    <li class="nav-item open {{ NavigationHelper::activeClass('catalog')?'active':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-book"></i>
            <span class="title">Catalog</span>
            <span class="arrow open"></span>
        </a>
        <ul class="sub-menu" style="display: block;">
            @can('access', ['view_product'])
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/product/index')?'active':'' }}">
                <a href="{{ route('backend.catalog.product.index') }}" class="nav-link"> Product </a>
            </li>
            @endcan

            @can('access', ['view_product_category'])
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/category')?'active':'' }}">
                <a href="{{ route('backend.catalog.category.index') }}" class="nav-link"> Category </a>
            </li>
            @endcan

            @can('access', ['view_product_attribute'])
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/product-attribute')?'active':'' }}">
                <a href="{{ route('backend.catalog.product_attribute.index') }}" class="nav-link"> Product Attribute </a>
            </li>
            @endcan

            @can('access', ['view_product_feature'])
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/product-feature')?'active':'' }}">
                <a href="{{ route('backend.catalog.product_feature.index') }}" class="nav-link"> Product Feature </a>
            </li>
            @endcan

            @can('access', ['view_manufacturer'])
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/manufacturer')?'active':'' }}">
                <a href="{{ route('backend.catalog.manufacturer.index') }}" class="nav-link"> Manufacturer </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcan

    @can('access', ['view_order', 'view_order_limit'])
    <li class="nav-item open {{ NavigationHelper::activeClass('sales')?'active':'' }}">
        <a href="javascript:;" class="nav-link">
            <i class="fa fa-smile-o"></i>
            <span class="title">Sales</span>
            <span class="arrow open"></span>
        </a>

        <ul class="sub-menu" style="display: block;">
            @can('access', ['view_order'])
            <li class="nav-item {{ NavigationHelper::activeClass('sales/order/')?'active':'' }}">
                <a href="{{ route('backend.sales.order.index') }}" class="nav-link"> Order </a>
            </li>
            @endcan

            @can('access', ['view_order_limit'])
            <li class="nav-item {{ NavigationHelper::activeClass('sales/order-limit')?'active':'' }}">
                <a href="javascript:;" class="nav-link nav-toggle">
                    <span class="title">Order Limit</span>
                    <span class="arrow"></span>
                </a>

                <ul class="sub-menu">
                    <li class="nav-item {{ NavigationHelper::activeClass('sales/order-limit/'.\Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT)?'active':'' }}">
                        <a href="{{ route('backend.order_limit.index', ['type' => \Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT]) }}" class="nav-link">Product Order Limit</a>
                    </li>

                    <li class="nav-item {{ NavigationHelper::activeClass('sales/order-limit/'.\Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT_CATEGORY)?'active':'' }}">
                        <a href="{{ route('backend.order_limit.index', ['type' => \Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT_CATEGORY]) }}" class="nav-link">Category Order Limit</a>
                    </li>
                </ul>
            </li>
            @endcan
        </ul>
    </li>
    @endcan

    @can('access', ['view_product_price_rule', 'view_cart_price_rule'])
    <li class="nav-item open {{ NavigationHelper::activeClass('price-rule')?'active':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-tags"></i>
            <span class="title">Price Rule</span>
            <span class="arrow open"></span>
        </a>
        <ul class="sub-menu" style="display: block;">
            @can('access', ['view_product_price_rule'])
            <li class="nav-item {{ NavigationHelper::activeClass('price-rule/cart')?'active':'' }}">
                <a href="{{ route('backend.price_rule.cart.index') }}" class="nav-link"> Cart Price Rules </a>
            </li>
            @endcan

            @can('access', ['view_cart_price_rule'])
            <li class="nav-item {{ NavigationHelper::activeClass('price-rule/product')?'active':'' }}">
                <a href="{{ route('backend.price_rule.product.index') }}" class="nav-link"> Product Price Rules </a>
            </li>
            @endcan
        </ul>
    </li>
    @endcan

    @can('access', ['view_customer'])
    <li class="nav-item open {{ NavigationHelper::activeClass('customer')?'active':'' }}">
        <a href="{{ route('backend.customer.index') }}" class="nav-link">
            <i class="fa fa-users"></i>
            <span class="title">Customer</span>
        </a>
    </li>
    @endcan

    @can('access', ['view_delivery_report', 'view_production_schedule', 'view_sales_report'])
    <li class="nav-item open {{ NavigationHelper::activeClass('report')?'active':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-bar-chart"></i>
            <span class="title">Report</span>
            <span class="arrow open"></span>
        </a>
        <ul class="sub-menu" style="display: block;">
            @can('access', ['view_sales_report'])
            <li class="nav-item {{ NavigationHelper::activeClass('report/sales')?'active':'' }}">
                <a href="{{ route('backend.report.sales_year') }}" class="nav-link"> Sales </a>
            </li>
            @endcan

            @if(config('project.enable_delivery_date', false))
            @can('access', ['view_delivery_report'])
            <li class="nav-item {{ NavigationHelper::activeClass('report/delivery')?'active':'' }}">
                <a href="{{ route('backend.report.delivery') }}" class="nav-link"> Delivery </a>
            </li>
            @endcan

            @can('access', ['view_production_schedule'])
            <li class="nav-item {{ NavigationHelper::activeClass('report/production-schedule')?'active':'' }}">
                <a href="{{ route('backend.report.production_schedule') }}" class="nav-link"> Production Schedule </a>
            </li>
            @endcan
            @endif
        </ul>
    </li>
    @endcan

    @can('access', ['view_tax', 'view_payment_method', 'view_shipping_method', 'view_store', 'view_address'])
    <li class="nav-item open {{ NavigationHelper::activeClass('configuration')?'active':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-wrench"></i>
            <span class="title">Configuration</span>
            <span class="arrow open"></span>
        </a>
        <ul class="sub-menu" style="display: block;">
            @can('access', ['view_tax'])
            <li class="nav-item {{ NavigationHelper::activeClass('configuration/tax')?'active':'' }}">
                <a href="{{ route('backend.tax.index') }}" class="nav-link">Tax</a>
            </li>
            @endcan

            @can('access', ['view_payment_method'])
            <li class="nav-item {{ NavigationHelper::activeClass('configuration/payment-method')?'active':'' }}">
                <a href="{{ route('backend.payment_method.index') }}" class="nav-link"> Payment Method </a>
            </li>
            @endcan

            @can('access', ['view_shipping_method'])
            <li class="nav-item {{ NavigationHelper::activeClass('configuration/shipping-method')?'active':'' }}">
                <a href="{{ route('backend.shipping_method.index') }}" class="nav-link"> Shipping Method </a>
            </li>
            @endcan

            @can('access', ['view_store'])
            <li class="nav-item {{ NavigationHelper::activeClass('configuration/store')?'active':'' }}">
                <a href="{{ route('backend.store.index') }}" class="nav-link">Store</a>
            </li>
            @endcan

            @can('access', ['view_address'])
            <li class="nav-item {{ NavigationHelper::activeClass('configuration/address/country')?'active':'' }}">
                <a href="{{ route('backend.configuration.address.index', ['type' => 'country']) }}" class="nav-link">Address</a>
            </li>
            @endcan
        </ul>
    </li>
    @endcan

    @can('access', ['view_warehouse'])
    <li class="nav-item open {{ NavigationHelper::activeClass('warehouse')?'active':'' }}">
        <a href="{{ route('backend.warehouse.index') }}" class="nav-link">
            <i class="fa fa-archive"></i>
            <span class="title">Warehouse</span>
        </a>
    </li>
    @endcan

    @can('access', ['view_page', 'view_banner', 'view_menu'])
    <li class="nav-item open {{ NavigationHelper::activeClass('cms')?'active':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-book"></i>
            <span class="title">CMS</span>
            <span class="arrow open"></span>
        </a>
        <ul class="sub-menu" style="display: block;">
            @can('access', ['view_menu'])
            <li class="nav-item {{ NavigationHelper::activeClass('menu')?'active':'' }}">
                <a href="{{ route('backend.cms.menu.index') }}" class="nav-link">Menus</a>
            </li>
            @endcan
            @can('access', ['view_page'])
            <li class="nav-item {{ NavigationHelper::activeClass('page')?'active':'' }}">
                <a href="{{ route('backend.cms.page.index') }}" class="nav-link">Pages</a>
            </li>
            @endcan
            @can('access', ['view_banner'])
            <li class="nav-item {{ NavigationHelper::activeClass('banner')?'active':'' }}">
                <a href="{{ route('backend.cms.banner_group.index') }}" class="nav-link">Banners</a>
            </li>
            @endcan
        </ul>
    </li>
    @endcan

    @can('access', ['view_user', 'view_role'])
    <li class="nav-item open {{ NavigationHelper::activeClass('user')?'active':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-lock"></i>
            <span class="title">Access</span>
        </a>
        <ul class="sub-menu" style="display: block;">
            @can('access', ['view_user'])
            <li class="nav-item {{ NavigationHelper::activeClass('user/index')?'active':'' }}">
                <a href="{{ route('backend.user.index') }}" class="nav-link">Users</a>
            </li>
            @endcan

            @can('access', ['view_role'])
            <li class="nav-item {{ NavigationHelper::activeClass('user/role/index')?'active':'' }}">
                <a href="{{ route('backend.user.role.index') }}" class="nav-link">Roles</a>
            </li>
            @endcan
        </ul>
    </li>
    @endcan
</ul>