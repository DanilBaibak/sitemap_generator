<?php

namespace Core;

class View
{
    /**
     * @var string
     */
    private $_path;

    /**
     * @var
     */
    private $content;

    /**
     * @var array
     */
    private $_var = [];

    /**
     * @var string
     */
    private $_layoutPath;

    /**
     * @var
     */
    public $title;

    /**
     * @var
     */
    public $layout;

    /**
     * @var
     */
    public $layoutContent;

    /**
     * @var
     */
    public $description;

    /**
     * @var
     */
    public $keywords;

    /**
     * @var
     */
    public $cont;

    /**
     * Set path to the templates
     * @param string $path - path to the templates
     */
    public function __construct($path = '')
    {
        $this->_path = empty($path) ? BASE_PATH . 'Application/Views/' : $path;
        $this->_layoutPath = BASE_PATH . 'Application/Views/layouts/';
    }

    /**
     * Set variable
     *
     * @param string $name - name of the variable
     * @param string $value - value of the variables
     */
    public function set($name, $value)
    {
        $this->_var[$name] = $value;
    }

    /**
     * Get variable
     *
     * @param string $name - name of the variable
     * @return string mixed - variable if it exists
     * @throws \Exception - if variable doesn't exist
     */
    public function __get($name)
    {
        if (isset($this->_var[$name])) {
            return $this->_var[$name];
        } else {
            throw new \Exception(sprintf('Variable "%s" does not exist!', $name));
        }
    }

    /**
     * Check variable in the scope
     *
     * @param string $name - name of the variable
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_var[$name]);
    }

    /**
     * Remove variable from the scope
     *
     * @param string $name - name of the variable for remove
     */
    public function __unset($name)
    {
        unset($this->_var[$name]);
    }

    /**
     * Required current template
     *
     * @param string $template - name of the file
     * @throws \Exception - if current template doesn't exist
     */
    public function display($template)
    {
        $this->content = $this->_path . $template;
        //check current file
        if (!file_exists($this->content)) {
            throw new \Exception(sprintf('View "%s" does not exist!', $this->content));
        }

        include_once($this->_layoutPath . $this->layout);
    }

    /**
     * Function for change language
     *
     * @param string $ln - language that is needed
     * @return string - url for changing language
     *
     * Created by Danil Baibak danil.baibak@gmail.com
     */
    public function changeLn($ln = '')
    {
        $requestUrl = strpos($_SERVER['REQUEST_URI'], 'en') === false ?
            substr($_SERVER['REQUEST_URI'], 1) :
            substr($_SERVER['REQUEST_URI'], 4);
        $ln = $ln == 'en' ? 'en/' : '';
        return 'http://' . $_SERVER['SERVER_NAME'] . '/' . $ln . $requestUrl;
    }
}
