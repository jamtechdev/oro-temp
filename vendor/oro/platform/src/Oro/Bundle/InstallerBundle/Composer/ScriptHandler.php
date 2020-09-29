<?php

namespace Oro\Bundle\InstallerBundle\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * Script handler for composer.
 * - installs npm assets, copies them to public directory
 * - sets assets version in parameters.yml
 * - sets permission on app directories
 */
class ScriptHandler
{
    /**
     * Installs npm assets
     *
     * @param Event $event A instance
     */
    public static function installAssets(Event $event)
    {
        $options = self::getOptions($event);
        $npmAssets = self::collectNpmAssets($event->getComposer());
        if (!$npmAssets) {
            return;
        }

        $filesystem = new Filesystem();

        // File package.json with actual dependencies is required for correct work of npm.
        $packageJson = [
            'description' => 'FILE IS GENERATED PROGRAMMATICALLY, NOT TRACKED BY VCS, ALL MANUAL CHANGES WILL BE LOST',
            'dependencies' => $npmAssets,
            'private' => true,
        ];
        $filesystem->dumpFile('package.json', json_encode($packageJson));

        if (!$filesystem->exists('package-lock.json')) {
            // Creates lock file, installs assets.
            self::npmInstall($event->getIO(), $options['process-timeout'], $event->isDevMode());
        } else {
            // Installs assets using lock file.
            self::npmCi($event->getIO(), $options['process-timeout'], $event->isDevMode());
        }

        self::copyAssets('node_modules', $options['symfony-web-dir'] . '/bundles/npmassets');
    }

    /**
     * Collects npm assets from extra.npm section of installed packages.
     *
     * @param Composer $composer
     *
     * @return array
     */
    private static function collectNpmAssets(Composer $composer): array
    {
        $rootPackage = $composer->getPackage();

        // Gets array of installed packages.
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

        $npmAssets = [[]];
        $packageNpm = $rootPackage->getExtra()['npm'] ?? [];
        if (is_array($packageNpm)) {
            $npmAssets[$rootPackage->getName()] = $packageNpm;
        }

        foreach ($packages as $package) {
            $packageNpm = $package->getExtra()['npm'] ?? [];

            if ($packageNpm && is_array($packageNpm) && !isset($npmAssets[$package->getName()])) {
                $npmAssets[$package->getName()] = $packageNpm;
            }
        }

        $npmAssets = array_merge(...array_values($npmAssets));

        return $npmAssets;
    }

    /**
     * Runs "npm install", updates package-lock.json, installs assets to "node_modules/"
     *
     * @param IOInterface $inputOutput
     * @param int $timeout
     * @param bool $verbose
     */
    private static function npmInstall(
        IOInterface $inputOutput,
        int $timeout = 60,
        bool $verbose = false
    ): void {
        $logLevel = $verbose ? 'info' : 'error';
        $npmInstallCmd = 'npm install --no-audit --save-exact --no-optional --loglevel ' . $logLevel;

        if (self::runProcess($inputOutput, $npmInstallCmd, $timeout) !== 0) {
            throw new \RuntimeException('Failed to generate package-lock.json');
        }
    }

    /**
     * @param IOInterface $inputOutput
     * @param string $cmd
     * @param int $timeout
     *
     * @return int
     */
    private static function runProcess(IOInterface $inputOutput, string $cmd, int $timeout): int
    {
        $inputOutput->write($cmd);

        $npmInstall = new Process($cmd, null, null, null, $timeout);
        $npmInstall->run(function ($outputType, string $data) use ($inputOutput) {
            if ($outputType === Process::OUT) {
                $inputOutput->write($data, false);
            } else {
                $inputOutput->writeError($data, false);
            }
        });

        return $npmInstall->getExitCode();
    }

    /**
     * Runs "npm ci", installs assets to "node_modules/" using only package-lock.json
     *
     * @param IOInterface $inputOutput
     * @param int $timeout
     * @param bool $verbose
     */
    private static function npmCi(IOInterface $inputOutput, int $timeout = 60, bool $verbose = false): void
    {
        $logLevel = $verbose ? 'info' : 'error';
        $npmCiCmd = 'npm ci --loglevel ' . $logLevel;

        if (self::runProcess($inputOutput, $npmCiCmd, $timeout) !== 0) {
            throw new \RuntimeException('Failed to install npm assets');
        }
    }

    /**
     * @param string $from
     * @param string $to
     */
    private static function copyAssets(string $from, string $to): void
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($from)) {
            $filesystem->remove($to);
            $filesystem->mirror($from, $to);
        }
    }

    /**
     * Set permissions for directories
     *
     * @param Event $event
     */
    public static function setPermissions(Event $event)
    {
        $options = self::getOptions($event);

        $webDir = $options['symfony-web-dir'];

        $parametersFile = self::getParametersFile($options);

        $directories = [
            'var/cache',
            'var/logs',
            'var/attachment',
            $webDir,
            $parametersFile
        ];

        $permissionHandler = new PermissionsHandler();
        foreach ($directories as $directory) {
            $permissionHandler->setPermissions($directory);
        }
        if (file_exists($importExportDir = 'var/import_export')) {
            $permissionHandler->setPermissions($importExportDir);
        }
    }

    /**
     * Sets the global assets version
     *
     * @param Event $event A instance
     */
    public static function setAssetsVersion(Event $event)
    {
        $options = self::getOptions($event);

        $parametersFile = self::getParametersFile($options);
        if (is_file($parametersFile) && is_writable($parametersFile)) {
            $values               = self::loadParametersFile($parametersFile);
            $parametersKey        = self::getParametersKey($options);
            $assetsVersionHandler = new AssetsVersionHandler($event->getIO());
            if (isset($values[$parametersKey])
                && $assetsVersionHandler->setAssetsVersion($values[$parametersKey], $options)
            ) {
                self::saveParametersFile($parametersFile, $values);
            }
        } else {
            $event->getIO()->write(
                sprintf(
                    '<comment>Cannot set assets version because "%s" file does not exist or not writable</comment>',
                    $parametersFile
                )
            );
        }
    }

    /**
     * @param string $parametersFile
     *
     * @return array
     */
    protected static function loadParametersFile($parametersFile)
    {
        $yamlParser = new Parser();

        return $yamlParser->parse(file_get_contents($parametersFile));
    }

    /**
     * @param string $parametersFile
     * @param array  $values
     */
    protected static function saveParametersFile($parametersFile, array $values)
    {
        file_put_contents(
            $parametersFile,
            "# This file is auto-generated during the composer install\n" . Yaml::dump($values, 99)
        );
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected static function getParametersFile($options)
    {
        return $options['incenteev-parameters']['file'] ?? 'config/parameters.yml';
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected static function getParametersKey($options)
    {
        return $options['incenteev-parameters']['parameter-key'] ?? 'parameters';
    }

    /**
     * @param Event $event
     * @return array
     */
    protected static function getOptions(Event $event)
    {
        $composer = $event->getComposer();
        $config = $composer->getConfig();

        return array_merge(
            ['symfony-web-dir' => 'public'],
            $composer->getPackage()->getExtra(),
            [
                'process-timeout' => $config->get('process-timeout'),
                'vendor-dir' => $config->get('vendor-dir'),
            ]
        );
    }
}