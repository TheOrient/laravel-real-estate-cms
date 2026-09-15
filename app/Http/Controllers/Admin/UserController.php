<?php

namespace App\Http\Controllers\Admin;

use App\Constants\UserRolesConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     */
    public function index(\Illuminate\Http\Request $request)
    {
        // Get filters from request
        $filters = [
            'search' => $request->get('search'),
            'role' => $request->get('role'),
            'status' => $request->get('status'),
        ];
        $showTrashed = $request->get('show_trashed', false);

        $users = $this->userService->getFilteredUsers($filters, 15, $showTrashed);

        return view('admin.users.index', compact('users', 'showTrashed'));
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['listings' => function($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'total_listings' => $user->listings()->count(),
            'active_listings' => $user->listings()->where('is_active', true)->where('is_approved', true)->count(),
            'pending_listings' => $user->listings()->where('is_approved', false)->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     *
     * A single-agency site can still have a small internal consultant team.
     * This is deliberately separate from public registration: only an admin
     * can add an account and the form defaults to the agent role.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store an internal consultant account. Marketplace-style public users
     * remain disabled; only `agent` is accepted from this screen.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validatedWithDefaults();

        if (($data['user_role'] ?? null) !== UserRolesConstant::AGENT) {
            return back()->withInput()->withErrors([
                'user_role' => __('admin/users.only_agent_creation'),
            ]);
        }

        $this->userService->createUser($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('admin/users.agent_created_successfully'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        // Prevent editing super admin (ID 1) by other admins
        if ($user->id === 1 && auth()->id() !== 1) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', __('admin/users.cannot_edit_super_admin'));
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // Prevent editing super admin (ID 1) by other admins
        if ($user->id === 1 && auth()->id() !== 1) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', __('admin/users.cannot_edit_super_admin'));
        }

        $data = $request->validatedForUpdate();
        $this->userService->updateUser($user, $data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('admin/users.updated_successfully'));
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(User $user)
    {
        // Prevent deleting super admin (ID 1)
        if ($user->id === 1) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', __('admin/users.cannot_delete_super_admin'));
        }

        // Prevent self-deletion
        if ($user->id === \Illuminate\Support\Facades\Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', __('admin/users.cannot_delete_self'));
        }

        $this->userService->deleteUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('admin/users.moved_to_trash'));
    }

    /**
     * Permanently delete user (admin only)
     */
    public function forceDestroy($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        // Prevent deleting super admin (ID 1)
        if ($user->id === 1) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', __('admin/users.cannot_delete_super_admin'));
        }

        // Prevent self-deletion
        if ($user->id === \Illuminate\Support\Facades\Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', __('admin/users.cannot_delete_self'));
        }

        $this->userService->forceDeleteUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('admin/users.deleted_permanently'));
    }

    /**
     * Restore soft deleted user (admin only)
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->userService->restoreUser($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('admin/users.restored_successfully'));
    }
}
