<?php

/**
 * Main application controller
 * Class IndexController
 */
class IndexController extends Controller
{
    function actionIndex()
    {
        $data = [
            'success' => false,
            'message' => 'Method not exist or not selected'
        ];
        echo $this->sendResult($data);
    }
}