<?php

namespace Application\Controllers;

use Core\AbstractController;
use Application\Services\SiteMapService;

include BASE_PATH . "Core/simple_html_dom.php";

class IndexController extends AbstractController
{
    /**
     * Main page
     *
     * @return array
     */
    public function index()
    {
        $this->title = "Sitemap generator";
    }

    /**
     * Action for create site map
     */
    public function createSiteMap()
    {
        $status = false;
        $message = sprintf('Website by this link %s is not available', $this->fromPost('siteUrl'));

        if ($this->fromPost('siteUrl') && SiteMapService::isUrlAvailable($this->fromPost('siteUrl'))) {
            //create service for XML generation
            $service = new SiteMapService(
                $this->fromPost('siteUrl'),
                $this->fromPost('modifyData'),
                $this->fromPost('priority')
            );

            //scan current site
            $service->scanSite($this->fromPost('depthScan'));
            //generate XML
            $service->saveSiteMap($this->fromPost('frequencyUpdate'));
            $status = true;
            $message = '';
        }

        $response = [
            'status'  => $status,
            'message' => $message,
        ];

        echo json_encode($response);
        die;
    }
}
