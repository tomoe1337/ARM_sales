<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserOrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Автоматически фильтрует пользователей по организации и отделу
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Не применяем scope если пользователь не аутентифицирован
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        
        // Не применяем scope если у пользователя нет organization_id
        if (!$user || !$user->organization_id) {
            return;
        }

        $table = $model->getTable();
        
        // Супер-админ организации видит всех пользователей своей организации
        if ($user->isOrganizationAdmin()) {
            $builder->where($table . '.organization_id', $user->organization_id);
        }
        // Руководитель отдела видит всех пользователей своего отдела
        elseif ($user->isHead()) {
            $builder->where($table . '.organization_id', $user->organization_id)
                    ->where($table . '.department_id', $user->department_id);
        }
        // Менеджер видит только себя
        else {
            $builder->where($table . '.id', $user->id);
        }
    }
}
