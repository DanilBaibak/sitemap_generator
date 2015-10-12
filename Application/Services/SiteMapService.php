<?php
/**
 * Created by Danil Baibak danil.baibak@gmail.com
 * Date: 24/03/15
 * Time: 14:00
 */

namespace Application\Services;

class SiteMapService
{
    const MAX_PRIORITY = 1.00;
    const MIN_PRIORITY = 0.2;
    const PRIORITY_STEP = 0.2;

    /**
     * @var array priority for each link
     */
    private $linkPriority = [];

    /**
     * @var array of modify date for those links that is possible
     */
    private $modifyDate = [];

    /**
     * @var array of unique links that were found
     */
    private $listOfLinks = [];

    /**
     * @var data for input to the XML file
     */
    private $content;

    /**
     * @var string url of the site
     */
    private $mainUrl;

    /**
     * @var string host
     */
    private $baseUrl;

    /**
     * @var bool need or not save date of last modification
     */
    private $isModifyDate;

    /**
     * @var bool need or not save priority
     */
    private $needPriority;

    /**
     * @var
     */
    private $nodeUrl;

    /**
     * Create settings of the current sitemap generation
     *
     * @param string $mainUrl url of the site
     * @param string $needModifyDate mode for add date of modify
     * @param string $needPriority mode for add priority
     */
    public function __construct($mainUrl, $needModifyDate, $needPriority)
    {
        $urlData = parse_url($mainUrl);
        $this->baseUrl = $urlData['scheme'] . '://' . $urlData['host'];
        $this->mainUrl = $mainUrl;
        $this->nodeUrl = 'http://' . $_SERVER['SERVER_NAME'] . ':9090/';

        $this->isModifyDate = $needModifyDate  === 'true' ? true : false;
        $this->needPriority = $needPriority === 'true' ? true : false;
    }

