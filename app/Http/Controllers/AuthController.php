<?php

namespace App\Http\Controllers;

use App\Support\SampleData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // Already logged in? Go to the correct home page.
        if ($request->session()->has('user')) {
            return redirect(SampleData::homeFor($request->session()->get('user')['role']));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:passenger,operator',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $account = SampleData::findAccount($data['username'], $data['password']);

        if (! $account) {
            return back()->withErrors(['username' => 'Wrong username/email or password.'])->withInput($request->only('username', 'role'));
        }

        // The Figma login only has Passenger and Operator tabs.
        // The admin account can sign in from the same form.
        if ($account['role'] !== 'admin' && $account['role'] !== $data['role']) {
            return back()->withErrors(['username' => 'This is a '.$account['role'].' account. Please choose the '.ucfirst($account['role']).' tab.'])->withInput($request->only('username', 'role'));
        }

        if ($account['role'] === 'operator' && empty($account['approved'])) {
            return back()->withErrors(['username' => 'Your operator account is still waiting for admin approval.'])->withInput($request->only('username', 'role'));
        }

        $request->session()->regenerate();
        $request->session()->put('user', $account);

        return redirect()->intended(SampleData::homeFor($account['role']));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:passenger,operator',
            'username' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:6|confirmed',
            'contact' => 'required|string|max:20',
            'business' => 'required_if:role,operator|nullable|string|max:100',
            'lantsa' => 'required_if:role,operator|nullable|string|max:100',
        ]);

        if (SampleData::accountExists($data['username'], $data['email'])) {
            return back()->withErrors(['username' => 'That username or email is already used.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $account = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'name' => $data['username'],
            'contact' => $data['contact'],
        ];

        $accounts = $request->session()->get('registered_accounts', []);

        if ($data['role'] === 'operator') {
            $account['business'] = $data['business'];
            $account['lantsa'] = $data['lantsa'];
            $account['approved'] = false; // an admin must approve it first
            $account['app_id'] = 'OP-2026-0'.(42 + count($accounts));
        }

        // No database yet, so we keep new accounts in the session.
        $accounts[] = $account;
        $request->session()->put('registered_accounts', $accounts);

        if ($data['role'] === 'operator') {
            return redirect()->route('login')->with('status', 'Application sent. An administrator must approve your operator account before you can log in.');
        }

        // Passenger: log in right away
        unset($account['password']);
        $request->session()->put('user', $account);

        return redirect()->route('home')->with('status', 'Welcome to SURSEA, '.$account['name'].'! Your account was created.');
    }

    public function logout(Request $request)
    {
        // We only remove the user so the sample reservations stay for testing.
        $request->session()->forget('user');
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
