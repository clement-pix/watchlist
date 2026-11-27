<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([1 => 'Super administrateur', 2 => 'Membre', 3 => 'Gestionnaire'] as $id => $label) {
            DB::table('Role')->updateOrInsert(['id_role' => $id], ['label' => $label]);
        }

        foreach (['Film', 'Série'] as $type) {
            DB::table('categorie')->updateOrInsert(['type' => $type]);
        }

        $rewards = [
            ['Première séance', '🎬', 'Ajoutez votre premier film ou votre première série.', 'watchlist', 1, 10],
            ['Collectionneur de séances', '🍿', 'Conservez 5 œuvres dans votre liste.', 'watchlist', 5, 20],
            ['Marathon du week-end', '📽️', 'Conservez 15 œuvres dans votre liste.', 'watchlist', 15, 30],
            ['Première critique', '⭐', 'Publiez votre premier avis WatchList.', 'reviews', 1, 40],
            ['Plume de cinéma', '✍️', 'Publiez 5 avis sur vos découvertes.', 'reviews', 5, 50],
            ['Critique incontournable', '🏆', 'Publiez 10 avis utiles à la communauté.', 'reviews', 10, 60],
            ['Légende WatchList', '👑', 'Atteignez le cap exceptionnel de 50 avis.', 'reviews', 50, 70],
            ['Premier échange', '💬', 'Envoyez 5 messages dans le Salon.', 'messages', 5, 80],
            ['Débat animé', '🔥', 'Envoyez 10 messages dans le Salon.', 'messages', 10, 90],
            ['Habitué du Salon', '🎭', 'Envoyez 20 messages dans le Salon.', 'messages', 20, 100],
            ['Voix de la communauté', '🌟', 'Envoyez 30 messages dans le Salon.', 'messages', 30, 110],
        ];
        foreach ($rewards as [$name, $emoji, $description, $criterion, $threshold, $order]) {
            DB::table('rewards')->updateOrInsert(['name' => $name], [
                'emoji' => $emoji,
                'description' => $description,
                'criterion_type' => $criterion,
                'threshold' => $threshold,
                'enabled' => true,
                'sort_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Le catalogue public vient désormais de TMDB : aucun faux titre n'est injecté.
    }
}
