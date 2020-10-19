<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2018 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Order;

use App;
use Component\Cart\Cart;
use Component\Cart\CartAdmin;
use Component\Coupon\Coupon;
use Component\Database\DBTableField;
use Framework\Utility\GodoUtils;

class OrderMultiShipping
{
    protected $db;

    public function __construct()
    {

    }

    /**
     * 복수배송지 사용 여부 [true-사용가능, false-사용불가]
     *
     * @param void
     *
     * @return boolean
     *
     */
    public static function isUseMultiShipping()
    {
        //플러스샵앱 설치여부
        if(GodoUtils::isPlusShop(PLUSSHOP_CODE_MULTISHIPPING) !== true){
            return false;
        }

        //복수배송지 사용설정 여부
        $orderBasic = gd_policy('order.basic');
        if($orderBasic['useMultiShippingFl'] !== 'y'){
            return false;
        }

        return true;
    }

    /**
     * 복수배송지 표현을 위하여 데이터 배열을 조작해야 하는 경우를 체크
     *
     * @param string $multiShippingFl 복수배송지로 주문된 주문건인지 체크
     *
     * @return boolean
     *
     */
    public function checkChangeOrderListKey($multiShippingFl=null)
    {
        $requestData = \Request::request()->toArray();

        /*
         * 주문리스트
         */
        $keyChangePossiblePage = [
            'order_list_all.php', //주문통합 리스트
            'order_list_order.php', //입금대기 리스트
            'order_list_pay.php', //결제완료 리스트
            'order_list_goods.php', //상품준비중 리스트
            'order_list_delivery.php', //배송중 리스트
            'order_list_delivery_ok.php', //배송완료 리스트
            'order_list_settle.php', //구매확정 리스트
        ];
        if($this->isUseMultiShipping() === true && in_array(\Request::getFileUri(), $keyChangePossiblePage) && \Request::get()->get('view') === 'orderGoods'){
            return true;
        }

        /*
         * 클레임 주문리스트
         */
        $keyChangePossiblePage = [
            'order_list_cancel.php', //취소리스트
            'order_list_exchange.php', //교환리스트
            'order_list_back.php', //반품리스트
            'order_list_refund.php', //환불리스트
            'order_list_user_exchange.php', //고객 교환/반품/환불신청 관리
        ];
        if($this->isUseMultiShipping() === true && in_array(\Request::getFileUri(), $keyChangePossiblePage)){
            return true;
        }

        /*
         * 주문상세 상품정보
         */
        $keyChangePossiblePage = [
            'inc_order_view.php' //주문상세
        ];
        $keyChangePossiblePageParameter = [
            'order', //주문내역
            'cancel', //취소내역
            'exchange', //(구)교환내역
            'exchangeCancel', //교환취소내역
            'exchangeAdd', //교환추가내역
            'back', //반품내역
            'refund', //환불내역
            'fail', //결제 중단/실패 내역
        ];
        if($multiShippingFl === 'y' && in_array(\Request::getFileUri(), $keyChangePossiblePage)){
            if(in_array($requestData['orderStatusMode'], $keyChangePossiblePageParameter)){
                return true;
            }
        }

        /*
         * 주문내역서, 주문내역서(고객용)
         */
        $keyChangePossiblePage = [
            'order_print.php' //주문내역서
        ];
        $keyChangePossiblePageParameter = [
            'report', //주문내역서
            'customerReport', //주문내역서(고객용)
        ];
        if($multiShippingFl === 'y' && in_array(\Request::getFileUri(), $keyChangePossiblePage)){
            if(in_array($requestData['orderPrintMode'], $keyChangePossiblePageParameter)){
                return true;
            }
        }

        /*
         * 클레임 팝업창
         */
        $keyChangePossiblePage = [
            'popup_order_view_status.php'
        ];
        $keyChangePossiblePageParameter = [
            'exchange', //교환
            'add', //추가
            'refund', //환불
            'cancel', //취소
        ];
        if($multiShippingFl === 'y' && in_array(\Request::getFileUri(), $keyChangePossiblePage)){
            if(in_array($requestData['actionType'], $keyChangePossiblePageParameter)){
                return true;
            }
        }

        /*
         * 환불접수 후 환불처리 팝업창
         */
        $keyChangePossiblePage = [
            'refund_view.php'
        ];
        if($multiShippingFl === 'y' && in_array(\Request::getFileUri(), $keyChangePossiblePage)){
            return true;
        }

        /*
         * 환불처리로직
         */
        $keyChangePossiblePage = [
            'order_change_ps.php'
        ];
        $keyChangePossiblePageParameter = [
            'refund_complete', //환불완료
        ];
        if($multiShippingFl === 'y' && in_array(\Request::getFileUri(), $keyChangePossiblePage)){
            if(in_array($requestData['mode'], $keyChangePossiblePageParameter)){
                return true;
            }
        }

        /*
         * 상품상세페이지
         */
        $keyChangePossiblePage = [
            'order_view.php'
        ];
        if($multiShippingFl === 'y' && in_array(\Request::getFileUri(), $keyChangePossiblePage)){
            if(trim($requestData['orderNo']) !== ''){
                return true;
            }
        }


        return false;
    }

