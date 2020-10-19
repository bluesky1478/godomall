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
namespace Bundle\Controller\Admin\Order;

use Component\Order\Order;
use Component\Delivery\Delivery;
use Exception;
use Request;

/**
 * 주문 상품 로그 레이어 페이지
 * [관리자 모드] 주문 상품 로그 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerVisitAddressController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
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
            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 페이지 같이 사용
            $this->getView()->setPageName('order/layer_visit_address.php');
        } catch (Exception $e) {
            $this->layer($e->getMessage());
        }
    }
}
