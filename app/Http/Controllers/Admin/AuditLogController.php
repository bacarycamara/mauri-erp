<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE DES LOGS
    |--------------------------------------------------------------------------
    | Le middleware auto.permission vérifie déjà 'view audit_logs'
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = AuditLog::query()
            ->with('user')
            ->latest();

        // Recherche globale
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        // Filtres avancés (Scopes Model)
        $query->action($request->action)
              ->model($request->model)
              ->userFilter($request->user_id)
              ->dateRange($request->from, $request->to);

        $logs    = $query->paginate(30)->withQueryString();
        $users   = User::select('id', 'name')->orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        // ✅ Stats pour les compteurs (toujours sur TOUTE la table, pas filtrée)
        $stats = AuditLog::selectRaw('action, count(*) as total')
                         ->groupBy('action')
                         ->pluck('total', 'action')
                         ->toArray();

        // ✅ Total global pour "· X entrées"
        $total = AuditLog::count();

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'stats', 'total'));
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL LOG
    |--------------------------------------------------------------------------
    */
    public function show(AuditLog $auditLog)
    {
        return view('admin.audit-logs.show', compact('auditLog'));
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT CSV
    |--------------------------------------------------------------------------
    | Le middleware auto.permission vérifie déjà 'export audit_logs'
    |--------------------------------------------------------------------------
    */
    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::with('user')->latest();

        // Mêmes filtres que l'index pour exporter la sélection filtrée
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $query->action($request->action)
              ->model($request->model)
              ->userFilter($request->user_id)
              ->dateRange($request->from, $request->to);

        $logs     = $query->limit(5000)->get();
        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($logs) {

            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Date',
                'Utilisateur',
                'Action',
                'Model',
                'Model ID',
                'IP Address',
            ], ';');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    optional($log->created_at)->format('d/m/Y H:i:s'),
                    $log->user?->name ?? '—',
                    $log->action      ?? '—',
                    $log->model_name  ?? '—',
                    $log->model_id    ?? '—',
                    $log->ip_address  ?? '—',
                ], ';');
            }

            fclose($handle);

        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR LOGS
    |--------------------------------------------------------------------------
    | Le middleware auto.permission vérifie déjà 'delete audit_logs'
    |--------------------------------------------------------------------------
    */
    public function clear()
    {
        AuditLog::truncate();

        return back()->with('success', 'Logs supprimés avec succès.');
    }
}