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

namespace Bundle\Controller\Mobile\Event;


use App;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Component\Coupon\Coupon;
/**
 * Class AttendStampController
 * @package Bundle\Controller\Front\Event
 * @author  yjwee
 */
class CouponzoneController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /** @var \Bundle\Controller\Front\Event\couponzoneController $front */
        $front = App::load('\\Controller\\Front\\Event\\CouponzoneController');
        $front->index();
        $this->setData($front->getData());
    }
}
