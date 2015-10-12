<?php
/**
 * Abstract controller.
 * Contains methods that are needed in controllers.
 *
 * Created by Danil Baibak danil.baibak@gmail.com
 */

namespace Core;

class AbstractController
{
    /**
     * @var
     */
    public $layout;

    /**
     * @var
     */
    public $title;

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
     * Make "strip_tags", "addslashes", "trim" for all variables recursively
     *
     * @param array|string $data - incoming data
     * @return array|string - purified data
     */
    protected function clearData($data)
    {
        //check, is incoming data array or sting
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->clearData($data[$key]);
            }
        } else {
            $data = trim(addslashes(strip_tags($data)));
        }

        return $data;
    }

    /**
     * Check is the method of the request 'post'
     *
     * @return bool
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' ? true : false;
    }

    /**
     * Check is the method of the request 'get'
     *
     * @return bool
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET' ? true : false;
    }

    /**
     * Check is the method of the request 'delete'
     *
     * @return bool
     */
    protected function isDelete()
    {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE' ? true : false;
    }

    /**
     * Check is the method of the request 'put'
     *
     * @return bool
     */
    protected function isPut()
    {
        return $_SERVER['REQUEST_METHOD'] === 'PUT' ? true : false;
    }

    /**
     * Return REQUEST_METHOD
     *
     * @return string
     */
    protected function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get data from request
     *
     * @return array
     */
    protected function getParams()
    {
        return (array) json_decode(file_get_contents("php://input"), true);
    }

    /**
     * Get current value from the GET
     *
     * @param string $valueName - name of the value, that is needed
     * @return bool
     */
    protected function fromGet($valueName)
    {
        return isset($_GET[$valueName]) ? $_GET[$valueName] : false;
    }

    /**
     * Get current value from the POST
     *
     * @param $valueName - name of the value, that is needed
     * @return bool
     */
    public function fromPost($valueName)
    {
        return isset($_POST[$valueName]) ? $_POST[$valueName] : false;
    }

    /**
     * Get current value from the SESSION
     *
     * @param $valueName - name of the value, that is needed
     * @return bool
     */
    protected function fromSession($valueName)
    {
        return isset($_SESSION[$valueName]) ? $_SESSION[$valueName] : false;
    }

    /**
     * Delete value from session
     *
     * @param string, array $valueName - delete value from session
     */
    protected function deleteFromSession($valueName)
    {
        if (is_array($valueName)) {
            foreach ($valueName as $value) {
                $this->removeFromSession($value);
            }
        } else {
            $this->removeFromSession($valueName);
        }
    }

    /**
     * Delete value from session
     *
     * @param string $valueName - delete value from session
     */
    private function removeFromSession($valueName)
    {
        if (isset($_SESSION[$valueName])) {
            $_SESSION[$valueName] = '';
        }
    }

    /**
     * Redirect to the chosen path
     *
     * @param string $path - current path
     */
    protected function redirect($path = "/")
    {
        header("Location: " . $path);
        exit;
    }
}
