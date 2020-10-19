<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Goods;

use Component\Cart\Cart;
use Component\Database\DBTableField;
use Component\Delivery\Delivery;
use Component\Goods\AddGoods;
use Component\Goods\Goods;
use Component\Naver\NaverPay;
use Component\Order\Order;
use Component\Order\OrderAdmin;
use Component\Policy\Policy;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertOnlyException;
use Request;
use Framework\Utility\SkinUtils;
use Framework\Utility\GodoUtils;

class NaverPayController extends \Controller\Front\Controller
{
    const NOT_STRING = Array('%01', '%02', '%03', '%04', '%05', '%06', '%07', '%08', '%09', '%0A', '%0B', '%0C', '%0D', '%0E', '%0F', '%10', '%11', '%12', '%13', '%14', '%15', '%16', '%17', '%18', '%19', '%1A', '%1B', '%1C', '%1D', '%1E', '%1F');
    private $db;
    private $orderData;
    private $delivery;

    public function index()
    {
        try {
            $naverPay = new NaverPay();
            $naverPayConfig = $naverPay->getConfig();

            $policy = new Policy();
            $naverpayPolicy = $policy->getNaverPaySetting();

            if ($naverPay->checkUse() === false) {
                throw new \Exception(__('네이버페이 사용을 중단하였습니다.'));
            }

            if (!is_object($this->db)) {
                $this->db = \App::load('DB');
            }

            $cart = new Cart();
            $cart->setChannel('naverpay');
            $goods = new Goods();
            $order = new Order();
            $this->delivery = new Delivery();

            $postValue = \Request::request()->all();

            //상품할인쿠폰 초기화
            unset($postValue['couponApplyNo']);
            $postValue['couponApplyNo'][0] = null;
            unset($postValue['couponSalePriceSum']);
            $postValue['couponSalePriceSum'][0] = null;
            unset($postValue['couponAddPriceSum']);
            $postValue['couponAddPriceSum'][0] = null;

            if ($postValue['mode'] == 'cart') { //장바구니에서 접근
                $backUrl = URI_HOME . "order/cart.php?inflow=naverPay";
                $arrCartSno = $postValue['cartSno'];
            } else {
                if (!$postValue['goodsNo']) {
                    throw new AlertOnlyException(__('상품을 선택해주세요.'));
                }
                $backUrl = URI_HOME . "goods/goods_view.php?inflow=naverPay&goodsNo=" . $postValue['goodsNo'][0];
                $postValue['cartMode'] = 'd';

                // 메인 분류 통계
                $request = \App::getInstance('request');
                $referer = $request->getReferer();
                unset($mtn);
                parse_str($referer);
                gd_isset($mtn);
                if (empty($mtn) === false) {
                    $postValue['linkMainTheme'] = $mtn;
                }
                $arrCartSno = $cart->saveInfoCart($postValue, 'y');
            }

            /**
             * 주문데이터 저장
             */
            $cartInfo = $cart->getCartGoodsData($arrCartSno, null, null, true);
            // 주문불가한 경우 진행 중지
            if (!$cart->orderPossible) {
                throw new \Exception(__('구매 불가 상품이 포함되어 있으니 장바구니에서 확인 후 다시 주문해주세요.'));
            }

            foreach ($cartInfo as $val) {
                foreach ($val as $_val) {
                    foreach ($_val as $row) {
                        if ($row['price']['goodsPriceSubtotal'] == 0 && !$row['addGoodsNo']) { //할인 적용된 가격 (추가상품은 제외)
                            throw new \Exception(__('판매가보다 할인적용이 높은 상품은 네이버페이로 구매하실 수 없습니다.'));
                        }
                    }
                }
            }

            $data = [
                'orderChannelFl' => 'naverpay',
                'cartSno' => $arrCartSno,
                'shipping' => 'on',
                'totalDeliveryCharge' => $cart->totalDeliveryCharge,
                'settlePrice' => $cart->totalSettlePrice,
                'payment' => 'on',
            ];

            $orderPrice = $cart->setOrderSettleCalculation($data);
            $goodsPrice = $orderPrice['settleTotalGoodsPrice'];
            $result = $order->saveOrderInfo($cartInfo, $data, $orderPrice, false);
            $orderNo = $order->orderNo;
            $acecounterCommonScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
            $acecounterUse = $acecounterCommonScript->getAcecounterUseCheck();
            if ($acecounterUse) {
                if (\Cookie::has('_ACU' . $acecounterCommonScript->config['aceCode'])) {
                    $checkoutData['aceCid'] = \Cookie::get('_ACU' . $acecounterCommonScript->config['aceCode']);
                }
                if ($acecounterCommonScript->hasAceAet()) {
                    $checkoutData['aceAet'] = $acecounterCommonScript->getAceAet();
                } else {
                    $checkoutData['aceAet'] = $acecounterCommonScript->setAceAet();
                }
            }
            $checkoutData['setDeliveryInfo'] = $cart->setDeliveryInfo;
            $checkoutData = json_encode($checkoutData, JSON_UNESCAPED_UNICODE);
            $arrBind = $this->db->get_binding(DBTableField::tableOrder(), ['checkoutData' => $checkoutData], 'update', ['checkoutData']);
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
            if ($result === false) {
                exit('error');
            }

            // S: 튜닝 여부 확인 *** 수정 금지 ***
            $tuningFl = GodoUtils::getTuningFl('20_711', true);
            // E : 튜닝여부 확인 *** 수정 금지 ***

            $orderGoodsList = $order->getOrderGoods($orderNo);

            $addOrderGoodsData = null;
            $beforeOrderGoodsNo = null;
            $freeCondition = null;//조건포함배송비정책인경우 해당하는 공급사 배송들은 모두 무료
            foreach ($orderGoodsList as $orderGoodsData) {
                if ($orderGoodsData['goodsType'] == 'addGoods' && $beforeOrderGoodsNo) {
                    $addOrderGoodsData[$beforeOrderGoodsNo][$orderGoodsData['parentGoodsNo']][] = $orderGoodsData;
                } else {
                    $beforeOrderGoodsNo = $orderGoodsData['sno'];
                }
                $deliveryData = $this->delivery->getDataBySno($orderGoodsData['orderDeliverySno']);
                $basicDeliveryData = $this->delivery->getSnoDeliveryBasic($deliveryData['deliverySno']);
                if ($basicDeliveryData['freeFl'] == 'y' && $basicDeliveryData['fixFl'] == 'free') {
                    $freeCondition[$basicDeliveryData['scmNo']] = 'free';
                }
                $goodsDeliveryFl = $deliveryData['goodsDeliveryFl'];
                $deliveryCollectFl = $orderGoodsData['goodsDeliveryCollectFl'];
                if ($goodsDeliveryFl == 'n' && $deliveryCollectFl == 'pre' && $orderGoodsData['goodsType'] != 'addGoods') {  //상품별 배송비고 선불이면
                    $deliveryInfo[$orderGoodsData['orderDeliverySno']][] = $orderGoodsData['sno'];
                } else {
                    $getFeePay[$orderGoodsData['orderDeliverySno']][] = $orderGoodsData['goodsDeliveryCollectFl'];
                }
            }

            $arr_data[] = '<?xml version="1.0" encoding="utf-8"?>';
            $arr_data[] = '<order>';
            $arr_data[] = '<merchantId>' . $naverPayConfig['naverId'] . '</merchantId>';//네이버 공통 인증키
            $arr_data[] = '<certiKey>' . $naverPayConfig['connectId'] . '</certiKey>';//연동 인증키

            $logGroupId = null;
            $index = 0;
            $goodsNoArr = [];
            foreach ($orderGoodsList as $orderGoodsData) {
                if ($orderGoodsData['goodsType'] == 'addGoods') {
                    continue;
                }
                $goodsNo = $orderGoodsData['goodsNo'];
                $goodsNoArr[] = $goodsNo;
                $goodsData = $goods->getGoodsView($goodsNo);
                $mcstCultureBenefitYn = $mcstCultureBenefitYn ?? $goodsData['cultureBenefitFl'];
                if($mcstCultureBenefitYn != $goodsData['cultureBenefitFl']) {
                    $this->alert(__('도서공연비 소득공제 상품은 일반 상품과 함께 네이버페이로 구매하실 수 없습니다.'));
                }
                $deliverySno = $orderGoodsData['orderDeliverySno'];
                $isNotOption = $goodsData['optionFl'] != 'y' && $goodsData['optionTextFl'] != 'y' ? true : false;

                $goodsImage = $goods->getGoodsImage($goodsNo, 'main');
                if ($goodsImage) {
                    $goodsImageName = $goodsImage[0]['imageName'];
                    $goodsImageSize = $goodsImage[0]['imageSize'];
                    $_imageInfo = pathinfo($goodsImageName);
                    if (!$goodsImageSize) {
                        $goodsImageSize = SkinUtils::getGoodsImageSize($_imageInfo['extension']);
                        $goodsImageSize = $goodsImageSize['size1'];
                    }
                }
                $goodsImageSrc = SkinUtils::imageViewStorageConfig($goodsImageName, $goodsData['imagePath'], $goodsData['imageStorage'], $goodsImageSize, 'goods', false)[0];
                if($goodsData['imageStorage'] == 'local'){
                    $goodsImageSrc = str_replace('https', 'http', $goodsImageSrc);
                    $goodsImageSrc = str_replace(':443', '', $goodsImageSrc);
                }


                $basePrice = (int)$orderGoodsData['goodsPrice'];// - (int)$orderGoodsData['goodsDcPrice'];
                $goodsCnt = $orderGoodsData['goodsCnt'];

                $arr_data[] = '<product>';
                $arr_data[] = '<id>' . $orderGoodsData['goodsNo'] . '</id>';
                $arr_data[] = '<ecMallProductId>' . $goodsData['goodsNo'] . '</ecMallProductId>';
                $arr_data[] = '<name>' . $this->cdata(urldecode(str_replace(NOT_STRING, '', urlencode($orderGoodsData['goodsNm'])))) . '</name>';

                if ($naverpayPolicy['saleFl'] == 'y') {
                    $basePrice = $basePrice - (($orderGoodsData['goodsDcPrice'] + $orderGoodsData['myappDcPrice']) / $goodsCnt);
                }

                $arr_data[] = '<basePrice>' . $basePrice . '</basePrice>';
                $arr_data[] = '<taxType>' . $this->getTaxType($goodsData['taxFreeFl']) . '</taxType>';
                $arr_data[] = '<infoUrl>' . $this->cdata(URI_HOME . 'goods' . DS . 'goods_view.php?inflow=naverPay&goodsNo=' . $goodsData['goodsNo']) . '</infoUrl>';
                $arr_data[] = '<imageUrl>' . $this->cdata($goodsImageSrc) . '</imageUrl>';

                if (count($addOrderGoodsData[$orderGoodsData['sno']][$goodsNo]) > 0) {
                    $arrAddGoods = $addOrderGoodsData[$orderGoodsData['sno']][$goodsNo];
                    foreach ($arrAddGoods as $val) {
                        $addGoodsPrice = (int)$val['goodsPrice'] - ($val['myappDcPrice'] / $val['goodsCnt']);
                        if ($addGoodsPrice < 0) {
                            $this->alert(__('추가상품가격이 음수(마이너스)인 상품은 네이버페이로 구매하실 수 없습니다'));
                        }

                        $addGoodsOptionText = '';
                        $addGoodsOption = $this->decodeJson($val['optionInfo']);
                        if ($addGoodsOption[0][1]) {
                            $addGoodsOptionText = '(' . $addGoodsOption[0][1] . ')';
                        }
                        $arr_data[] = '<supplement>';
                        //                        debug($orderGoodsData,true);
                        $arr_data[] = '<id>addGoods'.'_'.$val['parentGoodsNo'].'_'.$val['goodsNo'].'</id>';
                        $arr_data[] = '<name>' . $this->cdata($val['goodsNm'] . $addGoodsOptionText) . '</name>';
                        $arr_data[] = '<price>' . $addGoodsPrice . '</price>';
                        $arr_data[] = '<quantity>' . $val['goodsCnt'] . '</quantity>';
                        $arr_data[] = '</supplement>';
                    }
                }

                //옵션체크
                if ($isNotOption) { //단품
                    $arr_data[] = '<single>';
                    $arr_data[] = '<quantity>' . $goodsCnt . '</quantity>';
                    $arr_data[] = '</single>';
                } else {
                    $arr_data[] = '<option>';
                    $optionPrice = 0;
                    $arr_data[] = '<quantity>' . $goodsCnt . '</quantity>';
                    if ($orderGoodsData['optionInfo'] && $goodsData['optionFl'] == 'y') {    //옵션선택형
                        $optionInfo = $this->decodeJson($orderGoodsData['optionInfo']);
                        foreach ($optionInfo as $opt) {
                            $_name = $opt[0];
                            $_value = $opt[1];
                            if (!$_name) {
                                continue;
                            }
                            $arr_data[] = '<selectedItem>';
                            $arr_data[] = '<type>SELECT</type>';
                            $arr_data[] = '<name>' . $this->cdata($_name) . '</name>';
                            $arr_data[] = '<value>';
                            $arr_data[] = '<id>' . $this->getOptionValueId('S', $_name, $_value) . '</id>';
                            $arr_data[] = '<text>' . $this->cdata($_value) . '</text>';
                            $arr_data[] = '</value>';
                            $arr_data[] = '</selectedItem>';
                        }

                        if ($orderGoodsData['optionPrice'] < 0) {  //옵션가격이 음수면
                            if ($basePrice / 2 < abs((int)$orderGoodsData['optionPrice'])) {
                                $this->alert(__('옵션가가 판매가의 50% 이하인 상품은 네이버페이로 구매하실 수 없습니다.'));
                            }
                        }

                        $optionPrice += $orderGoodsData['optionPrice'];
                    }
                    if ($goodsData['optionTextFl'] == 'y') {
                        $optionTextData = $goodsData['optionText'];
                        $arrOptionText = json_decode($orderGoodsData['optionTextInfo'], true);
                        foreach ($optionTextData as $key => $val) {
                            $_name = gd_htmlspecialchars_decode($val['optionName']);
                            $_value = '';
                            $tKey = $val['sno'];
                            foreach ($arrOptionText as $_key => $_val) {
                                if ($_key == $tKey) {
                                    $_value = $_val[1];
                                    break;
                                }
                            }
                            if (!$_value) {
                                $_value = __('구매자가 옵션정보를 입력하지 않았습니다.');
                            }

                            $arr_data[] = '<selectedItem>';
                            $arr_data[] = '<type>INPUT</type>';
                            $arr_data[] = '<name>' . $this->cdata($_name) . '</name>';
                            $arr_data[] = '<value>';
                            $arr_data[] = '<id>T' . $val['sno']  . '</id>';
                            $arr_data[] = '<text>' . $this->cdata($_value) . '</text>';
                            $arr_data[] = '</value>';
                            $arr_data[] = '</selectedItem>';
                        }

                        $optionPrice += $orderGoodsData['optionTextPrice'];
                    }

                    // S : 튜닝여부 확인 *** 수정 금지 ***
                    if($tuningFl) {
                        $arr_data[] = '<manageCode>option' . $orderGoodsData['optionSno'] . '</manageCode>';
                    } else {
                        $arr_data[] = '<manageCode>option' . $orderGoodsData['optionSno'] . '_' . $orderGoodsData['sno'] . '</manageCode>';
                    }
                    // E : 튜닝여부 확인 *** 수정 금지 ***
                    $arr_data[] = '<price>' . (int)$optionPrice . '</price>';
                    $arr_data[] = '</option>';
                }

                $deliveryData = $this->delivery->getDataBySno($deliverySno);
                $deliveryPolicy = json_decode($deliveryData['deliveryPolicy']);
                //상품별배송비인데 같은 상품번호면 합산된 배송비가 나오기땜에 나눠서 보내줘야함.
                $goodsDeliveryFl = $deliveryData['goodsDeliveryFl'];
                if ($orderGoodsData['goodsDeliveryCollectFl'] == 'pre') {   //선불이면
                    if ($goodsDeliveryFl == 'n') {    //상품별배송비면
                        if ($deliveryPolicy->sameGoodsDeliveryFl == 'y') {
                            $feePrice = (int)$cart->setDeliveryInfo[$deliveryData['deliverySno']][$orderGoodsData['goodsNo']]['goodsDeliveryPrice'];
                        } else {
                            if ($deliveryInfo) {
                                $feePrice = (int)$deliveryData['deliveryCharge'] / count($deliveryInfo[$deliveryData['sno']]);
                            } else {
                                $feePrice = (int)$deliveryData['deliveryCharge'];
                            }
                        }
                    } else {  //상품별배송비면
                        $feePrice = (int)$deliveryData['deliveryCharge'];
                    }
                } else if ($orderGoodsData['goodsDeliveryCollectFl'] == 'later') {  //착불인경우
                    if ($goodsDeliveryFl == 'n') {
                        $feePrice = (int)$orderGoodsData['goodsDeliveryCollectPrice'];
                    } else {  //상품별배송비면
                        $feePrice = (int)$deliveryData['deliveryCollectPrice'];
                    }
                } else {
                    $feePrice = (int)$deliveryData['deliveryCharge'];
                }


                if ($deliveryData['deliveryFixFl'] == 'weight') {    //무개별 배송비면
                    $this->alert(__('무개별 배송비 상품은 네이버페이로 구매하실 수 없습니다'));
                }

                $groupId = $goodsDeliveryFl == 'y' ? $deliveryData['deliverySno'] : $deliveryData['deliverySno'] . '_' . $index;
                if ($goodsDeliveryFl != 'y' && $deliveryPolicy->sameGoodsDeliveryFl == 'y') {
                    $groupId = $deliveryData['deliverySno'] . '_' . $orderGoodsData['goodsNo'];
                }
                switch ($orderGoodsData['deliveryMethodFl']) {
                    case 'delivery' :   //택배
                    case 'packet' :   //등기, 소포
                        $deliveryMethod = 'DELIVERY';
                        break;
                    case 'visit' :  //방문접수
                        $deliveryMethod = 'VISIT_RECEIPT';
                        break;
                    case 'quick' :  //퀵배송
                        $deliveryMethod = 'QUICK_SVC';
                        break;
                    case 'cargo' :  //화물배송
                    case 'etc' :    //기타
                        $deliveryMethod = 'DIRECT_DELIVERY';
                        break;
                    default :
                        $deliveryMethod = 'DELIVERY';
                }

                $arr_data[] = '<shippingPolicy>';
                $arr_data[] = '<groupId>' . $groupId . '</groupId>';    //배송비 묶음그룹 (선택)
                $arr_data[] = '<method>'.$deliveryMethod.'</method>';

                if ($freeCondition[$orderGoodsData['scmNo']] == 'free' || $orderGoodsData['deliveryMethodFl'] == 'visit' && $deliveryPolicy->deliveryVisitPayFl == 'n') {
                    $feePrice = 0;
                    $feeType = 'FREE';
                    $feePayType = 'FREE';
                } else {
                    //조건배송비
                    $deliveryPolicy = json_decode($deliveryData['deliveryPolicy']);
                    $chargeCount = count($deliveryPolicy->charge);
                    $conditionFeePayType = '';
                    if ($deliveryData['deliveryFixFl'] == 'price') { //2뎁스 초과 제한
                        if ($chargeCount == 2) {
                            foreach ($deliveryPolicy->charge as $row) {
                                if ((int)$row->price == 0) {
                                    $arr_data[] = '<conditionalFree>';
                                    $arr_data[] = '<basePrice>' . (int)$row->unitStart . '</basePrice>';
                                    $arr_data[] = '</conditionalFree>';
                                    $conditionFeePayType = 'CONDITIONAL_FREE';
                                    break;
                                } else {
                                    $feePrice = (int)$row->price;
                                }
                            }

                            if (!$conditionFeePayType) {
                                $this->alert(__('지원하지 않은 배송비 조건입니다. 금액별 조건배송인 경우 무료조건이 포함되있어야 합니다.'));
                            }

                        } else {
                            $this->alert(__('지원하지 않은 배송비 조건입니다. 네이버페이 구매가 불가합니다.'));
                        }
                    } else if ($deliveryData['deliveryFixFl'] == 'count') {    //3뎁스 초과 제한
                        if ($chargeCount < 4) {
                            $chargeType = 'RANGE';
                            if ($deliveryPolicy->rangeRepeat == 'y') {
                                $chargeType = 'REPEAT';
                            }
                            $feePrice = 0;  //수량별일때 배송비 0으로 초기화
                            $arr_data[] = '<chargeByQuantity>';
                            $arr_data[] = '<type>' . $chargeType . '</type>';
                            if ($deliveryPolicy->rangeRepeat == 'y') {
                                $arr_data[] = '<repeatQuantity>'.(int)($deliveryPolicy->charge[1]->unitEnd).'</repeatQuantity>';
                                $feePrice = (int)($deliveryPolicy->charge[1]->price);
                            } else {
                                $arr_data[] = '<range>';
                                $arr_data[] = '<type>' . ($chargeCount) . '</type>';
                                $i = 2;
                                foreach ($deliveryPolicy->charge as $row) {
                                    if ($row->unitStart == 0) {
                                        $feePrice = (int)$row->price;
                                        continue;
                                    }
                                    $unitStart = $row->unitStart == 0 ? 1 : $row->unitStart;
                                    $rangeFeePrice = $row->price - $feePrice;
                                    if ($rangeFeePrice < 0) {
                                        $this->alert(__('수량별배송비일 경우 조건에 의한 무료배송인경우 네이버페이 주문이 안됩니다.'));
                                    }
                                    $arr_data[] = '<range' . $i . 'From>' . (int)$unitStart . '</range' . $i . 'From>';
                                    $arr_data[] = '<range' . $i . 'FeePrice>' . (int)$rangeFeePrice . '</range' . $i . 'FeePrice>';
                                    $i++;
                                }
                                $arr_data[] = '</range>';
                            }
                            $arr_data[] = '</chargeByQuantity>';
                            $conditionFeePayType = 'CHARGE_BY_QUANTITY';
                        } else {
                            $this->alert(__('지원하지 않은 배송비 조건입니다. 네이버페이 구매가 불가합니다.'));
                        }
                    }
                }

                $deliveryCollectFl = $orderGoodsData['goodsDeliveryCollectFl'];
                $feeType = 'CHARGE';
                if ($goodsDeliveryFl == 'n') {   //상품별배송비면
                    if ($deliveryCollectFl == 'pre') {   //선불
                        $feePayType = 'PREPAYED';
                    } else if ($deliveryCollectFl == 'later') { //착불
                        $feePayType = 'CASH_ON_DELIVERY';
                    } else {
                        $feePayType = 'FREE';
                    }
                } else {
                    $_feePayType = $getFeePay[$orderGoodsData['orderDeliverySno']][0];
                    if ($_feePayType == 'pre') {   //선불
                        $feePayType = 'PREPAYED';
                    } else if ($_feePayType == 'later') { //착불
                        $feePayType = 'CASH_ON_DELIVERY';
                    } else {
                        $feePayType = 'FREE';
                    }
                }

                if ($feePrice < 1 && $feePayType == 'PREPAYED') {   //선불인데 배송비가0인경우 무료배송비
                    $feePrice = 0;
                    $feeType = 'FREE';
                    $feePayType = 'FREE';
                }

                if ($conditionFeePayType) {
                    $feeType = $conditionFeePayType;
                }

                if ($orderGoodsData['deliveryMethodFl'] == 'quick') {   //퀵배송인경우
                    if($feePayType != 'CASH_ON_DELIVERY') {    //착불인경우 무조건 가능
                        $this->alert(__('네이버페이를 통한 퀵배송 주문은 "착불"만 가능합니다.'));
                    }
                }
                else if($orderGoodsData['deliveryMethodFl'] == 'visit') {   //방문수령인경우
                    if($feePrice > 0 || ($deliveryData['deliveryCharge']>0 || $deliveryData['deliveryCollectPrice']>0)) { //배송비가 무조건 무료인경우만 가능
                        $this->alert(__('네이버페이를 통한 방문수령 주문은 "배송비 무료상품"만 가능합니다.'));
                    }
                }

                /** 10.10 같은배송조건에 같은상품으로 선불,후발 같이 장바구니에 담은경우 **/
                if ($goodsDeliveryFl == 'y') {
                    if ($logGroupId[$groupId]['feePayType'] == 'CASH_ON_DELIVERY' && $feePayType == 'FREE') { //같은상품 같은배송비조건으로 1.후불 2.선불인경우 => 후불처리
                        $feePayType = 'CASH_ON_DELIVERY';
                        $feePrice = $logGroupId[$groupId]['feePrice'];
                        $feeType = $logGroupId[$groupId]['feeType'];
                    } else if ($logGroupId[$groupId]['feePayType'] == 'PREPAYED' && $feePayType == 'FREE') {
                        $feePayType = 'PREPAYED';
                        $feePrice = $logGroupId[$groupId]['feePrice'];
                        $feeType = $logGroupId[$groupId]['feeType'];
                    }
                    $logGroupId[$groupId] = ['feePayType' => $feePayType, 'feePrice' => $feePrice, 'feeType' => $feeType];
                }
                /****/

                $arr_data[] = '<feePayType>' . $feePayType . '</feePayType>';    //배송비유형 FREE=무료, PREPAYED=선물, CASH_ON_DELIVERY=착불
                $arr_data[] = '<feePrice>' . $feePrice . '</feePrice>';    //배송비 금액
                $arr_data[] = '<feeType>' . $feeType . '</feeType>';    //배송비유형 FREE=무료, CHARGE=유료, CONDITIONAL_FREE=조건부무료, CHARGE_BY_QUANTITY=수량별부과

                $naverpayDeliveryData = $naverPayConfig['deliveryData'][$orderGoodsData['scmNo']];
                if ($naverpayDeliveryData['areaDelivery'] != 'n' && $naverpayDeliveryData['areaDelivery'] != '') {
                    $arr_data[] = '<surchargeByArea>';
                    $arr_data[] = '<splitUnit>' . $naverpayDeliveryData['areaDelivery'] . '</splitUnit>';
                    if ($naverpayDeliveryData['areaDelivery'] == '2') {
                        $arr_data[] = '<area2Price>' . $naverpayDeliveryData['area22Price'] . '</area2Price>';    ////2권역 지역별 배송비
                    } else if ($naverpayDeliveryData['areaDelivery'] == '3') {
                        $arr_data[] = '<area2Price>' . $naverpayDeliveryData['area32Price'] . '</area2Price>';    ////2권역 지역별 배송비
                        $arr_data[] = '<area3Price>' . $naverpayDeliveryData['area33Price'] . '</area3Price>';    //배송 방법. 기본값은 ‘택배·소포·등기
                    } else {
                        throw new \Exception(__('지역별 배송비가 잘못설정 되어 있습니다.\n 관리자에게 문의해 주세요.'));
                    }
                    $arr_data[] = '</surchargeByArea>';    //배송 방법. 기본값은 ‘택배·소포·등기
                }

                $arr_data[] = '</shippingPolicy>';
                $arr_data[] = '</product>';
                $index++;
            }

            $arr_data[] = '<backUrl>' . $this->cdata($backUrl) . '</backUrl>';
            $arr_data[] = '<interface>';
            $arr_data[] = '<salesCode></salesCode>';    //경로별 매출 코드. 매출 코드가 필요한 경우에 입력한다. 최대 300자(선택)
            $arr_data[] = '<cpaInflowCode>' . \Cookie::get("CPAValidator") . '</cpaInflowCode>';//지식쇼핑 가맹점에서 사용.(쿠키에서 CPAValidator 값을 입력)
            $arr_data[] = '<mileageInflowCode>' . \Cookie::get('NA_MI') . '</mileageInflowCode>';//네이버페이 포인트 가맹점에서 사용(네이버 페이 포인트 연동 가이드 > 유입 파라미터 참고)
            $arr_data[] = '<naverInflowCode>' . \Cookie::get("NA_CO") . '</naverInflowCode>';//쿠키에서 NA_CO 값을 입력
            $arr_data[] = '<saClickId>' . \Cookie::get("NVADID") . '</saClickId>';//네이버 검색광고에서 사용
            $arr_data[] = '<merchantCustomCode1>' . $orderNo . '</merchantCustomCode1>';//네이버 주문등록 sno (300자)
            $arr_data[] = '</interface>';
            if($mcstCultureBenefitYn == 'y'){
                $arr_data[] = '<mcstCultureBenefitYn>true</mcstCultureBenefitYn>';//문화체육관광부의 도서공연비 적용여부
            }
            $arr_data[] = '</order>';
            $result = implode(chr(10), $arr_data);
            \Logger::channel('naverPay')->info('주문XML' . __METHOD__, [$result]);

            // FBEv2 네이버페이 사용에 따른 분기처리
            $arrMakBind = [];
            $query = "SELECT value FROM " . DB_MARKETING . " WHERE company = ? ";
            $this->db->bind_param_push($arrMakBind, 's', 'facebookExtensionV2');
            $fbValue = $this->db->query_fetch($query, $arrMakBind, false);
            if(empty($fbValue) === true) {
                $fbScript = '';
                if (!\Cookie::get("NPAY_BUY_UNIQUE_KEY")) {
                    \Cookie::set("NPAY_BUY_UNIQUE_KEY", time() . rand(1, 9999), 60 * 60);
                    $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
                    $currency = gd_isset(\Component\Mall\Mall::getSession('currencyConfig')['code'], 'KRW');
                    $fbConfig = $facebookAd->getExtensionConfig();
                    if (empty($fbConfig) === false && $fbConfig['fbUseFl'] == 'y') {
                        // 상품번호 추출
                        $fbScript = $facebookAd->getFbCommonScript();
                        $fbScript .= $facebookAd->getFbNPayScript($goodsNoArr, $goodsPrice, $currency);
                    }
                }
            }

            //주문 등록호출
            if ($naverPayConfig['testYn'] == 'y') {
                $url = "https://test-api.pay.naver.com/o/customer/api/order/v20/register";//테스트
            } else {
                $url = "https://api.pay.naver.com/o/customer/api/order/v20/register";//실서버
            }
            $headers = array('Content-Type: application/xml; charset=utf-8');
            $ci = curl_init();
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ci, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ci, CURLOPT_URL, $url);
            curl_setopt($ci, CURLOPT_POST, TRUE);
            curl_setopt($ci, CURLOPT_TIMEOUT, 10);
            curl_setopt($ci, CURLOPT_POSTFIELDS, $result);
            // 주문 등록 후 결과값 확인
            $response = curl_exec($ci);
            curl_close($ci);

            // 주문 등록 후 결과처리
            $param = explode(':', $response);

            if ($param[0] == "SUCCESS") {
                $requestParam = "/" . $param[1] . "/" . $param[2];
            } else {
                throw new \Exception($response);
            }

            // 주문서 URL 재전송(redirect)
            if ($naverPayConfig['testYn'] == 'y') {
                $redirectUrl = "https://test-order.pay.naver.com/customer/buy" . $requestParam;//테스트
            } else {
                $redirectUrl = "https://order.pay.naver.com/customer/buy" . $requestParam;//실서버
            }

            ?>
            <html>
            <body>
            <form name="frm" method="get" action="<?= $redirectUrl ?>">
            </form>
            </body>
            <?php echo $fbScript; ?>
            <script>
                <?php if(\Request::isMobileDevice()){ ?>
                <?php if($naverPayConfig['mobileButtonTarget'] == 'new') {?>
                document.frm.target = "_blank";
                <?php }
                else {?>
                document.frm.target = "_top";
                <?php }?>
                document.frm.submit();
                <?php }else{ ?>
                document.frm.submit();
                <?php } ?>
            </script>
            </html>

            <?php
            exit;
        } catch (\Exception $e) {
            if(\Request::request()->get('popupMode') == 'y'){
                throw new AlertCloseException($e->getMessage());
            } else {
                throw new AlertOnlyException($e->getMessage());
            }
        }
    }

    public function getOptionValueId($type, $name, $valueName)
    {
        $name = gd_htmlspecialchars_stripslashes($name);
        $valueName = gd_htmlspecialchars_stripslashes($valueName);
        $code = md5(str_replace([' ', '\r', '\n', '\"', '\\\'','"'], '', $name) . str_replace([' ', '\r', '\n', '\"', '\\\'','"'], '', $valueName));
        if (strlen($code) > 30) {
            $code = substr($code, 0, 30);
        }
        $result = $type . '_' . $code;
        return $result;
    }

    private function decodeJson($jsonString)
    {
        $jsonString = str_replace(['\r', '\n', '\"', '\\\''], '', $jsonString);
        return json_decode(gd_htmlspecialchars_stripslashes($jsonString), true);
    }

    private function cdata($value)
    {
        $value = stripslashes(str_replace(['\r', '\n', '"', '\''], '', $value));
        $value = str_replace(['&lt;', '&gt;'], ['<', '>'], $value);
        return '<![CDATA[' . ($value) . ']]>';
    }

    private function getTaxType($type)
    {
        switch ($type) {
            case 't' :  //과세
                return 'TAX';
                break;
            case 'n' :  //비과세
                return 'ZERO_TAX';
                break;
            case 'f' :  //면세
                return 'TAX_FREE';
                break;
        }
    }
}

