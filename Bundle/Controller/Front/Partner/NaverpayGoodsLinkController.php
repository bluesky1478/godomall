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

namespace Bundle\Controller\Front\Partner;


use Component\Cart\Cart;
use Component\Delivery\Delivery;
use Component\Goods\AddGoods;
use Component\Goods\Goods;
use Component\Naver\NaverPay;
use Component\Order\Order;
use Component\Policy\Policy;
use Component\Order\OrderAdmin;
use Framework\Utility\ArrayUtils;
use Framework\Utility\GodoUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;

/**
 * 네이버페이 상품정보제공URL
 * @package Bundle\Controller\Front\Partner
 * @author Lee Namju <lnjts@godo.co.kr>
 */
class NaverpayGoodsLinkController extends \Controller\Front\Controller
{
    const NOT_STRING = Array('%01', '%02', '%03', '%04', '%05', '%06', '%07', '%08', '%09', '%0A', '%0B', '%0C', '%0D', '%0E', '%0F', '%10', '%11', '%12', '%13', '%14', '%15', '%16', '%17', '%18', '%19', '%1A', '%1B', '%1C', '%1D', '%1E', '%1F');

    //배송방법
    const METHOD_DELIVERY = 'DELIVERY';    //택배·소포·등기
    const METHOD_QUICK_SVC = 'QUICK_SVC';    //퀵서비스
    const METHOD_DIRECT_DELIVERY = 'DIRECT_DELIVERY';    //직접전달
    const METHOD_VISIT_RECEIPT = 'VISIT_RECEIPT';    //방문수령
    const METHOD_NOTHING = 'NOTHING';    //방문수령

    //배송비 결제 방법.
    const FEE_PAY_TYPE_FREE = 'FREE';    //무료
    const FEE_PAY_TYPE_PREPAYED = 'PREPAYED';    //선불
    const FEE_PAY_TYPE_CASH_ON_DELIVERY = 'CASH_ON_DELIVERY';    //착불
    const FEE_PAY_TYPE_PAY_SELECT = 'PAY_SELECT';    //선불 또는 착불

    //배송비 유형
    const FEE_TYPE_FREE = 'FREE';    //무료
    const FEE_TYPE_CHARGE = 'CHARGE';    //유료
    const FEE_TYPE_CONDITIONAL_FREE = 'CONDITIONAL_FREE';    //조건부 무료
    const FEE_TYPE_CHARGE_BY_QUANTITY = 'CHARGE_BY_QUANTITY';    //수량별 부과

    protected $db;
    protected $orderData;
    protected $delivery;
    protected $request;
    protected $config;
    protected $goods;
    protected $addGoods;
    protected $goodsData;

    public function index()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->request = \Request::request()->all();
        \Logger::channel('naverPay')->info('REQUEST_' . __METHOD__, [$this->request]);
        \Logger::channel('naverPay')->info('상품정보제공URL' . __METHOD__, [\Request::getDomainUrl() . \Request::getRequestUri()]);

        $naverPay = new NaverPay();
        $this->config = $naverPay->getConfig();
        $this->addGoods = new AddGoods();

        if ($naverPay->checkUse() === false) {
            throw new \Exception('네이버페이 사용을 중단하였습니다.');
        }

        $this->goods = new Goods();
        $order = new Order();
        $orderAdmin = new OrderAdmin();
        $this->delivery = new Delivery();

        if(!$this->request['test']){
            header('Content-Type: application/xml;charset=utf-8');
        }

        $orderNo = $this->request['merchantCustomCode1'];
        if (empty($orderNo)) {
            $this->searchGoods();
            exit;
        }

        // S: 튜닝 여부 확인 *** 수정 금지 ***
        $tuningFl = GodoUtils::getTuningFl('20_711', true);
        // E : 튜닝여부 확인 *** 수정 금지 ***

        $orderGoodsList = $order->getOrderGoods($orderNo);
        $getOrderData = $orderAdmin->getOrderView($orderNo);
        $this->orderData = $getOrderData['goods'];
        $checkoutData = is_array($getOrderData['checkoutData']) ? $getOrderData['checkoutData'] : $this->decodeJson($getOrderData['checkoutData']);
        $freeCondition = null;//조건포함배송비정책인경우 해당하는 공급사 배송들은 모두 무료
        foreach ($orderGoodsList as $orderGoodsData) {
            $deliveryData = $this->delivery->getDataBySno($orderGoodsData['orderDeliverySno']);
            $basicDeliveryData = $this->delivery->getSnoDeliveryBasic($deliveryData['deliverySno']);
            if ($basicDeliveryData['freeFl'] == 'y' && $basicDeliveryData['fixFl'] == 'free') {
                $freeCondition[$basicDeliveryData['scmNo']] = 'free';
            }
            $goodsDeliveryFl = $deliveryData['goodsDeliveryFl'];
            $deliveryCollectFl = $orderGoodsData['goodsDeliveryCollectFl'];
            if ($goodsDeliveryFl == 'n' && $deliveryCollectFl == 'pre' && $orderGoodsData['goodsType'] != 'addGoods') {  //상품별 배송비면
                $deliveryInfo[$orderGoodsData['orderDeliverySno']][] = $orderGoodsData['sno'];
            } else {
                $getFeePay[$orderGoodsData['orderDeliverySno']][] = $orderGoodsData['goodsDeliveryCollectFl'];
            }
        }
        $orderGoodsData = null;

