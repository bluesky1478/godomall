<?php

namespace Bundle\Controller\Admin\Goods;

/*
 * 검색창 관련 설정
 */
class SearchSettingsController extends \Controller\Admin\Controller
{

    public function Index()
    {
        $this->callMenu('goods', 'displayConfig', 'searchSettings');

        try {
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
            $data = $goods->getDateSearchDisplay();
            $paycoSearch = \App::load('\\Component\\Nhn\\Paycosearch');
        } catch (\Exception $e) {
            throw $e;
        }

        $this->setData('data', $data['data']);
        $this->setData('checked', $data['checked']);
        $this->setData('selected', $data['selected']);
        $this->setData('paycoSearchUseFl', $paycoSearch->neSearchConfigIsset);
    }
}
