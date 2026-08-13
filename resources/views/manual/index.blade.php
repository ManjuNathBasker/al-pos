@extends('layouts.app')

@section('content')
<div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-4 sm:-mt-6" x-data="manualApp()" x-init="init()">
    <style>
        [x-cloak] { display: none !important; }
    </style>
  <!-- Mobile Sidebar Backdrop -->
  <div x-show="sidebarOpen" class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden" @click="sidebarOpen = false" x-transition.opacity x-cloak></div>

  <div class="flex h-[calc(100vh-4rem)] bg-white relative">
    
    <!-- LEFT SIDEBAR -->
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 overflow-y-auto transition-transform duration-300 ease-in-out flex flex-col h-full lg:h-auto lg:block">
      
      <!-- Search Input -->
      <div class="p-4 border-b border-slate-200 sticky top-0 bg-white z-10 relative">
        <div class="relative">
          <svg class="w-5 h-5 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          <input type="text" x-model="searchQuery" @input="handleSearch" placeholder="Search manual..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-[#F5703E] focus:border-[#F5703E] text-sm outline-none">
        </div>
        
        <!-- Search Results Dropdown -->
        <div x-show="searchQuery.length >= 2" class="absolute left-4 right-4 top-16 bg-white border border-slate-200 shadow-lg rounded-lg max-h-64 overflow-y-auto z-20" x-cloak>
          <template x-if="searchResults.length === 0">
            <div class="p-4 text-sm text-slate-500 text-center">No results found</div>
          </template>
          <template x-for="result in searchResults" :key="result.id">
            <a href="#" @click.prevent="goToPage(result.id); searchQuery = ''" class="block p-3 border-b border-slate-100 hover:bg-slate-50 last:border-0">
              <div class="text-xs text-slate-400 mb-1" x-text="result.section"></div>
              <div class="text-sm font-medium text-[#172033]" x-text="result.title"></div>
            </a>
          </template>
        </div>
      </div>

      <!-- Navigation Tree -->
      <nav class="flex-1 p-4 space-y-4">
         <template x-for="(section, index) in sections" :key="index">
            <div class="mb-2">
                <button @click="toggleSection(section.id)" class="w-full flex items-center justify-between text-left focus:outline-none">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="section.name"></span>
                    <svg :class="expandedSections[section.id] ? 'rotate-90' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <div x-show="expandedSections[section.id]" class="mt-2 space-y-1">
                    <template x-for="pageId in section.pages" :key="pageId">
                        <a href="#" @click.prevent="goToPage(pageId)" 
                           :class="currentPage === pageId ? 'bg-[#FFF3EE] text-[#F5703E] border-l-2 border-[#F5703E]' : 'text-slate-600 hover:text-[#172033] hover:bg-slate-50 border-l-2 border-transparent'" 
                           class="block pl-4 py-2 text-sm transition-colors rounded-r-lg">
                            <span x-text="pages[pageId].title"></span>
                        </a>
                    </template>
                </div>
            </div>
         </template>
      </nav>

      <div class="p-4 border-t border-slate-200 mt-auto shrink-0">
        <a href="{{ route('dashboard') }}" class="text-sm text-slate-600 hover:text-[#F5703E] flex items-center gap-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
          &larr; Back to Application
        </a>
      </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col h-full overflow-hidden w-full lg:w-auto">
        
      <!-- Top Bar -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-white shrink-0">
        <div class="flex items-center gap-2 text-sm text-slate-500 overflow-x-auto whitespace-nowrap hide-scrollbar">
          <template x-for="(crumb, index) in currentBreadcrumbs" :key="index">
            <div class="flex items-center gap-2">
              <span class="bg-slate-100 px-2 py-1 rounded" x-text="crumb"></span>
              <span x-show="index < currentBreadcrumbs.length - 1" class="text-slate-400">&rarr;</span>
            </div>
          </template>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg ml-4 shrink-0 focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
      </div>

      <!-- Scrollable Content Area -->
      <div class="flex-1 overflow-y-auto" x-ref="contentArea">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 py-8">
            <div x-html="pages[currentPage].content" class="mb-12 content-html"></div>
            
            <!-- Prev/Next Navigation -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-slate-200">
                <div>
                    <template x-if="pages[currentPage].prev">
                        <a href="#" @click.prevent="goToPage(pages[currentPage].prev)" class="block p-4 rounded-lg border border-slate-200 hover:border-[#F5703E] hover:shadow-sm transition-all group">
                            <div class="text-xs text-slate-400 mb-1 flex items-center gap-1 group-hover:text-[#F5703E]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                Previous
                            </div>
                            <div class="text-sm font-semibold text-slate-700 group-hover:text-slate-900" x-text="pages[pages[currentPage].prev].title"></div>
                        </a>
                    </template>
                </div>
                <div class="text-right">
                    <template x-if="pages[currentPage].next">
                        <a href="#" @click.prevent="goToPage(pages[currentPage].next)" class="block p-4 rounded-lg border border-slate-200 hover:border-[#F5703E] hover:shadow-sm transition-all group flex flex-col items-end">
                            <div class="text-xs text-slate-400 mb-1 flex items-center gap-1 group-hover:text-[#F5703E]">
                                Next
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                            <div class="text-sm font-semibold text-slate-700 group-hover:text-slate-900" x-text="pages[pages[currentPage].next].title"></div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function manualApp() {
    return {
        currentPage: 'getting-started-intro',
        searchQuery: '',
        searchResults: [],
        sidebarOpen: false,
        expandedSections: {},
        
        init() {
            // Expand all sections by default
            this.sections.forEach(s => this.expandedSections[s.id] = true);
            
            // Check hash for direct links
            if (window.location.hash) {
                const hash = window.location.hash.replace('#', '');
                if (this.pages[hash]) {
                    this.currentPage = hash;
                }
            }

            // Watch for hash changes (browser back/forward)
            window.addEventListener('hashchange', () => {
                const hash = window.location.hash.replace('#', '');
                if (this.pages[hash] && this.currentPage !== hash) {
                    this.currentPage = hash;
                    this.scrollToTop();
                }
            });
        },
        
        get currentBreadcrumbs() {
            return this.pages[this.currentPage].breadcrumb;
        },
        
        goToPage(id) {
            this.currentPage = id;
            window.location.hash = id;
            this.sidebarOpen = false;
            this.scrollToTop();
            
            // Ensure the section containing this page is expanded
            const section = this.sections.find(s => s.pages.includes(id));
            if (section) {
                this.expandedSections[section.id] = true;
            }
        },

        scrollToTop() {
            if (this.$refs.contentArea) {
                this.$refs.contentArea.scrollTop = 0;
            }
        },
        
        toggleSection(id) {
            this.expandedSections[id] = !this.expandedSections[id];
        },
        
        handleSearch() {
            const query = this.searchQuery.toLowerCase();
            if (query.length < 2) {
                this.searchResults = [];
                return;
            }
            
            const results = [];
            for (const [id, page] of Object.entries(this.pages)) {
                if (page.searchText.toLowerCase().includes(query) || page.title.toLowerCase().includes(query)) {
                    results.push(page);
                }
            }
            this.searchResults = results;
        },

        sections: [
            { id: 'sec-getting-started', name: 'Getting Started', pages: ['getting-started-intro', 'getting-started-login', 'getting-started-register', 'getting-started-dashboard', 'getting-started-navigation', 'getting-started-company-switching', 'getting-started-roles'] },
            { id: 'sec-pos', name: 'POS Terminal', pages: ['pos-overview', 'pos-shift', 'pos-service-modes', 'pos-browsing', 'pos-cart', 'pos-discounts', 'pos-customer', 'pos-payments', 'pos-card-payments', 'pos-checkout', 'pos-receipt'] },
            { id: 'sec-products', name: 'Products', pages: ['products-list', 'products-add', 'products-edit', 'products-delete'] },
            { id: 'sec-categories', name: 'Categories', pages: ['categories-list', 'categories-add', 'categories-edit'] },
            { id: 'sec-customers', name: 'Customers', pages: ['customers-list', 'customers-detail'] },
            { id: 'sec-orders', name: 'Orders', pages: ['orders-list', 'orders-detail'] },
            { id: 'sec-coupons', name: 'Coupons', pages: ['coupons-list'] },
            { id: 'sec-inventory', name: 'Inventory', pages: ['inventory-items', 'inventory-recipes'] },
            { id: 'sec-restaurant', name: 'Restaurant', pages: ['restaurant-table-map', 'restaurant-table-settings', 'restaurant-kitchen', 'restaurant-waiter', 'restaurant-guest-qr'] },
            { id: 'sec-purchase', name: 'Purchase & Supply', pages: ['suppliers-list', 'purchases-list', 'purchases-create', 'purchases-detail'] },
            { id: 'sec-accounting', name: 'Accounting', pages: ['accounting-accounts', 'accounting-journals', 'accounting-expenses'] },
            { id: 'sec-reports', name: 'Reports', pages: ['reports-sales', 'reports-inventory', 'reports-purchases', 'reports-wallet', 'reports-pnl', 'reports-balance-sheet', 'reports-card-commission', 'reports-card-settlements', 'reports-register-sessions', 'reports-export'] },
            { id: 'sec-settings', name: 'Settings & Administration', pages: ['settings-companies', 'settings-modules', 'settings-card-types', 'settings-delivery-partners', 'settings-users', 'settings-roles', 'settings-profile'] },
            { id: 'sec-needs-attention', name: 'Needs Attention', pages: ['needs-attention'] },
        ],
        
        pages: {
            // SECTION 1: GETTING STARTED
            'getting-started-intro': {
                id: 'getting-started-intro',
                section: 'Getting Started',
                title: 'Introduction & System Overview',
                breadcrumb: ['User Manual', 'Getting Started', 'Introduction'],
                prev: null,
                next: 'getting-started-login',
                searchText: 'Welcome POS system multi-tenant point-of-sale business management sales inventory restaurant purchasing accounting reports companies stores modules table kitchen waiter',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Introduction & System Overview</h1>
                    <p class="text-slate-500 mb-8">Welcome to the POS system.</p>
                    <p class="text-slate-600 leading-relaxed mb-4">This application is a powerful, multi-tenant point-of-sale and business management system. It covers everything from POS sales and inventory management to restaurant operations, purchasing, and comprehensive accounting and reporting.</p>
                    <p class="text-slate-600 leading-relaxed mb-4">A key feature of this system is its support for <strong>multiple companies or stores</strong> within a single account. You can seamlessly switch between your different business locations.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Core Modules</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>POS Terminal:</strong> The main sales interface for processing orders and payments.</li>
                        <li><strong>Inventory Management:</strong> Track raw materials, stock levels, and link them to products via recipes.</li>
                        <li><strong>Restaurant Features:</strong> Includes Table Management, Kitchen Display System (KDS), Waiter Panel, and Guest QR ordering.</li>
                        <li><strong>Accounting:</strong> Full chart of accounts, journal entries, and expense tracking.</li>
                        <li><strong>Reports:</strong> Detailed insights into sales, purchases, profit & loss, and more.</li>
                    </ul>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">Modules like Table Management and Kitchen Display can be toggled on or off per company in the Settings section.</p>
                    </div>
                `
            },
            'getting-started-login': {
                id: 'getting-started-login',
                section: 'Getting Started',
                title: 'Login',
                breadcrumb: ['User Manual', 'Getting Started', 'Login'],
                prev: 'getting-started-intro',
                next: 'getting-started-register',
                searchText: 'login email password remember me forgot password dashboard registration',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Login</h1>
                    <p class="text-slate-500 mb-8">How to log in to the system.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">To access your POS, navigate to the application URL in your web browser.</p>
                    
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Enter your registered <strong>Email</strong> (required).</li>
                        <li>Enter your <strong>Password</strong> (required).</li>
                        <li>Optionally, check <strong>Remember Me</strong> to stay logged in.</li>
                        <li>Click the Login button.</li>
                    </ol>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">If you have forgotten your password, use the "Forgot password" link to reset it. After a successful login, you will be redirected to your Dashboard. If you are a brand new user logging in for the very first time, you may be redirected to Registration.</p>
                `
            },
            'getting-started-register': {
                id: 'getting-started-register',
                section: 'Getting Started',
                title: 'Registration',
                breadcrumb: ['User Manual', 'Getting Started', 'Registration'],
                prev: 'getting-started-login',
                next: 'getting-started-dashboard',
                searchText: 'register setup first account company name owner role cash account',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Registration</h1>
                    <p class="text-slate-500 mb-8">First-time setup for your business.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Creating the first account sets up the foundation for your business in the system. When you register, you are also creating the very first company.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Required Fields</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Name:</strong> Your personal name.</li>
                        <li><strong>Email:</strong> Your login email address.</li>
                        <li><strong>Password & Confirm Password:</strong> Secure password for your account.</li>
                        <li><strong>Company Name:</strong> The name of your business or first store location.</li>
                    </ul>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">The first registered user automatically becomes the Owner. After registration, the system automatically creates a default company, assigns you the Owner role, and sets up a default Cash accounting ledger.</p>
                    </div>
                `
            },
            'getting-started-dashboard': {
                id: 'getting-started-dashboard',
                section: 'Getting Started',
                title: 'Dashboard Overview',
                breadcrumb: ['User Manual', 'Getting Started', 'Dashboard'],
                prev: 'getting-started-register',
                next: 'getting-started-navigation',
                searchText: 'dashboard overview real-time KPI sales today orders monthly revenue chart recent orders quick action',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Dashboard Overview</h1>
                    <p class="text-slate-500 mb-8">Your real-time business command center.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The dashboard provides a real-time overview of your current active company. It is the first screen you see after logging in.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Dashboard Components</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>KPI Cards:</strong> Quick stats showing Sales Today, Orders Today, and Monthly Sales.</li>
                        <li><strong>Revenue Chart:</strong> A visual line/bar chart displaying sales trends over time.</li>
                        <li><strong>Recent Orders Table:</strong> Displays the latest transactions including Order #, Customer, Amount, and Status.</li>
                        <li><strong>Quick Action Buttons:</strong> Fast links to jump straight into POS, Orders, Products, or Reports.</li>
                    </ul>
                    
                    <div class="bg-slate-100 border-2 border-dashed border-slate-300 rounded-lg p-8 text-center mb-6">
                        <p class="text-slate-400 text-sm">📷 Screenshot: The Dashboard interface showing KPI cards and the revenue chart</p>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Clicking on any KPI card will navigate you directly to its respective detailed report or page.</p>
                `
            },
            'getting-started-navigation': {
                id: 'getting-started-navigation',
                section: 'Getting Started',
                title: 'Navigation & Layout',
                breadcrumb: ['User Manual', 'Getting Started', 'Navigation'],
                prev: 'getting-started-dashboard',
                next: 'getting-started-company-switching',
                searchText: 'layout sidebar top header flash messages success error modules',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Navigation & Layout</h1>
                    <p class="text-slate-500 mb-8">Understanding the system interface.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The application uses a standard layout to make navigation intuitive.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Left Sidebar</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Contains the main navigation menu grouped into logical sections:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li>Main (Dashboard, POS, Orders, etc.)</li>
                        <li>Inventory</li>
                        <li>Restaurant</li>
                        <li>Purchase & Supply</li>
                        <li>Accounting</li>
                        <li>Reports</li>
                        <li>System Settings</li>
                    </ul>
                    <p class="text-slate-600 leading-relaxed mb-4">Note: Some sidebar items only appear if their corresponding modules are enabled for the current company or if your user role has permission to view them.</p>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Top Header Bar</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Contains the <strong>Company Switcher</strong> dropdown (left) and the <strong>User Menu</strong> (right) for profile settings and logging out.</p>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">System Messages</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Flash messages appear just below the header. Green banners indicate a successful action, while red banners alert you to errors.</p>
                `
            },
            'getting-started-company-switching': {
                id: 'getting-started-company-switching',
                section: 'Getting Started',
                title: 'Company / Store Switching',
                breadcrumb: ['User Manual', 'Getting Started', 'Company Switching'],
                prev: 'getting-started-navigation',
                next: 'getting-started-roles',
                searchText: 'company store switcher multiple locations active company data',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Company / Store Switching</h1>
                    <p class="text-slate-500 mb-8">Managing multiple business locations.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">If your account has access to multiple companies or stores, you can switch between them seamlessly without logging out.</p>
                    
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Locate the <strong>Company Switcher</strong> dropdown in the top header bar.</li>
                        <li>Click the current company name to open the dropdown.</li>
                        <li>Select the name of the company/store you wish to switch to.</li>
                    </ol>
                    
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-amber-800">⚠️ Warning</p>
                        <p class="text-sm text-amber-700 mt-1">When you switch companies, the page refreshes. The entire system context changes — you will now only see products, orders, customers, and settings belonging to the selected company.</p>
                    </div>
                `
            },
            'getting-started-roles': {
                id: 'getting-started-roles',
                section: 'Getting Started',
                title: 'Understanding Roles',
                breadcrumb: ['User Manual', 'Getting Started', 'Roles'],
                prev: 'getting-started-company-switching',
                next: 'pos-overview',
                searchText: 'roles permissions access control admin owner staff',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Understanding Roles</h1>
                    <p class="text-slate-500 mb-8">Role-based access control (RBAC).</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The system restricts what users can see and do based on their assigned roles. Roles determine which sidebar items are visible and which actions are permitted.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Default System Roles</h2>
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="text-left p-3 font-semibold text-slate-700">Role</th>
                                    <th class="text-left p-3 font-semibold text-slate-700">Access Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-slate-100">
                                    <td class="p-3 font-medium text-slate-800">Admin</td>
                                    <td class="p-3 text-slate-600">Full system-wide access. Bypasses all permission checks. Can manage other companies.</td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="p-3 font-medium text-slate-800">Owner</td>
                                    <td class="p-3 text-slate-600">Full access to their assigned company. Bypasses permission checks for their company.</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-medium text-slate-800">Staff</td>
                                    <td class="p-3 text-slate-600">Limited access. Typically can use the POS, and view products/orders/customers based on explicit permissions.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">You can customize roles and granular permissions in <strong>Settings → Roles</strong>.</p>
                `
            },

            // SECTION 2: POS TERMINAL
            'pos-overview': {
                id: 'pos-overview',
                section: 'POS Terminal',
                title: 'POS Overview & Layout',
                breadcrumb: ['User Manual', 'POS Terminal', 'Overview'],
                prev: 'getting-started-roles',
                next: 'pos-shift',
                searchText: 'pos terminal layout panels grid list density',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">POS Overview & Layout</h1>
                    <p class="text-slate-500 mb-8">The main interface for processing sales.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">POS Terminal</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The POS Terminal uses a full-screen layout designed for speed and efficiency at the checkout counter.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Layout Structure</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Left Sidebar:</strong> Select service modes (Counter, Dine-In, etc.) and filter by product categories.</li>
                        <li><strong>Center Panel:</strong> The product catalog. Features a search bar and customizable display (grid or list view).</li>
                        <li><strong>Right Panel:</strong> The shopping cart, discounts, customer linking, and checkout totals.</li>
                    </ul>
                    
                    <div class="bg-slate-50 border border-slate-200 p-4 mb-6 rounded-lg">
                        <p class="text-sm text-slate-600">You can toggle between a Grid view and List view for products. In grid view, you can adjust the column density (3, 4, or 5 columns) to fit your screen size.</p>
                    </div>
                `
            },
            'pos-shift': {
                id: 'pos-shift',
                section: 'POS Terminal',
                title: 'Opening & Closing Shifts',
                breadcrumb: ['User Manual', 'POS Terminal', 'Shift Management'],
                prev: 'pos-overview',
                next: 'pos-service-modes',
                searchText: 'register session shift open close opening amount closing cash drawer discrepancy',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Opening & Closing Shifts</h1>
                    <p class="text-slate-500 mb-8">Managing your cash register sessions.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Before you can start processing sales, you must have an active register session (shift) open.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Opening a Shift</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">When you open the POS without an active shift, a modal appears automatically.</p>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Enter the <strong>Opening Amount</strong>. This is the physical cash currently in your till drawer.</li>
                        <li>Click to open the register.</li>
                    </ol>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Closing a Shift</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">At the end of the day or shift change:</p>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Click <strong>Close Register</strong> in the POS left sidebar.</li>
                        <li>Enter the actual <strong>Closing Amount</strong> (the physical cash currently in the till).</li>
                        <li>Add any optional notes.</li>
                        <li>Confirm closure.</li>
                    </ol>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">The system calculates the expected amount based on your opening balance plus cash sales. It will record any discrepancy between expected and actual amounts for auditing in Reports → Register Sessions.</p>
                    </div>
                `
            },
            'pos-service-modes': {
                id: 'pos-service-modes',
                section: 'POS Terminal',
                title: 'Service Modes',
                breadcrumb: ['User Manual', 'POS Terminal', 'Service Modes'],
                prev: 'pos-shift',
                next: 'pos-browsing',
                searchText: 'service modes counter dine-in table takeaway delivery address kot',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Service Modes</h1>
                    <p class="text-slate-500 mb-8">Choosing how to serve the customer.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">There are up to 4 service modes available in the left sidebar of the POS, depending on your active modules.</p>
                    
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Counter (Default):</strong> Standard retail sale. Customer orders and pays immediately at the counter.</li>
                        <li><strong>Dine-In:</strong> For restaurants. Requires selecting a table. Completing an order generates a Kitchen Order Ticket (KOT).</li>
                        <li><strong>Takeaway:</strong> For packaged orders being picked up.</li>
                        <li><strong>Delivery:</strong> Requires a customer address. Used to track delivery orders.</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Selecting a mode alters the checkout flow slightly. For example, selecting Dine-In will prompt a table selection button at the top of the cart panel.</p>
                `
            },
            'pos-browsing': {
                id: 'pos-browsing',
                section: 'POS Terminal',
                title: 'Browsing & Searching Products',
                breadcrumb: ['User Manual', 'POS Terminal', 'Browsing Products'],
                prev: 'pos-service-modes',
                next: 'pos-cart',
                searchText: 'search products category filter grid list all items sku barcode',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Browsing & Searching Products</h1>
                    <p class="text-slate-500 mb-8">Finding items to add to the sale.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Products are displayed in the center panel.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Finding Products</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Search Bar:</strong> Use the search input at the top to find products by Name, SKU, or Barcode.</li>
                        <li><strong>Category Tabs:</strong> Click categories in the left sidebar to filter the product list. Click "All Items" to view the entire catalog.</li>
                    </ul>
                    
                    <div class="bg-slate-100 border-2 border-dashed border-slate-300 rounded-lg p-8 text-center mb-6">
                        <p class="text-slate-400 text-sm">📷 Screenshot: Product grid showing items with images and prices</p>
                    </div>
                `
            },
            'pos-cart': {
                id: 'pos-cart',
                section: 'POS Terminal',
                title: 'Managing the Cart',
                breadcrumb: ['User Manual', 'POS Terminal', 'Cart'],
                prev: 'pos-browsing',
                next: 'pos-discounts',
                searchText: 'cart right panel quantity subtotal tax grand total clear cart',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Managing the Cart</h1>
                    <p class="text-slate-500 mb-8">Building the customer's order.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Clicking a product in the center panel adds it to the cart located on the right side.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Cart Actions</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Adjust Quantity:</strong> Use the <code>+</code> and <code>-</code> buttons next to an item in the cart to change its quantity.</li>
                        <li><strong>Remove Item:</strong> Click the <code>X</code> button to remove a specific item.</li>
                        <li><strong>Clear Cart:</strong> Use the "Clear Cart" button at the top to empty the entire cart at once.</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The cart automatically calculates the subtotal, applicable taxes (e.g., 8%), and the grand total.</p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">The cart state persists in your session. You will not lose your cart items if you accidentally refresh the page or navigate away and return later.</p>
                    </div>
                `
            },
            'pos-discounts': {
                id: 'pos-discounts',
                section: 'POS Terminal',
                title: 'Discounts & Coupons',
                breadcrumb: ['User Manual', 'POS Terminal', 'Discounts & Coupons'],
                prev: 'pos-cart',
                next: 'pos-customer',
                searchText: 'discount coupon percentage fixed amount apply validate code',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Discounts & Coupons</h1>
                    <p class="text-slate-500 mb-8">Applying reductions to the order total.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Expand the Discount section located in the cart panel (usually just above the totals).</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Manual Discounts</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Percentage:</strong> Enter a percentage (e.g., 10 for 10% off).</li>
                        <li><strong>Fixed Amount:</strong> Enter an exact currency amount to deduct from the subtotal.</li>
                    </ul>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Coupon Codes</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Enter a valid coupon code and click <strong>Apply</strong>. The system will automatically validate the code, checking its expiration date and usage limits. If valid, the discount is calculated and applied to the cart. Only one coupon can be applied per order.</p>
                `
            },
            'pos-customer': {
                id: 'pos-customer',
                section: 'POS Terminal',
                title: 'Customer & Wallet',
                breadcrumb: ['User Manual', 'POS Terminal', 'Customer & Wallet'],
                prev: 'pos-discounts',
                next: 'pos-payments',
                searchText: 'customer link phone number wallet balance credit use wallet',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Customer & Wallet</h1>
                    <p class="text-slate-500 mb-8">Linking a customer and using store credit.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">During the checkout process, you have the option to link the sale to a specific customer.</p>
                    
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>In the customer field, enter the customer's phone number (minimum 7 digits).</li>
                        <li>The system will auto-search for existing customers.</li>
                        <li>If found, their name and current wallet balance will appear.</li>
                        <li>If not found, enter a new name. The system will automatically create the customer profile when the order is completed.</li>
                    </ol>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Customer Wallet</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">If the customer has a wallet balance, a "Use Wallet" toggle appears. Enabling this will apply their available store credit toward the payment. If their payment (cash, card, etc.) exceeds the grand total, the extra amount is automatically added to their wallet as store credit for future use.</p>
                `
            },
            'pos-payments': {
                id: 'pos-payments',
                section: 'POS Terminal',
                title: 'Payments & Split Payments',
                breadcrumb: ['User Manual', 'POS Terminal', 'Payments'],
                prev: 'pos-customer',
                next: 'pos-card-payments',
                searchText: 'checkout billing split payment cash card upi method difference',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Payments & Split Payments</h1>
                    <p class="text-slate-500 mb-8">Handling how the customer pays.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Click the <strong>Checkout</strong> button at the bottom of the cart to open the billing modal.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">By default, the payment method is set to Cash for the full order amount.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Split Payments</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">If a customer wants to pay using multiple methods (e.g., part cash, part card):</p>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Click <strong>Add Payment</strong> to add another payment row.</li>
                        <li>Select the Account (payment method) for each row.</li>
                        <li>Adjust the Amount field for each method.</li>
                    </ol>
                    
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-red-800">🔴 Important</p>
                        <p class="text-sm text-red-700 mt-1">The sum of all payment splits must exactly equal the order's Grand Total before you can complete the sale. The system displays any remaining difference in real-time.</p>
                    </div>
                `
            },
            'pos-card-payments': {
                id: 'pos-card-payments',
                section: 'POS Terminal',
                title: 'Card Payments & Bank Offers',
                breadcrumb: ['User Manual', 'POS Terminal', 'Card Payments'],
                prev: 'pos-payments',
                next: 'pos-checkout',
                searchText: 'card terminal pos machine visa mastercard mdr commission service charge bank offer',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Card Payments & Bank Offers</h1>
                    <p class="text-slate-500 mb-8">Processing credit/debit card transactions.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">When you select a "Card" account as a payment method in the checkout modal, additional options will appear.</p>
                    
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Select the physical <strong>Card Terminal</strong> (POS Machine) being used.</li>
                        <li>Select the <strong>Card Network/Type</strong> (e.g., Visa, Mastercard).</li>
                    </ol>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The system automatically calculates merchant costs behind the scenes: MDR commission, service charges, and any applicable bank offer discounts. These calculations affect your business's net settlement amount and accounting entries, but they do <strong>not</strong> affect the final bill amount shown to the customer.</p>
                `
            },
            'pos-checkout': {
                id: 'pos-checkout',
                section: 'POS Terminal',
                title: 'Completing a Sale',
                breadcrumb: ['User Manual', 'POS Terminal', 'Completing Sale'],
                prev: 'pos-card-payments',
                next: 'pos-receipt',
                searchText: 'confirm complete order finalize deduct inventory accounting journal kot success',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Completing a Sale</h1>
                    <p class="text-slate-500 mb-8">Finalizing the transaction.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Once payments are balanced, click <strong>Confirm & Complete Order</strong> to finalize the sale.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The system instantly performs several actions in the background:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li>Creates the permanent Order record.</li>
                        <li>Deducts inventory stock (if products have recipe mapping).</li>
                        <li>Records accounting journal entries for income and cash/bank accounts.</li>
                        <li>Creates wallet transactions (if the customer's wallet was used or credited).</li>
                        <li>Creates a card transaction record (if a card payment was used) for later bank reconciliation.</li>
                        <li>Generates a Kitchen Order Ticket (KOT) if the mode is Dine-In.</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">On success, the "Order Completed" modal will appear showing the final order total.</p>
                `
            },
            'pos-receipt': {
                id: 'pos-receipt',
                section: 'POS Terminal',
                title: 'Receipt',
                breadcrumb: ['User Manual', 'POS Terminal', 'Receipt'],
                prev: 'pos-checkout',
                next: 'products-list',
                searchText: 'print receipt whatsapp share start next order summary',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Receipt</h1>
                    <p class="text-slate-500 mb-8">Providing proof of purchase to the customer.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">After successfully completing an order, the receipt modal appears with three primary options:</p>
                    
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Print Receipt:</strong> Opens the browser's native print dialog to print a thermal receipt.</li>
                        <li><strong>Share via WhatsApp:</strong> Opens WhatsApp (web or app) with a pre-formatted order summary sent to the customer's linked phone number.</li>
                        <li><strong>Start Next Order:</strong> Closes the modal, clears the cart, and readies the POS for the next customer.</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The receipt includes the Order Number, Date/Time, an itemized list with quantities and prices, Subtotal, Discount, Tax, Grand Total, and the Payment Method(s) used.</p>
                `
            },

            // SECTION 3: PRODUCTS
            'products-list': {
                id: 'products-list',
                section: 'Products',
                title: 'Product List',
                breadcrumb: ['User Manual', 'Products', 'Product List'],
                prev: 'pos-receipt',
                next: 'products-add',
                searchText: 'products list table category price stock status active edit delete add new search',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Product List</h1>
                    <p class="text-slate-500 mb-8">Managing your sales catalog.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Products</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">This page shows all products available in your company.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The data table includes columns for: Product (name and thumbnail image), Category, Price, Stock, Status (Active/Inactive), and Actions.</p>
                    
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li>Use the Search bar to quickly filter products by name.</li>
                        <li>Products with low stock are visually highlighted with a warning indicator.</li>
                        <li>Click <strong>Add Product</strong> at the top right to create a new item.</li>
                        <li>Use the Edit and Delete action buttons on each row to modify products.</li>
                    </ul>
                `
            },
            'products-add': {
                id: 'products-add',
                section: 'Products',
                title: 'Adding a Product',
                breadcrumb: ['User Manual', 'Products', 'Add Product'],
                prev: 'products-list',
                next: 'products-edit',
                searchText: 'add create product form name category sku barcode price cost stock threshold track image',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Adding a Product</h1>
                    <p class="text-slate-500 mb-8">Creating a new item in your catalog.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Products</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Add Product</span>
                    </div>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Form Fields</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Name (Required):</strong> The display name shown in POS and receipts.</li>
                        <li><strong>Category (Required):</strong> Select an existing category.</li>
                        <li><strong>SKU & Barcode (Optional):</strong> For tracking and scanning.</li>
                        <li><strong>Price (Required):</strong> The selling price to the customer.</li>
                        <li><strong>Cost Price (Optional):</strong> Your purchase cost, used for profit reports.</li>
                        <li><strong>Tax Rate (Required):</strong> The tax percentage applied to this item during sale.</li>
                        <li><strong>Stock Qty & Low Stock Threshold:</strong> Set current inventory levels and when to be alerted.</li>
                        <li><strong>Track Stock (Checkbox):</strong> Enable this if you want the system to manage inventory counts.</li>
                        <li><strong>Is Active (Checkbox):</strong> Keep checked to show this item in the POS terminal.</li>
                        <li><strong>Image (Upload):</strong> Add a product photo for the POS grid view.</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Click <strong>Save Product</strong> when finished. You will be redirected back to the product list.</p>
                `
            },
            'products-edit': {
                id: 'products-edit',
                section: 'Products',
                title: 'Editing a Product',
                breadcrumb: ['User Manual', 'Products', 'Edit Product'],
                prev: 'products-add',
                next: 'products-delete',
                searchText: 'edit modify update product change price image',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Editing a Product</h1>
                    <p class="text-slate-500 mb-8">Updating existing product information.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Navigate: Click the <strong>Edit</strong> button on a product row in the Product List.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The edit form contains the exact same fields as the Add Product form, pre-filled with the current values. Make your desired changes and click <strong>Update Product</strong>.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">If the product has an image, the existing image is shown. You can upload a new one to replace it.</p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">Changing a product's price here only affects future sales. It does NOT affect historical order records that already included this product.</p>
                    </div>
                `
            },
            'products-delete': {
                id: 'products-delete',
                section: 'Products',
                title: 'Deleting a Product',
                breadcrumb: ['User Manual', 'Products', 'Delete Product'],
                prev: 'products-edit',
                next: 'categories-list',
                searchText: 'delete remove product warning deactivate active',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Deleting a Product</h1>
                    <p class="text-slate-500 mb-8">Removing items from your catalog.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Click the <strong>Delete</strong> action button on a product row. A confirmation dialog will appear asking "Are you sure?". Click OK to confirm.</p>
                    
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-amber-800">⚠️ Warning</p>
                        <p class="text-sm text-amber-700 mt-1">Deleting a product removes it permanently from the POS catalog. However, it does NOT delete historical order records that included this product.</p>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">Instead of deleting a product (especially if you might sell it again later), consider simply editing it and unchecking the "Is Active" box. This hides it from the POS without deleting the data.</p>
                    </div>
                `
            },

            // SECTION 4: CATEGORIES
            'categories-list': {
                id: 'categories-list',
                section: 'Categories',
                title: 'Category List',
                breadcrumb: ['User Manual', 'Categories', 'Category List'],
                prev: 'products-delete',
                next: 'categories-add',
                searchText: 'categories list organize color icon sort order products count',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Category List</h1>
                    <p class="text-slate-500 mb-8">Organizing your products.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Categories</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Categories group products together to make them easier to find in the POS terminal.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The table displays: Category Name (with optional icon/color), Appearance preview, Sort Order, Products (count of items in this category), Status, and Actions.</p>
                `
            },
            'categories-add': {
                id: 'categories-add',
                section: 'Categories',
                title: 'Adding a Category',
                breadcrumb: ['User Manual', 'Categories', 'Add Category'],
                prev: 'categories-list',
                next: 'categories-edit',
                searchText: 'add create category form icon emoji color sort order active',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Adding a Category</h1>
                    <p class="text-slate-500 mb-8">Creating a new product group.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Categories</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Add Category</span>
                    </div>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Form Fields</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Name (Required):</strong> Display name for the category tab.</li>
                        <li><strong>Description (Optional):</strong> Internal notes.</li>
                        <li><strong>Icon (Optional):</strong> An emoji or icon class name to represent the category.</li>
                        <li><strong>Color (Optional):</strong> A hex color code (e.g., #FF0000). The color picker is synced with the text input.</li>
                        <li><strong>Sort Order (Optional):</strong> A number to control the display order of category tabs in the POS (lower numbers appear first).</li>
                        <li><strong>Is Active (Checkbox):</strong> Controls visibility.</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Click <strong>Save Category</strong>.</p>
                `
            },
            'categories-edit': {
                id: 'categories-edit',
                section: 'Categories',
                title: 'Editing / Deleting a Category',
                breadcrumb: ['User Manual', 'Categories', 'Edit Category'],
                prev: 'categories-add',
                next: 'customers-list',
                searchText: 'edit update delete category warning unassigned',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Editing & Deleting Categories</h1>
                    <p class="text-slate-500 mb-8">Modifying category groups.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Editing</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click Edit on a category row. The form is identical to Add Category. Click Update when finished.</p>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Deleting</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click the Delete action button and confirm.</p>
                    
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-amber-800">⚠️ Warning</p>
                        <p class="text-sm text-amber-700 mt-1">Deleting a category does NOT delete the products inside it. However, those products will lose their category assignment and will only be visible in the POS under the "All Items" tab until reassigned.</p>
                    </div>
                `
            },

            // SECTION 5: CUSTOMERS
            'customers-list': {
                id: 'customers-list',
                section: 'Customers',
                title: 'Customer List',
                breadcrumb: ['User Manual', 'Customers', 'Customer List'],
                prev: 'categories-edit',
                next: 'customers-detail',
                searchText: 'customers list phone wallet balance registered auto-created pos checkout',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Customer List</h1>
                    <p class="text-slate-500 mb-8">Viewing your client base.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Customers</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">This table lists all customers recorded in the system. It displays: Name, Phone Number, Current Wallet Balance, and Registration Date.</p>
                    
                    <div class="bg-slate-50 border border-slate-200 p-4 mb-6 rounded-lg">
                        <p class="text-sm text-slate-600"><strong>Note:</strong> There is no separate "Add Customer" form in the admin panel. New customers are automatically created when a new phone number is entered during the POS checkout process.</p>
                    </div>
                `
            },
            'customers-detail': {
                id: 'customers-detail',
                section: 'Customers',
                title: 'Customer Details & Wallet',
                breadcrumb: ['User Manual', 'Customers', 'Customer Details'],
                prev: 'customers-list',
                next: 'orders-list',
                searchText: 'customer details history ledger wallet adjustment credit debit process manual balance',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Customer Details & Wallet</h1>
                    <p class="text-slate-500 mb-8">Viewing a customer profile and managing store credit.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Navigate: Click <strong>View Details</strong> on a customer row.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The detail page shows the customer's top-level information (name, phone, current wallet balance) and is split into two main sections.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Order History</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">A table listing all past orders placed by this customer, including status and totals. Includes a link to view the full order details.</p>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Wallet Ledger</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">A complete history of changes to the customer's wallet balance (credits and debits), linked to order references when applicable.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Manual Wallet Adjustment</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">You can manually add or remove store credit directly from this page.</p>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Select the Type (Credit to add money, Debit to remove money).</li>
                        <li>Enter the Amount.</li>
                        <li>Add an optional description (e.g., "Refund for complaint").</li>
                        <li>Click <strong>Process Adjustment</strong>.</li>
                    </ol>
                `
            },

            // SECTION 6: ORDERS
            'orders-list': {
                id: 'orders-list',
                section: 'Orders',
                title: 'Order List',
                breadcrumb: ['User Manual', 'Orders', 'Order List'],
                prev: 'customers-detail',
                next: 'orders-detail',
                searchText: 'orders list ORD- date total amount status filters search',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Order List</h1>
                    <p class="text-slate-500 mb-8">Viewing all sales transactions.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Orders</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Displays all orders across the company. The table shows the auto-generated Order # (e.g., ORD-00001), Date & Time, Total Amount, and Status (Paid/Cancelled).</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Filtering Orders</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Use the filters at the top to narrow down the list:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Search:</strong> By order number or customer name.</li>
                        <li><strong>Status:</strong> Filter by Paid or Cancelled.</li>
                        <li><strong>Date:</strong> Select a specific date.</li>
                    </ul>
                    <p class="text-slate-600 leading-relaxed mb-4">Click "Filter" to apply, and "Clear" to reset the list.</p>
                `
            },
            'orders-detail': {
                id: 'orders-detail',
                section: 'Orders',
                title: 'Order Details',
                breadcrumb: ['User Manual', 'Orders', 'Order Details'],
                prev: 'orders-list',
                next: 'coupons-list',
                searchText: 'order details items table breakdown print bill whatsapp cancel order restore stock wallet transactions',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Order Details</h1>
                    <p class="text-slate-500 mb-8">Comprehensive view of a single transaction.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Navigate: Click <strong>View</strong> on any order in the Order List.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The detail page shows everything about the sale: the customer, service type, itemized list, and a full payment breakdown (subtotal, tax, discounts, and split payment methods).</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Action Buttons</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Print Bill:</strong> Opens the browser print dialog.</li>
                        <li><strong>WhatsApp:</strong> Shares the order summary.</li>
                        <li><strong>Cancel Order:</strong> Changes status to cancelled. </li>
                    </ul>

                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-amber-800">⚠️ Warning: Cancelling Orders</p>
                        <p class="text-sm text-amber-700 mt-1">Cancelling an order automatically restores inventory stock. However, it does <strong>NOT</strong> automatically reverse or refund wallet transactions. You must adjust the customer's wallet manually if needed.</p>
                    </div>
                `
            },

            // SECTION 7: COUPONS
            'coupons-list': {
                id: 'coupons-list',
                section: 'Coupons',
                title: 'Coupons',
                breadcrumb: ['User Manual', 'Coupons', 'Coupon List'],
                prev: 'orders-detail',
                next: 'inventory-items',
                searchText: 'coupons discount code expiry usage limit percentage fixed active direct url',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Coupons</h1>
                    <p class="text-slate-500 mb-8">Managing promotional codes.</p>
                    
                    <div class="bg-slate-50 border border-slate-200 p-4 mb-6 rounded-lg">
                        <p class="text-sm text-slate-600"><strong>Note:</strong> In the current layout, Coupons may not appear in the sidebar. You can access them directly via the URL: <code>/coupons</code></p>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The coupons page lists active and inactive discount codes that customers can use at checkout.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Adding a Coupon</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click "Add New Coupon" and fill out the fields:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Code:</strong> The text string the customer enters (e.g., SUMMER10).</li>
                        <li><strong>Type:</strong> Percentage (e.g., 10%) or Fixed Amount (e.g., $5).</li>
                        <li><strong>Value:</strong> The numeric discount amount.</li>
                        <li><strong>Expiry Date:</strong> Coupon is invalid after this date.</li>
                        <li><strong>Usage Limit:</strong> Maximum number of times this code can be used across all customers.</li>
                        <li><strong>Is Active:</strong> Enable or disable the coupon.</li>
                    </ul>
                `
            },

            // SECTION 8: INVENTORY
            'inventory-items': {
                id: 'inventory-items',
                section: 'Inventory',
                title: 'Inventory Items',
                breadcrumb: ['User Manual', 'Inventory', 'Items'],
                prev: 'coupons-list',
                next: 'inventory-recipes',
                searchText: 'inventory raw materials ingredients stock unit kg pcs dozen minimum stock cost price warning badge',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Inventory Items</h1>
                    <p class="text-slate-500 mb-8">Managing raw materials and ingredients.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Inventory Items</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed mb-4 text-xs italic">Requires Inventory Management module enabled.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">This section manages raw materials, which are distinct from the finished products you sell.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Adding Ingredients</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click "Add New Ingredient" to open the creation modal.</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Unit Type:</strong> Define how this item is measured (kg, grams, liters, ml, pieces, dozens).</li>
                        <li><strong>Current Stock:</strong> Starting quantity.</li>
                        <li><strong>Minimum Stock:</strong> Alerts you when inventory drops below this level.</li>
                        <li><strong>Cost Price:</strong> Average cost per unit.</li>
                    </ul>

                    <div class="bg-slate-50 border border-slate-200 p-4 mb-6 rounded-lg">
                        <p class="text-sm text-slate-600">Items falling below their minimum stock level are visually highlighted with a yellow/red warning badge on the list view.</p>
                    </div>
                `
            },
            'inventory-recipes': {
                id: 'inventory-recipes',
                section: 'Inventory',
                title: 'Recipe Mapping',
                breadcrumb: ['User Manual', 'Inventory', 'Recipe Mapping'],
                prev: 'inventory-items',
                next: 'restaurant-table-map',
                searchText: 'recipe mapping link products ingredients automatic deduction pos sale track',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Recipe Mapping</h1>
                    <p class="text-slate-500 mb-8">Connecting finished products to raw ingredients.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Recipe Mapping</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Recipe mapping is the key to automatic inventory tracking. It tells the system exactly which raw materials are consumed when a product is sold.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Setting up a Recipe</h2>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Find the product you want to configure and click it.</li>
                        <li>In the modal, click to add an ingredient row.</li>
                        <li>Select an Inventory Item from the dropdown.</li>
                        <li>Enter the exact Quantity needed to make one unit of the product.</li>
                        <li>Click "Add Ingredient" to add more rows.</li>
                        <li>Click "Save Recipe".</li>
                    </ol>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">Once a recipe is saved, any time that product is sold in the POS, the system will automatically deduct those exact ingredient quantities from your inventory levels.</p>
                    </div>
                `
            },

            // SECTION 9: RESTAURANT
            'restaurant-table-map': {
                id: 'restaurant-table-map',
                section: 'Restaurant',
                title: 'Table Map',
                breadcrumb: ['User Manual', 'Restaurant', 'Table Map'],
                prev: 'inventory-recipes',
                next: 'restaurant-table-settings',
                searchText: 'table map floor layout sections color-coded status available occupied reserved cleaning ajax real-time read-only',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Table Map</h1>
                    <p class="text-slate-500 mb-8">Visual overview of your dining floor.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Table Map</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed mb-4 text-xs italic">Requires Table Management module.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The Table Map provides a real-time visual layout of all your tables, grouped by sections (e.g., Main Hall, Patio).</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Status Colors</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><span class="text-green-600 font-bold">Green:</span> Available</li>
                        <li><span class="text-red-600 font-bold">Red:</span> Occupied (guests currently dining/ordering)</li>
                        <li><span class="text-yellow-600 font-bold">Yellow:</span> Reserved</li>
                        <li><span class="text-gray-500 font-bold">Gray:</span> Cleaning</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The table map updates in real-time via AJAX. <strong>Note:</strong> This view is read-only. To add, edit, or delete tables, use Table Settings.</p>
                `
            },
            'restaurant-table-settings': {
                id: 'restaurant-table-settings',
                section: 'Restaurant',
                title: 'Table Settings',
                breadcrumb: ['User Manual', 'Restaurant', 'Table Settings'],
                prev: 'restaurant-table-map',
                next: 'restaurant-kitchen',
                searchText: 'table settings capacity qr code sections floor edit delete warning delete section',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Table Settings</h1>
                    <p class="text-slate-500 mb-8">Configuring your physical dining space.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Table Settings</span>
                    </div>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Adding Tables</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click "Add Table" to create a new seating area.</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Table Name:</strong> Identifier (e.g., T1, Window 4).</li>
                        <li><strong>Capacity:</strong> Number of seats.</li>
                        <li><strong>Section:</strong> The physical zone the table belongs to.</li>
                    </ul>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">QR Codes</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Every table automatically generates a unique QR code for Guest QR Ordering. Click "Show QR" on the table list to view or print the code for display on the physical table.</p>

                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-amber-800">⚠️ Warning: Deleting Sections</p>
                        <p class="text-sm text-amber-700 mt-1">If you delete a floor section, ALL tables assigned to that section will also be permanently deleted.</p>
                    </div>
                `
            },
            'restaurant-kitchen': {
                id: 'restaurant-kitchen',
                section: 'Restaurant',
                title: 'Kitchen Display (KDS)',
                breadcrumb: ['User Manual', 'Restaurant', 'Kitchen Display'],
                prev: 'restaurant-table-settings',
                next: 'restaurant-waiter',
                searchText: 'kitchen kds tickets kot pending preparing ready served auto-generated',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Kitchen Display System (KDS)</h1>
                    <p class="text-slate-500 mb-8">Digital order management for the kitchen.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Kitchen (KDS)</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed mb-4 text-xs italic">Requires Kitchen Display module.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The KDS shows active Kitchen Order Tickets (KOTs) on a grid, replacing paper tickets.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Ticket Workflow</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Tickets are auto-generated when dine-in orders are placed via POS, Waiter Panel, or Guest QR.</p>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Pending:</strong> New order arrives.</li>
                        <li><strong>Preparing:</strong> Kitchen staff clicks to indicate they are cooking.</li>
                        <li><strong>Ready:</strong> Food is plated and waiting for pickup.</li>
                        <li><strong>Served:</strong> Food taken to the table (ticket is archived).</li>
                    </ol>

                    <p class="text-slate-600 leading-relaxed mb-4">Kitchen staff can update the status of individual items on a ticket or update the entire ticket at once.</p>
                `
            },
            'restaurant-waiter': {
                id: 'restaurant-waiter',
                section: 'Restaurant',
                title: 'Waiter Panel',
                breadcrumb: ['User Manual', 'Restaurant', 'Waiter Panel'],
                prev: 'restaurant-kitchen',
                next: 'restaurant-guest-qr',
                searchText: 'waiter panel tables order kot track complete status',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Waiter Panel</h1>
                    <p class="text-slate-500 mb-8">Mobile-friendly order taking for staff.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Waiter Panel</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed mb-4 text-xs italic">Requires Waiter Panel module.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The Waiter Dashboard shows all tables, filterable by status or section.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Taking an Order</h2>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Click an available or occupied table to open its Order Panel.</li>
                        <li>Browse categories and tap products to add them to the cart.</li>
                        <li>Click <strong>Place Order</strong>. This instantly generates a KOT and sends it to the kitchen.</li>
                    </ol>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">From the panel, waiters can track kitchen preparation status. An order can only be marked as "Complete" when all kitchen items are served and the bill is paid.</p>
                `
            },
            'restaurant-guest-qr': {
                id: 'restaurant-guest-qr',
                section: 'Restaurant',
                title: 'Guest QR Ordering',
                breadcrumb: ['User Manual', 'Restaurant', 'Guest QR Ordering'],
                prev: 'restaurant-waiter',
                next: 'suppliers-list',
                searchText: 'guest qr ordering scan menu table place order track no login',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Guest QR Ordering</h1>
                    <p class="text-slate-500 mb-8">Self-service ordering directly from the table.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">If enabled, guests can use their own smartphones to place orders without waiting for staff.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">How it works</h2>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Guest scans the unique QR code on their table using their phone camera.</li>
                        <li>The digital menu loads immediately (no login or app download required).</li>
                        <li>Guest browses, adds items, and clicks Place Order.</li>
                        <li>A KOT is sent directly to the kitchen, and the table status turns to "Occupied".</li>
                        <li>Guests track their order status live on their phone screen.</li>
                    </ol>

                    <p class="text-slate-600 leading-relaxed mb-4">Staff can monitor and manage guest-initiated orders seamlessly from the standard POS or Waiter Panel.</p>
                `
            },

            // SECTION 10: PURCHASE & SUPPLY
            'suppliers-list': {
                id: 'suppliers-list',
                section: 'Purchase & Supply',
                title: 'Suppliers',
                breadcrumb: ['User Manual', 'Purchase & Supply', 'Suppliers'],
                prev: 'restaurant-guest-qr',
                next: 'purchases-list',
                searchText: 'suppliers vendor contact phone balance opening edit view',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Suppliers</h1>
                    <p class="text-slate-500 mb-8">Managing your vendors.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Suppliers</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Keep track of the companies you buy inventory and supplies from.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Adding a Supplier</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click "Add Supplier". The only strictly required field is the Name, but adding Contact Person, Phone, Email, Address, and Tax Number helps keep records complete. You can also specify an Opening Balance if you owe them money prior to using this system.</p>
                `
            },
            'purchases-list': {
                id: 'purchases-list',
                section: 'Purchase & Supply',
                title: 'Purchase Orders',
                breadcrumb: ['User Manual', 'Purchase & Supply', 'Purchase Orders'],
                prev: 'suppliers-list',
                next: 'purchases-create',
                searchText: 'purchase orders po list supplier status filters',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Purchase Orders List</h1>
                    <p class="text-slate-500 mb-8">Tracking inventory purchases.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Purchase Orders</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Displays all purchase orders (POs) placed with suppliers.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Columns include PO Number, Date, Supplier, Total Amount, Order Status (Pending/Received/Cancelled), and Payment Status. Use the filters at the top to search or narrow down by status/supplier.</p>
                `
            },
            'purchases-create': {
                id: 'purchases-create',
                section: 'Purchase & Supply',
                title: 'Creating a Purchase Order',
                breadcrumb: ['User Manual', 'Purchase & Supply', 'Create PO'],
                prev: 'purchases-list',
                next: 'purchases-detail',
                searchText: 'create po purchase order supplier items unit cost totals save',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Creating a Purchase Order</h1>
                    <p class="text-slate-500 mb-8">Recording new stock purchases.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Purchase Orders</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Create Purchase Order</span>
                    </div>
                    
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Select a <strong>Supplier</strong> and <strong>Purchase Date</strong>.</li>
                        <li>In the Items section, select an <strong>Inventory Item</strong>.</li>
                        <li>Enter the purchased <strong>Quantity</strong> and <strong>Unit Cost</strong>.</li>
                        <li>Click "Add Item" to include additional rows.</li>
                        <li>Set the Order Status (e.g., Received if the goods are already delivered).</li>
                        <li>Optionally enter immediate payment details at the bottom.</li>
                        <li>Click "Save Purchase Order".</li>
                    </ol>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Subtotals, taxes, and grand totals are calculated automatically based on the items.</p>
                `
            },
            'purchases-detail': {
                id: 'purchases-detail',
                section: 'Purchase & Supply',
                title: 'Purchase Order Details',
                breadcrumb: ['User Manual', 'Purchase & Supply', 'PO Details'],
                prev: 'purchases-create',
                next: 'accounting-accounts',
                searchText: 'po details print record payment partial update status balance',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Purchase Order Details</h1>
                    <p class="text-slate-500 mb-8">Managing PO payments and status.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Navigate: Click View on any Purchase Order.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">This page shows the full details of the PO. From here, you can Print the order, Update Status (e.g., from Pending to Received), or Record Payments.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Recording Payments</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click "Record Payment" to open the payment modal. Select the payment method, enter the amount, and save.</p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">You can record partial payments against large purchase orders. The system will track the payments in a history table and maintain the outstanding balance.</p>
                    </div>
                `
            },

            // SECTION 11: ACCOUNTING
            'accounting-accounts': {
                id: 'accounting-accounts',
                section: 'Accounting',
                title: 'Chart of Accounts',
                breadcrumb: ['User Manual', 'Accounting', 'Chart of Accounts'],
                prev: 'purchases-detail',
                next: 'accounting-journals',
                searchText: 'chart accounts ledger type asset liability equity income expense pos payment system accounts',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Chart of Accounts</h1>
                    <p class="text-slate-500 mb-8">Managing your financial ledgers.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Chart of Accounts</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The core of the accounting module. Accounts are categorized into five types: Asset, Liability, Equity, Income, and Expense.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Adding an Account</h2>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Account Name & Code:</strong> e.g., "Bank of America" (1001).</li>
                        <li><strong>Type:</strong> Must be one of the five core types.</li>
                        <li><strong>Show in POS:</strong> Check this box if this account should be available as a payment method in the POS checkout (e.g., Cash, Card, UPI).</li>
                    </ul>

                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-amber-800">⚠️ System Accounts</p>
                        <p class="text-sm text-amber-700 mt-1">Certain accounts (like the default Cash account created during registration) are protected system accounts and cannot be deleted.</p>
                    </div>
                `
            },
            'accounting-journals': {
                id: 'accounting-journals',
                section: 'Accounting',
                title: 'Journal Entries',
                breadcrumb: ['User Manual', 'Accounting', 'Journal Entries'],
                prev: 'accounting-accounts',
                next: 'accounting-expenses',
                searchText: 'journal entries jnl debit credit lines auto-generated double entry',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Journal Entries</h1>
                    <p class="text-slate-500 mb-8">Double-entry accounting records.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Journal Entries</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">View and create manual double-entry journal records. Note that many journal entries are auto-generated by the system (e.g., completing a POS sale creates the necessary income and asset entries automatically).</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Creating a Manual Entry</h2>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 mb-6">
                        <li>Click "New Journal Entry".</li>
                        <li>Select Transaction Date and enter Notes.</li>
                        <li>In the dynamic lines section, select an Account.</li>
                        <li>Enter a Debit or Credit amount.</li>
                        <li>Add more lines as needed.</li>
                    </ol>
                    
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-red-800">🔴 Important</p>
                        <p class="text-sm text-red-700 mt-1">To satisfy double-entry accounting rules, the total of all Debits must exactly equal the total of all Credits before you can save the entry.</p>
                    </div>
                `
            },
            'accounting-expenses': {
                id: 'accounting-expenses',
                section: 'Accounting',
                title: 'Expenses',
                breadcrumb: ['User Manual', 'Accounting', 'Expenses'],
                prev: 'accounting-journals',
                next: 'reports-sales',
                searchText: 'expenses record paid from category accounting entry',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Expenses</h1>
                    <p class="text-slate-500 mb-8">Tracking money going out.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Expenses</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Easily record business expenses without needing to understand double-entry journals.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Recording an Expense</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click "Record Expense" and provide:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li>Date and Amount.</li>
                        <li><strong>Category:</strong> What the expense was for (e.g., Utilities, Maintenance). You can manage categories via the "Categories" button.</li>
                        <li><strong>Paid From Account:</strong> Which asset account the money came from (e.g., Cash, Bank).</li>
                    </ul>

                    <p class="text-slate-600 leading-relaxed mb-4">Behind the scenes, saving an expense automatically creates the proper accounting journal entry (debiting the expense account and crediting the payment account).</p>
                `
            },

            // SECTION 12: REPORTS
            'reports-sales': {
                id: 'reports-sales',
                section: 'Reports',
                title: 'Sales Report',
                breadcrumb: ['User Manual', 'Reports', 'Sales'],
                prev: 'accounting-expenses',
                next: 'reports-inventory',
                searchText: 'reports sales filter date status service type export pdf excel',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Sales Report</h1>
                    <p class="text-slate-500 mb-8">Analyzing revenue performance.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Sales Reports</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">View detailed sales data. Use the filters at the top to specify a date range, order status (paid/cancelled), or specific service types (dine-in, delivery, etc.).</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Summary statistics are shown in cards above the main data table.</p>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Exporting</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click the <strong>PDF</strong> or <strong>Excel</strong> buttons to download the report. The exported file will strictly reflect the filters you currently have applied on screen.</p>
                `
            },
            'reports-inventory': {
                id: 'reports-inventory',
                section: 'Reports',
                title: 'Inventory Report',
                breadcrumb: ['User Manual', 'Reports', 'Inventory'],
                prev: 'reports-sales',
                next: 'reports-purchases',
                searchText: 'inventory report stock value low out of stock export',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Inventory Report</h1>
                    <p class="text-slate-500 mb-8">Evaluating stock levels and value.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Inventory Reports</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">View the current state of your raw materials. The table shows current quantities, unit costs, and the total value of stock on hand.</p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">Use the Status filter to show only "Low Stock" items. This is highly useful for generating a quick reorder list before contacting suppliers.</p>
                    </div>
                `
            },
            'reports-purchases': {
                id: 'reports-purchases',
                section: 'Reports',
                title: 'Purchase Report',
                breadcrumb: ['User Manual', 'Reports', 'Purchase'],
                prev: 'reports-inventory',
                next: 'reports-wallet',
                searchText: 'purchase report suppliers volume trend chart spend export',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Purchase Report</h1>
                    <p class="text-slate-500 mb-8">Analyzing vendor spending.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Purchase Reports</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">View purchasing data filtered by date and status. The page includes summary KPI cards, a breakdown of Top Suppliers by volume, and a Monthly Spending Trend chart.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Available for PDF and Excel export.</p>
                `
            },
            'reports-wallet': {
                id: 'reports-wallet',
                section: 'Reports',
                title: 'Wallet Report',
                breadcrumb: ['User Manual', 'Reports', 'Wallet'],
                prev: 'reports-purchases',
                next: 'reports-pnl',
                searchText: 'wallet report transactions credit debit customer',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Wallet Report</h1>
                    <p class="text-slate-500 mb-8">Auditing store credit movements.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Wallet Reports</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">This report aggregates wallet transactions (credits and debits) across all customers in the company. You can filter by date range, specific customer, or transaction type.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Note: This report is currently for on-screen viewing only (no export).</p>
                `
            },
            'reports-pnl': {
                id: 'reports-pnl',
                section: 'Reports',
                title: 'Profit & Loss Statement',
                breadcrumb: ['User Manual', 'Reports', 'Profit & Loss'],
                prev: 'reports-wallet',
                next: 'reports-balance-sheet',
                searchText: 'profit loss pnl statement income expenses net export',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Profit & Loss Statement</h1>
                    <p class="text-slate-500 mb-8">Your business bottom line.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Profit & Loss</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The P&L statement shows Total Income, Total Expenses, and Net Profit for a selected date range.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The data is broken down into detailed tables for Operating Income and Operating Expenses, categorized by specific accounting ledgers.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Exporting to PDF or Excel will submit the form with the current date range filters applied.</p>
                `
            },
            'reports-balance-sheet': {
                id: 'reports-balance-sheet',
                section: 'Reports',
                title: 'Balance Sheet',
                breadcrumb: ['User Manual', 'Reports', 'Balance Sheet'],
                prev: 'reports-pnl',
                next: 'reports-card-commission',
                searchText: 'balance sheet assets liabilities equity financial position export',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Balance Sheet</h1>
                    <p class="text-slate-500 mb-8">Snapshot of financial position.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Balance Sheet</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Unlike the P&L (which shows a date range), the Balance Sheet shows your financial position <strong>as of a specific End Date</strong>.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">It uses a standard two-column layout:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Left Column:</strong> Assets (Cash, Bank, Inventory value, Receivables).</li>
                        <li><strong>Right Column:</strong> Liabilities + Equity.</li>
                    </ul>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The totals at the bottom of both columns should always balance.</p>
                `
            },
            'reports-card-commission': {
                id: 'reports-card-commission',
                section: 'Reports',
                title: 'Card Commission Report',
                breadcrumb: ['User Manual', 'Reports', 'Card Commission'],
                prev: 'reports-balance-sheet',
                next: 'reports-card-settlements',
                searchText: 'card commission merchant costs deduction net handling method tax',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Card Commission Report</h1>
                    <p class="text-slate-500 mb-8">Tracking payment processing costs.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Card Commission</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">This report details the merchant costs associated with card payments processed at the POS.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">For each card transaction, it displays the Order, Card Type, gross Bill Amount, calculated Commission, Tax on Commission, Total Deduction, and the Expected Net Received amount.</p>
                `
            },
            'reports-card-settlements': {
                id: 'reports-card-settlements',
                section: 'Reports',
                title: 'Card & Settlement Report',
                breadcrumb: ['User Manual', 'Reports', 'Card & Settlements'],
                prev: 'reports-card-commission',
                next: 'reports-register-sessions',
                searchText: 'card settlements bank direct url transactions reconciliation difference net expected actual paid',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Card & Settlement Report</h1>
                    <p class="text-slate-500 mb-8">Reconciling bank payouts.</p>
                    
                    <div class="bg-slate-50 border border-slate-200 p-4 mb-6 rounded-lg">
                        <p class="text-sm text-slate-600"><strong>Note:</strong> This report is accessible via direct URL only: <code>/reports/cards</code></p>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The report features two tabs:</p>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Transactions Tab:</strong> Shows individual card payments, gross amounts, fee breakdowns (Discount, Service Charge, MDR), and net expected amounts.</li>
                        <li><strong>Settlements Tab:</strong> Groups transactions by settlement batches. Compare the Expected Net against the Actual Paid by the bank to identify any Reconciliation Differences.</li>
                    </ul>
                `
            },
            'reports-register-sessions': {
                id: 'reports-register-sessions',
                section: 'Reports',
                title: 'Register Sessions',
                breadcrumb: ['User Manual', 'Reports', 'Register Sessions'],
                prev: 'reports-card-settlements',
                next: 'reports-export',
                searchText: 'register sessions shift audit cashier discrepancy expected actual closed',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Register Sessions</h1>
                    <p class="text-slate-500 mb-8">Auditing POS shifts.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Register Sessions</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Review all past POS shifts. Each entry displays who opened the register, the Opening Amount, the Closing Amount entered by the cashier, and the Expected Amount calculated by the system based on sales.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Look at the Difference column to quickly spot cash discrepancies (overages or shortages) at the end of shifts.</p>
                `
            },
            'reports-export': {
                id: 'reports-export',
                section: 'Reports',
                title: 'Exporting Reports',
                breadcrumb: ['User Manual', 'Reports', 'Exporting'],
                prev: 'reports-register-sessions',
                next: 'settings-companies',
                searchText: 'export download pdf excel filter apply reports',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Exporting Reports</h1>
                    <p class="text-slate-500 mb-8">Getting data out of the system.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Most major reports (Sales, Inventory, Purchase, P&L, Balance Sheet) support exporting in two formats:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>PDF:</strong> Generates a clean, formatted document ready for printing or email.</li>
                        <li><strong>Excel (.xlsx):</strong> Generates a raw spreadsheet for further data manipulation.</li>
                    </ul>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">Always apply your desired date or status filters first, <em>before</em> clicking export. The exported file will mirror exactly what is configured in your on-screen filters.</p>
                    </div>
                `
            },

            // SECTION 13: SETTINGS & ADMINISTRATION
            'settings-companies': {
                id: 'settings-companies',
                section: 'Settings & Administration',
                title: 'Companies',
                breadcrumb: ['User Manual', 'Settings', 'Companies'],
                prev: 'reports-export',
                next: 'settings-modules',
                searchText: 'companies stores multi-tenant switch modules business type add create edit delete',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Companies</h1>
                    <p class="text-slate-500 mb-8">Managing business locations.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Companies</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">This page manages the different stores or business entities under your account.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Company Actions</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Switch:</strong> Click to immediately set this company as your active environment.</li>
                        <li><strong>Modules:</strong> Configure which feature modules are enabled for this specific company.</li>
                        <li><strong>Edit/Delete:</strong> Modify details or permanently remove a location (Admin only).</li>
                    </ul>

                    <p class="text-slate-600 leading-relaxed mb-4">To add a new location, click "Add Company" and provide the Name and Business Type (retail, restaurant, cafe, etc.).</p>
                `
            },
            'settings-modules': {
                id: 'settings-modules',
                section: 'Settings & Administration',
                title: 'Module Configuration',
                breadcrumb: ['User Manual', 'Settings', 'Module Configuration'],
                prev: 'settings-companies',
                next: 'settings-card-types',
                searchText: 'modules features enable disable toggle table kitchen kds waiter inventory recommended',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Module Configuration</h1>
                    <p class="text-slate-500 mb-8">Turning features on and off per location.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Navigate: Go to Companies, then click the <strong>Modules</strong> button on a company row.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">You can tailor the system to fit the exact needs of each store by enabling or disabling modules:</p>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Table Management:</strong> Enables floor plans and QR ordering.</li>
                        <li><strong>Kitchen Display:</strong> Enables digital KOT screens.</li>
                        <li><strong>Waiter Panel:</strong> Enables the mobile ordering interface.</li>
                        <li><strong>Inventory Management:</strong> Enables raw materials and recipe tracking.</li>
                    </ul>

                    <p class="text-slate-600 leading-relaxed mb-4">Some modules show "Recommended" badges based on the Business Type selected when the company was created. Check the box to enable a module, then click Save.</p>
                `
            },
            'settings-card-types': {
                id: 'settings-card-types',
                section: 'Settings & Administration',
                title: 'Card Types & Commissions',
                breadcrumb: ['User Manual', 'Settings', 'Card Types'],
                prev: 'settings-modules',
                next: 'settings-delivery-partners',
                searchText: 'card types commission network visa mastercard handling write-off settlement expense',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Card Types & Commissions</h1>
                    <p class="text-slate-500 mb-8">Configuring payment processing rules.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Card Types</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Configure the commission structures for different card networks (e.g., Visa, Mastercard) used at the POS.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Adding a Card Type</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Commission Type & Value:</strong> Percentage or fixed amount charged by the bank.</li>
                        <li><strong>Handling Method:</strong> Choose how to track the fee (Ignore, Auto Write-Off, or Settlement Tracking).</li>
                        <li><strong>Expense Account:</strong> If writing off, select which accounting ledger takes the hit for the commission cost.</li>
                    </ul>
                `
            },
            'settings-delivery-partners': {
                id: 'settings-delivery-partners',
                section: 'Settings & Administration',
                title: 'Delivery Partners',
                breadcrumb: ['User Manual', 'Settings', 'Delivery Partners'],
                prev: 'settings-card-types',
                next: 'settings-users',
                searchText: 'delivery partners aggregator swiggy zomato ubereats commission settlement receivables',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Delivery Partners</h1>
                    <p class="text-slate-500 mb-8">Managing third-party delivery apps.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Delivery Partners</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Set up aggregators (like Swiggy, Zomato, UberEats). Add their Name and their Commission Percentage.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Settlement Tracking</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Click on a partner to view their dedicated settlements page. This shows all delivery orders processed through them, calculates the expected commission, and allows you to mark individual orders as Settled once you receive the payout from the partner.</p>
                `
            },
            'settings-users': {
                id: 'settings-users',
                section: 'Settings & Administration',
                title: 'User Management',
                breadcrumb: ['User Manual', 'Settings', 'Users'],
                prev: 'settings-delivery-partners',
                next: 'settings-roles',
                searchText: 'user management add staff employee assign companies role password',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">User Management</h1>
                    <p class="text-slate-500 mb-8">Controlling staff access.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Users</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Add and manage employees who need access to the system.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Adding a User</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">Provide their Name, Email, and Password. More importantly:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li><strong>Assign Companies:</strong> Check which store(s) this user is allowed to log into.</li>
                        <li><strong>Assign Roles:</strong> Define their access level (e.g., Staff).</li>
                    </ul>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-blue-800">💡 Tip</p>
                        <p class="text-sm text-blue-700 mt-1">When editing an existing user, you can leave the password fields blank to keep their current password unchanged.</p>
                    </div>
                `
            },
            'settings-roles': {
                id: 'settings-roles',
                section: 'Settings & Administration',
                title: 'Roles & Permissions',
                breadcrumb: ['User Manual', 'Settings', 'Roles & Permissions'],
                prev: 'settings-users',
                next: 'settings-profile',
                searchText: 'roles permissions access control granular module view create edit delete admin owner',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Roles & Permissions</h1>
                    <p class="text-slate-500 mb-8">Fine-tuning security access.</p>
                    
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                        <span class="bg-slate-100 px-2 py-1 rounded">Sidebar</span><span>→</span><span class="bg-slate-100 px-2 py-1 rounded">Roles</span>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Create custom roles with granular permissions down to the module level (e.g., allow a role to "View Products" but not "Create Products").</p>
                    
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-amber-800">⚠️ Warning</p>
                        <p class="text-sm text-amber-700 mt-1">Do not delete the default Admin or Owner roles, as they are essential for core system routing.</p>
                    </div>
                `
            },
            'settings-profile': {
                id: 'settings-profile',
                section: 'Settings & Administration',
                title: 'Profile Management',
                breadcrumb: ['User Manual', 'Settings', 'Profile'],
                prev: 'settings-roles',
                next: 'needs-attention',
                searchText: 'profile name email password delete account irreversible',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Profile Management</h1>
                    <p class="text-slate-500 mb-8">Managing your personal account.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">Navigate: Click your name in the top right header, then select <strong>Profile</strong>.</p>
                    
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Update Profile:</strong> Change your name and login email.</li>
                        <li><strong>Update Password:</strong> Requires entering your current password for security.</li>
                        <li><strong>Delete Account:</strong> Permanently removes your user record.</li>
                    </ul>

                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <p class="text-sm font-semibold text-red-800">🔴 Danger</p>
                        <p class="text-sm text-red-700 mt-1">Deleting your account is irreversible. All personal data associated with your user will be deleted.</p>
                    </div>
                `
            },

            // SECTION 14: NEEDS ATTENTION
            'needs-attention': {
                id: 'needs-attention',
                section: 'Needs Attention',
                title: 'Known Issues & Incomplete Features',
                breadcrumb: ['User Manual', 'Needs Attention'],
                prev: 'settings-profile',
                next: null,
                searchText: 'needs attention known issues missing views incomplete ui bug permissions staff missing link',
                content: `
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Known Issues & Incomplete Features</h1>
                    <p class="text-slate-500 mb-8">Important system notices for current version.</p>
                    
                    <p class="text-slate-600 leading-relaxed mb-4">The following items document features that are incomplete, missing, or have known bugs in the current version of the application.</p>
                    
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Missing Views</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Product Detail page (/products/{id}):</strong> The route exists but the Blade view file is missing. Accessing this URL directly results in an error.</li>
                        <li><strong>Customer Create page:</strong> No dedicated admin form exists for manually creating customers. Customers are only created automatically during POS checkout.</li>
                        <li><strong>Customer Edit page:</strong> There is currently no UI to edit customer details (like correcting a misspelled name or phone number).</li>
                    </ul>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Missing UI Components</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Cash Movement Modals:</strong> Backend routes exist for recording POS expenses, owner withdrawals, and cash deposits during an active shift, but the buttons/modals to trigger these actions do not exist in the POS interface.</li>
                    </ul>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Permission Issues (Affects Staff Role)</h2>
                    <ul class="list-disc list-inside space-y-2 text-slate-600 mb-6">
                        <li><strong>Sidebar Navigation Mismatch:</strong> The sidebar checks for permissions named 'access products', 'access categories', etc., but the database contains 'view products', 'view categories'. As a result, Staff users cannot see these menu items even when granted proper permissions.</li>
                        <li><strong>Restaurant Permissions Error:</strong> The Role management UI allows assigning restaurant permissions, but these specific permissions were not created in the database seeders. Saving a role with these checked causes a SQL error.</li>
                        <li><strong>Hidden Permissions:</strong> Category and Inventory permissions exist in the database but are missing from the Role management UI, making them impossible to assign.</li>
                        <li><strong>Register Sessions Blocked:</strong> The Register Sessions page requires a 'view accounts' permission which doesn't exist, blocking all non-Admin/Owner users.</li>
                    </ul>
                    <p class="text-sm text-slate-500 mb-6 italic">Note: Admin and Owner roles bypass all permission checks and are unaffected by these issues.</p>

                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">Features Without Sidebar Links</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">The following features are functional but lack links in the navigation sidebar. They must be accessed via direct URL:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 mb-6">
                        <li>Coupons <code>/coupons</code></li>
                        <li>Cards/POS Machines <code>/cards</code></li>
                        <li>Bank Offers <code>/offers</code></li>
                        <li>Bank Settlements <code>/settlements</code></li>
                    </ul>
                `
            }
        }
    };
}

if (window.Alpine) {
    window.Alpine.data('manualApp', manualApp);
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('manualApp', manualApp);
    });
}
</script>
<style>
    /* Add some basic styling for the dynamic HTML content */
    .content-html h1, .content-html h2 {
        color: #172033;
    }
    .content-html p {
        color: #475569;
    }
    .content-html ul li::marker {
        color: #F5703E;
    }
    
    /* Hide scrollbar for top nav on mobile */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush
@endsection