        $arr_data[] = '<?xml version="1.0" encoding="utf-8"?>';
        $arr_data[] = '<products>';
        $index = 0;
        $logGroupId = null;
        foreach ($this->request['product'] as $product) {
            $goodsNo = $product['id'];
            $goodsData = $this->goods->getGoodsView($goodsNo);
            $goodsInfo = $this->goods->getGoodsInfo($goodsNo);

            $isNotOption = $goodsData['optionFl'] != 'y' && $goodsData['optionTextFl'] != 'y' ? true : false;
            $orderGoodsDatas = $order->getOrderGoodsByGoodsNo($orderNo, $goodsNo);
            $orderGoodsData = $orderGoodsDatas[0];
            $basePrice = (int)$orderGoodsData['goodsPrice'];
            $goodsDcPrice = (int)$orderGoodsData['goodsDcPrice'];
            $goodsCnt = (int)$orderGoodsData['goodsCnt'];
            if ($this->config['saleFl'] == 'y') {
                $basePrice = $basePrice - ($goodsDcPrice / $goodsCnt);
            }

            //이미지명
            $goodsImage = $this->goods->getGoodsImage($goodsNo, 'main');
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

            $arr_data[] = '<product>';
            $arr_data[] = '<id>' . $goodsNo . '</id>';
            $arr_data[] = '<ecMallProductId>' . $goodsNo . '</ecMallProductId>';
            $arr_data[] = '<name>' . $this->cdata(urldecode(str_replace(self::NOT_STRING, '', urlencode($goodsData['goodsNm'])))) . '</name>';
            $arr_data[] = '<basePrice>' . $basePrice . '</basePrice>';
            $arr_data[] = '<taxType>' . $this->getTaxType($goodsData['taxFreeFl']) . '</taxType>';
            $arr_data[] = '<infoUrl>' . $this->cdata(URI_HOME . 'goods' . DS . 'goods_view.php?inflow=naverPay&goodsNo=' . $goodsData['goodsNo']) . '</infoUrl>';
            $arr_data[] = '<imageUrl>' . $this->cdata($goodsImageSrc) . '</imageUrl>';
            /**
             * 본상품의 재고 수량. 0 이상만 입력 가능하며, 입력하지 않으면 주문 시
             * 재고를 확인하지 않는다.
             *   0: 품절로 구매 불가
             *   1 이상: 주문 수량과 비교
             * 옵션 상품이고 옵션 관리 코드가 존재하면 본상품 재고 수량은 값을
             * 입력해도 사용하지 않는다.
             */
            if ($isNotOption) { //단품
                if ($goodsData['stockFl'] == 'y') {
                    $arr_data[] = '<stockQuantity>' . $goodsData['totalStock'] . '</stockQuantity>';
                }
            }

            if ($goodsData['soldOutFl'] == 'y') {
                $goodsStatus = 'SOLD_OUT';
            } else if ($goodsData['goodsSellFl'] == 'n') {
                $goodsStatus = 'NOT_SALE';
            } else {
                $goodsStatus = 'ON_SALE';
            }

            $optionSupport = ($isNotOption) ? 'false' : 'true';

            $supplementSupport = 'false';
            if ($product['supplementIds']) {
                $supplementSupport = 'true';
            }

            $arr_data[] = '<status>' . $goodsStatus . '</status>';
            $arr_data[] = '<supplementSupport>' . $supplementSupport . '</supplementSupport>';
            $arr_data[] = '<optionSupport>' . $optionSupport . '</optionSupport>';
            $naverpayDeliveryData = $this->config['deliveryData'][$goodsData['scmNo']];
            if ($naverpayDeliveryData['returnPrice'] != '') {
                $arr_data[] = '<returnShippingFee>' . (int)$naverpayDeliveryData['returnPrice'] . '</returnShippingFee>';//편도 반품 배송비
                $arr_data[] = '<exchangeShippingFee>' . (int)($naverpayDeliveryData['returnPrice'] * 2) . '</exchangeShippingFee>';//교환배송비
            }

            $deliverySno = $orderGoodsData['orderDeliverySno'];
            $deliveryData = $this->delivery->getDataBySno($deliverySno);
            $deliveryPolicy = json_decode($deliveryData['deliveryPolicy']);
            //상품별배송비인데 같은 상품번호면 합산된 배송비가 나오기땜에 나눠서 보내줘야함.
            $goodsDeliveryFl = $deliveryData['goodsDeliveryFl'];
            if ($orderGoodsData['goodsDeliveryCollectFl'] == 'pre') {   //선불이면
                if ($goodsDeliveryFl == 'n') {    //상품별배송비면
                    if ($deliveryPolicy->sameGoodsDeliveryFl == 'y') {
                        $feePrice = (int)$checkoutData['setDeliveryInfo'][$deliveryData['deliverySno']][$orderGoodsData['goodsNo']]['goodsDeliveryPrice'];
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
            $groupId = $goodsDeliveryFl == 'y' ? $deliveryData['deliverySno'] : $deliveryData['deliverySno'] . '_' . $index;
            if ($goodsDeliveryFl != 'y' && $deliveryPolicy->sameGoodsDeliveryFl == 'y') {
                $groupId = $deliveryData['deliverySno'] . '_' . $orderGoodsData['goodsNo'];
            }

            $arr_data[] = '<shippingPolicy>';
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
            $arr_data[] = '<method>' . $deliveryMethod . '</method>';
            $arr_data[] = '<groupId>' . $groupId . '</groupId>';    //배송비 묶음그룹 (선택)
            if ($freeCondition[$orderGoodsData['scmNo']] == 'free' || ($orderGoodsData['deliveryMethodFl'] == 'visit' && $deliveryPolicy->deliveryVisitPayFl == 'n')) {
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
                    } else {
                        $this->alert('지원하지 않은 배송비 조건입니다. 네이버페이 구매가 불가합니다.');
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
                            $arr_data[] = '<repeatQuantity>' . (int)($deliveryPolicy->charge[1]->unitEnd) . '</repeatQuantity>';
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
                                $arr_data[] = '<range' . $i . 'From>' . (int)$unitStart . '</range' . $i . 'From>';
                                $arr_data[] = '<range' . $i . 'FeePrice>' . (int)($row->price - $feePrice) . '</range' . $i . 'FeePrice>';
                                $i++;
                            }
                            $arr_data[] = '</range>';
                        }
                        $arr_data[] = '</chargeByQuantity>';
                        $conditionFeePayType = 'CHARGE_BY_QUANTITY';
                    } else {
                        $this->alert('지원하지 않은 배송비 조건입니다. 네이버페이 구매가 불가합니다.');
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

                if ($feePrice < 1 && $feePayType == 'PREPAYED') {
                    $feePrice = 0;
                    $feeType = 'FREE';
                    $feePayType = 'FREE';
                }

                if ($conditionFeePayType) {
                    $feeType = $conditionFeePayType;
                }
            }
            /** 10.10 같은배송조건에 같은상품으로 선불,후발 같이 장바구니에 담은경우 **/
            if ($goodsDeliveryFl == 'y') {
                if ($logGroupId[$groupId]['feePayType'] == 'CASH_ON_DELIVERY' && $feePayType == 'FREE') { //같은상품 같은배송비조건으로 1.후불 2.선불인경우 => 후불처리
                    $feePayType = 'CASH_ON_DELIVERY';
                    $feePrice = $logGroupId[$groupId]['feePrice'];
                    $feeType = $logGroupId[$groupId]['feeType'];
                } else if ($logGroupId[$groupId][$orderGoodsData['goodsNo']]['feePayType'] == 'PREPAYED' && $feePayType == 'FREE') {
                    $feePayType = 'PREPAYED';
                    $feePrice = $logGroupId[$groupId]['feePrice'];
                    $feeType = $logGroupId[$groupId]['feeType'];
                }
                $logGroupId[$groupId] = ['feePayType' => $feePayType, 'feePrice' => $feePrice, 'feeType' => $feeType];
            }
            /****/

            // 방문수령 배송비 무료 처리
            if ($orderGoodsData['deliveryMethodFl'] == 'visit' && $deliveryPolicy->deliveryVisitPayFl == 'n') {
                $feePrice = 0;
                $feeType = 'FREE';
                $feePayType = 'FREE';
            }

            $arr_data[] = '<feePayType>' . $feePayType . '</feePayType>';  //배송비유형 FREE=무료, PREPAYED=선물, CASH_ON_DELIVERY=착불
            $arr_data[] = '<feePrice>' . $feePrice . '</feePrice>';    //배송비 금액
            $arr_data[] = '<feeType>' . $feeType . '</feeType>';    //배송비유형 FREE=무료, CHARGE=유료, CONDITIONAL_FREE=조건부무료, CHARGE_BY_QUANTITY=수량별부과
            if ($naverpayDeliveryData['areaDelivery'] != 'n' && $naverpayDeliveryData['areaDelivery'] != '') {
                $arr_data[] = '<surchargeByArea>';
                //            $arr_data[] = '<apiSupport></apiSupport>';    //지역별 배송비 조회 API 사용 여부. 기본값은 false (선택)
                $arr_data[] = '<splitUnit>' . $naverpayDeliveryData['areaDelivery'] . '</splitUnit>';    //지역별 배송비 권역 구분. 지역별 추가 배송비를 사용하고 가맹점 별도API를 사용하지 않는 경우에 입력한다. 사용권역 2 = 2권역, 3 = 3권역
                if ($naverpayDeliveryData['areaDelivery'] == '2') {
                    $arr_data[] = '<area2Price>' . $naverpayDeliveryData['area22Price'] . '</area2Price>';    ////2권역 지역별 배송비
                } else if ($naverpayDeliveryData['areaDelivery'] == '3') {
                    $arr_data[] = '<area2Price>' . $naverpayDeliveryData['area32Price'] . '</area2Price>';    ////2권역 지역별 배송비
                    $arr_data[] = '<area3Price>' . $naverpayDeliveryData['area33Price'] . '</area3Price>';    //배송 방법. 기본값은 ‘택배·소포·등기
                } else {
                    throw new \Exception('지역별 배송비가 잘못설정 되어 있습니다.\n 관리자에게 문의해 주세요.');
                }
                $arr_data[] = '</surchargeByArea>';    //배송 방법. 기본값은 ‘택배·소포·등기
            }

            $arr_data[] = '</shippingPolicy>';

            //추가상품

            if ($product['supplementIds']) {
                $addGoodsIndex= 1 ;
                foreach (explode(',', $product['supplementIds']) as $optionManageCode) {
                    if (strpos($optionManageCode, 'addGoods') !== false) {
                        $query = "SELECT * FROM " . DB_ORDER_GOODS . " WHERE orderNo = ? AND goodsType = 'addGoods' AND goodsNo = ? AND parentGoodsNo = ?  limit 1";
                        $arrBind = null;
                        list($_goodsType,$parentGoodsNo,$addGoodsNo) = explode('_', $optionManageCode);
                        $this->db->bind_param_push($arrBind, 's', $orderNo);
                        $this->db->bind_param_push($arrBind, 'i', $addGoodsNo);
                        $this->db->bind_param_push($arrBind, 'i', $parentGoodsNo);
                        $addOrderGoodsData = gd_htmlspecialchars_stripslashes($this->db->query_fetch($query, $arrBind,false));
                        $addGoodsId = $optionManageCode ;
                        //                        debug($addOrderGoodsData);
                        //                        $addGoodsId = 'addGoods' . $orderCd . '_' . $addOrderGoodsData['goodsNo'] ;
                    } else {
                        $addOrderGoodsNo = substr($optionManageCode, 1);
                        $addOrderGoodsData = $order->getOrderGoods($orderNo, $addOrderGoodsNo)[0];
                        $addGoodsId = 'A' . $addOrderGoodsData['sno'];
                    }

                    $addGoodsPrice = (int)$addOrderGoodsData['goodsPrice'];
                    if ($addGoodsPrice < 0) {
                        $this->alert('추가상품가격이 음수(마이너스)인 상품은 네이버페이로 구매하실 수 없습니다');
                    }

                    $addGoodsOptionText = '';
                    $addGoodsOption = $this->decodeJson($addOrderGoodsData['optionInfo']);
                    if ($addGoodsOption[0][1]) {
                        $addGoodsOptionText = '(' . $addGoodsOption[0][1] . ')';
                    }

                    $arr_data[] = '<supplement>';
                    $arr_data[] = '<id>'.$addGoodsId.'</id>';
                    $arr_data[] = '<name>' . $this->cdata($addOrderGoodsData['goodsNm'] . $addGoodsOptionText) . '</name>';
                    $arr_data[] = '<price>' . $addGoodsPrice . '</price>';
                    $_addGoods = $this->addGoods->getAddGoods($addOrderGoodsData['goodsNo'])[0];
                    if (empty($_addGoods)) {
                        $addStockQuantity = 0;
                    } else if ($_addGoods['soldOutFl'] != 'y' && $_addGoods['viewFl'] == 'y') {
                        if (!$_addGoods['stockUseFl']) {
                            $addStockQuantity = 'nolimit';
                        } else {
                            if ($_addGoods['stockCnt'] >= 0) {
                                $addStockQuantity = $_addGoods['stockCnt'];
                            }
                        }
                    } else {
                        $addStockQuantity = 0;
                    }
                    $addStockQuantity = $addStockQuantity ? $addStockQuantity : 0;
                    if ($addStockQuantity !== 'nolimit') {
                        $arr_data[] = '<stockQuantity>' . $addStockQuantity . '</stockQuantity>';
                    }
                    $arr_data[] = '</supplement>';
                    $addGoodsIndex++;
                }
            }

            /*foreach ($orderGoodsDatas as $orderGoodsData) {
                if (count($addOrderGoodsData[$orderGoodsData['sno']][$goodsNo]) > 0) {
                    $arrAddGoods = $addOrderGoodsData[$orderGoodsData['sno']][$goodsNo];
                    foreach ($arrAddGoods as $val) {
                        $addGoodsPrice = (int)$val['goodsPrice'];
                        if ($addGoodsPrice < 0) {
                            $this->alert('추가상품가격이 음수(마이너스)인 상품은 네이버페이로 구매하실 수 없습니다');
                        }
                        $arr_data[] = '<supplement>';
                        $arr_data[] = '<id>A' . $val['sno'] . '</id>';
                        $arr_data[] = '<name>' . $this->cdata($val['goodsNm']) . '</name>';
                        $arr_data[] = '<price>' . $addGoodsPrice . '</price>';
                        $arr_data[] = '<quantity>' . $val['goodsCnt'] . '</quantity>';

                        $_addGoods = $this->goods->getGoodsInfo($val['goodsNo']);

                        if ($_addGoods['stockUseFl']) {
                            $addStockQuantity = $_addGoods['stockCnt'];
                            if($addStockQuantity>0){
                                $arr_data[] = '<stockQuantity>' . $addStockQuantity . '</stockQuantity>';
                            }
                        }
                        $arr_data[] = '</supplement>';
                    }
                }
            }*/

            if ($isNotOption === false) {
                $arr_data[] = '<option>';
                if ($goodsData['optionFl'] == 'y') {
                    if ($goodsData['optionDisplayFl'] == 'd') {  //옵션분리형
                        $arrOptionName = $goodsData['optionName'];
                    } else if ($goodsData['optionDisplayFl'] == 's') {  //옵션일체형
                        $arrOptionName = explode(STR_DIVISION, $goodsInfo['optionName']);
                    }
                    //                    debug($arrOptionName);
                    if ($arrOptionName) {
                        for ($i = 0; $i < count($arrOptionName); $i++) {
                            $_name = $arrOptionName[$i];
                            $arr_data[] = '<optionItem>';
                            $arr_data[] = '<type>SELECT</type>';
                            $arr_data[] = '<name>' . $this->cdata($_name) . '</name>';
                            $values = $this->goods->getOptionValuesByIndex($goodsNo, ($i + 1));
                            foreach ($values as $val) {
                                $arr_data[] = '<value>';
                                $arr_data[] = '<id>' . $this->getOptionValueId('S', $_name, $val) . '</id>';
                                $arr_data[] = '<text>' . $this->cdata($val) . '</text>';
                                $arr_data[] = '</value>';
                            }
                            $arr_data[] = '</optionItem>';
                        }
                    }
                }

                if ($goodsData['optionTextFl'] == 'y') {    //옵션입력형
                    $optionTextData = $this->goods->getGoodsOptionText($goodsNo);
                    foreach ($optionTextData as $key => $val) {
                        $_name = $val['optionName'];
                        $arr_data[] = '<optionItem>';
                        $arr_data[] = '<type>INPUT</type>';
                        $arr_data[] = '<name>' . $this->cdata($_name) . '</name>';
                        $arr_data[] = '</optionItem>';
                    }
                }

                foreach ($orderGoodsDatas as $_orderGoodsData) {
                    $optionPrice = 0;
                    $arr_data[] = '<combination>';
                    //                    $arr_data[] = '<manageCode>' . $_orderGoodsData['sno'] . '</manageCode>';
                    //$arr_data[] = '<manageCode>option' . $_orderGoodsData['optionSno'] . '</manageCode>';

                    // S : 튜닝여부 확인 *** 수정 금지 ***
                    if($tuningFl) {
                        $arr_data[] = '<manageCode>option' . $_orderGoodsData['optionSno'] . '</manageCode>';
                    } else {
                        $arr_data[] = '<manageCode>option' . $_orderGoodsData['optionSno'] . '_' . $_orderGoodsData['sno'] . '</manageCode>';
                    }
                    // E : 튜닝여부 확인 *** 수정 금지 ***

                    if ($_orderGoodsData['optionInfo']) {    //옵션선택형
                        $optionInfo = $this->decodeJson($_orderGoodsData['optionInfo']);

                        if ($goodsData['stockFl'] == 'y') {
                            $optionData = $this->goods->getGoodsOptionInfo($_orderGoodsData['optionSno']);
                            $arr_data[] = '<stockQuantity>' . $optionData['stockCnt'] . '</stockQuantity>';
                        }
                        foreach ($optionInfo as $opt) {
                            $_name = $opt[0];
                            $_value = $opt[1];
                            if (!$_name) {
                                continue;
                            }
                            $arr_data[] = '<options>';
                            $arr_data[] = '<name>' . $this->cdata($_name) . '</name>';
                            $arr_data[] = '<id>' . $this->getOptionValueId('S', $_name, $_value) . '</id>';
                            $arr_data[] = '</options>';
                        }
                        $optionPrice += $_orderGoodsData['optionPrice'];
                    }

                    if ($goodsData['optionTextFl'] == 'y') {    //옵션입력형
                        $optionTextData = $goodsData['optionText'];
                        $arrOptionText = json_decode($_orderGoodsData['optionTextInfo'], true);
                        foreach ($optionTextData as $key => $val) {
                            $_name = $val['optionName'];
                            $tKey = $val['sno'];
                            foreach ($arrOptionText as $_key => $_val) {
                                if ($_key == $tKey) {
                                    break;
                                }
                            }

                            $arr_data[] = '<options>';
                            $arr_data[] = '<name>' . $this->cdata($_name) . '</name>';
                            $arr_data[] = '<id>T' . $val['sno'] . '</id>';
                            $arr_data[] = '</options>';
                        }
                    }
                    $optionPrice += $_orderGoodsData['optionTextPrice'];
                    //                    debug($optionPrice);

                    $arr_data[] = '<price>' . (int)$optionPrice . '</price>';
                    $arr_data[] = '</combination>';
                }

                $arr_data[] = '</option>';
            }

            $arr_data[] = '</product>';
            $index++;
        }

        $arr_data[] = '</products>';
        //        debug($arr_data);
        $result = implode("\n", $arr_data);
        print_r($result);
        \Logger::channel('naverPay')->info('RESULT', [$result]);
        exit;
    }

