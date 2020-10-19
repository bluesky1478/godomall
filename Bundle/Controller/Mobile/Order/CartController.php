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

namespace Bundle\Controller\Mobile\Order;

use Component\CartRemind\CartRemind;
use Component\Naver\NaverPay;
use Component\Cart\Cart;
use Component\Member\Member;
use Component\Mall\Mall;
use Framework\Debug\Exception\AlertRedirectException;
use Globals;
use Session;
use Response;
use Request;
use Password;

/**
 * 장바구니
 *
 * @author Ahn Jong-tae <qnibus@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CartController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            $session = \App::getInstance('session');
            $session->del('related_goods_order');

            // 모듈 설정
            $cart = \App::Load(\Component\Cart\Cart::class);
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $paycoConfig = $dbUrl->getConfig('payco', 'config');
            if ($paycoConfig['paycoFl'] != 'y' && \Session::has('trackingKey') === true) {
                \Session::del('trackingKey');
            }

            // 기존 바로 구매 상품 삭제
            $cart->setDeleteDirectCartCont();

            if (Request::get()->get('cr') > 0) {
                $cartRemind = new CartRemind();
                // 장바구니 알림으로 접속 시 장바구니 알림 세션 생성
                $cartRemind->setCartRemindSession(Request::get()->get('cr'));
                //장바구니 알림 접속 카운트 증가
                $cartRemind->setCartRemindConnectCount();
            }

            // 장바구니 정보
            $cartInfo = $cart->getCartGoodsData(null, null, null, false, true);
            $this->setData('cartInfo', $cartInfo);

            //facebook Dynamic Ads 외부 스크립트 적용
            $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
            $currency = gd_isset(Mall::getSession('currencyConfig')['code'], 'KRW');
            // 상품번호 추출
            $goodsNo = [];
            foreach ($cartInfo as $key => $val){
                foreach($val as $key2 => $val2){
                    foreach($val2 as $key3=>$val3){
                        $goodsNo[] = $val3['goodsNo'];
                    }
                }
            }
            $fbScript = $facebookAd->getFbCartScript($goodsNo, $cart->totalGoodsPrice, $currency);
            $this->setData('fbCartScript', $fbScript);


            // 쿠폰 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            $this->setData('couponConfig', gd_isset($couponConfig)); // 쿠폰 설정
            $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부

            // 결제가능한 수단이 무통장인경우 간편결제(payco/naverpay)미노출처리
            if (count($cart->payLimit) == 1 && $cart->payLimit[0] == 'gb') {
                $onlyBankFl = 'y';
            } else {
                $onlyBankFl = 'n';
            }

            // 네이버 체크아웃 버튼
            $naverPay = new NaverPay();
            $naverPayBtnImage = $naverPay->getNaverPayCart($cartInfo, true);
            $naverPayPcBtnImage = $naverPay->getNaverPayCart($cartInfo);
            $responseNaverPay = $naverPay->getNaverPayCart($cartInfo, \Request::isMobileDevice());
            if ($onlyBankFl == 'n') {
                $this->setData('naverPay', gd_isset($naverPayBtnImage));
                $this->setData('naverPayMobile', gd_isset($naverPayBtnImage));
                $this->setData('naverPayPc', gd_isset($naverPayPcBtnImage));
                $this->setData('responseNaverPay', gd_isset($responseNaverPay));
            }

            // 페이코 버튼
            $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
            $paycoCheckoutbuttonImage = $payco->getButtonHtmlCode('CHECKOUT', true, 'goodsCart');
            $paycoCheckoutbuttonPcImage = $payco->getButtonHtmlCode('CHECKOUT', false, 'goodsCart');
            $responsePaycoCheckoutbuttonImage = $payco->getButtonHtmlCode('CHECKOUT', \Request::isMobileDevice(), 'goodsCart');
            $this->setData('responsePayco', gd_isset($responsePaycoCheckoutbuttonImage));  //페이코 반응형 모바일버튼
            if (($paycoCheckoutbuttonImage !== false || $paycoCheckoutbuttonPcImage !== false) && $onlyBankFl == 'n') {
                $this->setData('payco', gd_isset($paycoCheckoutbuttonImage));
                $this->setData('paycoPc', gd_isset($paycoCheckoutbuttonPcImage));
                $this->setData('paycoMobile', gd_isset($paycoCheckoutbuttonImage));
            }

            // 다른 고객님들이 함께 구매한 상품
            $goodsData = $cart->getOrderGoodsWithOtherUser();
            if (empty($goodsData) === false) {
                $goodsData = array_chunk($goodsData,'5');
                $widgetTheme = array(
                    'lineCnt'=> '5',
                    'iconFl'=> 'y',
                    'soldOutIconFl'=> 'y',
                    'displayType'=>'04',
                    'displayField'=> array('brandCd','makerNm','goodsNm','fixedPrice','goodsPrice','coupon','mileage','shortDescription')
                );
                $this->setData('widgetGoodsList', gd_isset($goodsData));
                $this->setData('widgetTheme', gd_isset($widgetTheme));
            }

            // 장바구니 모드
            $this->setData('orderItemMode', 'cart');

            // 마일리지 지급 정보
            $this->setData('mileage', $cart->mileageGiveInfo['info']);

            // 세금계산서 이용안내
            $taxInfo = gd_policy('order.taxInvoice');
            if (gd_isset($taxInfo['taxInvoiceUseFl']) == 'y') {
                $taxInvoiceInfo = gd_policy('order.taxInvoiceInfo');
                if ($taxInfo['taxinvoiceInfoUseFl'] == 'y') {
                    $this->setData('taxinvoiceInfo', nl2br($taxInvoiceInfo['taxinvoiceInfo']));
                }
            }

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

            // 장바구니 객체에서 계산된 상품정보
            $this->setData('cartCnt', $cart->cartCnt); // 장바구니 수량
            $this->setData('shoppingUrl', $cart->shoppingUrl); // 쇼핑 계속하기 URL
            $this->setData('cartScmInfo', $cart->cartScmInfo); // 장바구니 SCM 정보
            $this->setData('cartScmCnt', $cart->cartScmCnt); // 장바구니 SCM 수량
            $this->setData('cartScmGoodsCnt', $cart->cartScmGoodsCnt); // 장바구니 SCM 상품 갯수
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
            $this->setData('orderPossibleMessage', $cart->orderPossibleMessage); // 주문 불가 사유
            $this->setData('setDeliveryInfo', $cart->setDeliveryInfo); // 배송비조건별 배송 정보

        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
