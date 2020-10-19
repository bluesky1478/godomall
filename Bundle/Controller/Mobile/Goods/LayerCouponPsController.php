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

namespace Bundle\Controller\Mobile\Goods;

use App;
use Session;
use Request;
use Exception;

/**
 * Class LayerShippingPsController
 *
 * @package Bundle\Controller\Mobile\Order
 * @author  su
 */
class LayerCouponPsController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            if (!Request::isAjax()) {
                throw new Exception(__('비동기 통신 전용 페이지 입니다.'));
            }
            // 로그인 체크
            if(Session::has('member')) {
                $post = Request::post()->toArray();
                switch($post['mode']) {
                    // 쿠폰 다운
                    case 'coupon_down':
                    case 'coupon_down_all':
                        // --- 쿠폰 class
                        $coupon = \App::load('\\Component\\Coupon\\Coupon');
                        $coupon->setGoodsCouponMemberSave(Request::post()->get('couponNo'), Request::post()->get('goodsNo'),Session::get('member.memNo'),Session::get('member.groupSno'));
                        // 상품의 다운받을 수 있는 쿠폰의 개수
                        $goodsCouponCnt = $coupon->getGoodsCouponDownListCount(null, $post['goodsNo'], Session::get('member.memNo'), Session::get('member.groupSno'));

                        $this->json([
                            'code' => 200,
                            'message' => __('쿠폰이 발급되었습니다.'),
                            'goodsCouponCnt' => $goodsCouponCnt,
                        ]);

                        break;

                    // 삭제 액션
                    case 'shipping_delete':
                        if (!Request::post()->has('sno')) {
                            throw new Exception(__('배송지 관리 번호를 입력하세요.'));
                        }

                        $order = App::load(\Component\Order\Order::class);
                        $order->deleteShippingAddress(Request::post()->get('sno'));

                        $this->json([
                            'code' => 200,
                            'message' => __('배송지가 정상적으로 삭제되었습니다.'),
                        ]);

                        break;
                }
            } else {
                $this->json([
                    'error' => 10,
                    'message' => __('로그인하셔야 해당 서비스를 이용하실 수 있습니다.'),
                ]);
            }
        } catch (Exception $e) {
            $this->json([
                'code' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
