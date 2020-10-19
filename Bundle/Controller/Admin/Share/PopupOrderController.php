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
namespace Bundle\Controller\Admin\Share;

use Component\Order\OrderAdmin;
use Component\Board\ArticleListAdmin;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Request;

/**
 * Class PopupOrderController
 *
 * @package Bundle\Controller\Admin\Share
 * @author  NamJu Lee <lnjts@godo.co.kr>
 */
class PopupOrderController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertCloseException
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                ]
            );

            // --- 모듈 호출
            $order = new OrderAdmin();

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();

            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            $searchPeriod = $data['searchPeriod'] - 1;
            gd_isset($searchPeriod, 6);

            // --- 리스트 설정
            $getValue = Request::get()->toArray();
            $getValue['view'] = 'orderGoods';
            $getData = $order->getOrderListForAdmin($getValue, $searchPeriod);
            unset($getData['data']);
            if (Request::get()->get('checkType') == 'radio') {
                $checkType = 'radio';
            } else {
                $checkType = 'checkbox';
            }
            $this->setData('checkType', gd_isset($checkType));
            foreach($getData as $key=>$val){
                if(is_numeric($key) === false) {
                    continue;
                }
                $orderGoodsData[] = $val;
            }
            $this->setData('data', gd_isset($orderGoodsData));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($orderGoodsData));
            $this->setData('page', gd_isset($page));

            // --- 템플릿 정의
            $this->getView()->setDefine('layoutOrderSearchForm', 'share/layout_order_search_form.php');// 검색폼
            $this->getView()->setDefine('layoutOrderList', 'share/layout_order_list.php');// 리스트폼

            // --- 템플릿 변수 설정
            $this->setData('type', $order->getOrderType());
            $this->getView()->setDefine('layout', 'layout_blank.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
