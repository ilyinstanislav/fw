<?php

class Controller extends Singleton
{
    /**
     * @param $data
     * @return false|string
     */
    public function sendResult($data)
    {
        header('Content-Type: application/json');
        return json_encode($data);
    }

    /**
     * call Controller action
     * @param $methodName
     * @param array $args
     * @return mixed
     * @throws ReflectionException
     */
    function __call($methodName, $args = array())
    {
        if (method_exists($this, $methodName)) {
            $args = $this->checkArgs($this, $methodName, $args);
            return call_user_func_array(array($this, $methodName), $args);
        } else
            throw new Exception('In controller ' . get_called_class() . ' method ' . $methodName . ' not found!');
    }

    /**
     * @param $class
     * @param $method
     * @param array $args
     * @return array
     * @throws ReflectionException
     */
    protected function checkArgs($class, $method, $args = [])
    {
        $refm = new ReflectionMethod($class, $method);

        if (count($args) != $refm->getNumberOfParameters()) {
            throw new Exception('Number of function parameters does not match');
        }

        $result_args = [];

        foreach ($refm->getParameters() as $parameter) {
            if (!isset($args[$parameter->name])) {
                throw new Exception("Parameter {$parameter->name} does not exist");
            }

            if ($parameter->getType() && gettype($args[$parameter->name]) != $parameter->getType()) {
                throw new Exception("Parameter {$parameter->name} is not {$parameter->getType()}");
            }

            $result_args[$parameter->name] = $args[$parameter->name];
        }

        return $result_args;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function db()
    {
        return App::getInstance()->db;
    }
}