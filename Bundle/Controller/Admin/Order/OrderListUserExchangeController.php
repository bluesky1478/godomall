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

use Globals;
use Request;

/**
 * 교환 접수 리스트 페이지
 * [관리자 모드] 교환 접수 리스트 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderListUserExchangeController extends \Controller\Admin\Controller
{
    /**
     * @var array $_currentUserHandleMode 반품/교환/환불신청 상태
     */
    private $_currentUserHandleMode = 'e';

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'cancel', 'userExchange');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                    'sms.js',
                ]
            );

            // --- 모듈 호출
            /** @var \Bundle\Component\Order\OrderAdmin $order */
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();
            $isOrderSearchMultiGrid = gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n');
            $this->setData('isOrderSearchMultiGrid', $isOrderSearchMultiGrid);

            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            gd_isset($data['searchPeriod'], 6);

            $useClaimSetting = gd_policy('system.useClaimSetting');
            $this->setData('useClaimSetting', $useClaimSetting['use']);

            // -- _GET 값
            $getValue = Request::get()->toArray();

            //주문리스트 그리드 설정
            $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
            $getValue['orderAdminGridMode'] = $orderAdminGrid->getOrderAdminGridMode($getValue['view']);
            $this->setData('orderAdminGridMode', $getValue['orderAdminGridMode']);

            // 현재 페이지 값
            $currentView = gd_isset($getValue['view'], 'exchange');
            $this->_currentUserHandleMode = substr($currentView, 0, 1);

            // 반품/교환/환불신청 모드 설정
            $getValue['userHandleMode'] = $this->_currentUserHandleMode;
            $this->setData('currentUserHandleMode', $this->_currentUserHandleMode);

            // 정규식 패턴 view 파라미터 제거
            $pattern = '/view=[^&]+$|searchFl=[^&]+$|view=[^&]+&|searchFl=[^&]+&/';//'/[?&]view=[^&]+$|([?&])view=[^&]+&/';

            // view 제거된 쿼리 스트링
            $queryString = preg_replace($pattern, '', Request::getQueryString());
            $this->setData('queryString', $queryString);

            // --- 리스트 설정
            $getData = $order->getOrderListForAdmin($getValue, $data['searchPeriod'], true);
            $this->setData('checked', $getData['checked']);
            $this->setData('search', $getData['search']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('isUserHandle', gd_isset($getData['isUserHandle']));
            $this->setData('orderStatusCodeByAdmin', $order->getOrderStatusAdmin());
            $this->setData('orderGridConfigList', $getData['orderGridConfigList']);
            //복수배송지를 사용하여 리스트 데이터 배열의 키를 체인지한 데이터인지 체크
            $this->setData('useMultiShippingKey', $getData['useMultiShippingKey']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            // 반품/교환/환불 수량
            $this->setData('userHandleCount', $order->getCountUserHandles());

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
            $this->setData('handleFl', $order->getUserHandleFl());
            $this->setData('channel', $order->getOrderChannel());
            $this->setData('settle', $order->getSettleKind());
            $this->setData('formList', $order->getDownloadFormList());
            $this->setData('statusExcludeCd', []);

            // 공급사와 동일한 페이지 사용
            switch (Request::get()->get('view')) {
                case 'exchange':
                    $this->getView()->setPageName('order/order_list_user_exchange.php');
                    $currentTabView = 'order_list_user_exchange';
					break;
                case 'back':
                    $this->getView()->setPageName('order/order_list_user_back.php');
                    $currentTabView = 'order_list_user_return';
                    break;
                case 'refund':
                    $this->getView()->setPageName('order/order_list_user_refund.php');
                    $currentTabView = 'order_list_user_refund';
                    break;
            }
            // 레이어를 위한 Tab 별 페이지 view 선언
            $this->setData('currentTabView', $currentTabView);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
