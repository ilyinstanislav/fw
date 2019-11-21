<?php

/**
 * Class for work with products
 * Class ProductsController
 */
class ProductsController extends Controller
{
    /**
     * generate 20 random products
     */
    function actionGenerate()
    {
        $generated = 0;
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $values = [
                'name' => $this->generateRandomString(),
                'price' => mt_rand(50000, 100000) / 100
            ];
            if ($product->save($values)->exec()) {
                $generated++;
            }
        }

        echo $this->sendResult([
            'success' => true,
            'message' => "generated $generated products"
        ]);
    }

    /**
     * send all isset products
     */
    function actionGetAll()
    {
        $products = Product::getAll();
        $result = [];
        foreach ($products as $product) {
            $result[] = $product->__attributes;
        }

        echo $this->sendResult([
            'success' => true,
            'products' => $result
        ]);
    }

    /**
     * Save new order
     * @param $id
     * @return string
     * @throws Exception
     */
    public function actionOrder($id)
    {
        $products_id = explode(',', $id);
        $products = $this->getProducts($products_id);

        if ($products) {
            try {
                App::getInstance()->db->beginTransaction();

                $order = new Order();
                $success = $this->makeOrder($order, $products);

                if ($success) {
                    App::getInstance()->db->commit();
                } else {
                    App::getInstance()->db->rollBack();
                }
                echo $this->sendResult([
                    'success' => $success,
                    'message' => $success ? 'Order successfully saved' : 'Something go wrong. Order not saved',
                    'order_id' => $success ? $order->id : null
                ]);
                return;
            } catch (Exception $e) {
                App::getInstance()->db->rollBack();
            }
        }

        echo $this->sendResult([
            'success' => false,
            'message' => 'Products not found or not selected'
        ]);
        return;
    }

    /**
     * @param int $id
     * @param float $price
     */
    function actionPay($id, $price)
    {
        $id = intval($id);
        $price = floatval($price);
        $result = false;

        $order = Order::findOne($id);
        if (!$order || $order->price != $price || $order->status != OrderStatuses::RECENT) {
            echo $this->sendResult([
                'success' => false,
                'message' => 'Order not found, already payed or summ does not match'
            ]);
            return;
        }

        $httpCode = $this->sendRequest('https://ya.ru/');

        if ($httpCode == 200) {
            $values = [
                'status' => OrderStatuses::PAYED
            ];
            $result = $order->save($values)->exec();
        }

        echo $this->sendResult([
            'success' => $result,
            'message' => $result ? 'Order successfully payed' : 'Status is not 200 or order does not saved'
        ]);
    }

    /**
     * Get request code
     * @param $url
     * @return mixed
     */
    protected function sendRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }

    /**
     * @param array $products
     * @return bool
     * @throws Exception
     */
    protected function makeOrder($order, $products)
    {
        $values = [
            'price' => $this->getProductsPrice($products)
        ];
        $success = $order->save($values)->exec();
        if (!$success) {
            App::getInstance()->db->rollBack();
        }

        if (!$success || !$success = $this->saveOrderProducts($order->id, $products)) {
            App::getInstance()->db->rollBack();
        }
        return $success;
    }

    /**
     * @param int $order_id
     * @param array $products
     * @return bool
     */
    protected function saveOrderProducts($order_id, $products)
    {
        foreach ($products as $product) {
            $new = new OrderProduct();
            $values = [
                'order_id' => $order_id,
                'product_id' => $product->id
            ];
            if (!$new->save($values)->exec()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $products
     * @return float
     */
    protected function getProductsPrice($products)
    {
        $summ = 0;
        foreach ($products as $product) {
            $summ += $product->price;
        }
        return $summ;
    }

    /**
     * @param array $products_id
     * @return array|null
     */
    protected function getProducts($products_id)
    {
        $products = [];

        if (empty($products_id)) {
            return null;
        }

        foreach ($products_id as $product_id) {
            $product_id = intval($product_id);
            $product = Product::findOne($product_id);
            if ($product == null) {
                return null;
            }
            $products[] = $product;
        }

        return $products;
    }

    /**
     * @param int $length
     * @return string
     */
    protected function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}