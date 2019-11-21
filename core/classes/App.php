<?php

class App extends Singleton
{
    /**
     * @var array
     */
    public $config = [];
    public $db = null;

    public function __construct()
    {
        $this->initSystemHandlers();
        $this->config = include CORE . 'config.php';
        $this->db = new Mysql();
        $this->db->connect($this->config['db']);
    }

    /**
     * main application function
     * @throws Exception
     */
    function start()
    {
        $router = Router::getInstance();
        $router->parse();
        $controller = App::getInstance($router->controller . 'Controller');
        $controller->__call('action' . $router->action, $router->params);
    }

    public function handleError($code, $message, $file, $line)
    {
        if ($code & error_reporting()) {
            restore_error_handler();
            restore_exception_handler();
            try {
                $this->displayError($code, $message, $file, $line);
            } catch (Exception $e) {
                $this->displayException($e);
            }
        }
    }

    public function handleException($exception)
    {
        restore_error_handler();
        restore_exception_handler();
        $this->displayException($exception);
    }

    public function displayError($code, $message, $file, $line)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errorCode' => $code,
            'message' => $message
        ]);
        exit();
    }

    public function displayException($exception)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => get_class($exception) . ': ' . $exception->getMessage()
        ]);
    }

    protected function initSystemHandlers()
    {
        set_exception_handler(array($this, 'handleException'));
        set_error_handler(array($this, 'handleError'), error_reporting());
    }
}