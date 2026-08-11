<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use App\Models\Client;
use App\Models\Message;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProjects = Project::count();
        $totalServices = Service::count();
        $totalClients  = Client::count();
        $totalMessages = Message::count();
        $unreadMessages = Message::where('is_read', false)->count();
        $recentProjects = Project::latest()->take(5)->get();
        $recentMessages = Message::latest()->take(5)->get();

        // Statistik Pesan 7 Hari Terakhir
        $messageTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Message::whereDate('created_at', $date)->count();
            $messageTrend[] = [
                'day'   => now()->subDays($i)->translatedFormat('D'),
                'count' => $count
            ];
        }

        return view('admin.dashboard', compact(
            'totalProjects', 'totalServices', 'totalClients',
            'totalMessages', 'unreadMessages', 'recentProjects', 'recentMessages',
            'messageTrend'
        ));
    }

    public function backupDb()
    {
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $property = "Tables_in_{$dbName}";
        
        $sql = "-- PT Dwi Artha Prima Database Backup\n";
        $sql .= "-- Generated at: " . now()->toDateTimeString() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$property;
            
            // Create Table
            $createTable = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE {$tableName}")[0];
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable->{'Create Table'} . ";\n\n";
            
            // Insert Data
            $rows = \Illuminate\Support\Facades\DB::table($tableName)->get();
            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $keys = array_keys($rowArray);
                $values = array_values($rowArray);
                
                $escapedValues = array_map(function($value) {
                    if ($value === null) return 'NULL';
                    return "'" . addslashes((string) $value) . "'";
                }, $values);
                
                $sql .= "INSERT INTO `{$tableName}` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            $sql .= "\n";
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;";
        
        $filename = "backup_dap_" . date('Y-m-d_His') . ".sql";
        
        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