    private function cdata($value)
    {
        $value = stripslashes(str_replace(['\r', '\n', '"', '\''], '', $value));
        $value = str_replace(['&lt;', '&gt;'], ['<', '>'], $value);
        return '<![CDATA[' . ($value) . ']]>';
    }

    private function decodeJson($jsonString)
    {
        $jsonString = str_replace(['\r', '\n', '\"', '\\\''], '', $jsonString);
        return json_decode(gd_htmlspecialchars_stripslashes($jsonString), true);
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

    public function getOptionValueId($type, $name, $valueName)
    {
        //        return $name . '_' . $valueName;

        $name = gd_htmlspecialchars_stripslashes($name);
        $valueName = gd_htmlspecialchars_stripslashes($valueName);
        $code = md5(str_replace([' ', '\r', '\n', '\"', '\\\'', '"'], '', $name) . str_replace([' ', '\r', '\n', '\"', '\\\'', '"'], '', $valueName));
        if (strlen($code) > 30) {
            $code = substr($code, 0, 30);
        }
        $result = $type . '_' . $code;
        return $result;
    }

    public function searchGoods()
    {
        $orderGoodsData = null;

        if ($this->request['optionManageCodes']) {
            $arrOptionManageCodes = explode(',', $this->request['optionManageCodes']);
        }
        foreach($arrOptionManageCodes as $key => $val) {
            $_val = explode('_', $val);
            $arrOptionManageCodes[$key] = $_val[0];
        }

        $index = 0;
        $orderCd = 1;
        $productData = $optionItemData = null;
        foreach ($this->request['product'] as $product) {
            $data = null;
            $optionCombination = null;
            $goodsNo = $product['id'];
            $this->goodsData = $this->goods->getGoodsView($goodsNo);
            $goodsInfo = $this->goods->getGoodsInfo($goodsNo);
            $isNotOption = $this->isNotOption();
            $naverpayDeliveryData = $this->naverPayConfig['deliveryData'][$this->goodsData['scmNo']];
            $deliverySno = $this->goodsData['delivery']['basic']['sno'];
            //상품별배송비인데 같은 상품번호면 합산된 배송비가 나오기땜에 나눠서 보내줘야함.
            $goodsDeliveryFl = $this->goodsData['delivery']['basic']['goodsDeliveryFl'];    // y:조건배송비 n : 상품별배송비
            $groupId = $goodsDeliveryFl == 'y' ? $deliverySno : $deliverySno . '_' . $index;
            $goodsName = $this->cdata(urldecode(str_replace(self::NOT_STRING, '', urlencode($this->goodsData['goodsNm']))));

            $data['id'] = $goodsNo;
            $data['ecMallProductId'] = $goodsNo;
            $data['name'] = $goodsName;

            $data['basePrice'] = $this->getBasePrice();
            $data['taxType'] = $this->getTaxType($this->goodsData['taxFreeFl']);
            $data['infoUrl'] = $this->cdata(URI_HOME . 'goods' . DS . 'goods_view.php?goodsNo=' . $goodsNo . '&inflow=naverPay');
            $data['imageUrl'] = $this->getGoodsImageSrc($goodsNo, $this->goodsData['imagePath'], $this->goodsData['imageStorage']);
            if ($isNotOption) { //단품
                if ($this->goodsData['stockFl'] == 'y') {
                    $data['stockQuantity'] = $this->goodsData['totalStock'];
                }
            }
            $isSupplementSupport = $this->request['supplementSearch'] == 'true' ? 'true' : 'false';
            $data['status'] = $this->getStatus();
            $data['supplementSupport'] = $isSupplementSupport;
            $data['optionSupport'] = $isNotOption ? 'false' : 'true';
            $data['returnShippingFee'] = (int)$naverpayDeliveryData['returnPrice'];
            $data['exchangeShippingFee'] = (int)($naverpayDeliveryData['returnPrice'] * 2);
            $data['shippingPolicy']['groupId'] = $groupId;
            $data['shippingPolicy']['method'] = $this->getDeliveryMethod();
            $data['shippingPolicy']['feeType'] = $this->getFeeType();  //배송비 유형
            $data['shippingPolicy']['feePayType'] = $this->getFeePayType();  //배송비 유형
            $data['shippingPolicy']['feePrice'] = $this->getFeePrice($data['shippingPolicy']['feeType']);  //배송비 유형

            if ($data['shippingPolicy']['feeType'] == self::FEE_TYPE_CONDITIONAL_FREE) { //무료 조건부
                foreach ($this->goodsData['delivery']['charge'] as $row) {
                    if ((int)$row['price'] == 0) {
                        $data['shippingPolicy']['conditionalFree'][]['basePrice'] = (int)$row['unitStart'];
                        break;
                    }
                }
            } else if ($data['shippingPolicy']['feeType'] == self::FEE_TYPE_CHARGE_BY_QUANTITY) {  //수량별
                $chargeType = $this->goodsData['delivery']['basic']['rangeRepeat'] == 'y' ? 'REPEAT' : 'RANGE';
                $data['shippingPolicy']['chargeByQuantity']['type'] = $chargeType;
                if ($chargeType == 'REPEAT') {
                    $data['shippingPolicy']['chargeByQuantity']['repeatQuantity'] = (int)($this->goodsData['delivery']['charge'][1]['unitEnd']);
                } else {
                    $data['shippingPolicy']['chargeByQuantity']['range']['type'] = count($this->goodsData['delivery']['charge']);
                    $i = 2;
                    $feePrice = 0;
                    foreach ($this->goodsData['delivery']['charge'] as $row) {
                        if ($row['unitStart'] == 0) {
                            $feePrice = (int)$row['price'];
                            continue;
                        }
                        $unitStart = $row['unitStart'] == 0 ? 1 : $row['unitStart'];
                        $data['shippingPolicy']['range' . $i . 'From'][] = (int)$unitStart;
                        $data['shippingPolicy']['range' . $i . 'FeePrice'] = (int)($row['price'] - $feePrice);
                        $i++;
                    }
                }
            }

            if ($naverpayDeliveryData['areaDelivery'] != 'n' && $naverpayDeliveryData['areaDelivery'] != '') {
                $data['shippingPolicy']['surchargeByArea']['splitUnit'] = $naverpayDeliveryData['areaDelivery'];
                if ($naverpayDeliveryData['areaDelivery'] == '2') {
                    $data['shippingPolicy']['surchargeByArea']['area2Price'] = $naverpayDeliveryData['area22Price'];    ////2권역 지역별 배송비
                } else if ($naverpayDeliveryData['areaDelivery'] == '3') {
                    $data['shippingPolicy']['surchargeByArea']['area2Price'] = $naverpayDeliveryData['area32Price'];    ////2권역 지역별 배송비
                    $data['shippingPolicy']['surchargeByArea']['area3Price'] = $naverpayDeliveryData['area33Price'];
                }
            }

            if ($isSupplementSupport == 'true') {
                if ($product['supplementIds']) {
                    $arrAddGoodsNo = array_map(function ($val) use($goodsNo){
                        if (strpos($val, 'addGoods') !== false) {
                            list($goodsType,$parentGoodsNo,$addGoodsNo) = explode('_',$val);
                            if($goodsNo != $parentGoodsNo) {
                                return;
                            }
                            return $addGoodsNo;
                        }
                        return substr($val, 1);
                    }, explode(',', $product['supplementIds']));
                } else {
                    $addGoodsNo = null;
                    foreach ($this->goodsData['addGoods'] as $val) {
                        foreach ($val['addGoodsList'] as $_val) {
                            $addGoodsNo[] = $_val['addGoodsNo'];
                        }
                    }
                    $arrAddGoodsNo = $addGoodsNo;
                }

                if($arrAddGoodsNo){
                    $addGoodsDatas = $this->addGoods->getAddGoods($arrAddGoodsNo);
                    $data['supplement'] = null;
                    foreach ($addGoodsDatas as $addGoodsData) {
                        $addGoodsOptionText = $addGoodsData['optionNm'] ? '(' . $addGoodsData['optionNm'] . ')' : '';
                        $supplementData['id'] = 'addGoods_' . $goodsNo. '_' . $addGoodsData['addGoodsNo'];
                        $supplementData['name'] = $this->cdata($addGoodsData['goodsNm'] . $addGoodsOptionText);
                        $supplementData['price'] = (int)$addGoodsData['goodsPrice'];
                        $addStockQuantity = $this->getAddGoodsStock($addGoodsData);
                        if ($addStockQuantity !== 'nolimit') {
                            $supplementData['stockQuantity'] = $addStockQuantity;
                        }
                        $data['supplement'][] = $supplementData;
                    }
                }

            }
            $data['option']['optionItem'] = [];
            if ($isNotOption === false && $this->request['optionSearch'] == 'true') { //옵션이 있으면
                if ($this->goodsData['optionFl'] == 'y') {   //일반 옵션형 (일체/분리)
                    if ($this->goodsData['optionDisplayFl'] == 'd') {  //옵션분리형
                        $arrOptionName = $this->goodsData['optionName'];  //옵션이름
                    } else {  //옵션일체형 : s
                        $arrOptionName = explode(STR_DIVISION, $goodsInfo['optionName']);
                    }
                    if ($arrOptionName) {
                        for ($i = 0; $i < count($arrOptionName); $i++) {
                            $optionItem['value'] = null;
                            $_name = trim($arrOptionName[$i]);
                            $optionItem['type'] = 'SELECT';;
                            $optionItem['name'] = $this->cdata($_name);

                            if ($this->goodsData['optionDisplayFl'] == 's') {    //일체형
                                $optionValue = null;
                                foreach ($this->goodsData['option'] as $val) {
                                    $arrOptionValue = explode('/', $val['optionValue']);
                                    if (!$arrOptionValue[$i]) {
                                        continue;
                                    }
                                    $optionValue[$i][] = $arrOptionValue[$i];
                                }
                                foreach (array_unique($optionValue[$i]) as $val) {
                                    $optionValueData['id'] = $this->getOptionValueId('S', $_name, $val);
                                    $optionValueData['text'] = $this->cdata($val);
                                    $optionItem['value'][] = $optionValueData;
                                }
                            } else {  //분리형
                                $values = $this->goods->getOptionValuesByIndex($goodsNo, ($i + 1));
                                foreach ($values as $val) {
                                    $optionValueData['id'] = $this->getOptionValueId('S', $_name, $val);
                                    $optionValueData['text'] = $this->cdata($val);
                                    $optionItem['value'][] = $optionValueData;
                                }
                            }
                            array_push($data['option']['optionItem'], $optionItem);
                        }
                    }
                    $optionCombination = null;
                    if ($this->goodsData['option']) {
                        foreach ($this->goodsData['option'] as $option) {
                            $optionCombination['options'] = null;
                            $optionPrice = 0;
                            $mangaeCode = 'option' . $option['sno'];
                            $optionCombination['manageCode'] = $mangaeCode;   //manageCode optionSno
                            if ($arrOptionManageCodes) {
                                if (in_array($mangaeCode, $arrOptionManageCodes) === false) {
                                    continue;
                                }
                            }

                            if ($this->goodsData['stockFl'] == 'y') {
                                $optionStock = $option['optionSellFl'] == 'n' ? 0 : $option['stockCnt'];
                                $optionCombination['stockQuantity'] = $optionStock;
                            }
                            for ($y = 0; $y < count($arrOptionName); $y++) {
                                if ($this->goodsData['optionDisplayFl'] == 'd') {    //분리형
                                    $optionValue = trim($option['optionValue' . ($y + 1)]);
                                } else {  //일체형
                                    $optionValue = trim(explode('/', $option['optionValue'])[$y]);
                                }
                                $_optName = trim($arrOptionName[$y]);
                                $optionCombination['options'][$y]['name'] = $this->cdata($_optName);
                                $optionCombination['options'][$y]['id'] = $this->getOptionValueId('S', $_optName, $optionValue);
                                $optionPrice += $option['optionPrice'];
                            }

                            $_optionCombination = null;
                            foreach ($this->goodsData['optionText'] as $key => $val) {
                                $_optionCombination['name'] = $this->cdata($val['optionName']);
                                $_optionCombination['id'] = 'T' . $val['sno'];
                                $optionPrice += $val['addPrice'];
                                $optionCombination['options'][] = $_optionCombination;
                            }
                            $optionCombination['price'] = $optionPrice;
                            $data['option']['combination'][] = $optionCombination;
                        }
                    }
                }
                else if($this->goodsData['optionTextFl'] == 'y'){   //텍스트옵션만 있으면.

                    $optionPrice = 0;
                    foreach ($this->goodsData['optionText'] as $key => $val) {
                        $_optionCombination['name'] = $this->cdata($val['optionName']);
                        $_optionCombination['id'] = 'T'.$val['sno'];
                        $optionPrice += $val['addPrice'];
                        $optionCombination['options'][] = $_optionCombination;
                    }
                    $optionCombination['price'] = $optionPrice;
                    $optionCombination['manageCode'] = 'option'.$this->goodsData['option'][0]['sno'];
                    $data['option']['combination'][] = $optionCombination;
                }

                if ($this->goodsData['optionTextFl'] == 'y') {    //옵션입력형
                    $optionItemData = null;
                    foreach ($this->goodsData['optionText'] as $key => $val) {
                        $_optionItem['type'] = 'INPUT';
                        $_optionItem['name'] = $this->cdata($val['optionName']);
                        $optionItemData = $_optionItem;
                        array_push($data['option']['optionItem'], $optionItemData);
                    }
                }

                $productData['product'][] = $data;
            }
            $orderCd++;
        }
        $xmlData = $this->arrayToXML($productData);
        $xmlData = str_replace(['&lt;![CDATA',']]&gt;'],['<![CDATA',']]>'],$xmlData);
        \Logger::channel('naverPay')->info('RESULT', [$xmlData]);
        if ($this->request['test']) {
            //            debug($xmlData);
            //            debug($productData);
        } else {
            exit($xmlData);
        }

    }

    protected function getAddGoodsStock($addGoodsData)
    {
        $addStockQuantity = 0;
        if ($addGoodsData['soldOutFl'] != 'y' && $addGoodsData['viewFl'] == 'y') {
            if (!$addGoodsData['stockUseFl']) {
                $addStockQuantity = 'nolimit';
            } else {
                if ($addGoodsData['stockCnt'] >= 0) {
                    $addStockQuantity = $addGoodsData['stockCnt'];
                }
            }
        } else {
            $addStockQuantity = 0;
        }

        return $addStockQuantity;
    }

    protected function getFeePrice($feeType)
    {
        $deliveryPrice = (int)$this->goodsData['delivery']['charge'][0]['price'];
        $deliveryCharge = $this->goodsData['delivery']['charge'];
        $price = 0;
        switch ($feeType) {
            case self::FEE_TYPE_CONDITIONAL_FREE :  //조건부 무료
                foreach ($deliveryCharge as $val) {
                    if ($val['price'] == 0) {
                        $price = (int)$val['unitStart'];
                        break;
                    }
                }
                return $price;
            case self::FEE_TYPE_CHARGE_BY_QUANTITY :    //수량별
                if ($this->goodsData['delivery']['basic']['rangeRepeat'] == 'y') {
                    $price = $this->goodsData['delivery']['charge'][1]['price'];
                } else {
                    foreach ($deliveryCharge as $val) {
                        if ($val['unitStart'] == 0) {
                            $price = (int)$val['$val'];
                            break;
                        }
                    }
                }

                return $price;
            case self::FEE_TYPE_CHARGE :    //유료
                return $deliveryPrice;
            case self::FEE_TYPE_FREE :  //무료
                return 0;
        }
    }

    protected function getFeeType()
    {
        $deliveryPrice = (int)$this->goodsData['delivery']['charge'][0]['price'];
        switch ($this->goodsData['delivery']['basic']['fixFl']) {
            case 'price' :
                return self::FEE_TYPE_CONDITIONAL_FREE;
            case 'count' :
                return self::FEE_TYPE_CHARGE_BY_QUANTITY;
            default :
                return $deliveryPrice > 0 ? self::FEE_TYPE_CHARGE : self::FEE_TYPE_FREE;
        }
    }


    protected function getFeePayType()
    {
        switch ($this->goodsData['delivery']['basic']['collectFl']) {
            case 'pre' :
                return self::FEE_PAY_TYPE_PREPAYED;
            case 'later' :
                return self::FEE_PAY_TYPE_CASH_ON_DELIVERY;
            case 'collectFl' :
                return self::FEE_PAY_TYPE_PAY_SELECT;
            default :
                return self::FEE_PAY_TYPE_FREE;;
        }
    }

    protected function getDeliveryMethod()
    {
        $deliveryMethodFl = explode(STR_DIVISION, $this->goodsData['delivery']['basic']['deliveryMethodFl'])[0];
        $naverDeliveryMethod = $this->getNaverPayDeliveryMethod($deliveryMethodFl);
        return $naverDeliveryMethod;
    }

    protected function getBasePrice()
    {
        $basePrice = (int)$this->goodsData['goodsPrice'];
        if ($this->config['saleFl'] == 'y' && $this->goodsData['goodsDiscountFl'] == 'y') {
            if ($this->goodsData['goodsDiscountUnit'] == 'price') {
                $basePrice = $this->goodsData['goodsPrice'] - $this->goodsData['goodsDiscount'];
            } else {
                $basePrice = $this->goodsData['goodsPrice'] - (($this->goodsData['goodsDiscount'] / 100) * $this->goodsData['goodsPrice']);
            }
        }

        return $basePrice;
    }

    protected function isNotOption()
    {
        return $this->goodsData['optionFl'] != 'y' && $this->goodsData['optionTextFl'] != 'y' ? true : false;
    }

    protected function getGoodsImageSrc($goodsNo, $imagePath, $imageStorage)
    {
        $goodsImage = $this->goods->getGoodsImage($goodsNo, 'main');
        if ($goodsImage) {
            $goodsImageName = $goodsImage[0]['imageName'];
            $goodsImageSize = $goodsImage[0]['imageSize'];
            $_imageInfo = pathinfo($goodsImageName);
            if (!$goodsImageSize) {
                $goodsImageSize = SkinUtils::getGoodsImageSize($_imageInfo['extension']);
                $goodsImageSize = $goodsImageSize['size1'];
            }
        }

        $goodsImageSrc = SkinUtils::imageViewStorageConfig($goodsImageName, $imagePath, $imageStorage, $goodsImageSize, 'goods', false)[0];
        if($imageStorage == 'local'){
            $goodsImageSrc = str_replace('https', 'http', $goodsImageSrc);
            $goodsImageSrc = str_replace(':443', '', $goodsImageSrc);
        }

        return $goodsImageSrc;
    }

    protected function getStatus()
    {
        if ($this->goodsData['soldOutFl'] == 'y') {
            $goodsStatus = 'SOLD_OUT';
        } else if ($this->goodsData['goodsSellFl'] == 'n') {
            $goodsStatus = 'NOT_SALE';
        } else {
            $goodsStatus = 'ON_SALE';
        }

        return $goodsStatus;
    }

    protected function getNaverPayDeliveryMethod($method)
    {
        switch ($method) {
            case 'delivery' :   //택배
            case 'packet' :   //등기, 소포
                return self::METHOD_DELIVERY;
                break;
            case 'visit' :  //방문접수
                return self::METHOD_VISIT_RECEIPT;
                break;
            case 'quick' :  //퀵배송
                return self::METHOD_QUICK_SVC;
                break;
            case 'cargo' :  //화물배송
            case 'etc' :    //기타
                return self::METHOD_DIRECT_DELIVERY;
                break;
            default :
                return self::METHOD_DELIVERY;
        }
    }

    protected function arrayToXML(array $data, $startElement = 'products', $xml_version = '1.0', $xml_encoding = 'UTF-8'){
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument($xml_version, $xml_encoding);
        $xml->startElement($startElement);

        /**
         * Write XML as per Associative Array
         * @param object $xml XMLWriter Object
         * @param array $data Associative Data Array
         */
        function write(\XMLWriter $xml, $data){
            foreach($data as $key => $value){
                if (is_array($value) && isset($value[0])){
                    foreach($value as $itemValue){
                        //$xml->writeElement($key, $itemValue);

                        if(is_array($itemValue)){
                            $xml->startElement($key);
                            write($xml, $itemValue);
                            $xml->endElement();
                            continue;
                        }

                        if (!is_array($itemValue)){
                            $xml->writeElement($key, $itemValue."");
                        }
                    }
                }else if(is_array($value)){
                    $xml->startElement($key);
                    write($xml, $value);
                    $xml->endElement();
                    continue;
                }

                if (!is_array($value)){
                    $xml->writeElement($key, $value."");
                }
            }
        }
        write($xml, $data);

        $xml->endElement();//write end element
        //returns the XML results
        return $xml->outputMemory(true);
    }
}
