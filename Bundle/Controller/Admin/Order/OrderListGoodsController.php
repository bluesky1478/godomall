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
use App;
use Request;

/**
 * 상품준비중 리스트
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderListGoodsController extends \Controller\Admin\Controller
{
    /**
     * @var 기본 주문상태
     */
    private $_currentStatusCode = 'g';

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'order', 'goods');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                    'sms.js'
                ]
            );

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            /* 운영자별 검색 설정값 */
            $searchConf = \App::load('\\Component\\Member\\ManagerSearchConfig');
            $searchConf->setGetData();
            $isOrderSearchMultiGrid = gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n');
            $this->setData('isOrderSearchMultiGrid', $isOrderSearchMultiGrid);

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

            //상품준비중 리스트에서 배송정보를 주문별로 처리 할 수 있는지 체크 상태
            if (empty($getData['data']) === false && is_array($getData['data']) && $getValue['view'] !== 'orderGoods') {
                foreach ($getData['data'] as $orderNo => $orderData) {
                    //개별등록 해제 문구가 노출되어야 하는 상태 [true-노출, false-미노출]
                    $deliveryCombineDisplayMessage = false;

                    //주문별 배송정보 등록을 수정되지 않도록 막아야 하는 상태  [true-막음, false-안막음]
                    $deliveryCombinePrevent = false;

                    if(count($orderData['goods']) > 1){
                        //공급사가 여러개면 개별등록 해제 문구 노출, 배송정보 등록 불가
                        $getData['data'][$orderNo]['deliveryCombineDisplayMessage'] = true;
                        $getData['data'][$orderNo]['deliveryCombinePrevent'] = true;
                        continue;
                    }

                    foreach ($orderData['goods'] as $sKey => $sVal) {
                        $deliveryMethodFl = [];
                        $invoiceCompanySno = [];
                        $invoiceNo = [];
                        foreach ($sVal as $dKey => $dVal) {
                            foreach ($dVal as $key => $val) {
                                $deliveryMethodFl[] = $val['deliveryMethodFl'];
                                $invoiceCompanySno[] = $val['invoiceCompanySno'];
                                $invoiceNo[] = $val['invoiceNo'];
                            }
                        }

                        $deliveryMethodFlArr = array_unique($deliveryMethodFl);
                        $invoiceCompanySnoArr = array_unique($invoiceCompanySno);
                        $invoiceNoArr = array_unique($invoiceNo);
                        if(count($deliveryMethodFlArr) > 1){
                            //배송방식이 다를경우 개별등록 해제 문구 노출, 배송정보 등록 불가
                            $deliveryCombineDisplayMessage = true;
                            $deliveryCombinePrevent = true;
                            break;
                        }
                        if(count($invoiceCompanySnoArr) > 1 || count($invoiceNoArr) > 1){
                            //배송방식이 다를경우 개별등록 해제 문구 노출, 배송정보 등록 불가
                            $deliveryCombineDisplayMessage = true;
                            break;
                        }
                    }
                    $getData['data'][$orderNo]['deliveryCombineDisplayMessage'] = $deliveryCombineDisplayMessage;
                    $getData['data'][$orderNo]['deliveryCombinePrevent'] = $deliveryCombinePrevent;
                }
            }

            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('orderGridConfigList', $getData['orderGridConfigList']);
            //복수배송지를 사용하여 리스트 데이터 배열의 키를 체인지한 데이터인지 체크
            $this->setData('useMultiShippingKey', $getData['useMultiShippingKey']);

            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            // 정규식 패턴 view 파라미터 제거
            $pattern = '/view=[^&]+$|searchFl=[^&]+$|view=[^&]+&|searchFl=[^&]+&/';//'/[?&]view=[^&]+$|([?&])view=[^&]+&/';

            // view 제거된 쿼리 스트링
            $queryString = preg_replace($pattern, '', Request::getQueryString());
            $this->setData('queryString', $queryString);

            //상품준비중 리스트에서 주문번호별은 주문별통합되어 보여줌
            if($getValue['view'] !== 'orderGoods'){
                array_push($order->statusListCombine, 'g');
            }

            // --- 주문 일괄처리 셀렉트박스
            foreach ($order->getOrderStatusAdmin() as $key => $val) {
                if (in_array(substr($key, 0, 1), $order->statusStandardCode[$this->_currentStatusCode]) === true && in_array(substr($key, 0, 1), $order->statusExcludeCd) === false) {
//                    if (substr($key, 0, 1) == 'd' && $key != 'd1') {
//                        continue;
//                    }
                    $selectBoxOrderStatus[$key] = $val;
                }
            }
            $this->setData('selectBoxOrderStatus', $selectBoxOrderStatus);

            // 배송 업체
            $delivery = App::load(\Component\Delivery\Delivery::class);
            $tmpDelivery = $delivery->getDeliveryCompany(null, true);
            $deliveryCom[0] = $searchDeliveryCom[0] = '= ' . __('배송 업체') . ' =';
            $deliverySno = 0;
            if (empty($tmpDelivery) === false) {
                foreach ($tmpDelivery as $key => $val) {
                    // 기본 배송업체 sno
                    if ($key == 0) {
                        $deliverySno = $val['sno'];
                    }
                    if($val['deliveryFl'] === 'y'){
                        //택배 업체일때만
                        $deliveryCom[$val['sno']] = $val['companyName'];
                    }
                    //택배 등 모든 배송수단 포함 - 검색용
                    $searchDeliveryCom[$val['sno']] = $val['companyName'];
                }
                unset($tmpDelivery);
            }
            $this->setData('deliveryCom', gd_isset($deliveryCom));
            $this->setData('deliverySno', gd_isset($deliverySno));

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
            $this->setData('statusStandardCode', $order->statusStandardCode);
            $this->setData('statusStandardNm', $order->statusStandardNm);
            $this->setData('statusListCombine', $order->statusListCombine);
            $this->setData('statusListExclude', $order->statusListExclude);
            $this->setData('type', $order->getOrderType());
            $this->setData('channel', $order->getOrderChannel());
            $this->setData('settle', $order->getSettleKind());
            $this->setData('formList', $order->getDownloadFormList());
            $this->setData('statusExcludeCd', $order->statusExcludeCd);
            $this->setData('statusSearchableRange', $order->getOrderStatusList($this->_currentStatusCode));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/order_list_goods.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
