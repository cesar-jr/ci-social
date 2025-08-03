<?php

namespace CesarJr\Social\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;

class Setup extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Social Package';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'social:setup';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = "Initial setup for CesarJr Social.";

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = "social:setup";

    /**
     * the Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * the Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '-f' => 'Force overwrite ALL existing files in destination.',
    ];

    /**
     * The path to `CesarJr\Social\` src directory.
     *
     * @var string
     */
    protected $sourcePath;

    protected $distPath = APPPATH;

    public function run(array $params)
    {
        $this->sourcePath = __DIR__ . '/../';
        $this->publishConfigSocial();
        $this->setAutoloadHelpers();
    }

    private function publishConfigSocial(): void
    {
        $file = 'Config/Social.php';
        $replaces = [
            'namespace CesarJr\\Social\\Config;'   => 'namespace Config;',
            'use CodeIgniter\\Config\\BaseConfig;' => 'use CesarJr\\Social\\Config\\Social as SocialConfig;',
            'extends BaseConfig'                   => 'extends SocialConfig',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    /**
     * @param string $file     Relative file path like 'Config/Auth.php'.
     * @param array  $replaces [search => replace]
     */
    private function copyAndReplace(string $file, array $replaces): void
    {
        $path = "{$this->sourcePath}/{$file}";

        $content = file_get_contents($path);

        $content = strtr($content, $replaces);

        $this->writeFile($file, $content);
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     *
     * @param string $file Relative file path like 'Config/Auth.php'.
     */
    private function writeFile(string $file, string $content): void
    {
        $path = $this->distPath . $file;
        $cleanPath = clean_path($path);

        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (file_exists($path)) {
            $overwrite = (bool) CLI::getOption('f');

            if (!$overwrite && CLI::prompt("  File '{$cleanPath}' already exists in destination. Overwrite?", ['n', 'y']) === 'n') {
                CLI::error("  Skipped {$cleanPath}. If you wish to overwrite, please use the '-f' option or reply 'y' to the prompt.");
                return;
            }
        }

        if (write_file($path, $content)) {
            CLI::write(CLI::color('  Created: ', 'green') . $cleanPath);
        } else {
            CLI::error("  Error creating {$cleanPath}");
        }
    }

    private function setAutoloadHelpers(): void
    {
        $file = 'Config/Autoload.php';

        $path = $this->distPath . $file;
        $cleanPath = clean_path($path);

        $config = new Autoload();

        $helpers = $config->helpers;
        $newHelpers = array_unique(array_merge($helpers, ['social']));

        $content = file_get_contents($path);
        $output = $this->updateAutoloadHelpers($content, $newHelpers);

        if ($output === $content) {
            CLI::write(CLI::color('  Autoload Setup: ', 'green') . 'Everything is fine.');

            return;
        }

        if (write_file($path, $output)) {
            CLI::write(CLI::color('  Updated: ', 'green') . $cleanPath);
        } else {
            CLI::error("  Error updating file '{$cleanPath}'.");
        }
    }

    /**
     * @param string       $content    The content of Config\Autoload.
     * @param list<string> $newHelpers The list of helpers.
     */
    private function updateAutoloadHelpers(string $content, array $newHelpers): string
    {
        $pattern = '/^    public \$helpers = \[.*?\];/msu';
        $replace = '    public $helpers = [\'' . implode("', '", $newHelpers) . '\'];';

        return preg_replace($pattern, $replace, $content);
    }
}
