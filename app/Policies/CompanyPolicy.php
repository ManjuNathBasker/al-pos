<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can switch to the company.
     */
    public function switch(User $user, Company $company): bool
    {
        // 1. Super Admin can switch to any company
        if ($user->isAdmin()) {
            return true;
        }

        // 2. Owner can switch to any company they own
        if ($company->owner_id === $user->id) {
            return true;
        }

        // 3. Employee can switch to any company they are assigned to
        return $user->companies()->where('companies.id', $company->id)->exists();
    }

    /**
     * Standard view/update/delete checks
     */
    public function view(User $user, Company $company): bool
    {
        return $this->switch($user, $company);
    }

    public function update(User $user, Company $company): bool
    {
        return $user->isAdmin() || $company->owner_id === $user->id;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }
}
