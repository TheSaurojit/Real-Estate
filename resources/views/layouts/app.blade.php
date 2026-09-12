<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ERP Studio' }} - {{ $currentCompany->name ?? 'Real Estate ERP' }}</title>
    
    <!-- Tailwind CSS (Play CDN for instant styling + Alpine.js) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-sky-500 selection:text-white" x-data="{ sidebarOpen: false }">

    <!-- Top Navigation Header -->
    <header class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white shadow-md sticky top-0 z-40 border-b border-sky-800/40">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Branding & Context Toggle -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 focus:outline-none">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-500/20 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-building text-white text-xl"></i>
                        </div>
                        <div>
                            <span class="text-base font-bold tracking-tight text-white block leading-tight">
                                Real Estate ERP Studio
                            </span>
                            <span class="text-xs text-sky-400 font-medium tracking-wide">
                                Property & Accounts ERP
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Center: Project Context Switcher -->
                <div class="hidden md:flex items-center space-x-3">
                    <form action="{{ route('switch.context') }}" method="POST" class="flex items-center">
                        @csrf
                        <div class="relative flex items-center">
                            <div class="absolute left-3 pointer-events-none text-sky-400 text-sm">
                                <i class="fa-solid {{ isset($currentProject) ? 'fa-city' : 'fa-gauge-high' }}"></i>
                            </div>
                            <select name="target" onchange="this.form.submit()" class="pl-9 pr-8 py-1.5 text-xs font-semibold rounded-lg bg-slate-800/90 text-sky-100 border border-sky-700/60 hover:border-sky-500 focus:ring-2 focus:ring-sky-400 focus:outline-none transition cursor-pointer shadow-inner">
                                @if (auth()->user()->role === "super_admin" || auth()->user()->role === "admin" )
                                    
                                <option value="admin" {{ !isset($currentProject) ? 'selected' : '' }}>
                                    ⚙️ Admin Panel (Global Settings)
                                </option>
                                @endif
                                <optgroup label="── Active Projects ──">
                                    @foreach($allProjects ?? [] as $proj)
                                        <option value="{{ $proj->id }}" {{ (isset($currentProject) && $currentProject->id === $proj->id) ? 'selected' : '' }}>
                                            🏢 {{ $proj->name }} ({{ $proj->project_code }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </form>

                    @if(isset($currentProject))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                            Active: {{ $currentProject->nick_name }}
                        </span>
                    @endif
                </div>

                <!-- Right: User info & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-white leading-none">
                            {{ auth()->user()->name ?? 'Administrator' }}
                        </div>
                        <div class="text-xs text-sky-300 mt-0.5">
                            {{ auth()->user()->designation ?? (auth()->user()->user_code ?? 'Admin') }}
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 rounded-lg text-slate-300 hover:text-rose-400 hover:bg-slate-800 transition flex items-center space-x-1.5 text-xs font-medium border border-transparent hover:border-slate-700">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col border-r border-slate-800 transition-all duration-300 z-30"
               :class="{ 'fixed inset-y-0 left-0 pt-16': sidebarOpen, 'hidden lg:flex': !sidebarOpen }">
            
            <div class="p-4 flex-1 overflow-y-auto space-y-6 text-sm">
                
                @if(isset($currentProject))
                    <!-- Current Project Context Menu -->
                    <div>
                        <div class="px-3 text-xs font-bold uppercase tracking-wider text-sky-400 mb-2 flex items-center justify-between">
                            <span>Project Workspace</span>
                            <span class="text-[10px] bg-sky-950 px-1.5 py-0.5 rounded border border-sky-800 text-sky-300">
                                {{ $currentProject->project_code }}
                            </span>
                        </div>
                        <nav class="space-y-1">
                            <a href="{{ route('project.dashboard', $currentProject->id) }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('project.dashboard') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-house w-6 text-sky-400"></i>
                                <span>Project Home</span>
                            </a>
                            <div class="px-3 pt-2 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                Project Modules
                            </div>
                            @if(auth()->user()->hasPermission('view_bookings'))
                                <a href="{{ route('project.bookings.index', $currentProject->id) }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('project.bookings.*') ? 'bg-sky-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-file-signature w-6 text-sky-400"></i>
                                    <span>Bookings Master</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('view_transactions'))
                                <a href="{{ route('project.transactions.index', $currentProject->id) }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('project.transactions.*') ? 'bg-sky-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-money-bill-transfer w-6 text-emerald-400"></i>
                                    <span>Transactions Ledger</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('manage_expenses'))
                                <a href="{{ route('project.expenses.index', $currentProject->id) }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('project.expenses.*') ? 'bg-sky-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-cart-flatbed w-6 text-cyan-400"></i>
                                    <span>Site Expenses</span>
                                </a>
                                <a href="{{ route('project.stock-transfers.index', $currentProject->id) }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('project.stock-transfers.*') ? 'bg-sky-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-truck-ramp-box w-6 text-teal-400"></i>
                                    <span>Stock Transfers</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('view_booking_reports') || auth()->user()->hasPermission('view_financial_reports'))
                                <a href="{{ route('project.reports.index', $currentProject->id) }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('project.reports.*') ? 'bg-sky-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-chart-pie w-6 text-violet-400"></i>
                                    <span>Executive Reports</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('print_documents'))
                                <a href="{{ route('project.documents.index', $currentProject->id) }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('project.documents.*') ? 'bg-sky-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-print w-6 text-rose-400"></i>
                                    <span>Print Documents</span>
                                </a>
                            @endif
                        </nav>
                    </div>
                @endif

                <!-- Admin Settings Menu -->
                @php
                    $hasAdminMenuAccess = auth()->user()->isSuperAdmin() ||
                        auth()->user()->hasPermission('manage_companies') ||
                        auth()->user()->hasPermission('manage_roles') ||
                        auth()->user()->hasPermission('manage_autonumber') ||
                        auth()->user()->hasPermission('view_projects') ||
                        auth()->user()->hasPermission('view_bank_accounts') ||
                        auth()->user()->hasPermission('view_users');
                @endphp

                @if($hasAdminMenuAccess)
                    <div>
                        <div class="px-3 text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                            <span>Admin & Master Settings</span>
                            <i class="fa-solid fa-shield-halved text-slate-500"></i>
                        </div>
                        <nav class="space-y-1">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-gauge-high w-6 text-sky-400"></i>
                                <span>Admin Overview</span>
                            </a>

                            @if(auth()->user()->hasPermission('manage_companies'))
                                <a href="{{ route('admin.companies.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.companies.*') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-building w-6 text-rose-400"></i>
                                    <span>Companies Master</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('view_projects'))
                                <a href="{{ route('admin.projects.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.projects.*') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-city w-6 text-amber-400"></i>
                                    <span>Projects Master</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('view_bank_accounts'))
                                <a href="{{ route('admin.bank-accounts.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.bank-accounts.*') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-building-columns w-6 text-emerald-400"></i>
                                    <span>Bank Accounts</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('view_users'))
                                <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-users w-6 text-violet-400"></i>
                                    <span>User Management</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('manage_roles'))
                                <a href="{{ route('admin.roles.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.roles.*') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-shield-halved w-6 text-indigo-400"></i>
                                    <span>Roles & Permissions</span>
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('manage_autonumber'))
                                <a href="{{ route('admin.autonumber.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('admin.autonumber.*') ? 'bg-sky-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fa-solid fa-hashtag w-6 text-cyan-400"></i>
                                    <span>Auto-Numbering</span>
                                </a>
                            @endif
                        </nav>
                    </div>
                @endif

            </div>

            <!-- Footer in sidebar -->
            <div class="p-3 bg-slate-950 border-t border-slate-800/80 text-xs text-slate-500 flex items-center justify-between">
                <span>Real Estate ERP v1.0</span>
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto bg-slate-100 p-4 sm:p-6 lg:p-8">
            
            <!-- Global Flash Messages -->
            @if(session('warning'))
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start space-x-3 shadow-sm animate-fade-in" x-data="{ show: true }" x-show="show">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5"></i>
                    <div class="flex-1 font-medium text-sm">
                        {{ session('warning') }}
                    </div>
                    <button @click="show = false" class="text-amber-500 hover:text-amber-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-3 shadow-sm animate-fade-in" x-data="{ show: true }" x-show="show">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-lg mt-0.5"></i>
                    <div class="flex-1 font-medium text-sm">
                        {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start space-x-3 shadow-sm" x-data="{ show: true }" x-show="show">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg mt-0.5"></i>
                    <div class="flex-1 text-sm">
                        <div class="font-semibold mb-1">Please correct the following errors:</div>
                        <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                            @if(session('error'))
                                <li>{{ session('error') }}</li>
                            @endif
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            <!-- Modal Alerts -->
            @if(session('created_project'))
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-fade-in" x-data="{ open: true }" x-show="open">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center border border-slate-200" @click.away="open = false">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-1">Thank You!</h3>
                        <p class="text-sm text-slate-600 mb-4">Project Created Successfully!</p>
                        <div class="bg-slate-100 rounded-xl p-4 mb-5 border border-slate-200">
                            <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-1">Project ID</div>
                            <div class="text-2xl font-black text-sky-700 font-mono tracking-tight">{{ session('created_project')['code'] }}</div>
                            <div class="text-xs text-slate-600 mt-1 font-medium">{{ session('created_project')['name'] }}</div>
                        </div>
                        <button @click="open = false" class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl shadow-md shadow-sky-600/30 transition">
                            Continue to Projects
                        </button>
                    </div>
                </div>
            @endif

            @if(session('created_account'))
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-fade-in" x-data="{ open: true }" x-show="open">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center border border-slate-200" @click.away="open = false">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-1">Thank You!</h3>
                        <p class="text-sm text-slate-600 mb-4">Bank Account Created!</p>
                        <div class="bg-slate-100 rounded-xl p-4 mb-5 border border-slate-200">
                            <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-1">Account ID</div>
                            <div class="text-2xl font-black text-emerald-700 font-mono tracking-tight">{{ session('created_account')['code'] }}</div>
                            <div class="text-xs text-slate-600 mt-1 font-medium">{{ session('created_account')['name'] }}</div>
                        </div>
                        <button @click="open = false" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-md shadow-emerald-600/30 transition">
                            Continue
                        </button>
                    </div>
                </div>
            @endif

            <!-- Yield Page Content -->
            {{ $slot ?? '' }}
            @yield('content')

        </main>
    </div>

</body>
</html>
