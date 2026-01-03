<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        \Log::info('Attempting login with credentials:', $credentials);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $request->session()->save(); // Добавлена эта строка
            $user = Auth::user();
            \Log::info('Login successful for user:', ['user' => $user->toArray()]);

            if ($user->hasAnyRole(['manager', 'head', 'organization_owner', 'super_admin'])) {
                return redirect()->route('dashboard');
            }
        }

        \Log::info('Login failed');
        return back()->withErrors([
            'email' => 'Неверные учетные данные.',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // Создаем организацию с автоматическим названием (как в BlueSales)
        $organization = Organization::create([
            'name' => 'Организация ' . $validated['email'],
            'email' => $validated['email'],
            'is_active' => true,
            'is_single_department' => true, // По умолчанию один отдел
        ]);

        // Создаем отдел (название = название организации + " - Отдел продаж")
        $department = Department::create([
            'organization_id' => $organization->id,
            'name' => $organization->name . ' - Отдел продаж',
            'is_active' => true,
        ]);

        // Создаем пользователя (владелец организации)
        $user = User::create([
            'name' => $validated['name'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            // 'role' => ..., // ❌ УБРАНО - используем только Spatie
            'organization_id' => $organization->id,
            'department_id' => $department->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        // Назначаем роль владельца организации через Spatie
        $user->assignRole('organization_owner');

        // Загрузка аватара (если есть)
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();
        }

        // Устанавливаем руководителя отдела
        $department->update(['head_id' => $user->id]);

        // Автоматический вход
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Регистрация успешна! Вы можете переименовать организацию в настройках.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function dashboard()
    {
        $user = Auth::user();
        return view('dashboard', compact('user'));
    }
}
