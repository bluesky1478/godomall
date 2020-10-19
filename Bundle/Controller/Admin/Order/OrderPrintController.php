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
use Framework\Debug\Exception\AlertCloseException;
use Globals;
use UserFilePath;
use Request;
use Framework\Utility\ArrayUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Component\Member\Manager;

/**
 * 주문내역서/간이영수증/거래명세서/세금계산서 출력 팝업
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderPrintController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $delivery = \App::load('\\Component\\Delivery\\Delivery');
            $member = \App::load('\\Component\\Member\\MemberAdmin');
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');

            // 팝업 제목
            switch (Request::post()->get('orderPrintMode')) {
                // 세금계산서
                case 'taxInvoice':
                    $popupTitle = '세금계산서';
                    break;

                // 주문내역서
                case 'report':
                    $popupTitle = '주문내역서';
                    break;

                // 주문내역서 (고객용)
                case 'customerReport':
                    $popupTitle = '주문내역서 (고객용)';
                    break;

                // 간이영수증
                case 'reception':
                    $popupTitle = '간이영수증';
                    break;

                // 거래명세서
                case 'particular':
                    $popupTitle = '거래명세서';
                    break;
            }

            // 데이터 처리
            if (Request::post()->get('orderPrintMode') == 'taxInvoice') {
                $arrOrderNo = array_unique(ArrayUtils::removeEmpty(explode(INT_DIVISION, Request::post()->get('orderPrintCode'))));

                $tax = \App::load('\\Component\\Order\\Tax');
                foreach ($arrOrderNo as $oKey => $oVal) {
                    $orderData[$oVal] = $tax->getOrderTaxInvoice($oVal);
                }

                // 기타 주문채널 주문인쇄 노출 방지
                if(in_array('etc', array_column($orderData, 'orderChannelFl'))){
                    throw new Exception('기타주문채널의 주문건은 ('.$popupTitle.')를 지원하지 않습니다.');
                }

                $modeStr = Request::post()->get('modeStr');
                gd_isset($modeStr, 'all');

                // 출력 종류
                if ($modeStr == 'blue') {
                    $taxInfo['classids'] = ['cssblue'];                //-- 공급받는자용
                } else if ($modeStr == 'red') {
                    $taxInfo['classids'] = ['cssred'];                //-- 공급자용
                } else {
                    $taxInfo['classids'] = [
                        'cssblue',
                        'cssred',
                    ];    //-- 공급받는자용, 공급자용
                }
                $taxInfo['headuser'] = [
                    'cssblue' => __('공급받는자보관용'),
                    'cssred'  => __('공급자보관용'),
                ];

                $this->setData('taxInfo', gd_isset($taxInfo));
            } else {
                $orderData = $order->getOrderPrint(Request::post()->get('orderPrintCode'), Request::post()->get('orderPrintMode'));

                // 기타 주문채널 주문인쇄 노출 방지
                if (!in_array(Request::post()->get('orderPrintMode'), ['report', 'customerReport']) && in_array('etc', array_column($orderData, 'orderChannelFl'))) {
                    throw new Exception('기타주문채널의 주문건은 (' . $popupTitle . ')를 지원하지 않습니다.');
                }

                // 주문내역서
                if (Request::post()->get('orderPrintMode') == 'report' || Request::post()->get('orderPrintMode') == 'customerReport') {
                    //주문상세페이지 그리드 항목설정
                    $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
                    $orderAdminGridMode = $orderAdminGrid->getOrderAdminGridMode('order');
                    $orderGridConfigList = $orderAdminGrid->getSelectOrderGridConfigList($orderAdminGridMode);

                    //리스트 그리드 항목에 브랜드가 있을경우 브랜드 정보 포함
                    if(array_key_exists('brandNm', $orderGridConfigList)){
                        $brandData = [];
                        $brand = \App::load('\\Component\\Category\\Brand');
                        $brandOriginalData = $brand->getCategoryData(null, null, 'cateNm');
                        if(count($brandOriginalData) > 0){
                            $brandData = array_combine(array_column($brandOriginalData, 'cateCd'), array_column($brandOriginalData, 'cateNm'));
                            $this->setData('brandData', gd_isset($brandData));
                        }
                    }

                    // 배송업체 정보
                    $tmpDelivery = $delivery->getDeliveryCompany();
                    foreach ($tmpDelivery as $key => $val) {
                        $deliveryCom[$val['sno']] = $val['companyName'];
                    }
                    unset($tmpDelivery);

                    // 회원 정보
                    foreach ($orderData as $oKey => $oVal) {
                        $orderData[$oKey]['memInfo'] = $member->getMemberId($orderData[$oKey]['memNo']);
                        $orderData[$oKey]['addFieldData'] = $order->getOrderAddFieldView($oVal['addField']);
                        //주문내역서 출력 설정에 따른 정보표기
                        $orderData[$oKey]['orderPrint'] = $order->getOrderPrintOdData($orderData[$oKey], Request::post()->get('orderPrintMode'));
                        $orderData[$oKey]['totalSalePrintPreviewPrice'] = $orderData[$oKey]['totalSalePrice'];
                        $orderData[$oKey]['visitDeliveryInfo'] = $delivery->getVisitDeliveryInfo($orderData[$oKey]);
                        // 리스트 그리드 항목 설정
                        if (empty($orderGridConfigList) === false) {
                            $orderData[$oKey]['orderGridConfigList'] = $orderGridConfigList;
                        }

                        // 환불주문건을 체크하여 금액 변경
                        $originalDataCount = $orderReorderCalculation->getOrderOriginalCount($oVal['orderNo'], '', false);
                        if($originalDataCount > 0){
                            $isRecentClaimRefund = $orderReorderCalculation->getRefundClaimExistFl($oVal['orderNo']);
                            if($isRecentClaimRefund === true){
                                $refundFinalData = [];
                                $refundFinalData = $orderReorderCalculation->getOrderViewPriceRefundAdjust($oVal);
                                $orderData[$oKey]['totalDeliveryCharge'] = $refundFinalData['totalDeliveryCharge'];
                                $orderData[$oKey]['useDeposit'] = $refundFinalData['useDeposit'];
                                $orderData[$oKey]['useMileage'] = $refundFinalData['useMileage'];
                                $orderData[$oKey]['totalGoodsPrice'] = $refundFinalData['totalGoodsPrice'];
                                $orderData[$oKey]['totalGoodsDcPrice'] = $refundFinalData['totalGoodsDcPrice'];
                                $orderData[$oKey]['totalMemberDcPrice'] = $refundFinalData['totalMemberDcPrice'];
                                $orderData[$oKey]['totalMemberOverlapDcPrice'] = $refundFinalData['totalMemberOverlapDcPrice'];
                                $orderData[$oKey]['totalCouponOrderDcPrice'] = $refundFinalData['totalCouponOrderDcPrice'];
                                $orderData[$oKey]['totalCouponGoodsDcPrice'] = $refundFinalData['totalCouponGoodsDcPrice'];
                                $orderData[$oKey]['totalEnuriDcPrice'] = $refundFinalData['totalEnuriDcPrice'];
                                $orderData[$oKey]['totalCouponDeliveryDcPrice'] = $refundFinalData['totalCouponDeliveryDcPrice'];
                                $orderData[$oKey]['totalMemberDeliveryDcPrice'] = $refundFinalData['totalMemberDeliveryDcPrice'];
                                $orderData[$oKey]['totalGoodsMileage'] = $refundFinalData['totalGoodsMileage'];
                                $orderData[$oKey]['totalMemberMileage'] = $refundFinalData['totalMemberMileage'];
                                $orderData[$oKey]['totalCouponGoodsMileage'] = $refundFinalData['totalCouponGoodsMileage'];
                                $orderData[$oKey]['totalMileage'] = $refundFinalData['totalMileage'];
                                $orderData[$oKey]['totalSalePrintPreviewPrice'] = $refundFinalData['totalGoodsDcPrice'] + $refundFinalData['totalMemberDcPrice'] + $refundFinalData['totalMemberOverlapDcPrice'] + $refundFinalData['totalCouponOrderDcPrice'] + $refundFinalData['totalCouponGoodsDcPrice'] + $refundFinalData['totalCouponDeliveryDcPrice'] + $refundFinalData['totalMemberDeliveryDcPrice'] + $refundFinalData['totalEnuriDcPrice'];
                            }
                        }
                    }
                }

                // 간이영수증
                if (Request::post()->get('orderPrintMode') == 'reception') {
                    // 환불 정보
                    foreach ($orderData as $oKey => $oVal) {
                        $refundCtn = 0;
                        foreach ($oVal['goods'] as $sKey => $sVal) {
                            foreach ($sVal as $dKey => $dVal) {
                                foreach ($dVal as $gKey => $gVal) {
                                    if ($gVal['handleSno'] > 0 && $gVal['orderStatus'] == 'r3') {
                                        $orderData[$oKey]['refund']['refundFl'] = 'y';
                                        $orderData[$oKey]['refund']['refundPrice'] += $gVal['refundPrice'];
                                        if (empty($orderData[$oKey]['refund']['refundGoodsNm']) == true) {
                                            $orderData[$oKey]['refund']['refundGoodsNm'] = $gVal['goodsNm'];
                                        } else {
                                            $refundCtn++;
                                        }
                                    }
                                }
                            }
                        }

                        if (empty($orderData[$oKey]['refund']) == false && $orderData[$oKey]['refund']['refundFl'] == 'y') {
                            $orderData[$oKey]['refund']['refundCnt'] = $refundCtn + 1;
                            if ($refundCtn > 0) {
                                $orderData[$oKey]['refund']['refundGoodsNm'] = $orderData[$oKey]['refund']['refundGoodsNm'] . ' 외 ' . $refundCtn . '건';
                            }
                        }
                    }
                }
            }

            // 인감 이미지는 기존은 세금계산서 정보에, 현재는 기본 정보에 등록됨
            $taxInvoice = gd_policy('order.taxInvoice');
            $basicData = gd_policy('basic.info');
            if (empty($taxInvoice['taxStampIamge']) === false) {
                $sealPath = UserFilePath::data('etc', $taxInvoice['taxStampIamge'])->www();
            } else if (empty($basicData['stampImage']) === false) {
                $sealPath = UserFilePath::data('etc', $basicData['stampImage'])->www();
            } else {
                $sealPath = '';
            }
            unset($taxInvoice, $basicData);

//            // 주문데이터가 없는 경우 창닫기
//            if (empty($orderData) === false) {
//                throw new AlertOnlyException('관련 정보가 존재하지 않습니다.', null, null, 'parent.close();');
//            }

            $this->setData('orderData', gd_htmlspecialchars(gd_isset($orderData)));

            // 거래명세서, 주문내역서 (고객용) 번역
            if (Request::post()->get('orderPrintMode') == 'particular' || Request::post()->get('orderPrintMode') == 'customerReport') {
                foreach ($orderData as $oKey => $oVal) {
                    $locale[$oKey] = 'ko_KR';
                    if ($orderData[$oKey]['domainFl']) {
                        switch ($orderData[$oKey]['domainFl']) {
                            case 'us':
                                $locale[$oKey] = 'en_US';
                                break;
                            case 'jp':
                                $locale[$oKey] = 'ja_JP';
                                break;
                            case 'cn':
                                $locale[$oKey] = 'zh_CN';
                                break;
                        }
                    }
                    $translator[$oKey] = StringUtils::setTranslator($locale[$oKey]);
                }
                $this->setData('translator', $translator);
                $this->setData('locale', $locale);
            }

            // 마일리지 사용 정책
            $mileageUse = gd_policy('member.mileageBasic');
            $this->setData('mileageUse', $mileageUse);

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_blank.php');
            $this->getView()->setDefine('layoutForm', Request::getDirectoryUri() . '/order_print_' . Request::post()->get('orderPrintMode') . '.php');

            $this->getView()->setPageName('order/order_print.php');

            $this->setData('popupTitle', gd_isset($popupTitle));
            $this->setData('gMall', gd_htmlspecialchars((Globals::get('gMall'))));
            $this->setData('sealPath', gd_isset($sealPath));
            $this->setData('_delivery', Globals::get('gDelivery'));
            $this->setData('deliveryCom', gd_isset($deliveryCom));
            $this->setData('settle', gd_isset($settle));
            $this->setData('memInfo', gd_htmlspecialchars(gd_isset($memInfo)));
            $this->setData('orderPrintMode', Request::post()->get('orderPrintMode'));
            $this->setData('isProvider', Manager::isProvider());

        } catch (Exception $e) {
            throw new AlertCloseException($e->getMessage());
        }
    }
}
