<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Golf Review Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 min-h-screen">
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-600 p-2 rounded">
                        <i class="fas fa-golf-ball text-white"></i>
                    </div>
                    <div>
                        <h2 class="font-bold">Golf Review Hub</h2>
                        <p class="text-xs text-gray-400">Admin Panel</p>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=10b981&color=fff" 
                         class="w-10 h-10 rounded-full">
                    <div>
                        <p class="font-medium">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ ucfirst(Auth::user()->role) }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center space-x-3 p-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    @if(Auth::user()->isAdmin() || Auth::user()->isEditor())
                    <li>
                        <a href="{{ route('admin.products.index') }}" 
                           class="flex items-center space-x-3 p-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.products.*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-golf-club"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="{{ route('admin.reviews.index') }}" 
                           class="flex items-center space-x-3 p-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.reviews.*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-star"></i>
                            <span>Reviews</span>
                        </a>
                    </li>

                    @if(Auth::user()->isAdmin() || Auth::user()->isEditor())
                    <li>
                        <a href="{{ route('admin.articles.index') }}" 
                           class="flex items-center space-x-3 p-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.articles.*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-newspaper"></i>
                            <span>Articles</span>
                        </a>
                    </li>
                    @endif

                    @if(Auth::user()->isAdmin())
                    <li>
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center space-x-3 p-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    @endif

                    <li class="pt-4 mt-4 border-t border-gray-700">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="flex items-center space-x-3 p-2 rounded hover:bg-gray-700 w-full text-left">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Bar -->
            <div class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                    <div class="flex items-center space-x-4">
                        <button class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bell"></i>
                        </button>
                        <a href="{{ url('/') }}" target="_blank" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-6">
                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>