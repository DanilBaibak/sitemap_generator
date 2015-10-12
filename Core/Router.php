<?php
/**
 * Routing.
 * Current router is designed to simple urls. The urls should contains only one item.
 * Like 'https://test.com/product', 'https://test.com/groups' etc.
 *
 * Created by Danil Baibak danil.baibak@gmail.com
 */
namespace Core;

class Router
{
    /**
     * @var
     */
    private $config;

    /**
     * @var
     */
    private $controller;

    /**
     * @var
     */
    private $action;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Run application
     *
     * @throws \Exception
     */
    public function run()
    {
        $resource = $this->getResource();
        $this->controller = "Application\\Controllers\\" . ucfirst($resource['controller']) . "Controller";
        $this->action = $resource['action'];

        //load requested resource
        $this->controller = new $this->controller();

        if (method_exists($this->controller, $this->action)) {
            $view = $this->createView();
            $view->display(strtolower($resource['controller']) . '/' . $resource['action'] . '.phtml');
        } else {
            //or load "page not found"
            $this->controller = new \Application\Controllers\ErrorController();
            $this->controller->pageNotFound();
        }
    }

    /**
     * @return View
     */
    private function createView()
    {
        $action = $this->action;
        $renderVariables = $this->controller->$action();
        $view = new View();
        
        if (!empty($renderVariables)) {
            foreach ($renderVariables as $key => $renderVariable) {
                $view->set($key, $renderVariable);
            }
        }

        $view->title = empty($this->controller->title) ? $this->config['title'] : $this->controller->title;
        $view->description = empty($this->controller->description) ? '': $this->controller->description;
        $view->keywords = empty($this->controller->keywords) ? '' : $this->controller->keywords;
        $view->layout = empty($this->controller->layout) ? $this->config['layout'] : $this->controller->layout;
        $view->layoutContent = !empty($this->controller->layoutContent) ? $this->controller->layoutContent : [];

        return $view;
    }

    /**
     * Select controller and action according to the current url
     *
     * @return array
     * @throws \Exception
     */
    private function getResource()
    {
        $resource = [];
        //get resources
        $resources = include BASE_PATH . 'config/resources.php';
        //check resources
        if (empty($resources)) {
            throw new \Exception('File with resources is empty');
        }

        //remove GET-params
        if (strpos($_SERVER['REQUEST_URI'], "?") !== false) {
            $requestUrl = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], "?"));
        } else {
            $requestUrl = $_SERVER['REQUEST_URI'];
        }

        //check is url belong to index controller
        if (empty($requestUrl)) {
            $resource['controller'] = 'Index';
            $resource['action'] = 'index';
        } else {
            //check requested resource in the existing resource list
            foreach ($resources as $currentResource) {
                if ($currentResource['resource'] == $requestUrl) {
                    $resource = $currentResource;
                    break;
                }
            }
        }

        /**
         * if there is such resource in the resources list, load it
         */
        if (empty($resource)) {
            $resource['controller'] = 'Error';
            $resource['action'] = 'pageNotFound';
        }

        return $resource;
    }
}
