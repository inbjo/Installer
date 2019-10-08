<?php

namespace Flex\Installer\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Flex\Installer\Helpers\DatabaseManager;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        $admin = Cache::pull('admin');

        $response = $this->databaseManager->migrateAndSeed();

        //设置管理员账号密码
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

        return redirect()->route('LaravelInstaller::final')
            ->with(['message' => $response]);
    }
}
