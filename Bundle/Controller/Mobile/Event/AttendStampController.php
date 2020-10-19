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


use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;

/**
 * Class AttendStampController
 * @package Bundle\Controller\Mobile\Event
 * @author  yjwee
 */
class AttendStampController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        try {
            $sno = $request->get()->get('sno', '');
            $isPreview = $request->get()->get('preview', false);

            /** @var \Bundle\Component\Attendance\Attendance $attendance */
            $attendance = \App::load('\\Component\\Attendance\\Attendance');
            if ($request->get()->has('sno')) {
                $attendanceStorage = $attendance->getAttendance($sno);
            } else {
                $attendanceStorage = $attendance->getDataByActive('stamp');
            }

            if (!$isPreview) {
                if (!$attendanceStorage->has('startDt')) {
                    throw new AlertBackException(__('이벤트를 찾을수 없습니다.'));
                }
                if (!$attendance->isActiveEvent()) {
                    throw new AlertBackException(__('진행 중인 이벤트가 아닙니다.'));
                }
                $attendance->checkDevice($attendanceStorage->get('deviceFl'));
            }

            $currentDate = new \DateTime();
            if ($request->get()->has('currentDate')) {
                $currentDate = \DateTime::createFromFormat('Y-m-d', $request->get()->get('currentDate') . '-01');
                if ($request->get()->get('move', '') == 'prev') {
                    $currentDate->modify('first day of -1 month');
                } else if ($request->get()->get('move', '') == 'next') {
                    $currentDate->modify('first day of +1 month');
                }
            }

            if (!$attendance->isActiveEvent(null, null, $currentDate->format('Y-m'), 'Y-m')) {
                throw new AlertBackException(__('이벤트 진행 기간 내의 참석여부만 조회 가능합니다.'));
            }

            /** @var \Bundle\Component\Attendance\AttendanceCheck $check */
            $check = \App::load('\\Component\\Attendance\\AttendanceCheck');
            $checkStorage = $check->getAttendanceCheck($sno, $session->get('member.memNo'));
            if ($attendanceStorage->get('conditionFl', '') == 'continue') {
                $checkStorage->set('attendanceCount', $check->getContinueCount());
            }

            /** @var \Bundle\Component\Attendance\Stamp $stamp */
            $stamp = \App::load('\\Component\\Attendance\\Stamp');
            $stamp->setHistory($checkStorage->get('attendanceHistory'));
            $stamp->setStampDate($currentDate);
            $stamp->makeStamp();

            $this->setData('gPageName', $attendanceStorage->get('title'));
            $this->setData('currentDate', $currentDate->format('Y-m'));
            $this->setData('arrStamp', $stamp->getArrStamp());
            $this->setData('attendData', $attendanceStorage->all());
            $this->setData('checkData', $checkStorage->all());
            $this->setData('mode', $checkStorage->has('sno') ? AttendancePsController::UPDATE_STAMP : AttendancePsController::INSERT_STAMP);
            $this->setData('checkSno', $checkStorage->get('sno', ''));
            $this->setData('uploadPath', \UserFilePath::data('attendance', 'upload')->www());
        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
