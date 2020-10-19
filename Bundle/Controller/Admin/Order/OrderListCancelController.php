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

use Exception;
use Globals;
use Request;

/**
 * 주문취소 리스트
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderListCancelController extends \Controller\Admin\Controller
{
    /**
     * @var 기본 주문상태
     */
    private $_currentStatusCode = 'c';

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // --- 모듈 호출

        // --- 메뉴 설정
        $this->callMenu('order', 'cancel', 'cancel');
        $this->addScript(
            [
                'jquery/jquery.multi_select_box.js',
                'sms.js',
            ]
        );

        // --- 모듈 호출
        $order = \App::load('\\Component\\Order\\OrderAdmin');

        /* 운영자별 검색 설정값 */
        $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
        $searchConf->setGetData();
        $isOrderSearchMultiGrid = gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n');
        $this->setData('isOrderSearchMultiGrid', $isOrderSearchMultiGrid);

        // --- 주문 데이터
        try {
            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            gd_isset($data['searchPeriod'], 6);

            // -- _GET 값
            $getValue = Request::get()->toArray();

            //주문리스트 그리드 설정
            $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
            $getValue['orderAdminGridMode'] = $orderAdminGrid->getOrderAdminGridMode($getValue['view']);
            $this->setData('orderAdminGridMode', $getValue['orderAdminGridMode']);

            // 주문출력 범위 설정
            $getValue['statusMode'] = $this->_currentStatusCode;
            $this->setData('currentStatusCode', $this->_currentStatusCode);

            // --- 리스트 설정
            $getData = $order->getOrderListForAdmin($getValue, $data['searchPeriod']);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('orderGridConfigList', $getData['orderGridConfigList']);
            //복수배송지를 사용하여 리스트 데이터 배열의 키를 체인지한 데이터인지 체크
            $this->setData('useMultiShippingKey', $getData['useMultiShippingKey']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

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
            $this->getView()->setDefine('layoutOrderList', Request::getDirectoryUri() . '/layout_order_goods_list.php');// 리스트폼

            // --- 템플릿 변수 설정
            $this->setData('statusStandardCode', $order->statusStandardCode);
            $this->setData('statusStandardNm', $order->statusStandardNm);
            $this->setData('statusListCombine', $order->statusListCombine);
            $this->setData('statusListExclude', $order->statusListExclude);
            $this->setData('type', $order->getOrderType());
            $this->setData('channel', $order->getOrderChannel());
            $this->setData('settle', $order->getSettleKind());
            $this->setData('formList', $order->getDownloadFormList());
            $this->setData('statusSearchableRange', $order->getOrderStatusList($this->_currentStatusCode));

        } catch (Exception $e) {
            throw $e;
        }
    }
}
