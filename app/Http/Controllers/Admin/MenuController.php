<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::with(['parent', 'children', 'roles'])
            ->ordered()
            ->get();

        $access = [
            'createMenu' => auth()->user()->can('Add Menu'),
        ];

        return Inertia::render('Admin/Menu/List', [
            'menus' => $menus,
            'access' => $access,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentMenus = Menu::whereNull('parent_id')->ordered()->get();
        $roles = Role::all();

        return Inertia::render('Admin/Menu/Create', [
            'parentMenus' => $parentMenus,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route_name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $menu = Menu::create([
            'name' => $validated['name'],
            'route_name' => $validated['route_name'],
            'icon' => $validated['icon'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'order' => $validated['order'],
            'active' => $validated['active'] ?? true,
        ]);

        if (isset($validated['roles'])) {
            $menu->roles()->sync($validated['roles']);
        }

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menú creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $menu->load(['roles']);
        $parentMenus = Menu::whereNull('parent_id')
            ->where('id', '!=', $menu->id)
            ->ordered()
            ->get();
        $roles = Role::all();

        return Inertia::render('Admin/Menu/Update', [
            'menu' => $menu,
            'parentMenus' => $parentMenus,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route_name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Prevent circular reference
        if ($validated['parent_id'] == $menu->id) {
            return back()->withErrors(['parent_id' => 'Un menú no puede ser su propio padre']);
        }

        $menu->update([
            'name' => $validated['name'],
            'route_name' => $validated['route_name'],
            'icon' => $validated['icon'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'order' => $validated['order'],
            'active' => $validated['active'] ?? true,
        ]);

        if (isset($validated['roles'])) {
            $menu->roles()->sync($validated['roles']);
        } else {
            $menu->roles()->detach();
        }

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menú actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menú eliminado exitosamente');
    }
}
