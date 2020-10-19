<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use Exception;
use App;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class CJ대한통운 예약하기
 * @package Bundle\Controller\Admin\Order
 * @author  Lee Namju <lnjts@godo.co.kr>
 */
class LogisticsListController extends \Controller\Admin\Controller
{

    /**
     * {@inheritdoc}
     */

    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'epostParcel', 'logisticsList');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                    'sms.js'
                ]
            );

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\LogisticsOrder');
            $logisticsConfig = gd_policy('logistics.config');
            if(empty($logisticsConfig)) {
                throw new AlertBackException('CJ대한통운 설정 메뉴에서 대한통운 계약정보를 먼저 설정해주세요.');
            }

            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            gd_isset($data['searchPeriod'], 6);

            // -- _GET 값
            $getValue = Request::get()->toArray();
            //주문리스트 그리드 설정

            // 주문출력 범위 설정
            $getValue['statusMode'] = $this->_currentStatusCode;
            $this->setData('currentStatusCode', $this->_currentStatusCode);

            // --- 리스트 설정
            $getData = $order->getOrderListForAdmin($getValue, $data['searchPeriod']);
            //상품준비중 리스트에서 배송정보를 주문별로 처리 할 수 있는지 체크 상태
            $orderInfoGroup = null;
            if (empty($getData['data']) === false && is_array($getData['data']) && $getValue['view'] !== 'orderGoods') {
                foreach ($getData['data'] as $orderNo => $orderData) {
                    //개별등록 해제 문구가 노출되어야 하는 상태 [true-노출, false-미노출]
                    $deliveryCombineDisplayMessage = false;

                    //주문별 배송정보 등록을 수정되지 않도록 막아야 하는 상태  [true-막음, false-안막음]
                    $deliveryCombinePrevent = false;

                    foreach ($orderData['goods'] as $sKey => $sVal) {
                        $deliveryMethodFl = [];
                        $invoiceCompanySno = [];
                        $invoiceNo = [];
                        foreach ($sVal as $dKey => $dVal) {
                            foreach ($dVal as $key => &$val) {
                                $orderInfoGroup[$sKey][] = $val['sno'];
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

            $this->setData('orderInfoGroup', $orderInfoGroup);
            $this->setData('reservationInfo', $getData['reservationInfo']);
            $this->setData('checkOrderMpckKey', $getData['reservationMpckKeyInfo']);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('orderGridConfigList', $getData['orderGridConfigList']);
            //복수배송지를 사용하여 리스트 데이터 배열의 키를 체인지한 데이터인지 체크
            $this->setData('useMultiShippingKey', true);

            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            // 정규식 패턴 view 파라미터 제거
            $pattern = '/view=[^&]+$|searchFl=[^&]+$|view=[^&]+&|searchFl=[^&]+&/';//'/[?&]view=[^&]+$|([?&])view=[^&]+&/';

            // view 제거된 쿼리 스트링
            $queryString = preg_replace($pattern, '', Request::getQueryString());
            $this->setData('queryString', $queryString);

            // --- 템플릿 정의
            $this->getView()->setDefine('layoutOrderSearchForm', Request::getDirectoryUri() . '/logistics_layout_order_search_form.php');// 검색폼
            $this->getView()->setDefine('layoutOrderList', Request::getDirectoryUri() . '/logistics_layout_order_goods_list.php');// 리스트폼

            // --- 템플릿 변수 설정
            $this->setData('statusListCombine', $order->statusListCombine);
            $this->setData('type', $order->getOrderType());
            $this->setData('channel', $order->getOrderChannel());
            $this->setData('settle', $order->getSettleKind());
            $this->setData('formList', $order->getDownloadFormList());

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/logistics_order_list_goods.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
