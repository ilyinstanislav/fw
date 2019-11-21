<?php

class Router extends Singleton
{
    public $controller = null;
    public $action = 'index';
    public $params = [];

    /**
     * Parse url
     */
    function parse()
    {
        if (isset($_REQUEST['u']) && $_REQUEST['u'] != '') {
            $path = explode('/', $_REQUEST['u']);
            $this->controller = $path[0];
            $this->action = ArrayHelper::getValue($path, '1', 'index');

            unset($_REQUEST['u']);
            $this->params = $_REQUEST;
        }
    }
}