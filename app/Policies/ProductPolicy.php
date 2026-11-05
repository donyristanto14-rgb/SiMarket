<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Product $product): bool
    {
        return $product->status === 'active'
            || $product->user_id === $user->id
            || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_STUDENT;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->role === User::ROLE_STUDENT
            && $product->user_id === $user->id
            && $product->status !== 'archived';
    }

    public function archive(User $user, Product $product): bool
    {
        return $user->role === User::ROLE_STUDENT
            && $product->user_id === $user->id
            && $product->status !== 'archived';
    }

    public function moderate(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
