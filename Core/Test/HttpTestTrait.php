<?php

namespace Core\Test;

trait HttpTestTrait
{
    public $client;

    /**
     * Setup data for testing
     *
     * @throws \Exception
     */
    public function init()
    {
        $this->client = new \GuzzleHttp\Client();

        $pathToConfig = "config/config.php";
        //get config
        if (file_exists($pathToConfig)) {
            $config = include_once $pathToConfig;
            defined('SITE_URL') || define('SITE_URL', $config['siteUrl']);
        } else {
            throw new \Exception("File with configurations '" . $pathToConfig ."' wasn't set up");
        }
    }
}
