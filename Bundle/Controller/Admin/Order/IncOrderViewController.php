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

use App;
use Exception;
use Request;
use Session;

/**
 * 배송 업체 리스트 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class IncOrderViewController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);
            $data = $order->getOrderView(Request::request()->get('orderNo'), null, null, null, null, Request::request()->get('orderStatusMode'));
            $order->setChannel($data['orderChannelFl']);
            // 상품가공할 정보 처리
            $goods = $data['goods'];
            unset($data['goods'], $data['cnt']);

            // 기준몰? 멀티상점? 탭 선택시의 값
            $this->setData('isUseMall', Request::request()->get('isUseMall') === 'false' ? false : true);

            // request 값에 따른 주문내역 처리
            switch (Request::request()->get('orderStatusMode')) {
                case 'order':
                    $incTitle = __('주문내역');
                    if ($this->getData('isProvider')) {
                        $checkStatus = ['p', 'g', 'd', 's'];
                    } else {
                        $checkStatus = ['o', 'p', 'g', 'd', 's'];
                    }

                    $tmpDataStatusMode = $data['statusMode'];
                    if($data['statusMode'] === 'e'){
                        $tmpDataStatusMode = 'o';
                    }

                    // 주문상태 일괄 변경 처리용 셀렉트 값
                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$tmpDataStatusMode]) === true) {
                            // 실패나 취소의 경우 변경 불가하며, 환불완료는 특정 페이지에서만 처리 가능
                            if (substr($key, 0, 1) == 'f' || substr($key, 0, 1) == 'c' || $key == 'r3') {
                                continue;
                            }
                            // 환불상태가 아닌경우 환불접수 이상 단계를 보여주지 않는다.
                            if ($tmpDataStatusMode != 'r' && substr($key, 0, 1) == 'r' && substr($key, 1, 1) > 1) {
                                continue;
                            }

                            if (!in_array($tmpDataStatusMode, ['r', 'e', 'b']) && in_array(substr($key, 0, 1), $order->statusExcludeCd)) {
                                continue;
                            }

                            //페이코 주문인 경우 입금대기 상태로 변경 불가
                            if ($data['pgName'] == 'payco' && (substr($key, 0, 1) == 'o')) {
                                continue;
                            }

                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                case 'cancel':
                    $incTitle = __('취소내역');
                    $checkStatus = ['c'];
                    $data['statusMode'] = 'c';

                    $reOrderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
                    $originalCount = $reOrderCalculation->getOrderOriginalCount(Request::request()->get('orderNo'), $data['statusMode']);
                    $data['restoreStatus'] = false;
                    if ($originalCount > 0) {
                        $data['restoreStatus'] = true;
                    }
                    // 주문상태 일괄 변경 처리용 셀렉트 값
                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            if (in_array(substr($key, 0, 1), explode(',', 'c,d'))) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                //교환내역
                case 'exchange':
                    $incTitle = __('교환내역');
                    $checkStatus = ['e'];
                    $data['statusMode'] = 'e';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            if (in_array(substr($key, 0, 1), ['d'])) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                //교환취소내역
                case 'exchangeCancel':
                    $incTitle = __('교환취소내역');
                    $checkStatus = ['e'];
                    $data['statusMode'] = 'e';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            if (in_array(substr($key, 0, 1), ['d'])) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                //교환추가내역
                case 'exchangeAdd':
                    $incTitle = __('교환추가내역');
                    $checkStatus = ['z'];
                    $data['statusMode'] = 'z';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            if (in_array(substr($key, 0, 1), ['d'])) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                case 'back':
                    $incTitle = __('반품내역');
                    $checkStatus = ['b'];
                    $data['statusMode'] = 'b';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            // 환불접수 처리
                            if ((substr($key, 0, 1) == 'r' && substr($key, 1, 1) > 1) || in_array(substr($key, 0, 1), ['d'])) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                case 'refund':
                    $incTitle = __('환불내역');
                    $checkStatus = ['r'];
                    $data['statusMode'] = 'r';
                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if($data['orderChannelFl'] == 'naverpay'  ){
                            if (substr($key, 0, 1) == 'r' &&  count(explode('_',$key))>1){
                                $selectBoxOrderStatus[$key] = $val;
                            }
                            continue;
                        }

                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            // 환불접수 처리
                            if (in_array(substr($key, 0, 1), ['d']) || (substr($key, 0, 1) == 'g' && $key != 'g1') || (substr($key, 0, 1) == 'b' && $key != 'b1') || (substr($key, 0, 1) == 'r' && $key != 'r2')) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                case 'fail':
                    $incTitle = __('결제 중단/실패 내역');
                    $checkStatus = ['f'];
                    $data['statusMode'] = 'f';

                    foreach ($order->getOrderStatusAdmin() as $key => $val) {
                        if (in_array(substr($key, 0, 1), $order->statusStandardCode[$data['statusMode']]) === true) {
                            if ($key == 'f1' || in_array(substr($key, 0, 1), ['d'])) {
                                continue;
                            }
                            $selectBoxOrderStatus[$key] = $val;
                        }
                    }
                    break;

                default:
                    throw new Exception(__('호출 주문상태 파라미터가 정상적이지 않습니다.'));
                    break;
            }

            $this->setData('order', $order);
            $this->setData('orderStatusMode', Request::request()->get('orderStatusMode'));
            $this->setData('incTitle', $incTitle);
            $this->setData('selectBoxOrderStatus', $selectBoxOrderStatus);
            $this->setData('orderGridConfigList', $data['orderGridConfigList']);
            //복수배송지를 사용하여 리스트 데이터 배열의 키를 체인지한 데이터인지 체크
            $this->setData('useMultiShippingKey', $data['useMultiShippingKey']);

            // 주문내역 종류별 상품 재 구성
            $allStatusO = true;
            $goodsOrderStatusList = [];
            $goodsOriginalOrderStatusList = [];
            foreach ($goods as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        $goodsOriginalOrderStatusList[] = $val['orderStatus'];
                        $goodsOrderStatusList[] = substr($val['orderStatus'], 0, 1);
                        if (in_array(substr($val['orderStatus'], 0, 1), $checkStatus)) {
                            if($val['orderStatus'] !== 'o1'){
                                $allStatusO = false;
                            }

                            $data['goods'][$sKey][$dKey][] = $val;

                            // 테이블 UI 표현을 위한 변수
                            $addGoodsCnt = $val['addGoodsCnt'];
                            if($data['useMultiShippingKey'] === true){
                                $data['cnt']['multiShipping'][$sKey] += 1 + $addGoodsCnt;
                            }
                            else {
                                $data['cnt']['scm'][$sKey] += 1 + $addGoodsCnt;
                            }
                            $data['cnt']['goods']['all'] += 1 + $addGoodsCnt;
                            $data['cnt']['goods']['goods'] += 1;
                            $data['cnt']['goods']['addGoods'] += $addGoodsCnt;
                            if ($val['mallSno'] > DEFAULT_MALL_NUMBER) {
                                $deliveryUniqueKey = $val['deliverySno'];
                            } else {
                                $deliveryUniqueKey = $val['deliverySno'] . '-' . $val['orderDeliverySno'] . '-' . $val['deliveryMethodFl'] . '-' . $val['goodsDeliveryCollectFl'];
                            }
                            $data['cnt']['delivery'][$deliveryUniqueKey] += 1 + $addGoodsCnt;
                        }
                    }
                }
            }

            $checkBoxOnclickAction = '';
            if($allStatusO === true || in_array(gd_isset($data['statusMode']), $order->statusListCombine)){
                $checkBoxOnclickAction = 'js-checkall';
            }
            $this->setData('checkBoxOnclickAction', $checkBoxOnclickAction);

            //주문상세페이지 액션버튼 정의
            $actionButtonList = $order->getOrderViewStatusActionList(Request::request()->get('orderStatusMode'), $goodsOrderStatusList, $goodsOriginalOrderStatusList, Request::request()->get('orderNo'));
            $this->setData('actionButtonList', $actionButtonList);

            // 스크립트에서 사용하며 주문상태별 변경 가능한 상태를 표현한다. (환불, 반품, 교환은 제외 처리)
            $scriptStatusCode = $order->statusStandardCode; // 현재 상태에 대한 변경 가능 상태 기준표
            foreach ($order->statusExcludeCd as $excludeCd) {
                // 시도상태인 경우 입금대기 혹은 결제확인으로 이동 처리 가능하게 수정
                if ($excludeCd != 'f') {
//                    unset($scriptStatusCode[$excludeCd]);
                }
            }
            $scriptStatusCode = json_encode($scriptStatusCode);
            $this->setData('scriptStatusCode', gd_isset($scriptStatusCode));

            // 배송 업체
            $delivery = App::load(\Component\Delivery\Delivery::class);
            $tmpDelivery = $delivery->getDeliveryCompany(null, true, $data['orderChannelFl']);
            $deliveryCom[0] = '= ' . __('배송 업체') . ' =';
            $deliverySno = 0;

            if (empty($tmpDelivery) === false) {
                foreach ($tmpDelivery as $key => $val) {
                    // 기본 배송업체 sno
                    if ($key == 0) {
                        $deliverySno = $val['sno'];
                    }
                    if($val['deliveryFl'] === 'y') {
                        //택배 업체일때만
                        $deliveryCom[$val['sno']] = $val['companyName'];
                    }
                }
                unset($tmpDelivery);
            }
            $this->setData('deliveryCom', gd_isset($deliveryCom));
            $this->setData('deliverySno', gd_isset($deliverySno));

            // 상품º주문번호별 메모 등록여부 체크
            $ordGoodsMemoInfo = $order->getAdminOrdGoodsMemoToPrint($data['orderNo']);
            $data['adminOrdGoodsMemo'] = $ordGoodsMemoInfo ;

            // 주문데이터 반환
            $this->setData('data', $data);

            // 운영자 기능권한의 게시글 삭제 권한 없음 - 관리자페이지에서만
            $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
            if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
                $this->setData('orderStateChangeDisabled', 'disabled="disabled"');
            }

            // 템플릿 설정
            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/inc_order_view.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
