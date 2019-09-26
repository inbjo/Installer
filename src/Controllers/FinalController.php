<?php

namespace Flex\Installer\Controllers;

use Illuminate\Routing\Controller;
use Flex\Installer\Helpers\EnvironmentManager;
use Flex\Installer\Helpers\FinalInstallManager;
use Flex\Installer\Helpers\InstalledFileManager;
use Flex\Installer\Events\LaravelInstallerFinished;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @param \Flex\Installer\Helpers\InstalledFileManager $fileManager
     * @param \Flex\Installer\Helpers\FinalInstallManager $finalInstall
     * @param \Flex\Installer\Helpers\EnvironmentManager $environment
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return view('vendor.installer.finished', compact('finalMessages', 'finalStatusMessage', 'finalEnvFile'));
    }
}
