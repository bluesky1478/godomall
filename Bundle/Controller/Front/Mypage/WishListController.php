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

use Component\Cart\Cart;
use Component\Member\Member;
use Component\Naver\NaverCheckout;
use Framework\Debug\Exception\AlertBackException;
use Component\Mall\Mall;
use Session;
use Response;
use Request;

/**
 * 관련상품
 *
 * @author Ahn Jong-tae <qnibus@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class WishListController extends \Controller\Front\Controller
{
	/**
	 * @inheritdoc
	 */
	public function index()
	{
		try {

			if(Session::has('member')) {
			// 모듈 설정
			$wish = \App::load('\\Component\\Wish\\Wish');
			$cartInfo = gd_policy('order.cart');

			// 장바구니 정보
			$wishInfo	= $wish->getWishGoodsData();
			$this->setData('wishInfo', $wishInfo);
			$this->setData('moveWishPageFl', $cartInfo['moveWishPageFl']);

			//facebook Dynamic Ads 외부 스크립트 적용
            $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
            $currency = gd_isset(Mall::getSession('currencyConfig')['code'], 'KRW');
            $fbConfig = $facebookAd->getExtensionConfig();

            if(empty($fbConfig)===false && $fbConfig['fbUseFl'] == 'y') {
                // 상품번호 추출
                $goodsNo = [];
                foreach ($wishInfo as $key => $val){
                    foreach($val as $key2 => $val2){
                        foreach($val2 as $key3){
                            $goodsNo[] = $key3['goodsNo'];
                        }
                    }
                }
                $fbScript = $facebookAd->getFbWishListScript($goodsNo, $currency);
                $this->setData('fbWishListScript', $fbScript);
            }

			// 마일리지 지급 정보
			$this->setData('mileage', gd_mileage_give_info());

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

			} else {
				throw new AlertBackException(__('로그인하셔야 해당 서비스를 이용하실 수 있습니다.'));
			}

		} catch (\Exception $e) {
			throw $e;
		}
	}
}


