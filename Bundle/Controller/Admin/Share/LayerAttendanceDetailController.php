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

namespace Bundle\Controller\Admin\Share;

use App;
use Component\Attendance\Attendance;
use Component\Attendance\AttendanceCheck;
use Component\Page\Page;
use Controller\Admin\Promotion\AttendancePsController;
use Framework\Debug\Exception\AlertCloseException;
use Request;

/**
 * Class LayerAttendanceDetailController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class LayerAttendanceDetailController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $sno = Request::get()->get('sno');
        if (Request::get()->get('sno', '') == '') {
            throw new AlertCloseException(__('선택된 이벤트번호가 없습니다.'));
        }

        // 쿠폰 정보 설정
        Request::get()->set('couponUseType', 'product');
        Request::get()->set('couponSaveType', 'auto');
        Request::get()->set('couponEventType', 'attend');
        /** @var \Bundle\Component\Coupon\CouponAdmin $couponAdmin */
        $couponAdmin = App::load('\\Component\\Coupon\\CouponAdmin');
        $couponAdminList = $couponAdmin->getCouponAdminList();
        $couponData = [];
        foreach ($couponAdminList['data'] as $index => $item) {
            $couponData[$item['couponNo']] = $item;
        }
        unset($couponAdminList);

        /**
         * 페이지 데이터 설정
         */
        $page = Request::get()->get('page', 1);
        $pageNum = Request::get()->get('pageNum', 5);

        /** @var \Bundle\Component\Attendance\Attendance $attendance */
        $attendance = new Attendance();
        $attendanceStorage = $attendance->getAttendance($sno);
        /** @var \Bundle\Component\Attendance\AttendanceCheck $check */
        $check = new AttendanceCheck();
        $lists = $check->lists(Request::get()->all(), $page, $pageNum);
        $memberCouponInfo = $check->getAttendanceMemberCoupon($lists);
        $couponAmountInfo = $memberCouponInfo['couponAmountInfo'];
        unset($memberCouponInfo['couponAmountInfo']);
        unset($memberCouponInfo['couponInfo']);
        /**
         * set page number
         * @var \Bundle\Component\Page\Page $pageObject
         */
        $foundRows = $check->getSearchCount();
        $count = $check->getCount(DB_ATTENDANCE_CHECK, '1', ' WHERE attendanceSno=' . $sno);
        $pageObject = new Page($page, $foundRows, $count, $pageNum);
        $pageObject->setPage();
        $pageObject->setUrl(Request::getQueryString());

        $checked['rangeDtPeriod'][Request::get()->get('rangeDtPeriod', '-1')] =
        $checked['targetFl'][Request::get()->get('targetFl', 'select')] =
        $checked['benefitFl'][Request::get()->get('benefitFl', 'mileage')] =
        $checked['benefitDtFl'][Request::get()->get('benefitDtFl', '')] =
        $checked['conditionDtFl'][Request::get()->get('conditionDtFl', '')] = 'checked="checked"';

        $this->setData('layerPage', $pageObject);
        $this->setData('lists', $lists);
        $this->setData('attendance', $attendanceStorage);
        $this->setData('combineSearch', $check::COMBINE_SEARCH);
        $this->setData('rangeSearch', $check::RANGE_SEARCH);
        $this->setData('checked', $checked);
        $this->setData('couponData', $couponData);
        $this->setData('benefitGiveMode', AttendancePsController::MODE_INSERT_BENEFIT);
        $this->setData('memberCouponInfo', json_encode($memberCouponInfo));
        $this->setData('couponAmountInfo', json_encode($couponAmountInfo));
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
