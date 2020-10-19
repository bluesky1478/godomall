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

namespace Bundle\Controller\Front\Event;


use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Component\Coupon\Coupon;
/**
 * Class AttendStampController
 * @package Bundle\Controller\Front\Event
 * @author  yjwee
 */
class CouponzoneController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            $couponConfig = gd_policy('coupon.couponzone');
            $couponConfig['useFl'] = gd_isset($couponConfig['useFl'], 'n');
            if($couponConfig['useFl'] == 'n') {
                throw new AlertBackException(__('접근이 불가한 페이지입니다.'));
            }
            $coupon = new Coupon();
            $couponList = $coupon->getCouponzoneList();

            $this->setData('couponList', $couponList);

            if($couponConfig['couponImageType'] == 'basic') {
                $couponConfig['pcCouponImagePath'] = PATH_ADMIN_GD_SHARE . 'img/defaultCouponzonePc.png';
                $couponConfig['mobileCouponImagePath'] = PATH_ADMIN_GD_SHARE . 'img/defaultCouponzoneMobile.png';
            } else {
                if ($couponConfig['pcCouponImage']) $couponConfig['pcCouponImagePath'] = $coupon->getCouponImageData($couponConfig['pcCouponImage']);
                if ($couponConfig['mobileCouponImage']) $couponConfig['mobileCouponImagePath'] = $coupon->getCouponImageData($couponConfig['mobileCouponImage']);
            }
            $couponConfig['pcContents'] = gd_htmlspecialchars_decode($couponConfig['pcContents']);
            $couponConfig['pcContents'] = str_replace('<p>&nbsp;</p>', '', $couponConfig['pcContents']);
            if($couponConfig['descriptionSameFl'] == 'y') { // 모바일 동일 사용
                $couponConfig['mobileContents'] = $couponConfig['pcContents'];
            } else {
                $couponConfig['mobileContents'] = gd_htmlspecialchars_decode($couponConfig['mobileContents']);
                $couponConfig['mobileContents'] = str_replace('<p>&nbsp;</p>', '', $couponConfig['mobileContents']);
            }

            $this->setData('couponConfig', $couponConfig);
            //$this->getView()->setDefine('mypage', 'outline/side/mypage.html');
        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
