<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Автоматически фильтрует данные по организации и отделу пользователя
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check() || !auth()->user()->organization_id) {
            return;
        }

        $user = auth()->user();
        $table = $model->getTable();
        
        // Super admin видит все данные (не применяем фильтр)
        if ($user->isSuperAdmin()) {
            return;
        }
        
        // Владелец организации видит все отделы своей организации
        if ($user->isOrganizationOwner()) {
            $builder->where($table . '.organization_id', $user->organization_id);
        }
        // Руководитель отдела и менеджер видят всех клиентов своего отдела
        else {
            $builder->where($table . '.organization_id', $user->organization_id)
                    ->where($table . '.department_id', $user->department_id);
        }
    }
}
