<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'route_name',
        'icon',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relación con el menú padre
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Relación con los menús hijos
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    /**
     * Relación con roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'menu_role');
    }

    /**
     * Scope para obtener solo menús activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope para ordenar menús
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope para obtener solo menús de nivel superior (sin padre)
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope para filtrar menús por usuario
     * Si el menú no tiene roles asignados, es visible para todos
     * Si tiene roles, solo es visible si el usuario tiene alguno de esos roles
     */
    public function scopeForUser(Builder $query, $user): Builder
    {
        if (!$user) {
            return $query->whereDoesntHave('roles');
        }

        return $query->where(function ($q) use ($user) {
            $q->whereDoesntHave('roles')
              ->orWhereHas('roles', function ($roleQuery) use ($user) {
                  $roleQuery->whereIn('roles.id', $user->roles->pluck('id'));
              });
        });
    }
}
