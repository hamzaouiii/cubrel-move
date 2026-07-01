<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Users\SetupTokenService;
use App\Support\Settings;
use App\Support\Users\AccountRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SetupController extends Controller
{
    public function __construct(private SetupTokenService $tokens) {}

    protected function supportedLocales(): array
    {
        $locales = json_decode((string) Settings::get('enabled_languages'), true);

        return is_array($locales) && $locales !== [] ? $locales : ['en', 'de'];
    }

    public function show(string $token, Request $request): InertiaResponse|RedirectResponse
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        $setupToken = $this->tokens->validate($token);
        $locale = $request->query('locale');

        if (in_array($locale, $this->supportedLocales(), true)) {
            App::setLocale($locale);
        } else {
            $locale = null;
        }

        return Inertia::render('Setup', [
            'token'   => $token,
            'invalid' => $setupToken === null,
            'locale'  => $locale,
        ]);
    }

    public function store(string $token, Request $request): RedirectResponse
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        $setupToken = $this->tokens->validate($token);
        abort_if($setupToken === null, 410, 'This setup link has expired or already been used.');

        $data = $request->validate(array_merge(
            AccountRules::newAccount(),
            [
                'email'  => ['required', 'email', 'unique:users,email'],
                'locale' => ['nullable', 'in:' . implode(',', $this->supportedLocales())],
            ]
        ));

        $user = User::createFromAccountForm($data, ['is_admin' => true, 'is_root' => true]);

        if (! empty($data['locale'])) {
            Settings::set('app_locale', $data['locale']);
        }

        $this->tokens->consume($setupToken);

        Auth::login($user);

        return redirect()->route('onboarding.show');
    }
}
