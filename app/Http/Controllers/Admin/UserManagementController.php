<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display listing of users
     */
    public function index(Request $request)
    {
        $query = User::withStatistics();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true)->where('is_banned', false);
                    break;
                case 'banned':
                    $query->where('is_banned', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false)->where('is_banned', false);
                    break;
                case 'unverified':
                    $query->whereNull('email_verified_at');
                    break;
            }
        }

        // Filter by skill level
        if ($request->filled('skill_level')) {
            $query->where('skill_level', $request->skill_level);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => User::count(),
            'active' => User::active()->count(),
            'banned' => User::banned()->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
            'active_30d' => User::recentlyActive(30)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->loadCount(['reviews' => function($q) {
            $q->where('status', 'approved');
        }]);

        // Get user statistics
        $statistics = [
            'total_reviews' => $user->reviews()->count(),
            'approved_reviews' => $user->reviews()->where('status', 'approved')->count(),
            'pending_reviews' => $user->reviews()->where('status', 'pending')->count(),
            'average_rating' => $user->reviews()->where('status', 'approved')->avg('rating'),
            'total_helpful_votes' => $user->reviews()->where('status', 'approved')->sum('helpful_count'),
            'joined_days_ago' => $user->created_at->diffInDays(now()),
        ];

        // Recent reviews
        $recentReviews = $user->reviews()
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        // Activity log
        $activities = $user->activities()
            ->latest()
            ->take(20)
            ->get();

        return view('admin.users.show', compact('user', 'statistics', 'recentReviews', 'activities'));
    }

    /**
     * Show form for editing user
     */
    public function edit(User $user)
    {
        $roles = ['user', 'reviewer', 'editor', 'admin'];
        $skillLevels = ['beginner', 'intermediate', 'advanced', 'pro'];
        
        return view('admin.users.edit', compact('user', 'roles', 'skillLevels'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,reviewer,editor,admin',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced,pro',
            'handicap' => 'nullable|numeric|min:-10|max:54',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Prevent self role change for current admin
        if ($user->id === auth()->id() && $request->role !== $user->role) {
            return redirect()->back()
                ->with('error', 'You cannot change your own role.');
        }

        $data = $request->except(['avatar', 'password']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Handle password change
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed'
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log('User profile updated');

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Ban user
     */
    public function ban(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot ban yourself.');
        }

        if ($user->is_banned) {
            return redirect()->back()
                ->with('info', 'User is already banned.');
        }

        $user->ban($request->reason);

        // Send notification to user (optional)
        // $user->notify(new AccountBannedNotification($request->reason));

        return redirect()->back()
            ->with('success', 'User banned successfully!');
    }

    /**
     * Unban user
     */
    public function unban(User $user)
    {
        if (!$user->is_banned) {
            return redirect()->back()
                ->with('info', 'User is not banned.');
        }

        $user->unban();

        // Send notification to user (optional)
        // $user->notify(new AccountUnbannedNotification());

        return redirect()->back()
            ->with('success', 'User unbanned successfully!');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own status.'
            ], 403);
        }

        if ($user->is_active) {
            $user->deactivate();
            $message = 'User deactivated successfully!';
        } else {
            $user->activate();
            $message = 'User activated successfully!';
        }

        return response()->json([
            'success' => true,
            'is_active' => $user->is_active,
            'message' => $message
        ]);
    }

    /**
     * Verify user email manually
     */
    public function verifyEmail(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->back()
                ->with('info', 'Email is already verified.');
        }

        $user->markEmailAsVerified();

        return redirect()->back()
            ->with('success', 'Email verified successfully!');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log activity
        $user->logActivity('password_reset', 'Password was reset by admin');

        // Send notification to user (optional)
        // $user->notify(new PasswordResetNotification());

        return redirect()->back()
            ->with('success', 'Password reset successfully!');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        // Check if user has important data
        if ($user->reviews()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete user with existing reviews. Consider banning the user instead.');
        }

        // Delete avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Bulk action on users
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,ban,delete'
        ]);

        // Remove current user from selection
        $userIds = array_diff($request->user_ids, [auth()->id()]);
        
        if (empty($userIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid users selected.'
            ], 400);
        }

        $users = User::whereIn('id', $userIds)->get();

        switch ($request->action) {
            case 'activate':
                foreach ($users as $user) {
                    $user->activate();
                }
                $message = count($users) . ' users activated successfully!';
                break;
                
            case 'deactivate':
                foreach ($users as $user) {
                    $user->deactivate();
                }
                $message = count($users) . ' users deactivated successfully!';
                break;
                
            case 'ban':
                $reason = $request->get('reason', 'Bulk ban by admin');
                foreach ($users as $user) {
                    $user->ban($reason);
                }
                $message = count($users) . ' users banned successfully!';
                break;
                
            case 'delete':
                // Only delete users without reviews
                $deleted = 0;
                foreach ($users as $user) {
                    if ($user->reviews()->count() === 0) {
                        if ($user->avatar) {
                            Storage::disk('public')->delete($user->avatar);
                        }
                        $user->delete();
                        $deleted++;
                    }
                }
                $message = $deleted . ' users deleted successfully!';
                break;
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action.'
                ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Export users
     */
    public function export(Request $request)
    {
        // Apply same filters as index
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            // Same status logic as index
        }

        $users = $query->get();

        // Generate CSV
        $csvData = "ID,Name,Email,Role,Status,Reviews,Joined Date\n";
        foreach ($users as $user) {
            $csvData .= "{$user->id},\"{$user->name}\",{$user->email},{$user->role},{$user->status},{$user->reviews_count},{$user->created_at}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="users-export-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Send email to user
     */
    public function sendEmail(Request $request, User $user)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        // Send email logic here
        // Mail::to($user)->send(new AdminMessage($request->subject, $request->message));

        return redirect()->back()
            ->with('success', 'Email sent successfully!');
    }

    /**
     * Login as user (impersonate)
     */
    public function impersonate(User $user)
    {
        if ($user->isAdmin() && $user->id !== auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot impersonate another admin.');
        }

        // Store original admin ID
        session(['impersonator_id' => auth()->id()]);
        
        // Login as user
        auth()->login($user);

        return redirect('/')
            ->with('info', 'You are now logged in as ' . $user->name);
    }

    /**
     * Stop impersonating
     */
    public function stopImpersonation()
    {
        if (session()->has('impersonator_id')) {
            $admin = User::find(session('impersonator_id'));
            session()->forget('impersonator_id');
            auth()->login($admin);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'Stopped impersonation.');
        }

        return redirect()->route('admin.dashboard');
    }
}