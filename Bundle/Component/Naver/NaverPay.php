<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Naver;

use Component\Delivery\Delivery;

use Component\Policy\Policy;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;

class NaverPay
{
    private $db;
    public $config;

    public function __construct()
    {
        $this->db = \App::load('DB');
        $globals = \App::getInstance('globals');
        $policy = \App::load('Component\\Policy\\Policy');
        if($globals->has('gNaverPay')){
            $data = $globals->get('gNaverPay');
        }
        else {
            $data = $policy->getNaverPaySetting();
            $globals->set('gNaverPay', $data);
        }
        $this->config = $data;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function checkGoods($goodsData, &$result = null)
    {
        $goodsNo = is_array($goodsData) ? $goodsData['goodsNo'] : $goodsData;
        // 상품 체크
        if (ArrayUtils::isEmpty($this->config['exceptGoods']) === false && in_array($goodsNo, $this->config['exceptGoods'])) {
            return [
                'result' => 'n',
                'msg' => __('네이버 페이로 구매가 불가한 상품입니다.'),
            ];
        }

        $goods = \App::load('\\Component\\Goods\\Goods');
        if(is_array($goodsData) === false ) {
            try {
                $goodsData = $goods->getGoodsView($goodsData);
                $delivery = new Delivery();
                $deliveryData = $delivery->getDataSnoDelivery($goodsData['deliverySno']);
            } catch (\Exception $e) {
                return [
                    'result' => 'n',
                    'msg' => __($e->getMessage()),
                ];
            }
        }
        else {  //상품상세일 경우
            $deliveryData = $goodsData['delivery'];
        }
        $goodsCateCd = $goods->getGoodsNoToCateCd($goodsData['goodsNo']);
        if ($goodsData['goodsPrice'] < 1) {   //기본금액이 0원이면
            return [
                'result' => 'n',
                'msg' => __('판매가가 0원인 상품은 구매가 불가합니다.'),
            ];
        }

        if ($deliveryData['basic']['fixFl'] == 'weight') {   //무게별 제한
            return [
                'result' => 'n',
                'msg' => __('무게별 제한 배송비는 구매가 불가합니다.'),
            ];
        }

        if ($deliveryData['basic']['fixFl'] == 'price') {  //금액별 조건부 배송비 2depth 초과 상품 제한
            if (count($deliveryData['charge']) != 2) {
                return [
                    'result' => 'n',
                    'msg' => __('상품의 배송비 조건으로 인하여 네이버 페이로 구매할 수 없습니다. \n쇼핑몰 관리자에게 문의하여 주시기 바랍니다.'),
                ];
            } else {
                $isCondition = false;
                foreach ($deliveryData['charge'] as $row) {
                    if ((int)$row['price'] == 0) {
                        $isCondition = true;
                    }
                }
                if($isCondition === false) {
                    return [
                        'result' => 'n',
                        'msg' => __('상품의 배송비 조건으로 인하여 네이버 페이로 구매할 수 없습니다. \n쇼핑몰 관리자에게 문의하여 주시기 바랍니다.'),
                    ];
                }
            }
        }

        if ($deliveryData['basic']['fixFl'] == 'count' && count($deliveryData['charge']) >= 4) {  //수량별 조건부 배송비 3depth 초과 상품 제한
            return [
                'result' => 'n',
                'msg' => __('수량별 조건이 네이버페이 정책과 달라 구매가 불가합니다.'),
            ];
        }

        if (count(array_intersect($goodsCateCd['cateCd'], $this->config['exceptCateCd']['code'])) > 0) {
            return [
                'result' => 'n',
                'msg' => __('네이버 페이로 구매가 불가한 카테고리입니다.'),
            ];
        }
        if ($goodsData['orderPossible'] == 'n') {
            return [
                'result' => 'n',
                'msg' => __('구매불가 상품입니다.'),
            ];
        }
        $result = $goodsData;
        return [
            'result' => 'y',
            'msg' => '',
        ];
    }

    public function checkUse()
    {
        if ($this->config['useYn'] == 'y') {
            return true;
        } else {
            return false;
        }
    }

    public function checkTest()
    {
        if ($this->config['testYn'] == 'y') {
            return true;
        } else {
            return false;
        }
    }

    public function checkReviewFl()
    {
        if ($this->config['reviewFl'] == 'y') {
            return true;
        } else {
            return false;
        }
    }

    private function setBtn($mode, &$rtn, $isMobile)
    {
        $naverPayUrl = "../goods/naver_pay.php";
        $naverPayWishUrl = "../goods/naver_pay_wish.php";
        $goodsNo = \Request::get()->get('goodsNo');
        $failMsg = $rtn['status']['msg'];
        switch ($mode) {
            case 'view': {
                $btnCnt = 2;
                if ($rtn['status']['result'] == 'y') {
                    $javascript = 'function naverPay() {' . chr(10);
                    if(gd_is_skin_division()){
                        $funcName = "gd_goods_order";
                    }
                    else {
                        $funcName = "goods_order";
                    }
                    $javascript .= 'if(!'.$funcName.'(\'pa\')){ return false; };' . chr(10);
                    $javascript .= 'var frm = $("[name=frmView]");' . chr(10);
                    $javascript .= 'frm.attr("action", "' . $naverPayUrl . '");' . chr(10);
                    if ($isMobile) {
                        $javascript .= 'frm.attr("target","ifrmProcess")' . chr(10);
                    } else {
                        $javascript .= 'window.open("about:blank","naverPayWin");' . chr(10);
                        $javascript .= 'var htmlPopupMode = "<input type=\"hidden\" name=\"popupMode\" value=\"y\">";' . chr(10);
                        $javascript .= 'frm.append(htmlPopupMode)' . chr(10);
                        $javascript .= 'frm.attr("target","naverPayWin")' . chr(10);
                    }
                    $javascript .= 'frm.submit();' . chr(10);
                    $javascript .= 'frm.attr("action", "");' . chr(10);
                    $javascript .= '}' . chr(10);

                    $javascript .= 'var naverCheckoutWin = "";' . chr(10);

                    $javascript .= 'function wishNaverPay() {' . chr(10);
                    $javascript .= 'var frm = $("[name=frmView]");' . chr(10);
                    $javascript .= 'var htmlGoodsNo = "<input type=\"hidden\" name=\"wishGoodsNo\" value=\"' . $goodsNo . '\">"; ' . chr(10);
                    $javascript .= 'frm.append(htmlGoodsNo)' . chr(10);

                    $javascript .= 'frm.attr("action", "' . $naverPayWishUrl . '");' . chr(10);
                    if ($isMobile) {
                        $javascript .= 'frm.attr("target","ifrmProcess")' . chr(10);
                    } else {
                        $javascript .= 'window.open("about:blank","naverPayWish","toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=400,height=300,left = 312,top = 234");' . chr(10);
                        $javascript .= 'var htmlPopupMode = "<input type=\"hidden\" name=\"popupMode\" value=\"y\">";' . chr(10);
                        $javascript .= 'frm.append(htmlPopupMode)' . chr(10);
                        $javascript .= 'frm.attr("target","naverPayWish")' . chr(10);
                    }
                    $javascript .= 'frm.submit();' . chr(10);
                    $javascript .= 'frm.attr("action", "");' . chr(10);
                    $javascript .= '}' . chr(10);
                } else {
                    $javascript = 'function naverPay() {' . chr(10);
                    $javascript .= 'alert("' . $failMsg . '");' . chr(10);
                    $javascript .= '}' . chr(10);

                    $javascript .= 'function wishNaverPay() {' . chr(10);
                    $javascript .= 'alert("' . $failMsg . '");' . chr(10);
                    $javascript .= '}' . chr(10);
                }
                break;
            }
            case 'cart': {
                $btnCnt = 1;
                if ($rtn['status']['result'] == 'n') {
                    $javascript = 'function naverPay() {' . chr(10);
                    $javascript .= 'alert("[' . gd_htmlspecialchars_slashes(strip_tags($rtn['exceptionGoodsNm']), 'add') . '] ' . $failMsg . '");' . chr(10);
                    $javascript .= '}' . chr(10);
                    $javascript .= 'function wishNaverPay() {' . chr(10);
                    $javascript .= 'alert("[' . gd_htmlspecialchars_slashes(strip_tags($rtn['exceptionGoodsNm']), 'add') . '] ' . $failMsg . '");' . chr(10);
                    $javascript .= '}' . chr(10);
                } else {
                    $funcName = 'cart_cnt_info';
                    if (gd_is_skin_division()) $funcName = 'gd_cart_cnt_info';

                    $javascript = 'function naverPay() {' . chr(10);

                    $javascript .= "var checkedCnt = $('#frmCart  input:checkbox[name=\"cartSno[]\"]:checked').length;" . chr(10);
                    $javascript .= "if (checkedCnt == 0) {" . chr(10);
                    $javascript .= " alert('" . __('선택하신 상품이 없습니다.') . "');" . chr(10);
                    $javascript .= "return false;" . chr(10);
                    $javascript .= "}" . chr(10);
                    //장바구니 상품수량 체크
                    $javascript .= "var cartAlertMsg = '';" . chr(10);
                    $javascript .= "if (typeof " . $funcName . " !== 'undefined') {" . chr(10);
                    $javascript .= "cartAlertMsg = " . $funcName . "();" . chr(10);
                    $javascript .= "if (cartAlertMsg) {" . chr(10);
                    $javascript .= "alert(cartAlertMsg);" . chr(10);
                    $javascript .= "return false;" . chr(10);
                    $javascript .= "}" . chr(10);
                    $javascript .= "}" . chr(10);
                    //장바구니 상품수량 체크
                    $javascript .= 'var frm = $("#frmCart");' . chr(10);
                    $javascript .= 'var tmpAction = frm.attr("action");' . chr(10);
                    $javascript .= 'var tmpMode = frm.find("[name=mode]:hidden").val();' . chr(10);
                    $javascript .= 'frm.attr("action", "' . $naverPayUrl . '");' . chr(10);
                    $javascript .= 'frm.find("[name=mode]:hidden").val("cart");' . chr(10);
                    if (!$isMobile) {
                        $javascript .= 'window.open("about:blank","naverPayWin");' . chr(10);
                        $javascript .= 'var htmlPopupMode = "<input type=\"hidden\" name=\"popupMode\" value=\"y\">";' . chr(10);
                        $javascript .= 'frm.append(htmlPopupMode)' . chr(10);
                        $javascript .= 'frm.attr("target","naverPayWin")' . chr(10);
                    }
                    $javascript .= 'frm.submit();' . chr(10);
                    $javascript .= 'frm.attr("action", tmpAction);' . chr(10);
                    $javascript .= 'frm.find("[name=mode]:hidden").val(tmpMode);' . chr(10);
                    $javascript .= 'frm.attr("target","ifrmProcess")' . chr(10);
                    $javascript .= '}' . chr(10);
                }
                break;
            }
        }

        if ($this->checkTest()) {
            $btnScriptDomain = \Request::getScheme() . '://test-pay.naver.com';
        } else {
            $btnScriptDomain = \Request::getScheme() . '://pay.naver.com';
        }

        if ($isMobile) {
            $buttonColor = $rtn['mobileImgColor'];
            $buttonType = $rtn['mobileImgType'];
            $rtn['btnScript'] = '<script type="text/javascript" src="' . $btnScriptDomain . '/customer/js/mobile/naverPayButton.js" charset="UTF-8"></script>' . chr(10);
        } else {
            $buttonColor = $rtn['imgColor'];
            $buttonType = $rtn['imgType'];
            $rtn['btnScript'] = '<script type="text/javascript" src="' . $btnScriptDomain . '/customer/js/naverPayButton.js" charset="UTF-8"></script>' . chr(10);
        }
        $rtn['btnScript'] .= '<script type="text/javascript" >//<![CDATA[' . chr(10);
        $rtn['btnScript'] .= $javascript . chr(10);
        $rtn['btnScript'] .= '</script>' . chr(10);
        $rtn['btnScript'] .= '<script type="text/javascript" >//<![CDATA[' . chr(10);
        $rtn['btnScript'] .= 'naver.NaverPayButton.apply({' . chr(10);
        $rtn['btnScript'] .= 'BUTTON_KEY: "' . $rtn['imageId'] . '", // 체크아웃에서 제공받은 버튼 인증 키 입력' . chr(10);
        $rtn['btnScript'] .= 'TYPE: "' . $buttonType . '", // 버튼 모음 종류 설정' . chr(10);
        $rtn['btnScript'] .= 'COLOR: ' . $buttonColor . ', // 버튼 모음의 색 설정' . chr(10);
        $rtn['btnScript'] .= 'COUNT: ' . $btnCnt . ', // 버튼 개수 설정. 구매하기 버튼만 있으면(장바구니 페이지) 1, 관심상품 버튼도 있으면(상품 상세 페이지) 2를 입력.' . chr(10);
        $rtn['btnScript'] .= 'BUY_BUTTON_HANDLER: naverPay, ' . chr(10);
        if ($mode == 'view' && $btnCnt == 2) {
            $rtn['btnScript'] .= 'WISHLIST_BUTTON_HANDLER: wishNaverPay, ' . chr(10);
        }
        $rtn['btnScript'] .= 'ENABLE: "' . strtoupper($rtn['status']['result']) . '", // 품절 등의 이유로 버튼 모음을 비활성화할 때에는 "N" 입력' . chr(10);

        $rtn['btnScript'] .= '"":""' . chr(10);
        $rtn['btnScript'] .= '});' . chr(10);
        $rtn['btnScript'] .= '//]]></script>';
    }

    public function getDeliveryMethodCode($deliveryMethodFl){
        switch ($deliveryMethodFl){
            case 'delivery' :
                $naverDeliveryMethodCode = 'DELIVERY';
                break;
            case 'packet' :
                $naverDeliveryMethodCode = 'DELIVERY';
                break;
            case 'cargo' :
                $naverDeliveryMethodCode = 'DIRECT_DELIVERY';
                break;
            case 'visit' :
                $naverDeliveryMethodCode = 'VISIT_RECEIPT';
                break;
            case 'quick' :
                $naverDeliveryMethodCode = 'QUICK_SVC';
                break;
            case 'etc' :
                $naverDeliveryMethodCode = 'DELIVERY';
                break;
            default :
                $naverDeliveryMethodCode = 'DELIVERY';
                break;
        }
        return $naverDeliveryMethodCode;
    }

    public function getNaverPayView($goodsData, $isMobile = false)
    {
        if ($this->checkUse() === false) {
            return;
        }

        if ($this->checkTest() && gd_is_admin() === false) {
            return;
        }

        if (\Globals::get('gGlobal.isUse')) {
            $mallInfo = \Session::get(SESSION_GLOBAL_MALL);
            if ($mallInfo) {
                return;
            }
        }

        $rtn = $this->config;
        $rtn['status'] = $this->checkGoods($goodsData);
        $this->setBtn('view', $rtn, $isMobile);

        return gd_isset($rtn['javascript']) . chr(10) . gd_isset($rtn['btnScript']);
    }

    public function getNaverPayCart($item, $isMobile = false)
    {
        if ($this->checkUse() === false) {
            return;
        }

        if ($this->checkTest() && gd_is_admin() === false) {
            return;
        }

        $rtn = $this->config;
        if (ArrayUtils::isEmpty($item) === false) {
            $result = 'y';
            $allowGoods = true;
            foreach ($item as $scm) {
                if (!$allowGoods) {
                    break;
                }
                foreach ($scm as $goods) {
                    if (!$allowGoods) {
                        break;
                    }

                    foreach ($goods as $data) {
                        $status = $this->checkGoods($data['goodsNo'],$goodsData);
                        $cultureBenefitFl = $cultureBenefitFl ?? $goodsData['cultureBenefitFl'];
                        if($goodsData['cultureBenefitFl'] == 'y') {
                            $cultureBenifitGoodsNm = $goodsData['goodsNm'];
                        }
                        if($cultureBenefitFl != $goodsData['cultureBenefitFl']) {
                                $result = 'n';
                                $rtn['exceptionGoodsNm'] = $cultureBenifitGoodsNm;
                                $status['msg'] = __(sprintf("상품은 도서공연비 소득공제 상품입니다. 일반상품과 함께 네이버페이로 구매하실 수 없습니다."));
                            break;
                        }

                        if ($status['result'] != 'y') {
                            $result = 'n';
                            $rtn['exceptionGoodsNm'] = strip_tags($data['goodsNm']);
                            $allowGoods = false;
                            break;
                        }
                    }
                }
            }

            $rtn['status'] = [
                'result' => $result,
                'msg' => $status['msg'],
            ];
            $this->setBtn('cart', $rtn, $isMobile);
        } else {
            return '';
        }

        return gd_isset($rtn['javascript']) . chr(10) . gd_isset($rtn['btnScript']);
    }

    public function getStatus($checkoutData)
    {
        $claimStatus = $checkoutData['orderGoodsData']['ClaimStatus'];
        if ($claimStatus) {
            $status = [
                'CANCEL_REQUEST' => __('취소요청'),
                'CANCELING' => __('취소처리중'),
                'CANCEL_DONE' => __('취소처리완료'),
                'CANCEL_REJECT' => __('취소철회'),
                'RETURN_REQUEST' => __('반품요청'),
                'COLLECTING' => __('수거처리중'),
                'COLLECT_DONE' => __('수거완료'),
                'RETURN_DONE' => __('반품완료'),
                'RETURN_REJECT' => __('반품철회'),
                'EXCHANGE_REQUEST' => __('교환요청'),
                'EXCHANGE_REDELIVERING' => __('교환재배송중'),
                'EXCHANGE_DONE' => __('교환완료'),
                'EXCHANGE_REJECT' => __('교환거부'),
                'PURCHASE_DECISION_HOLDBACK' => __('구매확정보류'),
                'PURCHASE_DECISION_HOLDBACK_REDELIVERING' => __('구매확정보류 재배송중'),
                'PURCHASE_DECISION_REQUEST' => __('구매확정요청'),
                'PURCHASE_DECISION_HOLDBACK_RELEASE' => __('구매확정보류해제'),
                'ADMIN_CANCELING' => __('직권취소중'),
                'ADMIN_CANCEL_DONE' => __('직권취소완료'),
            ];
            $calimStatusText = $status[$claimStatus];

            $naverpayStatus = ['code' => $claimStatus, 'text' => $calimStatusText];
        }

        if ($this->getDelayStatus($checkoutData)) {   //발송지연(클레임없는상태)
            $reason = $this->getClaimReasonCode($checkoutData['orderGoodsData']['DelayedDispatchReason']);
            $detailContents = $checkoutData['orderGoodsData']['DelayedDispatchDetailedReason'];
            $naverpayStatus = ['code' => 'DelayProductOrder', 'reason' => $reason, 'text' => '발송지연', 'date' => substr($checkoutData['orderGoodsData']['ShippingDueDate'], 0, 10), 'contents' => $detailContents, 'notice' => DateTimeUtils::dateFormat('m/d', $checkoutData['orderGoodsData']['ShippingDueDate']) . '발송예정'];
        } else if ($checkoutData['orderGoodsData']['ClaimStatus'] == 'RETURN_REJECT') {  //반품거부
            $detailContents = $checkoutData['returnData']['RejectDetailContent'];   //TODO:제공안함
            $naverpayStatus = ['code' => 'RejectReturn', 'text' => '반품거부', 'contents' => $detailContents];
        } else if (($checkoutData['orderGoodsData']['ClaimStatus'] == 'RETURN_REQUEST' || $checkoutData['orderGoodsData']['ClaimStatus'] == 'COLLECT_DONE') && $checkoutData['returnData']['HoldbackStatus'] == 'HOLDBACK') {   //반품보류                        debug($checkoutData);
            $reason = $this->getClaimReasonCode($checkoutData['returnData']['HoldbackReason']);
            $detailContents = $checkoutData['returnData']['HoldbackDetailedReason']; //반품보류 상세사유
            $naverpayStatus = ['code' => 'WithholdReturn', 'reason' => $reason, 'text' => '반품보류', 'extraData' => $checkoutData['returnData']['EtcFeeDemandAmount'], 'contents' => $detailContents];
        } else if ($checkoutData['orderGoodsData']['ClaimType'] == 'EXCHANGE' && $checkoutData['exchangeData']['HoldbackStatus'] == 'HOLDBACK') { //교환보류
            $reason = $this->getClaimReasonCode($checkoutData['exchangeData']['HoldbackReason']);
            $detailContents = $checkoutData['exchangeData']['HoldbackDetailedReason']; //반품보류 상세사유
            $extraData = $checkoutData['exchangeData']['EtcFeeDemandAmount'];
            $naverpayStatus = ['code' => 'WithholdExchange', 'reason' => $reason, 'text' => '교환보류', 'extraData' => $extraData, 'contents' => $detailContents];
        } else if ($checkoutData['orderGoodsData']['ClaimStatus'] == 'EXCHANGE_REDELIVERING') {    //교환재배송
            $delivery = new Delivery();
            $deliveryCompanyData = $delivery->getDeliveryCompany($checkoutData['exchangeData']['ReDeliveryCompany'], true, 'naverpay');
            $deliveryCompaynNm = is_array($deliveryCompanyData) ? end($deliveryCompanyData)['companyName'] : $deliveryCompanyData['companyName'];
            $naverpayStatus = ['code' => 'ReDeliveryExchange', 'text' => '교환재배송', 'invoiceNo' => $checkoutData['exchangeData']['ReDeliveryTrackingNumber'], 'deliveryCompanyNm' => $deliveryCompaynNm];
        } else if ($claimStatus == 'EXCHANGE_REJECT') {  //교환거부
            $detailContents = $checkoutData['returnData']['RejectDetailContent'];   //TODO:제공안함
            $naverpayStatus = ['code' => 'RejectExchange', 'text' => '교환거부', 'contents' => $detailContents];
        }

        return $naverpayStatus;
    }

    public function getStatusText($checkoutData, $orderStatus)
    {
        $arrStatus = $this->getStatus($checkoutData, $orderStatus);

        return $arrStatus['text'];
    }

    public function getDeliveryMethod($checkoutData)
    {
        $data = $checkoutData['deliveryData'];
        switch ($data['DeliveryMethod']) {
            case 'DELIVERY' :
                $deliveryMethod = __('택배/등기/소포');
                break;
            case 'GDFW_ISSUE_SVC' :
                $deliveryMethod = __('굿스플로 송장 출력');
                break;
            case 'VISIT_RECEIPT' :
                $deliveryMethod = __('방문수령');
                break;
            case 'DIRECT_DELIVERY' :
                $deliveryMethod = __('직접배달');
                break;
            case 'QUICK_SVC' :
                $deliveryMethod = __('퀵서비스');
                break;
            case 'NOTHING' :
                $deliveryMethod = __('배송 없음');
                break;
            case 'RETURN_DESIGNATED' :
                $deliveryMethod = __('지정반품 택배');
                break;
            case 'RETURN_DELIVERY' :
                $deliveryMethod = __('일반반품 택배');
                break;
            case 'RETURN_INDIVIDUAL' :
                $deliveryMethod = __('직접 반송');
                break;
        }

        return $deliveryMethod;
    }

    /**
     * 클레임 처리상태코드
     *
     * @param $code
     *
     * @return
     * @internal param $status
     */
    public function getClaimStatus($code)
    {
        $status = [
            'CANCEL_REQUEST' => __('취소요청'),
            'CANCELING' => __('취소처리중'),
            'CANCEL_DONE' => __('취소처리완료'),
            'CANCEL_REJECT' => __('취소철회'),
            'RETURN_REQUEST' => __('반품요청'),
            'COLLECTING' => __('수거처리중'),
            'COLLECT_DONE' => __('수거완료'),
            'RETURN_DONE' => __('반품완료'),
            'RETURN_REJECT' => __('반품철회'),
            'EXCHANGE_REQUEST' => __('교환요청'),
            'EXCHANGE_REDELIVERING' => __('교환재배송중'),
            'EXCHANGE_DONE' => __('교환완료'),
            'EXCHANGE_REJECT' => __('교환거부'),
            'PURCHASE_DECISION_HOLDBACK' => __('구매확정보류'),
            'PURCHASE_DECISION_HOLDBACK_REDELIVERING' => __('구매확정보류 재배송중'),
            'PURCHASE_DECISION_REQUEST' => __('구매확정요청'),
            'PURCHASE_DECISION_HOLDBACK_RELEASE' => __('구매확정보류해제'),
            'ADMIN_CANCELING' => __('직권취소중'),
            'ADMIN_CANCEL_DONE' => __('직권취소완료'),
        ];

        return $status[$code];
    }

    /**
     * 발송지연 상태 가져오기
     *
     * @param $checkoutData
     *
     * @return null|string
     */
    public function getDelayStatus($checkoutData)
    {
        $delayedDispatchReason = $checkoutData['orderGoodsData']['DelayedDispatchReason'];
        $productOrderStatus = $checkoutData['orderGoodsData']['ProductOrderStatus'];
        $ClaimType = $checkoutData['orderGoodsData']['ClaimType'];
        if ($delayedDispatchReason && $productOrderStatus == 'PAYED' && empty($ClaimType)) {
            return __('발송지연');
        }

        return null;
    }

    /**
     * 보류 상태 값져오기
     *
     * @param $checkoutData
     *
     * @return null|string
     */
    public function getHoldStatus($checkoutData)
    {
        $cancelData = $checkoutData['cancelData']['HoldbackStatus'];
        $returnData = $checkoutData['returnData']['HoldbackStatus'];
        $exchangeData = $checkoutData['exchangeData']['HoldbackStatus'];
        $holdCode = $cancelData ?? $returnData ?? $exchangeData;
        switch ($holdCode) {
            case 'NOT_YET' :
                return __('미보류');
            case 'HOLDBACK' :
                return __('보류중');
            case 'RELEASED' :
                return __('보류해제');
            default :
                return null;
        }
    }

    /**
     * 반품 시 판매자 귀책 사유
     *
     * @param $reason
     * @return bool
     */
    public function isReturnSellerResponsibility($reason)
    {
        $responsibility  = [
            'DELAYED_DELIVERY',// => __('배송 지연'),
            'SOLD_OUT',// => __('상품 품절'),
            'DROPPED_DELIVERY',// => __('배송 누락'),
            'BROKEN',// => __('상품 파손'),
            'INCORRECT_INFO',// => __('상품 정보 상이'),
            'WRONG_DELIVERY',// => __('오배송'),
            'WRONG_OPTION',// => __('색상 등이 다른 상품을 잘못 배송'),
        ];

        return in_array($reason,$responsibility);
    }

    /**
     * 네이버페이 클레임 사유코드 가져오기
     *
     * @param        $code
     * @param string $mode
     * @param bool $isApiCode
     * @param bool $isSellerResponsibility 판매자 귀책 여부
     *
     * @return string
     */
    public function getClaimReasonCode($code = null, $mode = 'cancel', $isApiCode = true, $isSellerResponsibility = null)
    {
        $list['cancel'] = [
            'PRODUCT_UNSATISFIED' => __('서비스 및 상품 불만족'),
            'DELAYED_DELIVERY' => __('배송 지연'),
            'SOLD_OUT' => __('상품 품절'),
        ];

        $list['back'] = [
            'INTENT_CHANGED' => __('구매 의사 취소'),
            'COLOR_AND_SIZE' => __('색상 및 사이즈 변경'),
            'WRONG_ORDER' => __('다른 상품 잘못 주문'),
            'PRODUCT_UNSATISFIED' => __('서비스 및 상품 불만족'),
            'DELAYED_DELIVERY' => __('배송 지연'),
            'SOLD_OUT' => __('상품 품절'),
            'DROPPED_DELIVERY' => __('배송 누락'),
            'BROKEN' => __('상품 파손'),
            'INCORRECT_INFO' => __('상품 정보 상이'),
            'WRONG_DELIVERY' => __('오배송'),
            'WRONG_OPTION' => __('색상 등이 다른 상품을 잘못 배송'),
        ];

        $list['DelayProductOrder'] = [
            'PRODUCT_PREPARE' => '상품준비중',
            'CUSTOMER_REQUEST' => '고객요청',
            'CUSTOM_BUILD' => '주문 제작',
            'RESERVED_DISPATCH' => '예약 발송',
            'ETC' => '기타 사유'
        ];

        $list['WithholdReturn'] = [
            'RETURN_DELIVERYFEE' => '반품 배송비 청구',
            'EXTRAFEEE' => '기타 반품 비용 청구',
            'RETURN_DELIVERYFEE_AND_EXTRAFEEE' => '반품 배송비 및 기타 반품 비용 청구',
            'RETURN_PRODUCT_NOT_DELIVERED' => '반품 상품 미입고',
            'ETC' => '기타 사유'
        ];

        /* if($isSellerResponsibility === false && $isApiCode) {

             $list['WithholdReturn'] = [
                 'ETC' => '기타 사유',
                 'RETURN_PRODUCT_NOT_DELIVERED' => '반품상품 미입고'
             ];
         }
         else if($isSellerResponsibility === true && $isApiCode) {

             $list['WithholdReturn'] = [
                 'RETURN_DELIVERYFEE' => '반품 배송비 청구',
                 'EXTRAFEEE' => '기타 반품 비용 청구',
                 'RETURN_DELIVERYFEE_AND_EXTRAFEEE' => '반품 배송비 및 기타 반품 비용 청구',
             ];
         }*/


        $list['WithholdExchange'] = [
            'EXCHANGE_DELIVERYFEE' => '교환 배송비 청구',
            'EXCHANGE_EXTRAFEE' => '기타 교환 비용 청구',
            'EXCHANGE_PRODUCT_READY' => '교환 상품 준비 중',
            'EXCHANGE_PRODUCT_NOT_DELIVERED' => '교환 상품 미입고',
            'EXCHANGE_HOLDBACK' => '교환 구매 확정 보류',
            'ETC' => '기타 사유'
        ];

        if ($code) {
            $data = array_merge($list['cancel'], $list['back'], $list['DelayProductOrder'], $list['WithholdReturn'], $list['WithholdExchange']);

            return $data[$code];
        }

        $data = $list[$mode];

        if ($isApiCode === false) {
            $data = array_flip($data);
        }

        return $data;
    }

    /**
     * 네이버페이 애러메시지 변환
     *
     * @static
     * @param $msg
     * @return bool|string
     */
    public static function convertErrorMsg($msg) {
        return  substr($msg, 0, strpos($msg, 'Transaction'));
    }
}
