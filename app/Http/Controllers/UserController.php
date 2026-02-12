<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $users = User::query()
            ->with('roles:id,name')
            ->latest('id')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'personal' => $user->personal,
                'role' => $user->roles->first()?->name,
                'created_at' => $user->created_at?->toDateTimeString(),
            ]);

        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name');

        return Inertia::render('users/index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return to_route('users.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'personal' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'personal' => $validated['personal'] ?? null,
            'password' => $validated['password'],
        ]);

        $user->syncRoles([$validated['role']]);

        return to_route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $userId)
    {
        return to_route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $userId)
    {
        return to_route('users.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $userId)
    {
        $user = User::query()->findOrFail($userId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'personal' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'personal' => $validated['personal'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);
        $user->syncRoles([$validated['role']]);

        return to_route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $userId)
    {
        $user = User::query()->findOrFail($userId);

        if ($request->user()?->id === $user->id) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar tu propio usuario.',
            ]);
        }

        $user->delete();

        return to_route('users.index');
    }
}
