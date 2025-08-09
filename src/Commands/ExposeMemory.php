<?php

namespace CesarJr\Social\Commands;

use CodeIgniter\CLI\CLI;

class ExposeMemory extends BaseCommand
{
    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'social:expose';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = "Expose the memory service for CesarJr Social.";

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = "social:expose";

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
    protected $options = [];

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
        $this->publishMemoryService();
        $this->addServiceMemoryToApp();
    }

    private function publishMemoryService()
    {
        $file = 'Services/SocialMemory.php';
        $replaces = [
            'namespace CesarJr\\Social\\Services;'   => 'namespace App\Services;',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    private function addServiceMemoryToApp()
    {
        if (method_exists(\Config\Services::class, "socialMemory")) {
            CLI::error("  Service socialMemory already exists.");
            return;
        }

        $file = 'Config/Services.php';

        $path = $this->distPath . $file;
        $cleanPath = clean_path($path);
        $content = file_get_contents($path);

        $serviceStr = <<<PHP
        
            public static function socialMemory(bool \$getShared = true)
            {
                if (\$getShared) {
                    return static::getSharedInstance('socialMemory');
                }

                return new \App\Services\SocialMemory();
            }

        PHP;

        if ($pos = strrpos($content, "}")) {
            $output = substr_replace($content, $serviceStr, $pos, 0);
            if (write_file($path, $output)) {
                CLI::write(CLI::color('  Updated: ', 'green') . $cleanPath);
            } else {
                CLI::error("  Error updating file '{$cleanPath}'.");
            }
        }
    }
}
