<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller pengelolaan aparatur penyelenggara (Panitia & Dewan Juri) oleh Administrator
 */
class AdminOfficialController extends Controller
{
    /**
     * Daftar seluruh aparatur panitia dan dewan juri
     */
    public function index(Request $request): View
    {
        $roleFilter = $request->input('role', 'all');
        $search = $request->input('search');

        $query = User::with('roles')->whereHas('roles', function ($q) {
            $q->whereIn('name', [RoleEnum::Committee->value, RoleEnum::Judge->value]);
        });

        if ($roleFilter === 'committee') {
            $query->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value));
        } elseif ($roleFilter === 'judge') {
            $query->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value));
        } elseif ($roleFilter === 'dual') {
            $query->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))
                  ->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('institution', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('scout_id', 'like', "%{$search}%");
            });
        }

        $officials = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::whereHas('roles', fn ($q) => $q->whereIn('name', [RoleEnum::Committee->value, RoleEnum::Judge->value]))->count(),
            'committee' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))->count(),
            'judge' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))->count(),
            'dual' => User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Committee->value))
                          ->whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Judge->value))->count(),
        ];

        return view('admin.officials.index', compact('officials', 'stats', 'roleFilter', 'search'));
    }

    /**
     * Formulir penambahan staf aparatur baru
     */
    public function create(): View
    {
        return view('admin.officials.create');
    }

    /**
     * Simpan staf aparatur baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'scout_id' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:100'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:committee,judge'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
            'scout_id' => $validated['scout_id'] ?? null,
            'institution' => $validated['institution'] ?? 'Kwarcab Kota Padangsidimpuan',
            'position' => $validated['position'] ?? null,
            'is_active' => true,
            'qr_token' => (string) Str::uuid(),
            'qr_generated_at' => now(),
        ];

        if ($request->hasFile('avatar')) {
            $userData['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($userData);

        foreach ($validated['roles'] as $roleName) {
            $user->assignRole($roleName);
        }

        return redirect()->route('admin.officials.index')->with('success', "Aparatur {$user->name} berhasil didaftarkan.");
    }

    /**
     * Formulir edit data aparatur
     */
    public function edit(string $id): View
    {
        $official = User::with('roles')->findOrFail($id);

        return view('admin.officials.edit', compact('official'));
    }

    /**
     * Perbarui data aparatur
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $official = User::with('roles')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($official->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'scout_id' => ['nullable', 'string', 'max:50'],
            'institution' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:100'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:committee,judge'],
            'is_active' => ['required', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'scout_id' => $validated['scout_id'] ?? null,
            'institution' => $validated['institution'] ?? 'Kwarcab Kota Padangsidimpuan',
            'position' => $validated['position'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $updateData['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $official->update($updateData);

        // Sync roles (committee and/or judge)
        $roleIds = Role::whereIn('name', $validated['roles'])->pluck('id')->toArray();
        $official->roles()->sync($roleIds);

        return redirect()->route('admin.officials.index')->with('success', "Data aparatur {$official->name} berhasil diperbarui.");
    }
}
