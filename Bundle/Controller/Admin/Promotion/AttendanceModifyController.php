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

namespace Bundle\Controller\Admin\Promotion;


use App;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class AttendanceModifyController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class AttendanceModifyController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */

        try {
            $sno = Request::get()->get('sno', '');
            if ($sno == '') {
                throw new Exception(__('출석체크 수정 정보가 없습니다.'));
            }

            $this->callMenu('promotion', 'attendance', 'modify');

            Request::get()->set('couponSaveType', 'auto');
            Request::get()->set('couponEventType', 'attend');
            /** @var \Bundle\Component\Coupon\CouponAdmin $couponAdmin */
            $couponAdmin = App::load('\\Component\\Coupon\\CouponAdmin');
            $couponAdminList = $couponAdmin->getCouponAdminList();
            $couponData = [];
            foreach ($couponAdminList['data'] as $index => $item) {
                $couponData[$item['couponNo']] = $item;
            }
            Request::get()->clear();
            unset($couponAdminList);

            /** @var \Bundle\Component\Attendance\Attendance $attendance */
            $attendance = App::load('\\Component\\Attendance\\Attendance');
            $storage = $attendance->getAttendance($sno);
            /** @var \Bundle\Component\Attendance\AttendanceCheck $check */
            $check = App::load('\\Component\\Attendance\\AttendanceCheck');
            $attendanceCount = $check->getCount(DB_ATTENDANCE_CHECK, '1', ' WHERE attendanceSno=' . $sno);


            /** set checkbox, select property */
            $checked['eventEndDtFl'][$storage->get('eventEndDtFl', 'n')] =
            $checked['deviceFl'][$storage->get('deviceFl', 'pc')] =
            $checked['groupFl'][$storage->get('groupFl', 'all')] =
            $checked['methodFl'][$storage->get('methodFl', 'stamp')] =
            $checked['conditionFl'][$storage->get('conditionFl', 'sum')] =
            $checked['benefitGiveFl'][$storage->get('benefitGiveFl', 'auto')] =
            $checked['benefitFl'][$storage->get('benefitFl', 'mileage')] =
            $checked['designHeadFl'][$storage->get('designHeadFl', 'default')] =
            $checked['designBodyFl'][$storage->get('designBodyFl', 'stamp1')] =
            $checked['stampFl'][$storage->get('stampFl', 'default')] =
            $checked['deviceFl'][$storage->get('deviceFl', 'pc')] = 'checked="checked"';
            $disabledModify = ($attendanceCount > 0) ? 'disabled="disabled"' : '';

            $this->setData('hasWaitEvent', $attendance->hasWaitEvent());
            $this->setData('mode', AttendancePsController::MODE_MODIFY);
            $this->setData('sno', $sno);
            $this->setData('data', $storage);
            $this->setData('checked', $checked);
            $this->setData('couponData', $couponData);
            $this->setData('disabledModify', $disabledModify);
            $this->setData('benefitGiveFl', $attendance->getBenefitGiveFl());

            $this->getView()->setPageName('promotion/attendance_register.php');
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
