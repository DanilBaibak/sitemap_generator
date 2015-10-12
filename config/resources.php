<?php
/**
 * Resources. Each consists of:
 * 'resource' - url on which the data you'll be requested
 * 'controller' - name of the controller
 * 'action' - name of the action
 */
return array(
    array(
        'resource'   => '/',
        'controller' => 'index',
        'action'     => 'index'
    ),
    array(
        'resource'   => '/create_site_map',
        'controller' => 'index',
        'action'     => 'createSiteMap'
    ),
);
