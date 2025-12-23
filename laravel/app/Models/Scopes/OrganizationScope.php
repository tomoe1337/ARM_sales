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
        
        // Супер-админ организации видит все отделы своей организации
        if ($user->isOrganizationAdmin()) {
            $builder->where($table . '.organization_id', $user->organization_id);
        }
        // Руководитель отдела видит только свой отдел
        elseif ($user->isHead()) {
            $builder->where($table . '.organization_id', $user->organization_id)
                    ->where($table . '.department_id', $user->department_id);
        }
        // Менеджер видит только свои данные (если есть user_id в таблице)
        else {
            $builder->where($table . '.organization_id', $user->organization_id)
                    ->where($table . '.department_id', $user->department_id);
            
            // Если в таблице есть user_id, фильтруем по нему
            if ($builder->getModel()->getConnection()->getSchemaBuilder()->hasColumn($table, 'user_id')) {
                $builder->where($table . '.user_id', $user->id);
            }
        }
    }
}
