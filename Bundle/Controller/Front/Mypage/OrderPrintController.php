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
use Globals;
use Session;
use Request;
use Framework\Debug\Exception\AlertCloseException;
use Component\Member\Util\MemberUtil;
use UserFilePath;
use Framework\Utility\NumberUtils;

/**
 * 주문인쇄
 *
 * @author cjb3333 <cjb3333@godo.co.kr>
 */
class OrderPrintController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {

        try {

            if (Request::post()->get('orderPrintMode') == "reception" && gd_is_plus_shop(PLUSSHOP_CODE_RECEPTION) === false ) {
                throw new AlertCloseException('플러스샵 설치가 필요합니다.');
            }

            switch (Request::post()->get('orderPrintMode')) {
                // 간이영수증
                case 'reception':
                    $popupTitle = __('간이영수증');
                    break;
                case 'particular':
                    $popupTitle = __('거래명세서');
                    break;
            }

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $orderData = $order->getOrderPrint(Request::post()->get('orderPrintCode'), Request::post()->get('orderPrintMode'))[0];

            if (MemberUtil::checkLogin() == 'member' || MemberUtil::checkLogin() == 'guest') {
                if ((MemberUtil::checkLogin() == 'member' && $orderData['memNo'] != Session::get('member.memNo')) || (MemberUtil::checkLogin() == 'guest' && $orderData['orderName'] != Session::get('guest.orderNm'))) {
                    throw new \Exception('회원정보가 존재하지 않습니다.');
                }
            } else {
                throw new \Exception('회원정보가 존재하지 않습니다.');
            }

            // 기타 주문채널 주문인쇄 노출 방지
            if ($orderData['orderChannelFl'] === 'etc') {
                throw new AlertCloseException('기타주문채널의 주문건은 (' . $popupTitle . ')를 지원하지 않습니다.');
            }

            for ($i = 1; $i < 13; $i++) $fillSpace[] = '';

            // 거래명세서 정보
            if (Request::post()->get('orderPrintMode') == 'particular') {
                // 환불금액 및 세액 계산
                $isRefund = false;
                foreach ($orderData['goods'] as $sKey => $sVal) {
                    foreach ($sVal as $dKey => $dVal) {
                        foreach ($dVal as $gKey => $gVal) {
                            if ($gVal['handleSno'] > 0 && $gVal['orderStatus'] == 'r3') {
                                $isRefund = true;
                                // 환불금액 정보
                                $orderData['totalSalePrice'] -= $gVal['goodsDcPrice'] + $gVal['memberDcPrice'] + $gVal['memberOverlapDcPrice'] + $gVal['couponGoodsDcPrice'] + $gVal['totalDivisionCouponOrderDcPrice'];
                                $orderData['useMileage'] -= $gVal['refundUseMileage'];
                                $orderData['useDeposit'] -= $gVal['refundUseDeposit'];
                            }

                            //과세인경우 전체금액으로 10% 고정 재계산
                            if ($gVal['goodsTaxInfo'][0] == 't') {
                                $tmpPrice =  NumberUtils::taxAll($gVal['goodsSumPrice'], '10', 't');
                                $orderData['goods'][$sKey][$dKey][$gKey]['goodsVat']['tax'] = $tmpPrice['tax'];
                                $orderData['goods'][$sKey][$dKey][$gKey]['goodsVat']['supply'] = $tmpPrice['supply'];
                            }

                            //수량노출인 경우 총 수량 계산
                            if($gVal['orderStatus'] !== 'r3'){
                                $totalQuantity += (int)$gVal['goodsCnt'];
                            }
                        }
                    }
                }
                // 환불했을 경우 총 부가세율 및 금액 재계산
                if ($isRefund == true) {
                    $tmpPrice = NumberUtils::taxAll($orderData['realTaxSupplyPrice'] + $orderData['realTaxVatPrice'], '10', 't');
                    $orderData['supplyPrice'] = $tmpPrice['supply'];
                    $orderData['taxPrice'] = $tmpPrice['tax'];
                }

                $linePlus = 0;
                $saleVat = 0;
                $deliveryVat = 0;
                $useDepositVat = 0;
                $useMileageVat = 0;

                // 주문할인 가격 설정
                if ($orderData['totalSalePrice'] > 0) {
                    $saleVat = $orderData['saleVat'];
                    $linePlus = $linePlus + 1;
                    $totalQuantity += 1;
                }

                // 사용된 마일리지 가격 설정
                if ($orderData['useMileage'] > 0) {
                    $useMileageVat = gd_tax_all($orderData['useMileage'], 10);
                    $totalQuantity += 1;
                }

                // 사용된 예치금 가격 설정
                if ($orderData['useDeposit'] > 0) {
                    $useDepositVat = gd_tax_all($orderData['useDeposit'], 10);
                    $totalQuantity += 1;
                }

                // 사용된 배송비 가격 설정
                if ($orderData['totalDeliveryCharge'] > 0) {
                    $deliveryVat = $orderData['deliveryVat'];
                    $totalQuantity += 1;
                }


                //거래명세서 출력 설정에 따른 정보표기
                $orderData['orderPrint'] = $order->getOrderPrintData($orderData, true);
                if($orderData['orderPrint']['orderPrintSameDisplay'] === 'y'){
                    if($orderData['orderPrint']['orderPrintQuantityDisplay'] === 'y'){
                        $orderData['totalQuantity'] = $totalQuantity;
                    }
                }
                else {
                    unset($orderData['orderPrint']);
                }


                $this->setData('deliveryVat', $deliveryVat);
                $this->setData('saleVat', $saleVat);
                $this->setData('useMileageVat', $useMileageVat);
                $this->setData('useDepositVat', $useDepositVat);
                $this->setData('linePlus', $linePlus);
            }

            // 간이영수증
            if (Request::post()->get('orderPrintMode') == 'reception') {
                // 환불 정보
                $refundCtn = 0;
                foreach ($orderData['goods'] as $sKey => $sVal) {
                    foreach ($sVal as $dKey => $dVal) {
                        foreach ($dVal as $gKey => $gVal) {
                            if ($gVal['handleSno'] > 0 && $gVal['orderStatus'] == 'r3') {
                                $orderData['refund']['refundFl'] = 'y';
                                $orderData['refund']['refundPrice'] += $gVal['refundPrice'];
                                if (empty($orderData['refund']['refundGoodsNm']) == true) {
                                    $orderData['refund']['refundGoodsNm'] = $gVal['goodsNm'];
                                } else {
                                    $refundCtn++;
                                }
                            }
                        }
                    }
                }

                if (empty($orderData['refund']) == false && $orderData['refund']['refundFl'] == 'y') {
                    $orderData['refund']['refundCnt'] = $refundCtn + 1;
                    if ($refundCtn > 0) {
                        $orderData['refund']['refundGoodsNm'] = $orderData['refund']['refundGoodsNm'] . ' ' . __('외') . ' ' . $refundCtn . __('건');
                    }
                }
            }

            $this->setData('orderData', gd_htmlspecialchars(gd_isset($orderData)));

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

            $this->setData('fillSpace', $fillSpace);
            $this->setData('popupTitle', gd_isset($popupTitle));
            $this->setData('gMall', gd_htmlspecialchars((Globals::get('gMall'))));
            $this->setData('settle', gd_isset($settle));
            $this->setData('orderPrintMode', Request::post()->get('orderPrintMode'));
            $this->setData('sealPath', $sealPath);
            $this->getView()->setDefine('orderPrintTemplate', 'mypage/_' . Request::post()->get('orderPrintMode') . '.html');
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

}
