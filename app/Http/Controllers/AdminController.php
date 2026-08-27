<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * List all administrator accounts.
     */
    public function index(): View
    {
        $admins = User::withCount('batches')->latest()->get();

        return view('admins.index', ['admins' => $admins]);
    }

    /**
     * Create a new administrator account.
     */
    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'local';

        User::create([
            'name' => $request->validated('name'),
            'username' => $request->validated('username'),
            'email' => $request->validated('username')."@{$host}",
            'password' => $request->validated('password'),
        ]);

        return redirect()->route('admins.index')
            ->with('status', 'Administrator added.');
    }
}
