<?php

namespace Flex\Installer\Helpers;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Database\SQLiteConnection;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Models\User;

class DatabaseManager
{
    /**
     * Migrate and seed the database.
     *
     * @return array
     */
    public function migrateAndSeed()
    {
        $outputLog = new BufferedOutput;

        $this->sqlite($outputLog);

        return $this->migrate($outputLog);
    }

    /**
     * Run the migration and call the seeder.
     *
     * @param \Symfony\Component\Console\Output\BufferedOutput $outputLog
     * @return array
     */
    private function migrate(BufferedOutput $outputLog)
    {
        try {
            Artisan::call('migrate', ['--force'=> true], $outputLog);
        } catch (Exception $e) {
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->seed($outputLog);
    }

    /**
     * Seed the database.
     *
     * @param \Symfony\Component\Console\Output\BufferedOutput $outputLog
     * @return array
     */
    private function seed(BufferedOutput $outputLog)
    {
        try {
            Artisan::call('db:seed', ['--force' => true], $outputLog);
            $admin = Cache::pull('admin');
            if($admin){
                User::updateOrCreate(
                    ['id' => 1],
                    [
                        'name' => $admin['name'],
                        'email' => $admin['email'],
                        'avatar' => generateAvatar($admin['email']),
                        'password' => bcrypt($admin['password']),
                        'bio' => '这家伙很懒什么也没写~',
                        'email_verified_at' => Carbon::now()->toDateTimeString(),
                    ]
                );
            }
        } catch (Exception $e) {
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->response(trans('installer_messages.final.finished'), 'success', $outputLog);
    }

    /**
     * Return a formatted error messages.
     *
     * @param string $message
     * @param string $status
     * @param \Symfony\Component\Console\Output\BufferedOutput $outputLog
     * @return array
     */
    private function response($message, $status, BufferedOutput $outputLog)
    {
        return [
            'status' => $status,
            'message' => $message,
            'dbOutputLog' => $outputLog->fetch(),
        ];
    }

    /**
     * Check database type. If SQLite, then create the database file.
     *
     * @param \Symfony\Component\Console\Output\BufferedOutput $outputLog
     */
    private function sqlite(BufferedOutput $outputLog)
    {
        if (DB::connection() instanceof SQLiteConnection) {
            $database = DB::connection()->getDatabaseName();
            if (! file_exists($database)) {
                touch($database);
                DB::reconnect(Config::get('database.default'));
            }
            $outputLog->write('Using SqlLite database: '.$database, 1);
        }
    }
}
