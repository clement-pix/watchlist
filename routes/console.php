<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('watchlist:test-smtp {recipient}', function () {
    $recipient = $this->argument('recipient');
    if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $this->error('Adresse de destination invalide.');
        return 1;
    }

    // FR : exige le vrai transport SMTP, sans toucher aux comptes ni aux codes 2FA.
    // EN: Require the actual SMTP transport without touching accounts or 2FA codes.
    if (config('mail.default') !== 'smtp') {
        $this->error('Configurez MAIL_MAILER=smtp avant de lancer ce test.');
        return 1;
    }

    try {
        Mail::mailer('smtp')->raw('Ceci est un message de test SMTP de WatchList.', function ($message) use ($recipient) {
            $message->to($recipient)->subject('WatchList — Test SMTP');
        });
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface) {
        $this->error('Envoi SMTP impossible. Vérifiez le serveur, le port et les identifiants.');
        return 1;
    }

    $this->info('Message accepté par le serveur SMTP. Vérifiez la boîte de réception.');
    return 0;
})->purpose('Tester l’envoi SMTP sans modifier un compte');

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
