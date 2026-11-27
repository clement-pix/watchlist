<?php

namespace Tests\Feature;

use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    public function test_moderator_can_resolve_a_report_only_once(): void
    {
        // FR : réutilise la migration réelle sur la base de test en mémoire.
        // EN: Reuse the production migration on the in-memory test database.
        $migration = require database_path('migrations/2026_10_20_200000_add_reports_and_moderation_logs.php');
        $migration->up();

        $report = Report::create([
            'reporter_id' => 20, 'target_type' => 'chat_message',
            'target_id' => '42', 'reason' => 'spam', 'target_snapshot' => 'Message de test',
        ]);
        $moderator = new User(['id_role' => 3]);
        $moderator->id = 21;
        $url = route('admin.moderation.handle', $report);
        $payload = ['status' => 'dismissed', 'resolution' => 'Signalement vérifié'];

        $this->actingAs($moderator)->putJson($url, ['status' => 'dismissed'])
            ->assertUnprocessable()->assertJsonValidationErrors('resolution');
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'pending', 'handled_by' => null]);
        $this->assertDatabaseCount('moderation_logs', 0);

        $this->actingAs($moderator)->put($url, $payload)->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'dismissed', 'handled_by' => 21]);
        $log = ModerationLog::sole();
        $this->assertSame('report_dismissed', $log->action);
        $this->assertSame($report->id, $log->metadata['report_id']);
        $this->putJson($url, $payload)->assertUnprocessable();
        $this->assertDatabaseCount('moderation_logs', 1);
    }
}
