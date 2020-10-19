<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Front\Share;


use Component\Goods\Goods;

class LayerGoodsSearchController extends \Controller\Front\Controller
{
    public function index()
    {
        $getValue = \Request::get()->toArray();
        $goods = new Goods();
        $goodsData = $goods->getGoodsSearchList(10);
        $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        $page->setUrl(rawurldecode(\Request::server()->get('QUERY_STRING')));
        $pagination = $page->getPage('goAjaxPaging(\'PAGELINK\')');
        $this->setData('list', $goodsData['listData']);
        $this->setData('total', $page->getTotal());
        $this->setData('pagination', $pagination);
        $this->setData('soldoutDisplay', gd_policy('soldout.pc'));
    }
}
