@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container-fluid max-w-7xl">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.users.index') }}" class="mr-4">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">User Details</h1>
                </div>
                <div class="flex space-x-2">
                    @if(!$user->is_banned && $user->id !== auth()->id())
                        <button onclick="showBanModal({{ $user->id }}, '{{ $user->name }}')"
                                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                            <i class="fas fa-ban mr-2"></i>Ban User
                        </button>
                    @elseif($user->is_banned)
                        <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-check-circle mr-2"></i>Unban User
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    
                    @if($user->id !== auth()->id() && !$user->isAdmin())
                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                <i class="fas fa-user-secret mr-2"></i>Login As
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - User Info -->
                <div class="space-y-6">
                    <!-- Profile Card -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="text-center mb-4">
                            <img src="{{ $user->avatar_url }}" 
                                 alt="{{ $user->name }}"
                                 class="w-24 h-24 rounded-full mx-auto mb-3">
                            <h2 class="text-xl font-semibold">{{ $user->name }}</h2>
                            <p class="text-gray-600">{{ $user->email }}</p>
                            
                            <div class="mt-3">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-{{ $user->status_color }}-100 text-{{ $user->status_color }}-800">
                                    {{ $user->status_label }}
                                </span>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full ml-2
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                       ($user->role === 'editor' ? 'bg-blue-100 text-blue-800' : 
                                       ($user->role === 'reviewer' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        </div>

                        <dl class="space-y-2 text-sm">
                            @if($user->phone)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Phone:</dt>
                                <dd class="font-medium">{{ $user->phone }}</dd>
                            </div>
                            @endif
                            
                            @if($user->location)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Location:</dt>
                                <dd class="font-medium">{{ $user->location }}</dd>
                            </div>
                            @endif
                            
                            @if($user->skill_level)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Skill Level:</dt>
                                <dd class="font-medium capitalize">{{ $user->skill_level }}</dd>
                            </div>
                            @endif
                            
                            @if($user->handicap !== null)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Handicap:</dt>
                                <dd class="font-medium">{{ $user->handicap }}</dd>
                            </div>
                            @endif
                            
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Joined:</dt>
                                <dd class="font-medium">{{ $user->created_at->format('M d, Y') }}</dd>
                            </div>
                            
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Last Login:</dt>
                                <dd class="font-medium">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </dd>
                            </div>
                            
                            @if($user->last_login_ip)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Last IP:</dt>
                                <dd class="font-medium">{{ $user->last_login_ip }}</dd>
                            </div>
                            @endif
                            
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Email Verified:</dt>
                                <dd class="font-medium">
                                    @if($user->email_verified_at)
                                        <span class="text-green-600">Yes</span>
                                    @else
                                        <span class="text-red-600">No</span>
                                        <form action="{{ route('admin.users.verify-email', $user) }}" method="POST" class="inline ml-2">
                                            @csrf
                                            <button type="submit" class="text-xs text-blue-600 hover:underline">
                                                Verify Now
                                            </button>
                                        </form>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if($user->bio)
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm text-gray-600">Bio:</p>
                            <p class="text-sm mt-1">{{ $user->bio }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Ban Information -->
                    @if($user->is_banned)
                    <div class="bg-red-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-red-800 mb-3">Ban Information</h3>
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-red-600">Banned At:</dt>
                                <dd class="font-medium">{{ $user->banned_at->format('M d, Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-red-600">Reason:</dt>
                                <dd class="font-medium">{{ $user->banned_reason }}</dd>
                            </div>
                        </dl>
                    </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3">Quick Actions</h3>
                        <div class="space-y-2">
                            <button onclick="showResetPasswordModal()" 
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-left">
                                <i class="fas fa-key mr-2"></i>Reset Password
                            </button>
                            
                            <button onclick="showSendEmailModal()" 
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-left">
                                <i class="fas fa-envelope mr-2"></i>Send Email
                            </button>
                            
                            @if(!$user->email_verified_at)
                            <form action="{{ route('admin.users.verify-email', $user) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-left">
                                    <i class="fas fa-check mr-2"></i>Verify Email
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Middle Column - Statistics -->
                <div class="space-y-6">
                    <!-- Statistics Card -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">User Statistics</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-white rounded">
                                <p class="text-2xl font-bold text-blue-600">{{ $statistics['total_reviews'] }}</p>
                                <p class="text-xs text-gray-600">Total Reviews</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded">
                                <p class="text-2xl font-bold text-green-600">{{ $statistics['approved_reviews'] }}</p>
                                <p class="text-xs text-gray-600">Approved</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded">
                                <p class="text-2xl font-bold text-yellow-600">{{ $statistics['pending_reviews'] }}</p>
                                <p class="text-xs text-gray-600">Pending</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded">
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ number_format($statistics['average_rating'] ?? 0, 1) }}
                                </p>
                                <p class="text-xs text-gray-600">Avg Rating</p>
                            </div>
                            <div class="text-center p-3