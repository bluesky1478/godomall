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

use Component\Order\OrderAdmin;
use Exception;
use Globals;
use Request;

/**
 * 주문통합리스트
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderListAllController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'order', 'all');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                    'sms.js'
                ]
            );

            // --- 모듈 호출
            $order = new OrderAdmin();

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();
            $isOrderSearchMultiGrid = gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n');
            $this->setData('isOrderSearchMultiGrid', $isOrderSearchMultiGrid);

            // --- 리스트 설정
            $getValue = Request::get()->toArray();

            //주문리스트 그리드 설정
            $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
            $getValue['orderAdminGridMode'] = $orderAdminGrid->getOrderAdminGridMode($getValue['view']);
            $this->setData('orderAdminGridMode', $getValue['orderAdminGridMode']);

            $getData = $order->getOrderListForAdmin($getValue, 6);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('orderGridConfigList', $getData['orderGridConfigList']);
            //복수배송지를 사용하여 리스트 데이터 배열의 키를 체인지한 데이터인지 체크
            $this->setData('useMultiShippingKey', $getData['useMultiShippingKey']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            // 정규식 패턴 view 파라미터 제거
            $pattern = '/view=[^&]+$|searchFl=[^&]+$|view=[^&]+&|searchFl=[^&]+&/';//'/[?&]view=[^&]+$|([?&])view=[^&]+&/';

            // view 제거된 쿼리 스트링
            $queryString = preg_replace($pattern, '', Request::getQueryString());
            $this->setData('queryString', $queryString);

            // --- 주문 일괄처리 셀렉트박스
            foreach ($order->getOrderStatusAdmin() as $key => $val) {
                if (in_array($key, $order->statusListExclude) === false && in_array(substr($key, 0, 1), $order->statusExcludeCd) === false && substr($key, 0, 1) != 'o') {
                    $selectBoxOrderStatus[$key] = $val;
                }
            }
            $this->setData('selectBoxOrderStatus', $selectBoxOrderStatus);

            // 메모 구분
            $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');
            $tmpMemo = $orderAdmin->getOrderMemoList(true);
            $arrMemoVal = [];
            foreach($tmpMemo as $key => $val){
                $arrMemoVal[$val['itemCd']] = $val['itemNm'];
            }
            $this->setData('memoCd', $arrMemoVal);

            // --- 템플릿 정의
            $this->getView()->setDefine('layoutOrderSearchForm', Request::getDirectoryUri() . '/layout_order_search_form.php');// 검색폼
            if ($getData['search']['view'] === 'order') {
                $this->getView()->setDefine('layoutOrderList', Request::getDirectoryUri() . '/layout_order_list.php');// 리스트폼
            } elseif ($getData['search']['view'] === 'orderGoodsSimple') {
                $this->getView()->setDefine('layoutOrderList', Request::getDirectoryUri() . '/layout_order_goods_simple_list.php');// 리스트폼
            } else {
                $this->getView()->setDefine('layoutOrderList', Request::getDirectoryUri() . '/layout_order_goods_list.php');// 리스트폼
            }

            // --- 템플릿 변수 설정
            $this->setData('statusStandardNm', $order->statusStandardNm);
            $this->setData('statusStandardCode', $order->statusStandardCode);
            $this->setData('statusListCombine', $order->statusListCombine);
            $this->setData('statusListExclude', $order->statusListExclude);
            $this->setData('type', $order->getOrderType());
            $this->setData('channel', $order->getOrderChannel());
            $this->setData('settle', $order->getSettleKind());
            $this->setData('formList', $order->getDownloadFormList());
            $this->setData('statusExcludeCd', $order->statusExcludeCd);
            $this->setData('statusSearchableRange', $order->getOrderStatusAdmin());

            //        $this->setData('statusPreCode', $statusPreCode);
            //        $this->setData('search', gd_isset($getData['search']));
            //        $this->setData('checked', $checked = gd_isset($getData['checked']));
            //        $this->setData('statusMode', gd_isset($getValue['statusMode']));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/order_list_all.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
