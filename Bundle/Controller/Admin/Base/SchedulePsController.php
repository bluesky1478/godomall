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
namespace Bundle\Controller\Admin\Base;

use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\HttpException;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\StaticProxy\Proxy\Session;
use Message;
use Globals;
use Request;

/**
 * 스케줄(일정관리) 처리
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SchedulePsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws LayerException
     * @throws HttpException
     */
    public function index()
    {
        $postData = Request::post()->toArray();
        try {
            // schedule 정의
            $schedule = \App::load('\\Component\\Admin\\Schedule');

            switch (Request::request()->get('mode')) {
                // 스케줄있는 월별 일자
                case 'getExistDay':
                    $getData = Request::get()->toArray();
                    $result = $schedule->getExistDay($getData);
                    echo json_encode($result);
                    break;

                // 스케줄정보
                case 'getDayContents':
                    $getData = Request::get()->toArray();
                    $result = $schedule->getDayContents(gd_isset($getData['scdDt']));
                    echo json_encode($result);
                    break;

                // 스케쥴 등록
                case 'register':
                    try {
                        substr($postData['scdDt'], -2);
                        $schedule->insertScheduleData($postData);
                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (\Exception $e) {
                        throw new LayerException($e->getMessage());
                    }
                    break;

                // 스케쥴 수정
                case 'modify':
                    try {
                        $day = substr($postData['scdDt'], -2);
                        $schedule->modifyScheduleData($postData);
                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (\Exception $e) {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                    }
                    break;

                // 스케쥴 삭제
                case 'delete':
                    $getData = Request::get()->toArray();
                    $schedule->deleteScheduleData(gd_isset($getData['sno']));
                    break;

                // 알람설정
                case 'setAlarm':
                    try {
                        $policy = \App::load('\\Component\\Policy\\Policy');
                        $policy->saveSchedule($postData);
                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (\Exception $e) {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                    }
                    break;

                // 알람 팝업창 여부
                case 'isAlarmPopup':
                    $result = $schedule->isAlarmPopup();
                    echo json_encode($result);
                    break;

                // 스케줄 등록&추가
                case 'add' :
                    try {
                        // 주요일정 권한 체크
                        $adminMenu = \App::load('Component\\Admin\\AdminMenu');
                        if (method_exists($adminMenu, 'setAccessWriteEnabledMenu') && method_exists($adminMenu, 'getAccessMenuStatus')) {
                            $adminMenu->setAccessWriteEnabledMenu(Session::get('manager.sno'));
                            $mainServiceCalendarAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceCalendar', gd_is_provider());
                            if ($mainServiceCalendarAccess !== 'writable') $this->layerNotReload(__('주요일정의 쓰기 권한이 없습니다.'));
                        }

                        $schedule->addScheduleData(Session::get('manager.sno'), $postData);
                        $this->layer(__('저장이 완료되었습니다.'));

                    } catch (\Exception $e) {
                        throw new LayerNotReloadException($e->getMessage());
                    }
                break;
            }
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), 500);
        }

        exit();
    }
}
