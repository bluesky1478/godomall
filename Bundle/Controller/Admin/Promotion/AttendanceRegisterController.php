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
use Component\Attendance\Mode;
use Framework\Debug\Exception\AlertBackException;
use Framework\Object\SimpleStorage;
use Request;

/**
 * Class AttendanceRegisterController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class AttendanceRegisterController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        $this->callMenu('promotion', 'attendance', 'register');

        /** @var \Bundle\Component\Attendance\Attendance $attendance */
        $attendance = App::load('\\Component\\Attendance\\Attendance');
        $attendance->setRequestStorage([]);
        if ($attendance->hasLimitlessEvent(null, '9999-12-31')) {
            throw new AlertBackException(__('종료기간 제한없음이 설정된 출석체크가 있습니다. 출석체크 진행 기간은 중복되지 않습니다.'));
        }

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

        $storage = new SimpleStorage();

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

        $this->setData('mode', AttendancePsController::MODE_INSERT);
        $this->setData('data', $storage);
        $this->setData('checked', $checked);
        $this->setData('couponData', $couponData);
    }
}
