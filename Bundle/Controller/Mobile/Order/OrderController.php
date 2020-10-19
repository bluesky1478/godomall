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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Mobile\Order;

use Component\Agreement\BuyerInformCode;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Component\Database\DBTableField;
use Component\Cart\Cart;
use Component\Member\Member;
use Component\Order\Order;
use Component\Mall\Mall;
use Globals;
use Session;
use Request;
use Message;
use Encryptor;

/**
 * 주문서 작성
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class OrderController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true) {
                $this->addScript(['gd_multi_shipping.js']);
                $this->setData('isUseMultiShipping', true);
            }

            // 모듈 설정
            $cart = new Cart();
            $order = new Order();
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');

            // 선택된 상품만 주문서 상품으로
            if (Request::get()->has('cartIdx')) {
                $cartIdx = $cart->getOrderSelect(Request::get()->get('cartIdx'));
                $this->setData('cartIdx', $cartIdx);
            }

            // 장바구니 정보 (최상위 로드)
            $cartInfo = $cart->getCartGoodsData($cartIdx, null, null, false, true);
            $this->setData('cartInfo', $cartInfo);

            // 주문서에 상품이 없는 경우 처리
            if (empty($cartInfo) === true) {
                throw new AlertRedirectException(__('장바구니에 상품이 없습니다. 장바구니에 상품을 담으신 후 주문해주세요.'), null, null, '../main/index.php');
            }

            // 주문불가한 경우 진행 중지
            if (!$cart->orderPossible) {
                if(trim($cart->orderPossibleMessage) !== ''){
                    throw new AlertRedirectException(__($cart->orderPossibleMessage), null, null, '../order/cart.php');
                }
                else {
                    throw new AlertRedirectException(__('구매 불가 상품이 포함되어 있으니 장바구니에서 확인 후 다시 주문해주세요.'), null, null, '../order/cart.php');
                }
            }

            // EMS 배송불가
            if (!$cart->emsDeliveryPossible) {
                throw new AlertRedirectException(__('무게가 %sg 이상의 상품은 구매할 수 없습니다. (배송범위 제한)', '30k'), null, null, '../order/cart.php', 'top');
            }

            // 결제수단이 없는 경우 진행 중지
            if (empty($cart->payLimit) === false && in_array('false', $cart->payLimit)) {
                throw new AlertRedirectException(__('결제 가능한 결제수단이 없으니 장바구니에서 확인 후 다시 주문해주세요.'), null, null, '../order/cart.php');
            }

            // 설정 변경등으로 쿠폰 할인가등이 변경된경우
            if (!$cart->changePrice) {
                throw new AlertRedirectException(__('할인/적립 금액이 변경되었습니다. 상품 결제 금액을 확인해 주세요!'), null, null, '../order/cart.php');
            }

            // 이미 가져온 cart 데이터에서 적용된 쿠폰의 유효성 체크 진행 (두레이 번호:1853 참고)
            // : 쿠폰이 적용되어 있을 경우 중복으로 적용되어 있는지 체크
            $checkDuplMemberCouponNo = [];
            foreach ($cartInfo as $_tmpCartInfo1) {
                if (is_array($_tmpCartInfo1) === true && empty($_tmpCartInfo1) === false) {
                    foreach ($_tmpCartInfo1 as $_tmpCartInfo2) { //key : scmNo
                        if (is_array($_tmpCartInfo2) === true && empty($_tmpCartInfo2) === false) {
                            foreach ($_tmpCartInfo2 as $_cartInfo) { //key : deliverySno
                                if (empty($_cartInfo['memberCouponNo']) === false) {
                                    $tmpApplyMemberCouponList = explode(INT_DIVISION, $_cartInfo['memberCouponNo']);
                                    foreach ($tmpApplyMemberCouponList as $tmpApplyMemberCouponNo) {
                                        if (array_key_exists($tmpApplyMemberCouponNo, $checkDuplMemberCouponNo) === true) {
                                            throw new AlertRedirectException(__('이미 사용중인 쿠폰이 적용되어 있습니다.'), null, null, '../order/cart.php');
                                        }
                                        $checkDuplMemberCouponNo[$tmpApplyMemberCouponNo] = $tmpApplyMemberCouponNo;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // 추가 정보
            $addFieldInfo = $order->getOrderAddFieldUseList($cartInfo);
            $this->setData('addFieldInfo', $addFieldInfo);

            // 휴대폰 본인인증 설정
            $dreamInfo = gd_policy('member.auth_cellphone');
            $kcpInfo = gd_policy('member.auth_cellphone_kcp');
            $dreamInfo['useFlKcp']  = $kcpInfo['useFlKcp'];
            $dreamInfo['useDataModifyFlKcp']  = $kcpInfo['useDataModifyFlKcp'];
            $authData = $dreamInfo;

            // 장바구니 생성시 만들어진 금액 정보
            $this->setData('cartCnt', $cart->cartCnt); // 장바구니 수량
            $this->setData('cartScmInfo', $cart->cartScmInfo); // 장바구니 SCM 정보
            $this->setData('cartScmCnt', $cart->cartScmCnt); // 장바구니 SCM 수량
            $this->setData('cartScmGoodsCnt', $cart->cartScmGoodsCnt); // 장바구니 SCM 상품 갯수
            $this->setData('totalGoodsPriceSum', $cart->totalPrice); // 상품 총 (판매가격, 옵션가격, 텍스트 옵션 가격, 추가 상품 가격)
            $this->setData('totalGoodsPrice', $cart->totalGoodsPrice); // 상품 총 가격
            $this->setData('totalGoodsDcPrice', $cart->totalGoodsDcPrice); // 상품 할인 총 가격
            $this->setData('totalGoodsMileage', $cart->totalGoodsMileage); // 상품별 총 상품 마일리지
            $this->setData('totalScmGoodsPrice', $cart->totalScmGoodsPrice); // SCM 별 상품 총 가격
            $this->setData('totalScmGoodsDcPrice', $cart->totalScmGoodsDcPrice); // SCM 별 상품 할인 총 가격
            $this->setData('totalScmGoodsMileage', $cart->totalScmGoodsMileage); // SCM 별 총 상품 마일리지
            $this->setData('totalMemberDcPrice', $cart->totalMemberDcPrice); // 회원 그룹 추가 할인 총 가격
            $this->setData('totalMemberBankDcPrice', 0); // 회원 등급할인 브랜드 무통장 할인 총 가격
            $this->setData('totalMemberOverlapDcPrice', $cart->totalMemberOverlapDcPrice); // 회원 그룹 중복 할인 총 가격
            $this->setData('totalScmMemberDcPrice', $cart->totalScmMemberDcPrice); // scm 별 회원 그룹 추가 할인 총 가격
            $this->setData('totalScmMemberOverlapDcPrice', $cart->totalScmMemberOverlapDcPrice); // scm 별 회원 그룹 중복 할인 총 가격
            $this->setData('totalSumMemberDcPrice', $cart->totalSumMemberDcPrice); // 회원 할인 총 금액
            $this->setData('totalMyappDcPrice', $cart->totalMyappDcPrice); // 마이앱 할인 총 금액
            $this->setData('totalScmMyappDcPrice', $cart->totalScmMyappDcPrice); // SCM 별 마이앱 할인 총 금액
            $this->setData('totalMemberMileage', $cart->totalMemberMileage); // 회원 그룹 총 마일리지
            $this->setData('totalScmMemberMileage', $cart->totalScmMemberMileage); // scm 별 회원 그룹 총 마일리지
            $this->setData('totalCouponGoodsDcPrice', $cart->totalCouponGoodsDcPrice); // 상품 총 쿠폰 금액
            $this->setData('totalScmCouponGoodsDcPrice', $cart->totalScmCouponGoodsDcPrice); // scm 별 상품 총 쿠폰 금액
            $this->setData('totalCouponGoodsMileage', $cart->totalCouponGoodsMileage); // 상품 총 쿠폰 마일리지
            $this->setData('totalScmCouponGoodsMileage', $cart->totalScmCouponGoodsMileage); // scm 별 상품 총 쿠폰 마일리지
            $this->setData('totalDeliveryCharge', $cart->totalDeliveryCharge); // 상품 배송정책별 총 배송 금액
            $this->setData('totalScmGoodsDeliveryCharge', $cart->totalScmGoodsDeliveryCharge); // SCM 별 총 배송 금액
            $this->setData('totalSettlePrice', $cart->totalSettlePrice); // 총 결제 금액 (예정)
            $this->setData('totalMileage', $cart->totalMileage); // 총 적립 마일리지 (예정)
            $this->setData('orderPossible', $cart->orderPossible); // 주문 가능 여부
            $this->setData('setDeliveryInfo', $cart->setDeliveryInfo); // 배송비조건별 배송 정보
            $this->setData('totalDeliveryWeight', $cart->totalDeliveryWeight); // 배송 상품의 무게
            $this->setData('deliveryFree', gd_isset($cart->_memInfo['deliveryFree'], 'n')); // 회원 배송비 무료 여부
            $this->setData('authData', $authData); // 휴대폰 본인인증 설정
            $this->setData('cartGoodsCnt', $cart->cartGoodsCnt);
            $this->setData('cartAddGoodsCnt', $cart->cartAddGoodsCnt);
            $this->setData('authCellphoneFl', gd_use_auth_cellphone());
            $this->setData('authDataCpCode', gd_get_auth_cellphone());
            $this->setData('domainUrl', Request::getDomainUrl());
            $returnUrl = gd_php_self();
            $this->setData('returnUrl', $returnUrl);
            $this->setData('ipinFl', gd_use_ipin());
            $session = \App::getInstance('session');
            $memberAccess = gd_policy('member.access');
            if (gd_is_login() === false && ($session->has(Member::SESSION_DREAM_SECURITY) === true || $session->has(Member::SESSION_IPIN)) && $memberAccess['guestUnder14Fl'] == 's') {
                // 휴대폰 본인인증 session data
                $phoneAuthSession = $session->get(Member::SESSION_DREAM_SECURITY);
                // 아이핀 본인인증 session data
                $ipinAuthSession = $session->get(Member::SESSION_IPIN);
                $this->setData('memberSessionName', gd_isset($phoneAuthSession['name'], $ipinAuthSession['userName']));
                $this->setData('memberSessionPhone', $phoneAuthSession['phone']);
                $this->setData('memberGuestAuthFl', $session->get('certGuest.guestAuthFl'));
            }

            // 주문시 마일리지 사용하는 경우 적립마일리지 지급 여부
            $this->setData('mileageGiveExclude', $cart->mileageGiveExclude);

            // 사은품 증정 정책
            $giftConf = gd_policy('goods.gift');
            $this->setData('giftConf', $giftConf);
            if (gd_is_plus_shop(PLUSSHOP_CODE_GIFT) === true && $giftConf['giftFl'] == 'y') {
                // 사은품리스트
                $gift = \App::load('\\Component\\Gift\\Gift');
                $this->setData('giftInfo', $gift->getGiftPresentOrder($cart->giftForData));
            }

            // 마일리지 지급 정보
            $mileage = gd_mileage_give_info();
            $this->setData('mileage', $mileage['info']);

            // 회원 정보
            $memInfo = $this->getData('gMemberInfo');

            // 주문자 전화번호
            $this->setData('orderPhone', explode('-', $memInfo['phone']));
            $this->setData('orderCellPhone', explode('-', $memInfo['cellPhone']));

            // 쿠폰 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부
            $this->setData('couponConfig', gd_isset($couponConfig)); // 쿠폰설정
            $this->setData('productCouponChangeLimitType', $couponConfig['productCouponChangeLimitType']); // 상품쿠폰 주문서 제한여부

            // 결제가능한 수단이 무통장인경우 payco미노출처리
            if (!empty($cart->payLimit) && ((in_array('gb', $cart->payLimit) && !in_array('pg', $cart->payLimit)) || (!in_array('gb', $cart->payLimit) && !in_array('pg', $cart->payLimit)))) {
                $onlyBankFl = 'y';
            } else {
                $onlyBankFl = 'n';
            }

            // 마일리지 정책
            // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
            $mileagePrice = $cart->setMileageUseLimitPrice();
            // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
            $mileageUse = $cart->getMileageUseLimit(gd_isset($memInfo['mileage'], 0), $mileagePrice);
            /* 쿠폰의 결제수단 설정이 무통장만 사용 가능한 경우, 마일리지 및 예치금 사용 불가 정책으로 주석 처리 함.
            if ($mileageUse['payUsableFl'] == 'y' && $onlyBankFl == 'y') {
                $cart->payLimit[] = 'gm';
            }
            */
            if (gd_isset($cart->payLimit) && in_array('gm', $cart->payLimit) == false) {
                $mileageUse['payUsableFl'] = 'n';
            }
            $this->setData('mileageUse', $mileageUse);

            // 마일리지-쿠폰 동시사용 설정
            if ($couponConfig['couponUseType'] == 'y' && $mileageUse['payUsableFl'] == 'y') { // 마일리지와 쿠폰이 모두 사용상태일때만 동시사용설정 체크
                $this->setData('chooseMileageCoupon', gd_isset($memberAccess['chooseMileageCoupon'], 'n'));
            } else {
                $this->setData('chooseMileageCoupon', 'n');
            }

            // 예치금 정책
            $depositUse = gd_policy('member.depositConfig');
            /* 쿠폰의 결제수단 설정이 무통장만 사용 가능한 경우, 마일리지 및 예치금 사용 불가 정책으로 주석 처리 함.
            if ($depositUse['payUsableFl'] == 'y' && $onlyBankFl == 'y') {
                $cart->payLimit[] = 'gd';
            }
            */
            if (gd_isset($cart->payLimit) && in_array('gd', $cart->payLimit) == false) {
                $depositUse['payUsableFl'] = 'n';
            }
            $this->setData('depositUse', $depositUse);

            // 무통장 입금 은행
            $bank = $order->getBankInfo(null, 'y');
            $this->setData('bank', $bank);

            // 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
            $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅

            // 개인 정보 수집 동의 - 이용자 동의 사항
            $tmp = gd_buyer_inform('001003');
            $private = $tmp['content'];
            if (gd_is_html($private) === false) {
                $private = nl2br($private);
            }
            $this->setData('private', gd_isset($private));

            // 주문정책
            $this->setData('orderPolicy', $order->orderPolicy);

            // 결제 방법 설정
            if (gd_isset($memInfo['settleGb'], 'all') == 'all') {
                $settleSelect = $cart->couponSettleMethod; // 회원 등급별 결제 방법이 전체 인경우 쿠폰에 따른 결제 방법을 따름
            } else {
                $settleSelect = $memInfo['settleGb']; // 회원 등급별 결제 방법이 제한 인경우 등급에 따른 결제 방법을 따름
            }

            // 세금 계산서 사용 여부 cart->taxInvoice === true 체크 안함
            $taxInfo = gd_policy('order.taxInvoice');
            if ($cart->taxInvoice && gd_isset($taxInfo['taxInvoiceUseFl']) == 'y' && gd_isset($taxInfo['taxInvoiceOrderUseFl'], 'y') == 'y' && (gd_isset($taxInfo['gTaxInvoiceFl']) == 'y' || gd_isset($taxInfo['eTaxInvoiceFl']) == 'y')) {
                if (gd_isset($taxInfo['TaxMileageFl']) == 'y' || gd_isset($taxInfo['taxDepositFl']) == 'y') {
                    $receipt['taxZeroFl'] = 'y';
                } else {
                    $receipt['taxZeroFl'] = 'n';
                }

                $receipt['taxFl'] = 'y';

                // 세금계산서 이용안내
                $taxInvoiceInfo = gd_policy('order.taxInvoiceInfo');
                if ($taxInfo['taxinvoiceInfoUseFl'] == 'y') {
                    $this->setData('taxinvoiceInfo', nl2br($taxInvoiceInfo['taxinvoiceInfo']));
                }

                // 세금계산서 입력 정보 가져오기
                if (gd_is_login() === true) {
                    $tax = \App::load('\\Component\\Order\\Tax');
                    $memNo = Session::get('member.memNo');
                    $memberTaxInfo = $tax->getMemberTaxInvoiceInfo($memNo);
                    $memberInvoiceInfo['tax'] = $memberTaxInfo;
                }
            } else {
                $receipt['taxFl'] = 'n';
                $receipt['taxZeroFl'] = 'n';
            }
            // 배송비 무료일 경우 세금계산서 노출시 총 결제금액에서 배송비 차감 제외
            if ($cart->_memInfo['deliveryFree'] == 'y') {
                $taxInfo['taxDeliveryFl'] = 'y';
            }
            $this->setData('taxInfo', $taxInfo);

            // 현금 영수증 사용 여부 및 필수 신청 여부
            $pgConf = gd_pgs();
            if (empty($pgConf['pgId']) === false && $pgConf['cashReceiptFl'] == 'y') {
                $receipt['cashFl'] = 'y';

                // 현금영수증 입력 정보 가져오기
                if (gd_is_login() === true) {
                    $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
                    $memNo = Session::get('member.memNo');
                    $memberCashInfo = $cashReceipt->getMemberCashReceiptInfo($memNo);
                    $memberInvoiceInfo['cash'] = $memberCashInfo;
                }
            } else {
                $receipt['cashFl'] = 'n';
            }

            // 세금계산서/현금영수증 입력 정보
            if (empty($memberInvoiceInfo) == false) {
                $this->setData('memberInvoiceInfo', $memberInvoiceInfo);
            }

            // 영수증 우선 설정 (현금영수증과 안함만 있음)
            $receipt['aboveFl'] = $pgConf['aboveFl'];

            // 영수증 신청 가능한 주문 코드
            // 프론트 주문에서는 계좌이체 가상계좌방식은 영수증 신청 불가능하게 변경
            $settleKindReceiptPossible = array_diff($order->settleKindReceiptPossible, array('pb', 'pv', 'eb', 'ev'));
            $receipt['useReceiptCode'] = json_encode($settleKindReceiptPossible);
            $this->setData('receipt', gd_isset($receipt));

            if((int)Session::get('member.memNo') > 0){
                // 기본 배송지 정보 가져오기
                $defaultShippingAddress = $order->getDefaultShippingAddress();
                $this->setData('basiceShippingAddress', $defaultShippingAddress);
                $this->setData('shippingDefaultFl', $order->getShippingDefaultFlYn());
            }

            // 페이코 버튼
            foreach ($cartInfo as $cInfo) {
                foreach ($cInfo as $cI) {
                    foreach ($cI as $tempData) {
                        $arrGoodsNo[] = $tempData['goodsNo'];
                    }
                }
            }

            $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
            $paycoEasypaybuttonImage = $payco->getButtonHtmlCode('EASYPAY', true, '', $arrGoodsNo);
            $paycoEasypaybuttonPcImage = $payco->getButtonHtmlCode('EASYPAY', false, '', $arrGoodsNo);
            $responsePaycoCheckoutbuttonImage = $payco->getButtonHtmlCode('EASYPAY', \Request::isMobileDevice(), '', $arrGoodsNo);
            $this->setData('responsePayco', gd_isset($responsePaycoCheckoutbuttonImage));  //페이코 반응형 모바일버튼
            if (($paycoEasypaybuttonImage !== false || $paycoEasypaybuttonPcImage !== false) && $onlyBankFl == 'n') {
                $this->setData('payco', $paycoEasypaybuttonImage);
                $this->setData('paycoPc', $paycoEasypaybuttonPcImage);
                $this->setData('paycoMobile', $paycoEasypaybuttonImage);

                $paycoConfig = $dbUrl->getConfig('payco', 'config');
                if ($paycoConfig['paycoFl'] == 'y' && \Session::has('trackingKey') === true) {
                    $this->setData('usePaycoShopping', 'y');
                }
            }

            // 결제가 가능한 결제 수단 표기
            $settle = $order->useSettleKind($settleSelect, $pgConf, $cart->payLimit);
            // 카카오페이가 테스트하기 또는 예외상품에 등록된 상품이라면 카카오페이 미노출
            if($settle['general']['pk']['useFl'] == 'y'){
                $kakaopayConfig = gd_policy('pg.kakaopay');
                foreach ($cartInfo as $cIKey1 => $cIval1){
                    foreach($cIval1 as $cIKey2 => $cIval2){
                        foreach($cIval2 as $cIKey3){
                            if(in_array('goods', $kakaopayConfig['presentExceptFl']) === true && in_array($cIKey3['goodsNo'], $kakaopayConfig['exceptGoods']) === true){
                                unset($settle['general']['pk']);
                            }
                            if(in_array('brand', $kakaopayConfig['presentExceptFl']) === true && in_array($cIKey3['brandCd'], $kakaopayConfig['exceptBrand']) === true){
                                unset($settle['general']['pk']);
                            }
                            foreach($cIKey3['cateAllCd'] as $cateKey1 => $cateVal1){
                                if(in_array('category', $kakaopayConfig['presentExceptFl']) === true && in_array($cateVal1['cateCd'], $kakaopayConfig['exceptCategory']) === true){
                                    unset($settle['general']['pk']);
                                }
                            }
                        }
                    }
                }
            }

            if ($onlyBankFl == 'y') {
                foreach ($settle as $k => $v) {
                    if ($k == 'general') {
                        $tempSettle[$k] = $v;
                    }
                }
                $settle = $tempSettle;
            }

            $this->setData('settle', gd_isset($settle));

            //개별결제수단이 설정되어 있는데 모두 다른경우 결제 불가
            $settleOrderPossible = true;
            if (empty($cart->payLimit) === false && in_array('false', $cart->payLimit)) {
                $settleOrderPossible = false;
            }
            $this->setData('settleOrderPossible', gd_isset($settleOrderPossible));

            // --- Template_ 호출
            $this->setData('orderName', Session::get('member.memNm')); // 주문자명 (회원 이름)

            $this->setData('orderItemMode', 'order');

            /** @var  \Bundle\Component\Agreement\BuyerInform $inform */
            $inform = \App::load('\\Component\\Agreement\\BuyerInform');
            $privateGuestOffer = $inform->getAgreementWithReplaceCode(BuyerInformCode::PRIVATE_GUEST_ORDER);
            // 이용약관
            $agreementInfo = $inform->getAgreementWithReplaceCode(BuyerInformCode::AGREEMENT);
            // [필수] 개인정보 수집.이용 동의
            $privateApproval = $inform->getAgreementWithReplaceCode(BuyerInformCode::PRIVATE_APPROVAL);
            $this->setData('privateGuestOffer', $privateGuestOffer);
            $this->setData('agreementInfo', $agreementInfo);
            $this->setData('privateApproval', $privateApproval);
            if (gd_isset($memberAccess['guestUnder14Fl'])) {
                $this->setData('guestUnder14Fl', $memberAccess['guestUnder14Fl']);
            }

            // 비회원 마케팅 활용을 위한 개인정보 수집 동의
            $privateMarketing = $inform->getInformData(BuyerInformCode::PRIVATE_MARKETING);
            $this->setData('privateMarketing', $privateMarketing);
            $privateMarketingFl = gd_isset($privateMarketing['modeFl'], 'n') == 'y';
            $this->setData('privateMarketingFl', $privateMarketingFl);

            // 공급사 상품이 포함되었는지 여부
            $scmNoList = array_keys($cartInfo);
            $hasScmGoods = false;
            $tmpScmList = [];
            $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
            foreach ($scmNoList as $scmNo) {
                if ($scmNo > 1) {
                    $hasScmGoods = true;
                    $tmpScmList[$scmNo] = $scmNo;
                }
            }
            $this->setData('hasScmGoods', $hasScmGoods);
            if ($hasScmGoods) {
                $scmNmList = [];
                $scmData = $scmAdmin->getScmSelectList(implode(INT_DIVISION,$tmpScmList));
                foreach($scmData as $val) {
                    $scmNmList[$val['scmNo']] = $val['companyNm'];
                }
                ksort($scmNmList);
                $scmNm['scmNm'] = implode(', ',$scmNmList);
                $privateProvider = $inform->getAgreementWithReplaceCode(BuyerInformCode::PRIVATE_PROVIDER);
                $replaceCode = \App::load(\Component\Design\ReplaceCode::class);
                $replaceCode->setReplaceCodeByScmAgreement($scmNm);
                $privateProvider = $replaceCode->replace($privateProvider);
                $this->setData('privateProvider', $privateProvider);
            }

            // 쇼핑몰 정보
            $mall = new Mall();
            $serviceInfo = $mall->getServiceInfo();
            $this->setData('serviceInfo', $serviceInfo);

            // 주문데이터 가져오기
            $orderCountriesCode = $order->getCountriesCode();

            // 주문자 전화용 국가코드 셀렉트 박스 데이터
            foreach ($orderCountriesCode as $key => $val) {
                if ($val['callPrefix'] > 0) {
                    $orderCountryPhone[$val['code']] = __($val['countryNameKor']) . '(+' . $val['callPrefix'] . ')';
                }
            }
            $this->setData('orderCountryPhone', $orderCountryPhone);

            // 국가데이터 가져오기
            $countriesCode = $order->getUsableCountriesList();

            // 전화용 국가코드 셀렉트 박스 데이터
            foreach ($countriesCode as $key => $val) {
                if ($val['callPrefix'] > 0) {
                    $countryPhone[$val['code']] = __($val['countryNameKor']) . '(+' . $val['callPrefix'] . ')';
                }
            }
            $this->setData('countryPhone', $countryPhone);

            // 주소용 국가코드 셀렉트 박스 데이터
            $countryAddress = [];
            foreach ($countriesCode as $key => $val) {
                $countryAddress[$val['code']] = __($val['countryNameKor']) . '(' . $val['countryName'] . ')';
            }
            $this->setData('countryAddress', $countryAddress);

            // 주소셀렉트시 자동 전화번호 선택 처리를 위한 맵핑 데이터
            $countryMapping = [];
            foreach ($countriesCode as $key => $val) {
                $countryMapping[$val['code']] = $val['callPrefix'];
            }
            $this->setData('countryMapping', json_encode($countryMapping));

            // 추천 국가 선택 (기본주소지가 있는 경우 기본 주소지로)
            if (isset($defaultShippingAddress['shippingCountryCode'])) {
                $recommendCountryCode = $defaultShippingAddress['shippingCountryCode'];
            } else {
                $recommendCountryCode = SESSION::get(SESSION_GLOBAL_MALL)['recommendCountryCode'];
            }
            $this->setData('recommendCountryCode', $recommendCountryCode);
            $pgPaycoPolicy = ComponentUtils::getPolicy('pg.payco');
            StringUtils::strIsSet($pgPaycoPolicy['useEventPopupYn'], 'n');
            StringUtils::strIsSet($pgPaycoPolicy['paycoSellerKey'], '');
            if ($pgPaycoPolicy['useEventPopupYn'] == 'y' && $pgPaycoPolicy['paycoSellerKey'] != '') {
                $userFilePathResolver = \App::getInstance('user.path');
                $this->addScript([$userFilePathResolver->data('common', 'payco', 'event', 'mobile', 'gd_payco_event_popup.js')->www()], true);
            }

            // 안심번호 사용여부
            $orderBasic = gd_policy('order.basic');
            if ($orderBasic['safeNumberServiceFl'] != 'off' && isset($orderBasic['useSafeNumberFl'])) {
                $this->setData('useSafeNumberFl', $orderBasic['useSafeNumberFl']);
            }

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

            //facebook Dynamic Ads 외부 스크립트 적용
            $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
            $currency = gd_isset(Mall::getSession('currencyConfig')['code'], 'KRW');
            $fbConfig = $facebookAd->getExtensionConfig();
            if(empty($fbConfig)===false && $fbConfig['fbUseFl'] == 'y' ) {
                // 상품번호 추출
                $goodsNo = [];
                foreach ($cartInfo as $key => $val){
                    foreach($val as $key2 => $val2){
                        foreach($val2 as $key3){
                            $goodsNo[] = $key3['goodsNo'];
                        }
                    }
                }
                $fbScript = $facebookAd->getFbInitiateCheckoutScript($goodsNo, $cart->totalSettlePrice, $currency);
                $this->setData('fbInitiateCheckoutScript', $fbScript);
            }

            // 인증설정체크해서 데이터 추가 useDataJoinFl
            $authCellPhoneConfig = gd_get_auth_cellphone_info();
            $this->setData('reflectApplyMemberUseFl', !($authCellPhoneConfig['useFl'] == 'y' && ($authCellPhoneConfig['useDataJoinFl'] == 'y' || $authCellPhoneConfig['useDataModifyFl'] == 'y' || $authCellPhoneConfig['useDataModifyFlKcp'] == 'y')));
        } // --- 오류 발생시
        catch (Exception $e) {
            // echo ($e->ectMessage);
            // 설정 오류 : 화면 출력용
            if ($e->ectName == 'ERROR_VIEW') {
                $errorMessage = $e->ectMessage;
                $item = ($e->ectMessage ? ' - ' . str_replace('\n', ' - ', gd_isset($errorMessage, $e->ectMessage)) : '');
                throw new AlertRedirectException(__('안내') . $item, null, null, '../order/cart.php', 'parent');

                // 시스템 오류 : 실패 메시지만 보여주고 자세한 내용은 log 참고
            } else {
                $e->actLog();
                // echo ($e->ectMessage);
                throw new AlertRedirectException(__('오류') . ' - ' . __('오류가 발생 하였습니다.'), null, null, '../order/cart.php', 'parent');
            }
        }
    }
}
