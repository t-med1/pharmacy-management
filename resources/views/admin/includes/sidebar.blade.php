<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>

                <li class="menu-title"><span>Main Menu</span></li>

                <li class="{{ route_is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fe fe-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @can('view-category')
                <li class="{{ route_is('categories.*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}">
                        <i class="fe fe-layout"></i>
                        <span>Categories</span>
                    </a>
                </li>
                @endcan

                <li class="menu-title"><span>Inventory</span></li>

                @can('view-purchase')
                <li class="submenu {{ route_is('purchases.*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fas fa-boxes"></i>
                        <span>Purchases</span>
                        <span class="fas fa-chevron-down"></span>
                    </a>
                    <ul style="display: none;">
                        <li>
                            <a class="{{ route_is('purchases.index') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                                All Purchases
                            </a>
                        </li>
                        @can('create-purchase')
                        <li>
                            <a class="{{ route_is('purchases.create') ? 'active' : '' }}" href="{{ route('purchases.create') }}">
                                Add Purchase
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('view-products')
                <li class="submenu {{ route_is('products.*') || route_is('outstock') || route_is('expired') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fas fa-pills"></i>
                        <span>Products</span>
                        <span class="fas fa-chevron-down"></span>
                    </a>
                    <ul style="display: none;">
                        <li>
                            <a class="{{ route_is('products.index') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                All Products
                            </a>
                        </li>
                        @can('create-product')
                        <li>
                            <a class="{{ route_is('products.create') ? 'active' : '' }}" href="{{ route('products.create') }}">
                                Add Product
                            </a>
                        </li>
                        @endcan
                        @can('view-outstock-products')
                        <li>
                            <a class="{{ route_is('outstock') ? 'active' : '' }}" href="{{ route('outstock') }}">
                                <span class="text-warning">Out of Stock</span>
                            </a>
                        </li>
                        @endcan
                        @can('view-expired-products')
                        <li>
                            <a class="{{ route_is('expired') ? 'active' : '' }}" href="{{ route('expired') }}">
                                <span class="text-danger">Expired</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                <li class="menu-title"><span>Sales</span></li>

                @can('view-sales')
                <li class="submenu {{ route_is('sales.*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fas fa-cash-register"></i>
                        <span>Sales</span>
                        <span class="fas fa-chevron-down"></span>
                    </a>
                    <ul style="display: none;">
                        <li>
                            <a class="{{ route_is('sales.index') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                                All Sales
                            </a>
                        </li>
                        @can('create-sale')
                        <li>
                            <a class="{{ route_is('sales.create') ? 'active' : '' }}" href="{{ route('sales.create') }}">
                                New Sale
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('view-supplier')
                <li class="submenu {{ route_is('suppliers.*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fas fa-truck"></i>
                        <span>Suppliers</span>
                        <span class="fas fa-chevron-down"></span>
                    </a>
                    <ul style="display: none;">
                        <li>
                            <a class="{{ route_is('suppliers.index') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                                All Suppliers
                            </a>
                        </li>
                        @can('create-supplier')
                        <li>
                            <a class="{{ route_is('suppliers.create') ? 'active' : '' }}" href="{{ route('suppliers.create') }}">
                                Add Supplier
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                <li class="menu-title"><span>Reports &amp; Logs</span></li>

                @can('view-reports')
                <li class="submenu {{ route_is('sales.report') || route_is('purchases.report') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                        <span class="fas fa-chevron-down"></span>
                    </a>
                    <ul style="display: none;">
                        <li>
                            <a class="{{ route_is('sales.report') ? 'active' : '' }}" href="{{ route('sales.report') }}">
                                Sales Report
                            </a>
                        </li>
                        <li>
                            <a class="{{ route_is('purchases.report') ? 'active' : '' }}" href="{{ route('purchases.report') }}">
                                Purchase Report
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                <li class="{{ route_is('activity.index') ? 'active' : '' }}">
                    <a href="{{ route('activity.index') }}">
                        <i class="fas fa-history"></i>
                        <span>Activity Log</span>
                    </a>
                </li>

                <li class="menu-title"><span>Administration</span></li>

                @can('view-access-control')
                <li class="submenu {{ route_is('permissions.*') || route_is('roles.*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fe fe-lock"></i>
                        <span>Access Control</span>
                        <span class="fas fa-chevron-down"></span>
                    </a>
                    <ul style="display: none;">
                        @can('view-permission')
                        <li>
                            <a class="{{ route_is('permissions.index') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                                Permissions
                            </a>
                        </li>
                        @endcan
                        @can('view-role')
                        <li>
                            <a class="{{ route_is('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                Roles
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('view-users')
                <li class="{{ route_is('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <i class="fe fe-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                @endcan

                <li class="{{ route_is('profile') ? 'active' : '' }}">
                    <a href="{{ route('profile') }}">
                        <i class="fe fe-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>

                <li class="{{ route_is('backup.index') ? 'active' : '' }}">
                    <a href="{{ route('backup.index') }}">
                        <i class="fas fa-database"></i>
                        <span>Backups</span>
                    </a>
                </li>

                @can('view-settings')
                <li class="{{ route_is('settings') ? 'active' : '' }}">
                    <a href="{{ route('settings') }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                @endcan

            </ul>
        </div>
    </div>
</div>
