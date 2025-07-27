<?php

namespace App\Models\Scopes;

use App\Enums\Users\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CategoryScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Only apply category filtering for Category Supervisors (User models only)
        // Clients don't have user_type and should see all tickets they have access to
        if ($user instanceof User && $user->user_type === UserType::CATEGORY_SUPERVISOR) {
            // Get the category IDs that this user supervises
            $supervisedCategoryIds = $user->supervisedCategories()->pluck('id');

            if ($supervisedCategoryIds->isNotEmpty()) {
                $builder->whereIn('category_id', $supervisedCategoryIds);
            } else {
                // If user doesn't supervise any categories, they shouldn't see any tickets
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