    /**
     * Find and check all links on the current page
     *
     * @param string $url url od the current page
     * @param string host host of the current site
     * @param float $currentPriority priority of the current page
     *
     * @return array list of the new and unique links that were found on the current page
     */
    private function findLinks($url, $host, $currentPriority)
    {
        $newLinks = [];
        $html = file_get_html($url);
        if ($html) {
            foreach($html->find('a') as $e) {
                $link = str_replace('#', '', $e->href);
                //don't check not links
                if (strpos($link, 'mailto') === false) {
                    //get details of the current link
                    $linkDetail = parse_url($e->href);
                    /**
                     * Check details of the current link
                     */
                    if (
                        isset($linkDetail['scheme']) &&
                        isset($linkDetail['host']) &&
                        $linkDetail['host'] != $host ||
                        !isset($linkDetail['path']) ||
                        strpos($linkDetail['path'], 'http') !== false &&
                        $linkDetail['path'] != '/'
                    ) {
                        continue;
                    }

                    //check is the current link new
                    if (!in_array($linkDetail['path'], $this->listOfLinks)) {
                        preg_match('/(.png)?(.jpg)?(.zip)?(.pdf)?(.psd)?$/', $linkDetail['path'], $matches);
                        //check is current link seen on the web page
                        if (empty($matches[0])) {
                            //check is site available by current link
                            if (self::isUrlAvailable($this->baseUrl . $linkDetail['path'])) {
                                //save current link
                                $newLinks[] = $linkDetail['path'];
                                $this->listOfLinks[] = $linkDetail['path'];

                                //send data about current link that was found
                                $this->sendRequest($this->nodeUrl .'current_link?link=' . $linkDetail['path']);
                                //send link about number of the links that was found
                                $this->sendRequest(
                                    $this->nodeUrl .'links_number?links_number=' . count($this->listOfLinks)
                                );
                                //send data about memory usage
                                $this->sendRequest(
                                    $this->nodeUrl .'memory_usage?memory=' .
                                    round(memory_get_usage() / 1000000, 3)
                                );

                                //save priority
                                if ($this->needPriority) {
                                    $this->linkPriority[] = $currentPriority;
                                }

                                //try get date from server when file was modified last time
                                if ($this->isModifyDate) {
                                    $modifyDate = $this->getFileModifyDate($this->baseUrl . $linkDetail['path']);
                                    if ($modifyDate) {
                                        $linksKeys = array_keys($this->listOfLinks);
                                        $this->modifyDate[end($linksKeys)] = $modifyDate;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $newLinks;
    }

    /**
     * Scanning current page and find all unique links on it
     *
     * @param string $depthScan do we need scan all pages of the site
     */
    public function scanSite($depthScan)
    {
        $urlDetail = parse_url($this->mainUrl);
        $this->listOfLinks = [];
        $linksDepth = 1;

        $currentPriority = $this->needPriority ? self::MAX_PRIORITY - self::PRIORITY_STEP : false;

        //send data about depth of the searching
        $this->sendRequest($this->nodeUrl .'links_depth?depth=' . $linksDepth);
        $this->listOfLinks = $this->findLinks($this->mainUrl, $urlDetail['host'], $currentPriority);
        $scanLinksList = $this->listOfLinks;

        //do we need scan all pages of the site
        if ($depthScan === 'true') {
            do {
                //send data about depth of the searching
                $this->sendRequest($this->nodeUrl .'links_depth?depth=' . ++$linksDepth);
                $newLinks = [];
                //try find new links for each page
                foreach($scanLinksList as $link) {
                    $justFoundLinks = $this->findLinks($this->baseUrl . $link, $urlDetail['host'], $currentPriority);
                    $newLinks = array_unique(array_merge($newLinks, $justFoundLinks));
                }
                if ($this->needPriority) {
                    //calculate priority
                    $currentPriority = $currentPriority > self::MIN_PRIORITY ?
                        $currentPriority - self::PRIORITY_STEP :
                        $currentPriority;
                }
                $scanLinksList = $newLinks;
            } while(!empty($newLinks));
        }
    }

    /**
     * Create XML file with site map
     *
     * @param string $changefreq change frequency
     */
    public function saveSiteMap($changefreq)
    {
        $pathToFile = BASE_PATH . "public/data/site_map.xml";
        //headers
        $this->content = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
        $this->content.= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";

        //body of the site map
        if (!empty($this->listOfLinks)) {
            $lastMod = false;
            $priorityMainPage = $this->needPriority ? 1.00 : false;
            //put main page
            $this->putContent($this->mainUrl, $priorityMainPage, $lastMod, $changefreq);

            //put data about all links that were found according to the settings
            foreach ($this->listOfLinks as $key => $link) {
                if ($this->isModifyDate) {
                    $lastMod = isset($this->modifyDate[$key]) ? $this->modifyDate[$key] : false;
                }
                $priority = $this->needPriority ? $this->linkPriority[$key] : false;

                $this->putContent($this->baseUrl . $link, $priority, $lastMod, $changefreq);
            }
        }
        $this->content.= "</urlset>\n";

        $this->checkXmlFile($pathToFile);
        //save data into the file
        file_put_contents($pathToFile, $this->content);
    }

    /**
     * If file is not writable, we should change rules of the access
     *
     * @param $pathToFile
     */
    public function checkXmlFile($pathToFile)
    {
        if (!is_writable($pathToFile)) {
            chmod($pathToFile, 0755);
        }
    }

    /**
     * Create information for the current link
     *
     * @param string $link current link
     * @param bool/int $priority priority of the current page
     * @param bool/sting $lastMod date of the last modifications
     * @param sting $changefreq change frequency
     */
    public function putContent($link, $priority, $lastMod, $changefreq)
    {
        $this->content .= '    <url>' . "\n";
        $this->content .= '        <loc>' . $link . '</loc>' . "\n";
        //date of the last modifications
        if ($lastMod) {
            $this->content .= '        <lastmod>' . $lastMod . '</lastmod>' . "\n";
        }
        //change frequency
        if ($changefreq != 'none') {
            $this->content .= '        <changefreq>' . $changefreq . '</changefreq>' . "\n";
        }
        //priority of the current page
        if ($priority) {
            $this->content .= '        <priority>' . $priority . '</priority>' . "\n";
        }
        $this->content .= '    </url>' . "\n";
    }


    /**
     * Check is site available by current url
     *
     * @param string $url current link
     * @return bool
     */
    public static function isUrlAvailable($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $returnCode === 200 ? true : false;
    }

    /**
     * Simple curl for sending requests to the nodejs server
     *
     * @param string $url url of the request
     */
    private function sendRequest($url)
    {
        $ch = curl_init($url);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Get file modify date from server if it's possible
     *
     * @param string $filePath link to the file
     * @return bool|string
     */
    private function getFileModifyDate($filePath)
    {
        $response = false;
        $headers = get_headers($filePath, 1);
        //check headers
        if ($headers && isset($headers['Last-Modified'])) {
            $lastModified = is_array($headers['Last-Modified']) ?
                end($headers['Last-Modified']) :
                $headers['Last-Modified'];

            $modifyDate = new \DateTime($lastModified);
            $response = $modifyDate->format('Y-m-d H:i:s');
        }

        return $response;
    }
}
