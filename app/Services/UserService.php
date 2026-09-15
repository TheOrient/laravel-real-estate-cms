<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService implements ServiceInterface
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getAllPaginated($perPage);
    }

    /**
     * Get filtered users for admin panel
     */
    public function getFilteredUsers(array $filters = [], int $perPage = 15, $showTrashed = false): LengthAwarePaginator
    {
        return $this->userRepository->getFilteredUsers($filters, $perPage, $showTrashed);
    }

    public function getNewUserCount(int $days): int
    {
        return $this->userRepository->getNewUserCount($days);
    }

    public function createUser(array $data): User
    {
        return $this->userRepository->create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        $passwordChanged = !empty($data['password']);

        if ($passwordChanged) {
            $this->userRepository->incrementVersion($user);
        }

        return $this->userRepository->update($data, $user->id);
    }

    /**
     * Soft delete user (for normal users and admin panel)
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete(); // This will soft delete
    }

    /**
     * Permanent delete user (admin only)
     */
    public function forceDeleteUser(User $user): bool
    {
        return $user->forceDelete(); // This will permanently delete
    }

    /**
     * Restore soft deleted user (admin only)
     */
    public function restoreUser(User $user): bool
    {
        return $user->restore();
    }

    public function getTotalUserCount(): int
    {
        return $this->userRepository->all()->count();
    }

}
