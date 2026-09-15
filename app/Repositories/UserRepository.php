<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getNewUserCount(int $days): int
    {
        return $this->model->where('created_at', '>=', now()->subDays($days))->count();
    }

    public function incrementVersion(User $user): void
    {
        $user->incrementVersion();
    }

    /**
     * Get filtered users for admin panel
     */
    public function getFilteredUsers(array $filters = [], int $perPage = 15, $showTrashed = false): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Handle trashed users
        if ($showTrashed) {
            $query->onlyTrashed();
        }

        // Search functionality - search by name, email, or ID
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];

            $query->where(function ($q) use ($searchTerm) {
                // Search by ID (exact match)
                if (is_numeric($searchTerm)) {
                    $q->where('id', $searchTerm);
                }

                // Search by name or email (partial match)
                $q->orWhere('first_name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter by role
        if (isset($filters['role']) && !empty($filters['role'])) {
            $query->where('user_role', $filters['role']);
        }

        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
