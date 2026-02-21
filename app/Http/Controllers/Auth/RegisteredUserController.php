<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create tenant + admin user in a single transaction
        $user = DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name'   => $request->institution_name,
                'slug'   => Str::slug($request->institution_name) . '-' . Str::random(4),
                'active' => true,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
            ]);

            $user->assignRole('institution_admin');

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
