<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_homepage_is_available_without_a_tmdb_token(): void
    {
        config(['services.tmdb.token' => null]);
        Http::preventStrayRequests();

        // FR : le schéma historique MySQL est remplacé par les tables minimales pour cette page.
        // EN: Use the minimum tables needed by this page instead of the legacy MySQL schema.
        Schema::create('media', function (Blueprint $table) {
            $table->id('id_media');
            $table->date('date_ajout')->nullable();
        });
        Schema::create('featured_contents', function (Blueprint $table) {
            $table->id();
            $table->string('placement');
            $table->boolean('active');
            $table->integer('position');
        });

        $this->get('/')->assertOk()->assertSee('WatchList');
        Http::assertNothingSent();
    }

    public function test_guest_cannot_access_the_private_watchlist(): void
    {
        $this->get(route('liste-privee.index'))->assertRedirect(route('login'));
    }

    public function test_member_cannot_access_administration(): void
    {
        // FR : le membre n'est pas enregistré ; seul son rôle est vérifié.
        // EN: The member is not persisted; only their role is checked.
        $member = new User(['id_role' => 2]);

        $this->actingAs($member)->get(route('admin.index'))->assertForbidden();
        $this->get(route('admin.moderation.index'))->assertForbidden();
    }

    public function test_two_factor_codes_expire_lock_and_cannot_be_reused(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('two_factor_code')->nullable();
            $table->timestamp('two_factor_expires_at')->nullable();
            $table->integer('two_factor_attempts')->default(0);
            $table->string('two_factor_method')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // FR : utilise un code connu sans appeler de fournisseur.
        // EN: Use a known code without contacting any delivery provider.
        $user = User::create([
            'two_factor_code' => Hash::make('123456'),
            'two_factor_expires_at' => now()->addMinute(),
        ]);
        $user->refresh();
        $service = app(TwoFactorService::class);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->assertFalse($service->verify($user, '000000'));
        }
        $this->assertFalse($service->verify($user, '123456'));
        Mail::shouldReceive('raw')->once()->withArgs(function (string $body, callable $configure) use ($user) {
            return preg_match('/\b\d{6}\b/', $body, $match)
                && Hash::check($match[0], $user->two_factor_code);
        });
        $service->issue($user);

        $user->update(['two_factor_code' => Hash::make('123456'), 'two_factor_attempts' => 0, 'two_factor_expires_at' => now()->subMinute()]);
        $this->assertFalse($service->verify($user, '123456'));
        $user->update(['two_factor_expires_at' => now()->addMinute()]);
        $this->assertTrue($service->verify($user, '123456'));
        $this->assertFalse($service->verify($user, '123456'));
    }
}
