<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

try {
    Schema::disableForeignKeyConstraints();
    
    // Clear all child tables to keep relational DB consistent
    echo "Clearing child tables...\n";
    DB::table('patents')->truncate();
    DB::table('trademarks')->truncate();
    DB::table('copyrights')->truncate();
    DB::table('industrial_designs')->truncate();
    DB::table('applicants')->truncate();
    DB::table('documents')->truncate();
    DB::table('payments')->truncate();
    DB::table('appointments')->truncate();
    DB::table('chat_messages')->truncate();
    DB::table('notifications')->truncate();
    DB::table('workflow_histories')->truncate();
    DB::table('renewals')->truncate();
    DB::table('audit_logs')->truncate();
    DB::table('ip_applications')->truncate();
    
    // Fetch and delete all non-admin users
    $nonAdmins = User::where('role', '!=', 'admin')->get();
    $nonAdminsCount = $nonAdmins->count();
    
    echo "Deleting {$nonAdminsCount} non-admin users...\n";
    foreach ($nonAdmins as $user) {
        echo " - Deleting user: {$user->name} ({$user->email}, Role: {$user->role})\n";
        $user->delete();
    }
    
    Schema::enableForeignKeyConstraints();
    echo "\nSUCCESS: All non-admin users and their linked data have been removed. Only admin users remain!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
