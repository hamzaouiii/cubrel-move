<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = PersonalAccessToken::with('tokenable:id,name,email')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PersonalAccessToken $token) => $this->presentToken($token));

        return Inertia::render('Settings/ApiTokens/List', [
            'tokens' => $tokens,
        ]);
    }

    public function create()
    {
        $usersModule = Module::where('slug', 'users')->firstOrFail();

        return Inertia::render('Settings/ApiTokens/Create', [
            'userFields' => $usersModule->allFields(),
            'apiModules' => $this->grantableModules(),
        ]);
    }

    /**
     * Reveals the plaintext token exactly once
     **/
    public function show(Request $request, PersonalAccessToken $token)
    {
        $plaintext = $request->session()->pull('new_api_token');

        if (! $plaintext) {
            return redirect()->route('settings.api-tokens.index');
        }

        return Inertia::render('Settings/ApiTokens/Record', [
            'token' => $this->presentToken($token),
            'plaintextToken' => $plaintext,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:100'],
            'full_access' => ['boolean'],
            'abilities' => ['array'],
            'abilities.*' => ['string'],
        ]);

        $abilities = $validated['full_access'] ?? false
            ? ['*']
            : $this->sanitizeAbilities($validated['abilities'] ?? []);

        if (empty($abilities)) {
            return back()->with('error', 'Select at least one module permission, or grant full access.');
        }

        $user = User::findOrFail($validated['user_id']);
        $token = $user->createToken($validated['name'], $abilities);

        return redirect()
            ->route('settings.api-tokens.show', $token->accessToken->id)
            ->with('success', 'API token created.')
            ->with('new_api_token', $token->plainTextToken);
    }

    public function destroy(PersonalAccessToken $token)
    {
        $token->delete();

        return redirect()
            ->route('settings.api-tokens.index')
            ->with('success', 'API token revoked.');
    }

    protected function presentToken(PersonalAccessToken $token): array
    {
        $token->loadMissing('tokenable:id,name,email');

        return [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'owner_name' => $token->tokenable?->name,
            'owner_email' => $token->tokenable?->email,
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ];
    }

    /**
     * Never trust ability strings from the request body directly
     */
    protected function sanitizeAbilities(array $requested): array
    {
        $grantable = $this->grantableModules();

        $valid = [];
        foreach ($grantable as $module) {
            foreach ($module['verbs'] as $verb) {
                $ability = "{$module['slug']}:{$verb}";
                if (in_array($ability, $requested, true)) {
                    $valid[] = $ability;
                }
            }
        }

        return $valid;
    }

    /**
     * Modules offered in the token-creation checklist.
     */
    protected function grantableModules(): array
    {
        $excluded = config('api.excluded_modules', []);

        return Module::query()
            ->where('is_active', true)
            ->whereNotIn('slug', $excluded)
            ->orderBy('name')
            ->get(['slug', 'name'])
            ->map(fn (Module $module) => [
                'slug' => $module->slug,
                'name' => $module->name,
                'verbs' => ['read', 'write', 'delete'],
            ])
            ->values()
            ->all();
    }
}
