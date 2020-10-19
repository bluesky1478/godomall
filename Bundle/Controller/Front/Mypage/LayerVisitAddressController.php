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
namespace Bundle\Controller\Front\Mypage;

use Component\Order\Order;
use Component\Delivery\Delivery;
use Exception;
use Request;

/**
 * 방문수령시 주문상품당 방문주소
 * @author <kookoo135@godo.co.kr>
 */
class LayerVisitAddressController extends \Controller\Front\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $order = new Order();
        $delivery = new Delivery();
        $getValue = \Request::get()->all();
        if (is_array($getValue['goodsSno']) === false) {
            $getValue['goodsSno'] = json_decode($getValue['goodsSno'], true);
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);
        $goodsSno = array_slice($getValue['goodsSno'], ($getValue['page'] -1) * $getValue['pageNum'], $getValue['pageNum']);
        if (empty($goodsSno) === false) {
            $order->getOrderDataInfo($getValue['orderNo']);
            $data = $order->getOrderGoodsData($getValue['orderNo'], $goodsSno);
            foreach ($data as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($v['deliveryMethodFl'] != 'visit') unset($data[$key][$k]);
                }
                if (empty($data[$key]) === true) unset($data[$key]);
            }
        }

        $page = \App::load('\\Component\\Page\\Page');
        $page->recode['amount'] = $page->recode['total'] = count($getValue['goodsSno']); //전체 레코드 수
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->page['now'] = $page->block['now'] = $getValue['page'];
        $page->setPage();
        $this->setData('value', $getValue);
        $this->setData('data', $data);
        $this->setData('page', $page);
    }
}
