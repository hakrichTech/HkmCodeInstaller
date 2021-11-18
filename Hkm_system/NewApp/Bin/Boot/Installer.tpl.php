<@php
declare(strict_types=1);
namespace {VezirionNamespace}\Boot;


if (!defined('STDIN')) {
    define('STDIN', fopen('php://stdin', 'r'));
}

use Composer\Script\Event;
use Exception;

/**
 * Provides installation hooks for when this application is installed through
 * composer. Customize this class to suit your needs.
 */
class Installer
{
    /**
     * An array of directories to be made writable
     */
    public const REQUIRED_PERMISION_DIRS = [
        'App',
        'Bin',
        'vendor',
        'App/Controllers',
        'App/Filters',
        'App/Views',
        'App/Views/errors',
        'App/Views/errors/cli',
        'App/Views/errors/html',
        'Bin',
        'Bin/Boot',
        'public',
        'writable',
        'writable/cache',
        'writable/debugbar',
        'writable/logs',
        'writable/session',
        'writable/uploads',
    ];

    /**
     * Does some routine installation tasks so people don't have to.
     *
     * @param \Composer\Script\Event $event The composer event object.
     * @throws \Exception Exception raised by validator.
     * @return void
     */
    public static function postInstall(Event $event)
    {
        $io = $event->getIO();

        $rootDir = realpath(__DIR__."/../../");

        static::setFolderPermissions($rootDir, $io);

        
    }

    

    /**
     * Set globally writable permissions on the "tmp" and "logs" directory.
     *
     * This is not the most secure default, but it gets people up and running quickly.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function setFolderPermissions($dir, $io)
    {
        // ask if the permissions should be changed
        if ($io->isInteractive()) {
            $validator = function ($arg) {
                if (in_array($arg, ['Y', 'y', 'N', 'n'])) {
                    return $arg;
                }
                throw new Exception('This is not a valid answer. Please choose Y or n.');
            };
            $setFolderPermissions = $io->askAndValidate(
                '<info>Set Folder Permissions ? (Default to Y)</info> [<comment>Y,n</comment>]? ',
                $validator,
                10,
                'Y'
            );

            if (in_array($setFolderPermissions, ['n', 'N'])) {
                return;
            }
        }

        // Change the permissions on a path and output the results.
        $changePerms = function ($path) use ($io) {
            $currentPerms = fileperms($path) & 0777;
            $worldWritable = $currentPerms | 0007;
            if ($worldWritable == $currentPerms) {
                return;
            }

            $res = chmod($path, $worldWritable);
            if ($res) {
                $io->write('Permissions set on ' . $path);
            } else {
                $io->write('Failed to set permissions on ' . $path);
            }
        };

        $walker = function ($dir) use (&$walker, $changePerms) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir . '/' . $file;

                if (!is_dir($path)) {
                    continue;
                }

                $changePerms($path);
                $walker($path);
            }
        };

        foreach (Installer::REQUIRED_PERMISION_DIRS as $path) {
            $walker($path);

        }

    }

   
}
