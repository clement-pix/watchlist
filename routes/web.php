<?php

use App\Http\Controllers\AvisController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ComposerController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UtilisateursController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\FeaturedContentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MediaController::class, 'welcome'])->name('welcome');
Route::get('/catalogue/{type}/{id}', [MediaController::class, 'catalogueShow'])
    ->whereIn('type', ['movie', 'tv'])->whereNumber('id')->name('catalogue.show');
Route::get('/media/{id}', [MediaController::class, 'show'])->whereNumber('id')->name('media.show');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [InscriptionController::class, 'formulaire'])->name('register');
    Route::post('/inscription', [InscriptionController::class, 'traitement'])->name('register.store');
    Route::get('/connexion', [ConnexionController::class, 'formulaire'])->name('login');
    // FR : limite les tentatives avant la vérification du mot de passe.
    // EN: Limits attempts before checking the password.
    Route::post('/connexion', [ConnexionController::class, 'traitement'])->middleware('throttle:10,1')->name('login.attempt');
    Route::get('/connexion/verification', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
    Route::post('/connexion/verification', [TwoFactorChallengeController::class, 'verify'])->middleware('throttle:6,1')->name('two-factor.verify');
    Route::post('/connexion/verification/renvoyer', [TwoFactorChallengeController::class, 'resend'])->middleware('throttle:3,1')->name('two-factor.resend');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/superadmin', [DashboardController::class, 'superadmin'])->middleware('role:1')->name('dashboard.superadmin');
    Route::get('/dashboard/membreadmin', [DashboardController::class, 'membreadmin'])->middleware('role:1,3')->name('dashboard.membreadmin');
    Route::get('/dashboard/membre', [DashboardController::class, 'membre'])->middleware('role:2')->name('dashboard.membre');

    Route::get('/compte/parametres', [AccountSettingsController::class, 'index'])->name('account.settings');
    Route::put('/compte/profil', [AccountSettingsController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/compte/preferences', [AccountSettingsController::class, 'updatePreferences'])->name('account.preferences.update');
    Route::put('/compte/mot-de-passe', [AccountSettingsController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/compte/double-authentification', [AccountSettingsController::class, 'startTwoFactor'])->middleware('throttle:3,1')->name('account.two-factor.start');
    Route::post('/compte/double-authentification/confirmer', [AccountSettingsController::class, 'confirmTwoFactor'])->middleware('throttle:6,1')->name('account.two-factor.confirm');
    Route::delete('/compte/double-authentification', [AccountSettingsController::class, 'disableTwoFactor'])->name('account.two-factor.disable');
    Route::delete('/compte', [AccountSettingsController::class, 'destroy'])->name('account.destroy');
    Route::get('/membres/{user}', [CommunityController::class, 'profile'])->name('community.profile');
    Route::post('/compte/recompenses/{reward}', [CommunityController::class, 'equip'])->name('community.rewards.equip');
    Route::delete('/compte/recompenses', [CommunityController::class, 'unequip'])->name('community.rewards.unequip');

    Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/messages', [ChatController::class, 'store'])->middleware('throttle:5,1')->name('chat.store');
    Route::delete('/chat/messages/{message}', [ChatController::class, 'destroy'])->name('chat.destroy');
    Route::post('/chat/messages/{message}/reactions/{reward}', [ChatController::class, 'react'])->middleware('throttle:20,1')->name('chat.react');
    Route::post('/signalements/messages/{message}', [ReportController::class, 'message'])->middleware('throttle:5,1')->name('reports.messages.store');
    Route::post('/signalements/avis/{media}/{user}', [ReportController::class, 'review'])->middleware('throttle:5,1')->name('reports.reviews.store');

    Route::post('/media/{media}/comment', [AvisController::class, 'store'])->name('comment.store');
    Route::get('/media/{media}/avis/edit', [AvisController::class, 'edit'])->name('comment.edit');
    Route::put('/media/{media}/avis', [AvisController::class, 'update'])->name('comment.update');

    Route::get('/dashboard/liste-privee', [ComposerController::class, 'index'])->name('liste-privee.index');
    Route::post('/dashboard/liste-privee', [ComposerController::class, 'store'])->name('liste-privee.store');
    Route::post('/catalogue/{type}/{id}/liste', [ComposerController::class, 'storeExternal'])
        ->whereIn('type', ['movie', 'tv'])->whereNumber('id')->name('liste-privee.external.store');
    Route::post('/catalogue/{type}/{id}/avis', [ComposerController::class, 'prepareExternalReview'])
        ->whereIn('type', ['movie', 'tv'])->whereNumber('id')->name('catalogue.review.prepare');
    Route::delete('/dashboard/liste-privee/{id_media}', [ComposerController::class, 'destroy'])->name('liste-privee.destroy');
});

Route::middleware(['auth', 'role:1,3'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::delete('/admin/avis/{media}/{user}', [AdminController::class, 'destroyReview'])
        ->whereNumber('media')->whereNumber('user')->name('admin.reviews.destroy');
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::get('/media/create', [MediaController::class, 'create'])->name('media.create');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{id}', [MediaController::class, 'destroy'])->whereNumber('id')->name('media.destroy');
    Route::post('/admin/chat/{user}/suspendre', [ChatController::class, 'suspend'])->name('admin.chat.suspend');
    Route::post('/admin/chat/{user}/bannir', [ChatController::class, 'ban'])->name('admin.chat.ban');
    Route::delete('/admin/chat/{user}/suspendre', [ChatController::class, 'unsuspend'])->name('admin.chat.unsuspend');
    Route::get('/admin/moderation', [ModerationController::class, 'index'])->name('admin.moderation.index');
    Route::put('/admin/moderation/{report}', [ModerationController::class, 'handle'])->name('admin.moderation.handle');
    Route::get('/admin/mises-en-avant', [FeaturedContentController::class, 'index'])->name('admin.featured.index');
    Route::post('/admin/mises-en-avant', [FeaturedContentController::class, 'store'])->name('admin.featured.store');
    Route::put('/admin/mises-en-avant/{featuredContent}', [FeaturedContentController::class, 'update'])->name('admin.featured.update');
    Route::delete('/admin/mises-en-avant/{featuredContent}', [FeaturedContentController::class, 'destroy'])->name('admin.featured.destroy');
});

Route::middleware(['auth', 'role:1'])->group(function () {
    Route::get('/utilisateurs', [UtilisateursController::class, 'index'])->name('users.index');
    Route::get('/utilisateurs/{user}/edit', [UtilisateursController::class, 'edit'])->name('users.edit');
    Route::put('/utilisateurs/{user}', [UtilisateursController::class, 'update'])->name('users.update');
    Route::delete('/utilisateurs/{user}', [UtilisateursController::class, 'destroy'])->name('users.destroy');
    Route::get('/admin/recompenses', [RewardController::class, 'index'])->name('admin.rewards.index');
    Route::post('/admin/recompenses', [RewardController::class, 'store'])->name('admin.rewards.store');
    Route::put('/admin/recompenses/{reward}', [RewardController::class, 'update'])->name('admin.rewards.update');
    Route::delete('/admin/recompenses/{reward}', [RewardController::class, 'destroy'])->name('admin.rewards.destroy');
});
