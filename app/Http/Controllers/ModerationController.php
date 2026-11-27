<?php

namespace App\Http\Controllers;

use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;
use App\Services\ModerationLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString() ?: 'pending';
        if (! in_array($status, ['pending', 'actioned', 'dismissed', 'all'], true)) $status = 'pending';
        $reports = Report::with(['reporter', 'handler'])->latest();
        if ($status !== 'all') $reports->where('status', $status);

        return view('admin.moderation', [
            'reports' => $reports->paginate(20)->withQueryString(),
            'logs' => ModerationLog::with(['moderator', 'targetUser'])->latest('created_at')->take(40)->get(),
            'currentStatus' => $status,
            'pendingCount' => Report::where('status', 'pending')->count(),
        ]);
    }

    public function handle(Request $request, Report $report, ModerationLogService $logs)
    {
        abort_unless($report->status === 'pending', 422);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['actioned', 'dismissed'])],
            'resolution' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
        $report->update([
            'status' => $validated['status'],
            'resolution' => $validated['resolution'],
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);
        $target = $report->target_author_id ? User::find($report->target_author_id) : null;
        $logs->record($request->user(), $validated['status'] === 'actioned' ? 'report_actioned' : 'report_dismissed', $target, $report->target_type, $report->target_id, $validated['resolution'], ['report_id' => $report->id]);

        return back()->with('success', 'Le signalement a été traité et ajouté à l’historique.');
    }
}
