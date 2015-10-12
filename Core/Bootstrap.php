<?php
/**
 * Current class make initialization of whole application
 * and make all settings
 *
 * Created by Danil Baibak danil.baibak@gmail.com
 * Date: 7/12/14
 * Time: 9:33 PM
 */
namespace Core;

class Bootstrap
{
    /**
     * Initialise application
     *
     * Created by Danil Baibak danil.baibak@gmail.com
     */
    public static function init()
    {
        $pathToConfig = CONFIG . "config.php";

        //get config
        if (!file_exists($pathToConfig)) {
            throw new \Exception(sprintf('File with configurations "%s" wasn\'t set up', $pathToConfig));
        }

        $config = include_once $pathToConfig;

        //show/hide errors and warnings
        if ($config['displayErrors']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
        }

        $router = new Router($config);
        $router->run();
    }
}
