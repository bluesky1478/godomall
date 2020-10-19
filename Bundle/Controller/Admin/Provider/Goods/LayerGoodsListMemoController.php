<?php

namespace Bundle\Controller\Admin\Provider\Goods;

use App;
use Request;

/**
 * @author  <tomi@godo.co.kr>
 */
class LayerGoodsListMemoController extends \Controller\Admin\Goods\LayerGoodsListMemoController
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            parent::index();

        } catch (Exception $e) {
            throw $e;
        }
    }
}
