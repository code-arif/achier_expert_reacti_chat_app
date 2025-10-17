<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('dashboard') }}">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img desktop-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img toggle-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo1"
                    alt="logo">
            </a>
        </div>
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>
            <ul class="side-menu mt-2">
                <li>
                    <h3>Menu</h3>
                </li>
                {{-- dashboard --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link' : '' }}"
                        href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                            enable-background="new 0 0 24 24" viewBox="0 0 24 24">
                            <path
                                d="M19.9794922,7.9521484l-6-5.2666016c-1.1339111-0.9902344-2.8250732-0.9902344-3.9589844,0l-6,5.2666016C3.3717041,8.5219116,2.9998169,9.3435669,3,10.2069702V19c0.0018311,1.6561279,1.3438721,2.9981689,3,3h2.5h7c0.0001831,0,0.0003662,0,0.0006104,0H18c1.6561279-0.0018311,2.9981689-1.3438721,3-3v-8.7930298C21.0001831,9.3435669,20.6282959,8.5219116,19.9794922,7.9521484z M15,21H9v-6c0.0014038-1.1040039,0.8959961-1.9985962,2-2h2c1.1040039,0.0014038,1.9985962,0.8959961,2,2V21z M20,19c-0.0014038,1.1040039-0.8959961,1.9985962-2,2h-2v-6c-0.0018311-1.6561279-1.3438721-2.9981689-3-3h-2c-1.6561279,0.0018311-2.9981689,1.3438721-3,3v6H6c-1.1040039-0.0014038-1.9985962-0.8959961-2-2v-8.7930298C3.9997559,9.6313477,4.2478027,9.0836182,4.6806641,8.7041016l6-5.2666016C11.0455933,3.1174927,11.5146484,2.9414673,12,2.9423828c0.4853516-0.0009155,0.9544067,0.1751099,1.3193359,0.4951172l6,5.2665405C19.7521973,9.0835571,20.0002441,9.6313477,20,10.2069702V19z" />
                        </svg>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                {{-- chat --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('chat') ? 'has-link' : '' }}"
                        href="{{ route('admin.chat.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24"
                            fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.59 13.41l-7.59 7.59c-.36.36-.86.59-1.41.59s-1.05-.23-1.41-.59l-7.59-7.59c-.36-.36-.59-.86-.59-1.41s.23-1.05.59-1.41l7.59-7.59c.36-.36.86-.59 1.41-.59s1.05.23 1.41.59l7.59 7.59c.36.36.59.86.59 1.41s-.23 1.05-.59 1.41zM12 4.41L4.41 12 12 19.59 19.59 12 12 4.41z" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>
                        <span class="side-menu__label">Chat</span>
                    </a>
                </li>

                <h3>User and Chat Manage</h3>

                {{-- user, event, venue, promoter list --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('user') ? 'has-link' : '' }}"
                        href="{{ route('admin.user.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path
                                d="M12 2a5 5 0 1 1-5 5 5 5 0 0 1 5-5zm0 14c-4.4 0-8 2.2-8 5a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1c0-2.8-3.6-5-8-5z" />
                        </svg>
                        <span class="side-menu__label">Users List</span>
                    </a>
                </li>

                {{-- venues list --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('user') ? 'has-link' : '' }}"
                        href="{{ route('admin.venue.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path d="M4 22V10l8-6 8 6v12h-5v-6h-6v6H4zM12 4.8 6 9v11h2v-6h8v6h2V9l-6-4.2z" />
                        </svg>
                        <span class="side-menu__label">Venues</span>
                    </a>
                </li>

                {{-- Events lists --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('user') ? 'has-link' : '' }}"
                        href="{{ route('admin.event.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2
                            2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0
                            16H5V10h14v10zm0-12H5V6h14v2z" />
                        </svg>
                        <span class="side-menu__label">Events</span>
                    </a>
                </li>

                {{-- posts list --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('user') ? 'has-link' : '' }}"
                        href="{{ route('admin.post.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path
                                d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm1 3v2h14V7H5zm0 4v2h10v-2H5zm0 4v2h8v-2H5z" />
                        </svg>
                        <span class="side-menu__label">Posts</span>
                    </a>
                </li>

                <h3>CMS</h3>

                {{-- cms-management --}}
                {{-- home page --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path d=" M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                        </svg>
                        <span class="side-menu__label">Home Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.home.hero') }}" class="slide-item">Hero Section</a>
                        </li>
                        <li><a href="{{ route('cms.home.event') }}" class="slide-item">Event Section</a>
                        </li>
                        <li><a href="{{ route('cms.home.venues') }}" class="slide-item">Venue Section</a>
                        </li>
                        <li><a href="{{ route('cms.home.app.download') }}" class="slide-item">App Download
                                Section</a>
                        </li>
                    </ul>
                </li>

                {{-- event page --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path
                                d="M7 2v2H5a2 2 0 0 0-2 2v2h18V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm13 8H4v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10zm-2 4h-3v3h3v-3z" />
                        </svg>
                        <span class="side-menu__label">Event Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.event.hero') }}" class="slide-item">Event Hero Section</a>
                        </li>
                        <li><a href="{{ route('cms.event.upcoming') }}" class="slide-item">Upcoming Event</a>
                        </li>
                        <li><a href="{{ route('cms.event.details.hero') }}" class="slide-item">Event Details Hero
                                Section</a>
                        </li>
                    </ul>
                </li>


                {{-- features page --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path
                                d="M4 6h2v2H4V6zm0 5h2v2H4v-2zm0 5h2v2H4v-2zm4-10h12v2H8V6zm0 5h12v2H8v-2zm0 5h12v2H8v-2z" />
                        </svg>
                        <span class="side-menu__label">Features Page</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.feature.hero') }}" class="slide-item">Hero Section</a>
                        </li>
                        <li><a href="{{ route('cms.feature.items.index') }}" class="slide-item">Feature Item</a>
                        </li>
                    </ul>
                </li>

                {{-- newsletter --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('faq') ? 'has-link' : '' }}"
                        href="{{ route('cms.newsletter.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path
                                d="M20 4H4a2 2 0 0 0-2 2v1.8l10 6.25L22 7.8V6a2 2 0 0 0-2-2zm0 4.25-8 5-8-5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.25z" />
                        </svg>
                        <span class="side-menu__label">Newsletter</span>
                    </a>
                </li>

                {{-- Setttings --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" />
                        </svg>
                        <span class="side-menu__label">Settings</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('setting.general.index') }}" class="slide-item">Privacy Policy</a>
                        </li>
                        <li><a href="{{ route('setting.general.index') }}" class="slide-item">Terms or Services</a>
                        </li>
                        <li><a href="{{ route('setting.general.index') }}" class="slide-item">General Settings</a>
                        </li>
                        <li><a href="{{ route('setting.profile.index') }}" class="slide-item">Profile Settings</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->