    public function resetCart($postValue, $isOrderWrite=false)
    {
        $cartSno = $postValue['cartSno'];
        $selectGoods = $postValue['selectGoods'];

        $db = App::load('DB');
        $coupon = new Coupon();

        if($isOrderWrite === true){
            //수기주문
            $cart = new CartAdmin($postValue['memNo']);
            $dbTable = DB_CART_WRITE;
        }
        else {
            $cart = new Cart();
            $dbTable = DB_CART;
        }

        $cartInfo = $cart->getCartInfo($cartSno, null, null, true, false);
        $setCartInfo = $setCartSno = $orderInfoCd = $orderInfoCdBySno = $goodsCouponInfo = $memberDcInfo = [];

        // 상품쿠폰 주문허용을 위해 상품 합계 계산 배열 생성
        $couponPolicy = gd_policy('coupon.config');
        if($couponPolicy['productCouponChangeLimitType'] == 'n' && $postValue['productCouponChangeLimitType'] && $isOrderWrite === false) {
            $couponGoodsPriceSumArray = []; // 상품쿠폰 가격 파라미터 배열 선언
            foreach($cartInfo as $value) { // 카트 정보
                $couponGoodsPriceSumArray[$value] = json_decode($postValue['priceInfo'][$value['goodsNo']][$value['optionSno']], true); // 파라미터 배열 생성
            }
            $goodsCouponForTotalPrice = $cart->getProductCouponGoodsAllPrice($couponGoodsPriceSumArray); // 상품쿠폰 주문허용 주문sum 데이터 재계산
        }

        foreach ($cartInfo as $value) {
            $tmpPriceInfo = json_decode($postValue['priceInfo'][$value['goodsNo']][$value['optionSno']], true);

            $setCartInfo[$value['sno']] = $value;
            if (empty($value['memberCouponNo']) === false) {
                // @todo 기존 복수배송지 사용 시 상품쿠폰을 사용하는 경우 안분된 상품갯수*가격 이 기준금액보다 작을 경우 쿠폰초기화이슈 있음
                if($couponPolicy['productCouponChangeLimitType'] == 'n' && $postValue['productCouponChangeLimitType'] && $isOrderWrite === false) { // 상품쿠폰 주문서페이지 변경 제한안함일 때
                    $memberCouponNo = explode(INT_DIVISION, $value['memberCouponNo']);
                    foreach ($memberCouponNo as $dataCouponSno) { // 배열로 넘어오는 경우도 있어 foreach 처리
                        if ($dataCouponSno) {
                            $couponVal = $coupon->getMemberCouponInfo($dataCouponSno); // 쿠폰 정보 호출
                            if ($goodsCouponForTotalPrice && $couponVal['couponProductMinOrderType'] == 'order' && $isOrderWrite === false) { // 최소금액 기준 주문상품 가격
                                $goodsCouponForTotalPrice['goodsCnt'] = $tmpPriceInfo['goodsCnt']; // 전체쿠폰 합계 함수 리턴 객체에 안분선택된 상품 카운트 삽입
                                $goodsCouponInfo[$value['memberCouponNo']] = [
                                    'saleGoodsCnt' => $tmpPriceInfo['goodsCnt'],
                                    'mileageGoodsCnt' => $tmpPriceInfo['goodsCnt'],
                                    'info' => $coupon->getMemberCouponPrice($goodsCouponForTotalPrice, $value['memberCouponNo'])
                                ];
                                // 쿠폰적용 가격 기존으로 대체
                                unset($goodsCouponInfo[$value['memberCouponNo']]['info']['memberCouponSalePrice'], $goodsCouponInfo[$value['memberCouponNo']]['info']['memberCouponAddMileage']);
                                $tmpOriginProductPrice = $coupon->getMemberCouponPrice($tmpPriceInfo, $value['memberCouponNo']); // 기준 금액 변경 전 쿠폰적용가
                                $goodsCouponInfo[$value['memberCouponNo']]['info']['memberCouponSalePrice'] = $tmpOriginProductPrice['memberCouponSalePrice']; // 할인액
                                $goodsCouponInfo[$value['memberCouponNo']]['info']['memberCouponAddMileage'] = $tmpOriginProductPrice['memberCouponAddMileage']; // 적립액
                            } else { // 최소금액 기준 상품 개별 가격
                                $goodsCouponInfo[$value['memberCouponNo']] = [
                                    'saleGoodsCnt' => $tmpPriceInfo['goodsCnt'],
                                    'mileageGoodsCnt' => $tmpPriceInfo['goodsCnt'],
                                    'info' => $coupon->getMemberCouponPrice($tmpPriceInfo, $value['memberCouponNo'])
                                ];
                            }
                        }
                    }
                } else { // 상품쿠폰 주문서페이지 변경 제한함 일 때(기존 복수배송지 쿠폰 프로세스)
                    $goodsCouponInfo[$value['memberCouponNo']] = [
                        'saleGoodsCnt' => $tmpPriceInfo['goodsCnt'],
                        'mileageGoodsCnt' => $tmpPriceInfo['goodsCnt'],
                        'info' => $coupon->getMemberCouponPrice($tmpPriceInfo, $value['memberCouponNo'])
                    ];
                }
            }
        }
        unset($cartInfo);
        $cartKey = array_keys($setCartInfo);

        $setCart = [];
        foreach ($selectGoods as $key => $val) {
            if($isOrderWrite === true){
                $getData = json_decode($val, true);
            }
            else {
                $getData = json_decode(str_replace(['""', '"\\"'], ['"', '\\"'], $val), true);
            }

            foreach ($getData as $tKey => $tVal) {
                if ($tVal['goodsCnt'] > 0) {
                    $type = 'insert';
                    $addGoodsNo = $addGoodsCnt = '';
                    $tmpAddGoodsNo = $tmpAddGoodsCnt = [];
                    if (count($tVal['addGoodsNo']) > 0) {
                        foreach ($tVal['addGoodsCnt'] as $aKey => $aVal) {
                            if ($aVal > 0) {
                                $tmpAddGoodsNo[] = $tVal['addGoodsNo'][$aKey];
                                $tmpAddGoodsCnt[] = $tVal['addGoodsCnt'][$aKey];
                            }
                        }
                        $addGoodsNo = json_encode($tmpAddGoodsNo);
                        $addGoodsCnt = json_encode($tmpAddGoodsCnt);
                    }
                    if (in_array($tVal['sno'], $cartKey) === true) {
                        $type = 'update';
                        unset($cartKey[array_search($tVal['sno'], $cartKey)]);
                    }

                    if ($type == 'update') {
                        $arrData = [
                            'goodsCnt' => $tVal['goodsCnt'],
                            'addGoodsNo' => $addGoodsNo,
                            'addGoodsCnt' => $addGoodsCnt,
                        ];
                        if (empty($tVal['deliveryMethodFl']) === false) $arrData['deliveryMethodFl'] = $tVal['deliveryMethodFl'];
                        if (empty($tVal['deliveryCollectFl']) === false) $arrData['deliveryCollectFl'] = $tVal['deliveryCollectFl'];
                        $arrBind = $db->get_binding(DBTableField::tableCart(), $arrData, 'update', array_keys($arrData));
                        $db->bind_param_push($arrBind['bind'], 'i', $tVal['sno']);
                        $db->set_update_db($dbTable, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        $cartSno = $tVal['sno'];
                    } else {
                        $arrData = [
                            'mallSno' => $setCartInfo[$tVal['sno']]['mallSno'],
                            'siteKey' => $setCartInfo[$tVal['sno']]['siteKey'],
                            'memNo' => $setCartInfo[$tVal['sno']]['memNo'],
                            'directCart' => $setCartInfo[$tVal['sno']]['directCart'],
                            'goodsNo' => $tVal['goodsNo'],
                            'optionSno' => $setCartInfo[$tVal['sno']]['optionSno'],
                            'goodsCnt' => $tVal['goodsCnt'],
                            'addGoodsNo' => $addGoodsNo,
                            'addGoodsCnt' => $addGoodsCnt,
                            'optionText' => $setCartInfo[$tVal['sno']]['optionText'],
                            'deliveryCollectFl' => $setCartInfo[$tVal['sno']]['deliveryCollectFl'],
                            'deliveryMethodFl' => $setCartInfo[$tVal['sno']]['deliveryMethodFl'],
                            'memberCouponNo' => $setCartInfo[$tVal['sno']]['memberCouponNo'],
                            'tmpOrderNo' => $setCartInfo[$tVal['sno']]['tmpOrderNo'],
                            'useBundleGoods' => $setCartInfo[$tVal['sno']]['useBundleGoods'],
                        ];
                        if (empty($tVal['deliveryMethodFl']) === false) $arrData['deliveryMethodFl'] = $tVal['deliveryMethodFl'];
                        if (empty($tVal['deliveryCollectFl']) === false) $arrData['deliveryCollectFl'] = $tVal['deliveryCollectFl'];
                        $arrBind = $db->get_binding(DBTableField::tableCart(), $arrData, 'insert');
                        $db->set_insert_db($dbTable, $arrBind['param'], $arrBind['bind'], 'y');
                        $cartSno = $db->insert_id();
                    }
                    $setCartSno[] = $orderInfoCd[$key][] = $cartSno;
                    $orderInfoCdBySno[$cartSno] = $key;
                }
            }
        }
        return [
            'setCartSno' => $setCartSno,
            'orderInfoCd' => $orderInfoCd,
            'orderInfoCdBySno' => $orderInfoCdBySno,
            'goodscouponInfo' => $goodsCouponInfo,
        ];
    }

    /**
     * 수기주문에서의 복수배송지 사용시 post data 가공
     *
     * @param array $postValue
     *
     * @return array $returnData
     *
     */
    public function setOrderWritePostData($postValue)
    {
        $returnData = $postValue;
        $index = 0;

        $cart = new CartAdmin($postValue['memNo']);
        $cartInfo = $cart->getCartGoodsData(null, null, null, true);
        foreach($cartInfo as $key => $value){
            foreach($value as $key1 => $value1){
                foreach($value1 as $key2 => $value2) {
                    if($value2['priceInfo']){
                        $returnData['priceInfo'][$value2['goodsNo']][$value2['optionSno']] = $value2['priceInfo'];
                    }
                }
            }
        }

        foreach($postValue['receiverInfo'] as $value){
            if($index === 0){
                $returnData = array_merge((array)$returnData, (array)$value);
            }
            else {
                $returnData['receiverNameAdd'][$index] = $value['receiverName'];
                $returnData['receiverPhoneAdd'][$index] = $value['receiverPhone'];
                $returnData['receiverCellPhoneAdd'][$index] = $value['receiverCellPhone'];
                $returnData['receiverZonecodeAdd'][$index] = $value['receiverZonecode'];
                $returnData['receiverZipcodeAdd'][$index] = $value['receiverZipcode'];
                $returnData['receiverAddressAdd'][$index] = $value['receiverAddress'];
                $returnData['receiverAddressSubAdd'][$index] = $value['receiverAddressSub'];
                $returnData['orderMemoAdd'][$index] = $value['orderMemo'];
                $returnData['deliveryVisitAdd'][$index] = $value['deliveryVisit'];
                $returnData['visitAddressAdd'][$index] = $value['visitAddress'];
                $returnData['visitNameAdd'][$index] = $value['visitName'];
                $returnData['visitPhoneAdd'][$index] = $value['visitPhone'];
                $returnData['visitMemoAdd'][$index] = $value['visitMemo'];
                if (empty($value['receiverUseSafeNumberFl']) == false) {
                    $returnData['receiverUseSafeNumberFlAdd'][$index] = $value['receiverUseSafeNumberFl'];
                }
            }
            $index++;
        }
        $returnData['cartSno'] = json_decode($postValue['multiShippingCartSno'], true);
        unset($returnData['receiverInfo']);

        return $returnData;
    }

    /**
     * 복수배송지주문건 상품추가시 cart정보 변경을 위해 post data 를 가공
     *
     * @param array $postValue
     * @param array $multiShippingOrderInfo
     *
     * @return array $returnData
     *
     */
    public function setMultiShippingClaimAddData($postValue, $multiShippingOrderInfo)
    {
        $returnData = [
            'memNo' => 0,
        ];
        $receiverInfo = [];

        $multiShippingOrderInfoCd = [];
        $multiShippingOrderInfoCdArray = explode(STR_DIVISION, $postValue['multiShippingOrderInfoCd']);
        if(count($multiShippingOrderInfoCdArray) > 0){
            foreach($multiShippingOrderInfoCdArray as $data){
                $dataArray = explode(INT_DIVISION, $data);
                $multiShippingOrderInfoCd[$dataArray[0]] = $dataArray[1];
            }
        }

        $cartAdmin = new CartAdmin(0);
        $cartInfo = $cartAdmin->getCartGoodsData(null, null, null, false);
        foreach($cartInfo as $key => $value){
            foreach($value as $key1 => $value1){
                foreach($value1 as $key2 => $value2) {
                    $goodsNo = ($value2['goodsType'] === 'goods') ? $value2['goodsNo'] : $value2['addGoodsNo'];
                    $selectGoodsArrayKey = $multiShippingOrderInfoCd[$value2['sno'].$goodsNo];
                    $returnData['cartSno'][] = $value2['sno'];

                    $addGoodsNo = '';
                    $addGoodsCnt = '';
                    if(count($value2['addGoods']) > 0){
                        $addGoodsNo = [];
                        $addGoodsCnt = [];
                        foreach($value2['addGoods'] as $aKey => $aVal){
                            $addGoodsNo[] = $aVal['addGoodsNo'];
                            $addGoodsCnt[] = $aVal['addGoodsCnt'];
                        }
                    }
                    $selectGoodsArray[$selectGoodsArrayKey][] = [
                        'sno' => $value2['sno'],
                        'scmNo' => $value2['scmNo'],
                        'deliverySno' => $value2['deliverySno'],
                        'addGoodsNo' => $addGoodsNo,
                        'addGoodsCnt' => $addGoodsCnt,
                        'goodsNo' => $value2['goodsNo'],
                        'goodsCnt' => $value2['goodsCnt'],
                    ];
                    if($value2['priceInfo']){
                        $returnData['priceInfo'][$key] = $value2['priceInfo'];
                    }
                    $receiverInfo[$selectGoodsArrayKey] = $multiShippingOrderInfo[$selectGoodsArrayKey];
                }
            }
        }

        foreach($receiverInfo as $index => $value){
            $returnData['receiverNameAdd'][$index] = $value['receiverName'];
            $returnData['receiverPhoneAdd'][$index] = $value['receiverPhone'];
            $returnData['receiverCellPhoneAdd'][$index] = $value['receiverCellPhone'];
            $returnData['receiverZonecodeAdd'][$index] = $value['receiverZonecode'];
            $returnData['receiverZipcodeAdd'][$index] = $value['receiverZipcode'];
            $returnData['receiverAddressAdd'][$index] = $value['receiverAddress'];
            $returnData['receiverAddressSubAdd'][$index] = $value['receiverAddressSub'];
            $returnData['orderMemoAdd'][$index] = $value['orderMemo'];
        }

        if(count($selectGoodsArray) > 0){
            foreach($selectGoodsArray as $selectGoodsArrayKey => $valueArray){
                $returnData['selectGoods'][$selectGoodsArrayKey] = json_encode($valueArray);
            }
        }

        return $returnData;
    }

    /**
     * 동일 주소 체크
     *
     * @param array $postValue
     *
     * @return boolean
     *
     */
    public function checkReceiverInfoSame($postValue)
    {
        $checkReceiverInfoArray = [];

        if ($postValue['deliveryVisit'] != 'y') {
            $checkReceiverInfoArray[] = str_replace(' ', '', $postValue['receiverAddress']) . str_replace(' ', '', $postValue['receiverAddressSub']);
        }
        if(count(array_filter($postValue['receiverAddressAdd'])) > 0){
            foreach($postValue['receiverAddressAdd'] as $key => $value){
                if ($postValue['deliveryVisitAdd'][$key] != 'y') {
                    $checkReceiverInfoArray[] = str_replace(' ', '', $value) . str_replace(' ', '', $postValue['receiverAddressSubAdd'][$key]);
                }
            }
        }
        $allCount = count($checkReceiverInfoArray);
        $uniquecount = count(array_unique($checkReceiverInfoArray));

        if($allCount !== $uniquecount){
            return false;
        }

        return true;
    }

    /**
     * 기존 데이터를 복수배송지 첫번째 값으로 변환 merge 시 불필요한 값이 변환될 수 있으므로 직접대입
     *
     * @param array $originalData
     * @param array $multiShippingData
     *
     * @return array $originalData
     *
     */
    public function changeReceiverData($originalData, $multiShippingData)
    {
        foreach($multiShippingData as $receiverData){
            $originalData['receiverName'] = $receiverData['receiverName'];
            $originalData['receiverCountryCode'] = $receiverData['receiverCountryCode'];
            $originalData['receiverPhonePrefixCode'] = $receiverData['receiverPhonePrefixCode'];
            $originalData['receiverPhonePrefix'] = $receiverData['receiverPhonePrefix'];
            if(is_array($originalData['receiverPhone'])){
                $originalData['receiverPhone'] = explode("-", $receiverData['receiverPhone']);
            }
            else {
                $originalData['receiverPhone'] = $receiverData['receiverPhone'];
            }
            if(is_array($originalData['receiverCellPhone'])){
                $originalData['receiverCellPhone'] = explode("-", $receiverData['receiverCellPhone']);
            }
            else {
                $originalData['receiverCellPhone'] = $receiverData['receiverCellPhone'];
            }
            $originalData['receiverCellPhonePrefixCode'] = $receiverData['receiverCellPhonePrefixCode'];
            $originalData['receiverCellPhonePrefix'] = $receiverData['receiverCellPhonePrefix'];
            $originalData['receiverZipcode'] = $receiverData['receiverZipcode'];
            $originalData['receiverZonecode'] = $receiverData['receiverZonecode'];
            $originalData['receiverCountry'] = $receiverData['receiverCountry'];
            $originalData['receiverState'] = $receiverData['receiverState'];
            $originalData['receiverCity'] = $receiverData['receiverCity'];
            $originalData['receiverAddress'] = $receiverData['receiverAddress'];
            $originalData['receiverAddressSub'] = $receiverData['receiverAddressSub'];
            $originalData['orderMemo'] = $receiverData['orderMemo'];
            break;
        }

        return $originalData;
    }
}
?>
