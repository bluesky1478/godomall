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

/**
 * Class AttendReplyController
 * @package Bundle\Controller\Front\Event
 * @author  yjwee
 */
class AttendReplyController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $sno = \Request::get()->get('sno', '');
            $isPreview = \Request::get()->get('preview', false);

            /** @var \Bundle\Component\Attendance\Attendance $attendance */
            $attendance = \App::load('\\Component\\Attendance\\Attendance');
            if (\Request::get()->has('sno')) {
                $attendanceStorage = $attendance->getAttendance($sno);
            } else {
                $attendanceStorage = $attendance->getDataByActive('reply');
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

            /** @var \Bundle\Component\Attendance\AttendanceCheck $check */
            $check = \App::load('\\Component\\Attendance\\AttendanceCheck');
            $checkStorage = $check->getAttendanceCheck($sno, \Session::get('member.memNo'));
            if ($attendanceStorage->get('conditionFl', '') == 'continue') {
                $checkStorage->set('attendanceCount', $check->getContinueCount());
            }

            $page = \Request::get()->get('page', 1);
            $pageNum = 15;

            /** @var \Bundle\Component\Attendance\AttendanceReply $comment */
            $comment = \App::load('\\Component\\Attendance\\AttendanceReply');
            $commentLists = $comment->lists(\Request::get()->all(), $page, $pageNum);

            /** @var \Bundle\Component\Page\Page $pageObject */
            $pageObject = \App::load('Component\\Page\\Page', $page, $comment->getSearchCount(), $comment->getCountByLists(), $pageNum);
            $pageObject->setPage();
            $pageObject->setUrl(\Request::getQueryString());

            $this->setData('attendData', $attendanceStorage->all());
            $this->setData('checkData', $checkStorage->all());
            $this->setData('commentLists', $commentLists);
            $this->setData('page', $pageObject);
            $this->setData('mode', AttendancePsController::INSERT_REPLY);
            $this->setData('sno', $sno);
            $this->setData('checkSno', $checkStorage->get('sno', ''));

        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
