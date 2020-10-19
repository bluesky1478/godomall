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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Framework\Debug\Exception\LayerException;
use Globals;
use Request;
use Component\Member\Manager;
use Session;

class ScmCommissionListController extends \Controller\Admin\Controller
{
    /**
     * 공급사 리스트
     * [관리자 모드] 공급사 리스트
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        // 공급사 정보 설정
        $isProvider = Manager::isProvider();
        $this->setData('isProvider', $isProvider);

        // --- 메뉴 설정
        if($isProvider) { // 공급사인 경우
            $getValue['mode'] = 'schedule';
            $getValue['scmNo'] = Session::get('manager.scmNo');
            $this->callMenu('policy', 'basic', 'scmCommissionList');
        } else { // 본사
            $this->callMenu('scm', 'scm', 'scmCommissionList');
        }

        // --- 모듈 호출
        try {
            if(empty($getValue['mode']) === true) {
                $getValue['mode'] = 'calendar';
            }
            $scmAdmin = \App::load(\Component\Scm\ScmCommission::class);
            
            // 캘린더 데이터 + DB 조회 데이터
            $calendarData = $scmAdmin->scmCommissionScheduleCalendar($getValue);

            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('search', gd_isset($calendarData['search']));
        $this->setData('page', $page);

        $this->setData('calendarData', gd_isset($calendarData));

        $this->getView()->setDefine('layoutScmCommissionCalendar', 'share/layout_scm_commission_calendar.php');
        $this->getView()->setDefine('layoutScmCommissionSchedule', 'share/layout_scm_commission_schedule.php');

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('scm/scm_commission_list.php');
    }
}
