<?php

namespace Flex\Installer\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Flex\Installer\Helpers\DatabaseManager;
use Illuminate\Support\Carbon;

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
        $admin = session('admin');

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
