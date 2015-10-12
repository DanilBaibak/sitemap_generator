<?php

namespace Application\Tests;

use Core\Test\HttpTestTrait as HttpTestTrait;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    use HttpTestTrait;

    public function setUp()
    {
        $this->init();
        defined('BASE_PATH') || define('BASE_PATH', __DIR__ . '/../../');
    }

    /**
     * Test for the home page
     */
    public function testIndexAction()
    {
        $request = $this->client->get(SITE_URL);
        $this->assertEquals(200, $request->getStatusCode());
    }

    /**
     * Check if the url for scanning is not available
     */
    public function testWrongUrl()
    {
        $wrongUrl = 'http://wrong-url.com';
        $request = $this->client->request('POST', SITE_URL . '/create_site_map', [
            'form_params' => [
                'siteUrl'         => $wrongUrl,
                'modifyData'      => false,
                'priority'        => false,
                'depthScan'       => false,
                'frequencyUpdate' => false,

            ]
        ]);

        $response = json_decode($request->getBody()->getContents());

        $this->assertEquals(200, $request->getStatusCode());
        $this->assertFalse($response->status);
        $this->assertEquals(sprintf('Website by this link %s is not available', $wrongUrl), $response->message);
    }

    /**
     * Check main request for creating sitemap
     */
    public function testCreateSiteMapAction()
    {
        $request = $this->client->request('POST', SITE_URL . '/create_site_map', [
            'form_params' => [
                'siteUrl'         => 'http://google.com',
                'modifyData'      => false,
                'priority'        => false,
                'depthScan'       => false,
                'frequencyUpdate' => false,

            ]
        ]);

        $response = json_decode($request->getBody()->getContents());

        $this->assertEquals(200, $request->getStatusCode());
        $this->assertTrue($response->status);
        $this->assertEmpty($response->message);
    }
}
