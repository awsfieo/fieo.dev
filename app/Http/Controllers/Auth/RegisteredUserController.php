<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Public self-registration roles only (NOT internal roles)
        $allowedRoles = [
            'Exporter',
            'Importer',
            'Supplier',
            'Embassy',
            'EPC',
            'Trade Chamber',
            'Govt Official',
            'Bank',
            'MoU Partner',
            'EXIM Expert',
            'Student',
            'Job Aspirant',
            'Others',
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
                // Block @fieo.org registrations
                function ($attribute, $value, $fail) {
                    if (str_ends_with(strtolower($value), '@fieo.org')) {
                        $fail('Registration using @fieo.org email addresses is not permitted.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'register_as' => ['required', 'string', 'in:' . implode(',', $allowedRoles)],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign selected Spatie role (secure allow-list enforced above)
        $user->assignRole($request->register_as);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
