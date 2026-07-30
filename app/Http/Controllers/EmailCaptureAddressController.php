<?php

namespace App\Http\Controllers;

use App\Models\EmailCaptureAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmailCaptureAddressController extends Controller
{
    public function index(Request $request)
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');

        return Inertia::render('Settings/EmailCaptureAddresses/List', [
            'addresses' => EmailCaptureAddress::with('owner:id,name')
                ->orderBy('slug')
                ->get()
                ->map(fn (EmailCaptureAddress $a) => [
                    'id' => $a->id,
                    'slug' => $a->slug,
                    'label' => $a->label,
                    'owner_name' => $a->owner?->name,
                ]),
            'host' => $host,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => [
                'required', 'string', 'max:64', 'regex:/^[a-z0-9._-]+$/',
                Rule::unique('email_capture_addresses', 'slug'),
                Rule::notIn(User::pluck('username')),
            ],
            'label' => 'required|string|max:100',
            'owner_id' => 'nullable|exists:users,id',
        ], [
            'slug.regex' => 'Only lowercase letters, numbers, dots, dashes and underscores are allowed.',
            'slug.not_in' => 'That address is already taken by a user\'s personal capture address.',
        ]);

        EmailCaptureAddress::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('settings.email-capture-addresses.index')
            ->with('success', 'Address created.');
    }

    public function destroy(EmailCaptureAddress $emailCaptureAddress)
    {
        $emailCaptureAddress->delete();

        return redirect()
            ->route('settings.email-capture-addresses.index')
            ->with('success', 'Address deleted.');
    }
}
