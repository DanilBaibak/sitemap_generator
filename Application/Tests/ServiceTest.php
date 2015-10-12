<?php

namespace Application\Tests;

use Application\Services\SiteMapService;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for method 'isUrlAvailable'
     */
    public function testIsUrlAvailable()
    {
        $availableUrl = SiteMapService::isUrlAvailable('http://google.com');
        $notAvailableUrl = SiteMapService::isUrlAvailable('http://wrong-url.com');

        $this->assertTrue($availableUrl);
        $this->assertFalse($notAvailableUrl);
    }
}