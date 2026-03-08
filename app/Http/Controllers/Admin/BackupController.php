<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    private string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
    }

    /*
    |--------------------------------------------------------------------------
    | LISTE DES BACKUPS
    | Route: GET /admin/backups  → admin.backups.index
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }

        $files = collect(File::files($this->backupPath))
            ->sortByDesc(fn($f) => $f->getCTime())
            ->map(fn($f) => [
                'name'     => $f->getFilename(),
                'size'     => $this->formatSize($f->getSize()),
                'created'  => date('d/m/Y H:i', $f->getCTime()),
            ]);

        return view('admin.backups.index', compact('files'));
    }

    /*
    |--------------------------------------------------------------------------
    | GÉNÉRER BACKUP
    | Route: POST /admin/backups/create → admin.backups.create
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $path     = $this->backupPath . '/' . $filename;

        $db       = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host', '127.0.0.1');

        // Commande mysqldump sécurisée
        if (!empty($password)) {
            $command = "mysqldump --host={$host} --user={$user} --password={$password} {$db} > {$path} 2>&1";
        } else {
            $command = "mysqldump --host={$host} --user={$user} {$db} > {$path} 2>&1";
        }

        $result = null;
        system($command, $result);

        if ($result !== 0 || !file_exists($path) || filesize($path) === 0) {
            return back()->with('error', 'Erreur lors de la génération du backup. Vérifiez que mysqldump est installé.');
        }

        return back()->with('success', "Backup '{$filename}' généré avec succès !");
    }

    /*
    |--------------------------------------------------------------------------
    | TÉLÉCHARGER BACKUP
    | Route: GET /admin/backups/download/{file} → admin.backups.download
    |--------------------------------------------------------------------------
    */
    public function download(string $file)
    {
        // Sécurité : empêcher path traversal
        $file = basename($file);
        $path = $this->backupPath . '/' . $file;

        if (!file_exists($path)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Response::download($path);
    }

    /*
    |--------------------------------------------------------------------------
    | SUPPRIMER BACKUP
    | Route: DELETE /admin/backups/{file} → admin.backups.destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(string $file)
    {
        $file = basename($file);
        $path = $this->backupPath . '/' . $file;

        if (file_exists($path)) {
            unlink($path);
            return back()->with('success', 'Backup supprimé avec succès.');
        }

        return back()->with('error', 'Fichier introuvable.');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER — Formater la taille
    |--------------------------------------------------------------------------
    */
    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2)    . ' KB';
        return $bytes . ' B';
    }
}