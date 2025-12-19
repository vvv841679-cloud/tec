<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Enums\CustomerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\CompleteRegisterRequest;
use App\Models\Customer;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function registerForm()
    {
        if (auth('customer')->check()) {
            return redirect()->route('home');
        }
        return inertia('Customer/Auth/Register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => [
                'required', 'email', 'max:255', Rule::unique('customers', 'email')
                    ->where(fn($query) => $query->where('is_complete', 1))
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'numeric', 'unique:customers,mobile'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        // Crear el cliente completo directamente
        $customer = Customer::create([
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'mobile' => $data['mobile'] ?? null,
            'password' => $data['password'],
            'email_verified_at' => now(), // Verificación automática
            'is_complete' => true,
            'status' => CustomerStatus::Active,
        ]);

        auth('customer')->login($customer);

        return redirect()->intended(route('home'));
    }

    public function completeRegisterForm()
    {
        return inertia('Customer/Auth/CompleteRegister');
    }

    public function completeRegister(CompleteRegisterRequest $request)
    {
        $customer = auth('customer')->user();

        $data = $request->validated();
        $data = [
            ...$data,
            'is_complete' => true,
            'status' => CustomerStatus::Active,
        ];

        $customer->update($data);

        return redirect()->intended(route('home'));
    }

    public function backRegister()
    {
        $customer = auth('customer')->user();
        auth('customer')->logout();
        return redirect()->route('register')->with('old', ['email' => $customer->email]);
    }
}
