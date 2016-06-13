<ul class="page-sidebar-menu  page-header-fixed page-sidebar-menu-hover-submenu page-sidebar-menu-light" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px;">
    <li class="sidebar-toggler-wrapper hide">
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
        <div class="sidebar-toggler"> </div>
        <!-- END SIDEBAR TOGGLER BUTTON -->
    </li>

    <li class="nav-item start {{ NavigationHelper::activeClass('dashboard')?'active':'' }}">
        <a href="{{ route('backend.dashboard') }}" class="nav-link ">
            <i class="icon-home"></i>
            <span class="title">Dashboard</span>
            <span class="arrow"></span>
        </a>
    </li>

    <li class="nav-item {{ NavigationHelper::activeClass('catalog')?'active open':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-book"></i>
            <span class="title">Catalog</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/product/index')?'active':'' }}">
                <a href="{{ route('backend.catalog.product.index') }}" class="nav-link"> Product </a>
            </li>
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/category')?'active':'' }}">
                <a href="{{ route('backend.catalog.category.index') }}" class="nav-link"> Category </a>
            </li>
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/product-attribute')?'active':'' }}">
                <a href="{{ route('backend.catalog.product_attribute.index') }}" class="nav-link"> Product Attribute </a>
            </li>
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/product-feature')?'active':'' }}">
                <a href="{{ route('backend.catalog.product_feature.index') }}" class="nav-link"> Product Feature </a>
            </li>
            <li class="nav-item {{ NavigationHelper::activeClass('catalog/manufacturer')?'active':'' }}">
                <a href="{{ route('backend.catalog.manufacturer.index') }}" class="nav-link"> Manufacturer </a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ NavigationHelper::activeClass('sales')?'active open':'' }}">
        <a href="javascript:;" class="nav-link">
            <i class="fa fa-smile-o"></i>
            <span class="title">Sales</span>
        </a>

        <ul class="sub-menu">
            <li class="nav-item {{ NavigationHelper::activeClass('sales/order/')?'active':'' }}">
                <a href="{{ route('backend.sales.order.index') }}" class="nav-link"> Order </a>
            </li>

            <li class="nav-item {{ NavigationHelper::activeClass('sales/order-limit')?'active':'' }}">
                <a href="javascript:;" class="nav-link">
                    <span class="title">Order Limit</span>
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
        </ul>
    </li>

    <li class="nav-item {{ NavigationHelper::activeClass('price-rule')?'active open':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-tags"></i>
            <span class="title">Price Rule</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            <li class="nav-item {{ NavigationHelper::activeClass('price-rule/cart')?'active':'' }}">
                <a href="{{ route('backend.price_rule.cart.index') }}" class="nav-link"> Cart Price Rules </a>
            </li>
            <li class="nav-item {{ NavigationHelper::activeClass('price-rule/product')?'active':'' }}">
                <a href="{{ route('backend.price_rule.product.index') }}" class="nav-link"> Product Price Rules </a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ NavigationHelper::activeClass('customer')?'active open':'' }}">
        <a href="{{ route('backend.customer.index') }}" class="nav-link">
            <i class="fa fa-users"></i>
            <span class="title">Customer</span>
        </a>
    </li>

    <li class="nav-item {{ NavigationHelper::activeClass('report')?'active open':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-bar-chart"></i>
            <span class="title">Report</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            <li class="nav-item {{ NavigationHelper::activeClass('report/sales')?'active':'' }}">
                <a href="{{ route('backend.report.sales_year') }}" class="nav-link"> Sales </a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ NavigationHelper::activeClass('configuration')?'active open':'' }}">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-wrench"></i>
            <span class="title">Configuration</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            <li class="nav-item {{ NavigationHelper::activeClass('configuration/tax')?'active open':'' }}">
                <a href="{{ route('backend.tax.index') }}" class="nav-link">Tax</a>
            </li>

            <li class="nav-item {{ NavigationHelper::activeClass('configuration/payment-method')?'active':'' }}">
                <a href="{{ route('backend.payment_method.index') }}" class="nav-link"> Payment Method </a>
            </li>

            <li class="nav-item {{ NavigationHelper::activeClass('configuration/shipping-method')?'active':'' }}">
                <a href="{{ route('backend.shipping_method.index') }}" class="nav-link"> Shipping Method </a>
            </li>

            <li class="nav-item {{ NavigationHelper::activeClass('configuration/store')?'active open':'' }}">
                <a href="{{ route('backend.store.index') }}" class="nav-link">Store</a>
            </li>

            <li class="nav-item {{ NavigationHelper::activeClass('configuration/address/country')?'active open':'' }}">
                <a href="{{ route('backend.configuration.address.index', ['type' => 'country']) }}" class="nav-link">Address</a>
            </li>
        </ul>
    </li>

    <li class="nav-item {{ NavigationHelper::activeClass('warehouse')?'active open':'' }}">
        <a href="{{ route('backend.warehouse.index') }}" class="nav-link">
            <i class="fa fa-archive"></i>
            <span class="title">Warehouse</span>
        </a>
    </li>
</ul>