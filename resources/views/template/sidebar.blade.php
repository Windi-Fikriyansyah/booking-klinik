<ul class="menu-inner py-1">
    <!-- Dashboard -->
    <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div data-i18n="Dashboard">Dashboard</div>
        </a>
    </li>

    <!-- Booking -->
    <li class="menu-item {{ request()->is('booking*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-calendar-event"></i>
            <div data-i18n="Booking">Booking</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('booking/calendar') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Calendar">Calendar</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('booking/list') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="List Booking">List Booking</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('booking/create') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Create Booking">Create Booking</div>
                </a>
            </li>
        </ul>
    </li>

    <!-- Customers -->
    

    <!-- Treatment -->
    <li class="menu-item {{ request()->is('treatment*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-first-aid"></i>
            <div data-i18n="Treatment">Treatment</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('treatment.categories.*') ? 'active' : '' }}">
                <a href="{{ route('treatment.categories.index') }}" class="menu-link">
                    <div data-i18n="Categories">Categories</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('treatment.list.*') ? 'active' : '' }}">
                <a href="{{ route('treatment.list.index') }}" class="menu-link">
                    <div data-i18n="Treatments">Treatments</div>
                </a>
            </li>
        </ul>
    </li>

    <!-- Staff -->
    <li class="menu-item {{ request()->is('staff*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-group"></i>
            <div data-i18n="Staff">Staff</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('staff/list') ? 'active' : '' }}">
                <a href="{{ route('staff.list.index') }}" class="menu-link">
                    <div data-i18n="Staff">Staff</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('staff/schedule') ? 'active' : '' }}">
                <a href="{{ route('staff.schedule.index') }}" class="menu-link">
                    <div data-i18n="Schedule">Schedule</div>
                </a>
            </li>
        </ul>
    </li>
    <!-- Customers -->
    <li class="menu-item {{ request()->is('customer*') ? 'active' : '' }}">
        <a href="{{ route('customer.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-group"></i>
            <div data-i18n="Customers">Customers</div>
        </a>
    </li>

    <!-- Calendar -->
    <li class="menu-item {{ request()->is('calendar*') ? 'active' : '' }}">
        <a href="{{ route('calendar.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-calendar"></i>
            <div data-i18n="Calendar">Calendar</div>
        </a>
    </li>

    <!-- Transactions -->
    <li class="menu-item {{ request()->is('booking*') || request()->is('transactions*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-credit-card"></i>
            <div data-i18n="Transactions">Transactions</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('booking*') ? 'active' : '' }}">
                <a href="{{ route('booking.index') }}" class="menu-link">
                    <div data-i18n="Bookings">Bookings</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('transactions/payments') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Payments">Payments</div>
                </a>
            </li>
        </ul>
    </li>

    <!-- Marketing -->
    <li class="menu-item {{ request()->is('marketing*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-bullseye"></i>
            <div data-i18n="Marketing">Marketing</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('marketing/promo') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Promo">Promo</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('marketing/membership') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Membership">Membership</div>
                </a>
            </li>
        </ul>
    </li>

    <!-- Reports -->
    <li class="menu-item {{ request()->is('reports*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-chart"></i>
            <div data-i18n="Reports">Reports</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('reports/revenue') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Revenue">Revenue</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('reports/booking') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Booking">Booking</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('reports/customer') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Customer">Customer</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('reports/treatment') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Treatment">Treatment</div>
                </a>
            </li>
        </ul>
    </li>

    <!-- Website -->
    <li class="menu-item {{ request()->is('website*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-globe"></i>
            <div data-i18n="Website">Website</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('website/appearance') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Appearance">Appearance</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('website/pages') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Pages">Pages</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('website/domain') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Domain">Domain</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('website/preview') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Preview">Preview</div>
                </a>
            </li>
        </ul>
    </li>

    <!-- WhatsApp -->
    <li class="menu-item {{ request()->is('whatsapp*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bxl-whatsapp"></i>
            <div data-i18n="WhatsApp">WhatsApp</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('whatsapp/connection') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Connection">Connection</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('whatsapp/templates') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Templates">Templates</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('whatsapp/logs') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Logs">Logs</div>
                </a>
            </li>
        </ul>
    </li>

    <!-- Settings -->
    <li class="menu-item {{ request()->is('settings*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-cog"></i>
            <div data-i18n="Settings">Settings</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('settings/clinic') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Clinic">Clinic</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('settings/operating-hours') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Operating Hours">Operating Hours</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('settings/users') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Users">Users</div>
                </a>
            </li>
            <li class="menu-item {{ request()->is('settings/subscription') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div data-i18n="Subscription">Subscription</div>
                </a>
            </li>
        </ul>
    </li>
</ul>