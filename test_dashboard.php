<?php

use App\Models\User;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

$user = User::findOrFail(1);
Auth::setUser($user);
\Log::info('Manually set user for test', ['id' => $user->id, 'role' => $user->role]);

echo "Logged in as: " . $user->name . " (Role: " . $user->role . ")\n";

DB::enableQueryLog();
$controller = new AdminController();
try {
    $response = $controller->dashboard(new Request());
    echo "Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

foreach(DB::getQueryLog() as $log) {
    echo "SQL: " . $log['query'] . "\n";
    echo "Bindings: " . implode(', ', $log['bindings']) . "\n";
}
