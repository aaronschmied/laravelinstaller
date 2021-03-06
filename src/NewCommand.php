<?php

namespace Laravel\Installer\Console;

use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use ZipArchive;

class NewCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Laravel application')
            ->addArgument('name', InputArgument::OPTIONAL)
            ->addOption('dev', null, InputOption::VALUE_NONE, 'Installs the latest "development" release')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!extension_loaded('zip')) {
            throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
        }

        $directory = ($input->getArgument('name')) ? getcwd() . '/' . $input->getArgument('name') : getcwd();

        if (!$input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        $output->writeln('<info>Crafting application...</info>');

        $this->download($zipFile = $this->makeFilename(), $this->getVersion($input))
            ->extract($zipFile, $directory)
            ->prepareWritableDirectories($directory, $output)
            ->cleanUp($zipFile)
            ->copyTemplateFiles($directory, $output)
            ->updateAppConfig($directory, $output);


        $composer = $this->findComposer();

        $commands = [
            $composer . ' install --no-scripts',
            $composer . ' run-script post-root-package-install',
            $composer . ' run-script post-create-project-cmd',
            $composer . ' run-script post-autoload-dump',
            $composer . ' require --dev squizlabs/php_codesniffer',
            $composer . ' require --dev shipping-docker/vessel',
            'php artisan vendor:publish --provider="Vessel\VesselServiceProvider"',
            'bash vessel init',
            'yarn',
        ];

        if ($input->getOption('no-ansi')) {
            $commands = array_map(function ($value) {
                return $value . ' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet')) {
            $commands = array_map(function ($value) {
                return $value . ' --quiet';
            }, $commands);
        }

        $process = new Process(implode(' && ', $commands), $directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        $this->updateEnvFile($directory, $output);

        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param string $directory
     *
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Update the configuration for the app.
     *
     * @param                 $appDirectory
     * @param OutputInterface $output
     *
     * @return $this
     */
    protected function updateAppConfig($appDirectory, OutputInterface $output)
    {
        $projectName = explode(DIRECTORY_SEPARATOR, $appDirectory);
        $projectName = $projectName[sizeof($projectName) - 1];

        $output->writeln('<info>' . $projectName . ' is being configured</info>');

        $this
            ->fileReplaceString($appDirectory . '/docker-compose.yml', '[PROJECT]', strtolower($projectName))
            ->fileReplaceString($appDirectory . '/config/app.php', "'timezone' => 'UTC',", "'timezone' => 'Europe/Zurich',")
            ->fileReplaceString($appDirectory . '/config/app.php', "'locale' => 'en',", "'locale' => 'de',")
            ->fileReplaceString($appDirectory . '/config/app.php', "'faker_locale' => 'en_US',", "'faker_locale' => 'de_CH',")
            ->fileReplaceString($appDirectory . '/config/app.php', "// App\Providers\BroadcastServiceProvider::class,", "App\Providers\BroadcastServiceProvider::class,");

        (new Filesystem())->appendToFile($appDirectory . '/.gitignore', 'laravel-echo-server.json');

        return $this;
    }

    /**
     * Replace a string in a given file.
     *
     * @param $file
     * @param $search
     * @param $replace
     *
     * @return $this
     */
    protected function fileReplaceString($file, $search, $replace)
    {
        $fileContent = file_get_contents($file);
        $fileContent = str_replace($search, $replace, $fileContent);
        (new Filesystem())->dumpFile($file, $fileContent);

        return $this;
    }

    /**
     * Copy the templates into the newly created app.
     *
     * @param                 $appDirectory
     * @param OutputInterface $output
     *
     * @return $this
     */
    protected function copyTemplateFiles($appDirectory, OutputInterface $output)
    {
        $output->writeln('<info>Copying the template files</info>');

        $templateDirectory = dirname(__DIR__) . '/templates';

        $filesystem = new Filesystem;

        $filesystem->remove($appDirectory . '/resources/js');
        $filesystem->remove($appDirectory . '/resources/sass');
        $filesystem->remove($appDirectory . '/public/js');
        $filesystem->remove($appDirectory . '/public/css');

        $copyFiles = [
            'docker/app/default',
            'docker/app/h5bp/socket.io.conf',
            'resources/js',
            'resources/sass',
            'resources/lang/de',
            'laravel-echo-server.json',
            'laravel-echo-server.json.dist',
            'package.json',
            'webpack.mix.js',
            'yarn.lock',
            'docker-compose.yml',
            'phpunit.xml',
            '.gitlab-ci.yml',
        ];

        foreach ($copyFiles as $file) {
            $source = $templateDirectory . DIRECTORY_SEPARATOR . $file;
            $target = $appDirectory . DIRECTORY_SEPARATOR . $file;
            is_dir($source) ? $filesystem->mirror($source, $target) : $filesystem->copy($source, $target, true);
        }

        return $this;
    }

    /**
     * Clean-up the Zip file.
     *
     * @param string $zipFile
     *
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);

        @unlink($zipFile);

        return $this;
    }

    /**
     * Make sure the storage and bootstrap cache directories are writable.
     *
     * @param string                                            $appDirectory
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return $this
     */
    protected function prepareWritableDirectories($appDirectory, OutputInterface $output)
    {
        $filesystem = new Filesystem;

        try {
            $filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . 'bootstrap/cache', 0755, 0000, true);
            $filesystem->chmod($appDirectory . DIRECTORY_SEPARATOR . 'storage', 0755, 0000, true);
        }
        catch (IOExceptionInterface $e) {
            $output->writeln('<comment>You should verify that the "storage" and "bootstrap/cache" directories are writable.</comment>');
        }

        return $this;
    }

    /**
     * Extract the Zip file into the given directory.
     *
     * @param string $zipFile
     * @param string $directory
     *
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;

        $archive->open($zipFile);

        $archive->extractTo($directory);

        $archive->close();

        return $this;
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param string $zipFile
     * @param string $version
     *
     * @return $this
     */
    protected function download($zipFile, $version = 'master')
    {
        switch ($version) {
            case 'develop':
                $filename = 'latest-develop.zip';
                break;
            case 'master':
                $filename = 'latest.zip';
                break;
        }

        $response = (new Client)->get('http://cabinet.laravel.com/' . $filename);

        file_put_contents($zipFile, $response->getBody());

        return $this;
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd() . '/laravel_' . md5(time() . uniqid()) . '.zip';
    }

    /**
     * Get the version that should be downloaded.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return string
     */
    protected function getVersion(InputInterface $input)
    {
        if ($input->getOption('dev')) {
            return 'develop';
        }

        return 'master';
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd() . '/composer.phar';

        if (file_exists($composerPath)) {
            return '"' . PHP_BINARY . '" ' . $composerPath;
        }

        return 'composer';
    }

    /**
     * Copy the templates into the newly created app.
     *
     * @param                 $appDirectory
     * @param OutputInterface $output
     *
     * @return $this
     */
    protected function updateEnvFile($appDirectory, OutputInterface $output)
    {
        // Update the default user for the database
        $this
            ->fileReplaceString($appDirectory . '/.env', 'DB_DATABASE=laravel', 'DB_DATABASE=homestead')
            ->fileReplaceString($appDirectory . '/.env', 'DB_USERNAME=root', 'DB_USERNAME=homestead')
            ->fileReplaceString($appDirectory . '/.env', 'DB_PASSWORD=', 'DB_PASSWORD=secret');

        // Delete the env backup from vessel
        (new Filesystem())
            ->remove($appDirectory . '/.env.bak.vessel');

        return $this;
    }

}
