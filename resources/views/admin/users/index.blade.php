@extends('admin.layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-gray-500 text-xs uppercase">Total Users</div>
            <div class="text-2xl font-bold">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-gray-500 text-xs uppercase">Active</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-gray-500 text-xs uppercase">Banned</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($stats['banned']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-gray-500 text-xs uppercase">Unverified</div>
            <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['unverified']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-gray-500 text-xs uppercase">Admins</div>
            <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['admins']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-gray-500 text-xs uppercase">New Today</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['new_today']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-gray-500 text-xs uppercase">Active 30d</div>
            <div class="text-2xl font-bold text-indigo-600">{{ number_format($stats['active_30d']) }}</div>
        </div>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Users Management</h1>
                <p class="text-gray-600 mt-1">Manage all registered users</p>
            </div>
            <div class="flex space-x-2">
                <button onclick="exportUsers()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Name, email, phone..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Roles</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                        <option value="reviewer" {{ request('role') == 'reviewer' ? 'selected' : '' }}>Reviewer</option>
                        <option value="editor" {{ request('role') == 'editor' ? 'selected' : '' }}>Editor</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                        <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Skill Level</label>
                    <select name="skill_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">All Levels</option>
                        <option value="beginner" {{ request('skill_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ request('skill_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ request('skill_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                        <option value="pro" {{ request('skill_level') == 'pro' ? 'selected' : '' }}>Pro</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <p class="text-gray-600">Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</p>
                <div class="space-x-2">
                    <button id="bulkActivateBtn" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg hidden">
                        <i class="fas fa-check mr-2"></i>Activate
                    </button>
                    <button id="bulkBanBtn" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg hidden">
                        <i class="fas fa-ban mr-2"></i>Ban
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviews</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="user-checkbox rounded border-gray-300" value="{{ $user->id }}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="{{ $user->avatar_url }}" 
                                     alt="{{ $user->name }}"
                                     class="w-10 h-10 rounded-full mr-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        @if($user->skill_level)
                                            <span class="capitalize">{{ $user->skill_level }}</span>
                                            @if($user->handicap)
                                                • HCP {{ $user->handicap }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            @if($user->phone)
                                <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                            @endif
                            @if($user->location)
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $user->location }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                   ($user->role === 'editor' ? 'bg-blue-100 text-blue-800' : 
                                   ($user->role === 'reviewer' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $user->status_color }}-100 text-{{ $user->status_color }}-800">
                                {{ $user->status_label }}
                            </span>
                            @if($user->is_banned && $user->banned_reason)
                                <div class="text-xs text-red-600 mt-1" title="{{ $user->banned_reason }}">
                                    <i class="fas fa-info-circle"></i> Reason
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div>{{ $user->reviews_count ?? 0 }} reviews</div>
                                @if($user->reviews_avg_rating)
                                    <div class="text-xs text-gray-500">
                                        Avg: {{ number_format($user->reviews_avg_rating, 1) }} ⭐
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                            <div class="text-xs">{{ $user->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('M d, Y') }}
                                <div class="text-xs">{{ $user->last_login_at->diffForHumans() }}</div>
                            @else
                                <span class="text-gray-400">Never</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="text-green-600 hover:text-green-900"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                @if(!$user->is_banned && $user->id !== auth()->id())
                                    <button onclick="showBanModal({{ $user->id }}, '{{ $user->name }}')"
                                            class="text-orange-600 hover:text-orange-900"
                                            title="Ban">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @elseif($user->is_banned)
                                    <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-green-600 hover:text-green-900"
                                                title="Unban">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                @if($user->id !== auth()->id() && !$user->isAdmin())
                                    <button onclick="toggleStatus({{ $user->id }})"
                                            class="text-yellow-600 hover:text-yellow-900"
                                            title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                @endif
                                
                                @if($user->id !== auth()->id() && $user->reviews_count == 0)
                                    <form action="{{ route('admin.users.destroy', $user) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Ban Modal -->
<div id="banModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ban User</h3>
            <p class="text-sm text-gray-600 mb-4">
                You are about to ban: <span id="banUserName" class="font-semibold"></span>
            </p>
            <form id="banForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Ban</label>
                    <textarea name="reason" 
                              rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              required
                              placeholder="Enter reason for banning this user..."></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" 
                            onclick="closeBanModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Ban User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

// Individual checkbox
document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActivateBtn = document.getElementById('bulkActivateBtn');
    const bulkBanBtn = document.getElementById('bulkBanBtn');
    
    if (checkedBoxes.length > 0) {
        bulkActivateBtn.classList.remove('hidden');
        bulkBanBtn.classList.remove('hidden');
    } else {
        bulkActivateBtn.classList.add('hidden');
        bulkBanBtn.classList.add('hidden');
    }
}

// Show ban modal
function showBanModal(userId, userName) {
    document.getElementById('banUserName').textContent = userName;
    document.getElementById('banForm').action = `/admin/users/${userId}/ban`;
    document.getElementById('banModal').classList.remove('hidden');
}

// Close ban modal
function closeBanModal() {
    document.getElementById('banModal').classList.add('hidden');
    document.getElementById('banForm').reset();
}

// Toggle user status
function toggleStatus(userId) {
    fetch(`/admin/users/${userId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

// Bulk activate
document.getElementById('bulkActivateBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (userIds.length === 0) return;
    
    if (confirm(`Are you sure you want to activate ${userIds.length} user(s)?`)) {
        fetch('{{ route("admin.users.bulk-action") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                user_ids: userIds,
                action: 'activate'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
});

// Bulk ban
document.getElementById('bulkBanBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (userIds.length === 0) return;
    
    const reason = prompt('Enter reason for bulk ban:');
    if (!reason) return;
    
    fetch('{{ route("admin.users.bulk-action") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            user_ids: userIds,
            action: 'ban',
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
});

// Export users
function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.users.export") }}?' + params.toString();
}
</script>
@endpush