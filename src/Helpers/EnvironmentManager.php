<?php

namespace Flex\Installer\Helpers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnvironmentManager
{
    /**
     * @var string
     */
    private $envPath;

    /**
     * @var string
     */
    private $envExamplePath;

    /**
     * Set the .env and .env.example paths.
     */
    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');
    }

    /**
     * Get the content of the .env file.
     *
     * @return string
     */
    public function getEnvContent()
    {
        if (!file_exists($this->envPath)) {
            if (file_exists($this->envExamplePath)) {
                copy($this->envExamplePath, $this->envPath);
            } else {
                touch($this->envPath);
            }
        }

        return file_get_contents($this->envPath);
    }

    /**
     * Get the the .env file path.
     *
     * @return string
     */
    public function getEnvPath()
    {
        return $this->envPath;
    }

    /**
     * Get the the .env.example file path.
     *
     * @return string
     */
    public function getEnvExamplePath()
    {
        return $this->envExamplePath;
    }

    /**
     * Save the edited content to the .env file.
     *
     * @param Request $input
     * @return string
     */
    public function saveFileClassic(Request $input)
    {
        $message = trans('installer_messages.environment.success');

        try {
            file_put_contents($this->envPath, $input->get('envConfig'));
        } catch (Exception $e) {
            $message = trans('installer_messages.environment.errors');
        }

        return $message;
    }

    /**
     * Save the form content to the .env file.
     *
     * @param Request $request
     * @return string
     */
    public function saveFileWizard(Request $request)
    {
        $results = trans('installer_messages.environment.success');
        logger($request->all());
        $envFileData = [
            'APP_ENV' => 'production',
            'APP_URL' => $request->app_url,
            'DB_HOST' => $request->database_hostname,
            'DB_PORT' => $request->database_port,
            'DB_DATABASE' => $request->database_name,
            'DB_USERNAME' => $request->database_username,
            'DB_PASSWORD' => $request->database_password,
            'REDIS_HOST' => $request->redis_hostname,
            'REDIS_PASSWORD' => empty($request->redis_password) ? 'null' : $request->redis_password,
            'REDIS_PORT' => $request->redis_port,
            'SESSION_DRIVER' => 'redis',
            'CACHE_DRIVER' => 'redis',
        ];
        try {
            $contentArray = collect(file($this->envPath, FILE_IGNORE_NEW_LINES));
            $contentArray->transform(function ($item) use ($envFileData) {
                foreach ($envFileData as $key => $value) {
                    if (Str::contains($item, $key)) {
                        return $key . '=' . $this->getValue($value);
                    }
                }
                return $item;
            });
            $content = implode($contentArray->toArray(), "\n");
            file_put_contents($this->envPath, $content);
        } catch (Exception $e) {
            $results = trans('installer_messages.environment.errors');
        }

        return $results;
    }

    /**
     * check value is need add ""
     * @param $value
     * @return string
     */
    private function getValue($value)
    {
        if (Str::contains($value, ' ')) {
            return '"' . $value . '"';
        }
        if (Str::contains($value, '#')) {
            return '"' . $value . '"';
        }
        return $value;
    }
}
