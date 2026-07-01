<?php

namespace Tests\Feature;

use App\Mail\SetupInstanceMail;
use App\Models\SetupToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BootstrapInstanceCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_bails_when_users_already_exist(): void
    {
        User::factory()->create();

        $this->artisan('cubrel:bootstrap')
            ->expectsOutputToContain('already has users')
            ->assertExitCode(1);

        $this->assertSame(0, SetupToken::count());
    }

    public function test_prints_setup_url_when_no_users_exist(): void
    {
        $this->artisan('cubrel:bootstrap')
            ->expectsOutputToContain('/setup/')
            ->assertExitCode(0);

        $this->assertSame(1, SetupToken::count());
    }

    public function test_running_again_regenerates_a_fresh_single_token(): void
    {
        $this->artisan('cubrel:bootstrap')->assertExitCode(0);
        $this->artisan('cubrel:bootstrap')->assertExitCode(0);

        $this->assertSame(1, SetupToken::whereNull('used_at')->count());
    }

    public function test_sends_setup_email_when_address_given(): void
    {
        Mail::fake();

        $this->artisan('cubrel:bootstrap', ['email' => 'root@example.com'])
            ->expectsOutputToContain('Setup link sent to root@example.com')
            ->assertExitCode(0);

        Mail::assertSent(SetupInstanceMail::class, function (SetupInstanceMail $mail) {
            return $mail->hasTo('root@example.com') && str_contains($mail->setupUrl, '/setup/');
        });

        $this->assertSame(1, SetupToken::count());
    }

    public function test_rejects_invalid_email_address(): void
    {
        Mail::fake();

        $this->artisan('cubrel:bootstrap', ['email' => 'not-an-email'])
            ->expectsOutputToContain('not a valid email address')
            ->assertExitCode(1);

        Mail::assertNothingSent();
        $this->assertSame(0, SetupToken::count());
    }

    public function test_sends_setup_email_in_requested_locale(): void
    {
        Mail::fake();

        $this->artisan('cubrel:bootstrap', ['email' => 'root@example.com', '--locale' => 'de'])
            ->assertExitCode(0);

        Mail::assertSent(SetupInstanceMail::class, function (SetupInstanceMail $mail) {
            return $mail->locale === 'de' && str_contains($mail->setupUrl, 'locale=de');
        });
    }

    public function test_printed_url_carries_the_locale_query_param(): void
    {
        $this->artisan('cubrel:bootstrap', ['--locale' => 'de'])
            ->expectsOutputToContain('locale=de')
            ->assertExitCode(0);
    }

    public function test_printed_url_has_no_locale_param_when_omitted(): void
    {
        $this->artisan('cubrel:bootstrap')
            ->doesntExpectOutputToContain('locale=')
            ->assertExitCode(0);
    }

    public function test_rejects_unsupported_locale(): void
    {
        Mail::fake();

        $this->artisan('cubrel:bootstrap', ['email' => 'root@example.com', '--locale' => 'fr'])
            ->expectsOutputToContain('not a supported locale')
            ->assertExitCode(1);

        Mail::assertNothingSent();
        $this->assertSame(0, SetupToken::count());
    }
}
