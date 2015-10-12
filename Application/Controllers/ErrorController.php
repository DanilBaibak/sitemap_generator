<?php
/**
 * Error Controller
 *
 * Created by Danil Baibak danil.baibak@gmail.com
 */
namespace Application\Controllers;

class ErrorController
{
    /**
     * Page not found
     */
    public function pageNotFound()
    {
        header('HTTP/1.0 404 Not Found');
        return array('message' => 'Sorry, current page was not found.');
    }
}
