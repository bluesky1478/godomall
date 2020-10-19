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
namespace Bundle\Controller\Admin\Base;

use Component\Admin\AdminMain;
use Component\Admin\AdminMenu;
use Component\Policy\MainSettingPolicy;
use Component\MemberStatistics\MemberStatistics;
use Component\Order\OrderSalesStatistics;
use Component\VisitStatistics\VisitStatistics;
use Component\Member\Manager;
use Framework\Utility\NumberUtils;
use Request;
use DateTime;
use Session;

/**
 * 관리자 메인 통계 페이지
 *
 */
class MainStatisticsController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 메인 통계의 권한 체크
        $adminMenu = new AdminMenu();
        $sessionByManager = Session::get(Manager::SESSION_MANAGER_LOGIN);
        $adminMenu->setAccessMenu($sessionByManager['sno']);
        if (method_exists($adminMenu, 'getMainStatisticsFunctionAuthMenu') && $this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
            // 개편 후 : 관리자 기본 메뉴의 기능권한 검증 (통계 권한 분리)
            $mainStatisticsAccess = $adminMenu->getMainStatisticsFunctionAuthMenu(Manager::isProvider());
        } else {
            // 개편 전 : 통계 권한 검증
            $mainStatisticsAccess = $adminMenu->getMainStatisticsAccessMenu(Manager::isProvider());
        }

        $icon['sales'] = 'off';
        $icon['order'] = 'off';
        $icon['visit'] = 'off';
        $icon['member'] = 'off';
        if (Manager::isProvider()) {
            if ($mainStatisticsAccess['sales'] > 0) {
                $active['sales'] = 'active';
                $tab['sales'] = 'in active';
                $icon['sales'] = 'on';
            } else if ($mainStatisticsAccess['order'] > 0) {
                $active['order'] = 'active';
                $tab['order'] = 'in active';
                $icon['order'] = 'on';
            }
        } else {
            if ($mainStatisticsAccess['sales'] > 0) {
                $active['sales'] = 'active';
                $tab['sales'] = 'in active';
                $icon['sales'] = 'on';
            } else if ($mainStatisticsAccess['order'] > 0) {
                $active['order'] = 'active';
                $tab['order'] = 'in active';
                $icon['order'] = 'on';
            } else if ($mainStatisticsAccess['visit'] > 0) {
                $active['visit'] = 'active';
                $tab['visit'] = 'in active';
                $icon['visit'] = 'on';
            } else if ($mainStatisticsAccess['member'] > 0) {
                $active['member'] = 'active';
                $tab['member'] = 'in active';
                $icon['member'] = 'on';
            }
        }
        $this->setData('active', $active);
        $this->setData('tab', $tab);
        $this->setData('icon', $icon);
        $this->setData('mainStatisticsAccess', $mainStatisticsAccess);

        // 검색 기간 설정일
        $mainSettingPolicy = new MainSettingPolicy();
        $searchPeriod = $mainSettingPolicy->getPresentation($sessionByManager['sno']);
        $this->setData('searchPeriod', $searchPeriod);
        $searchPeriod = $searchPeriod - 1; // 오늘 포함하여 처리하므로 -1일 처리
        if ($searchPeriod > 0) {
            $modifyDate = '-' . $searchPeriod . ' days';
        } else {
            $modifyDate = $searchPeriod . ' days';
        }
        $todayDate = new DateTime();
        $startDate = $todayDate->modify($modifyDate);
        $endDate = new DateTime();
        $searchDate[0] = $startDate->format('Ymd');
        $searchDate[1] = $endDate->format('Ymd');

        // 통계 검색 조건
        $statisticsParam['orderYMD'][0] = $searchDate[0];
        $statisticsParam['orderYMD'][1] = $searchDate[1];
        $statisticsParam['mallSno'] = 'all';
        $statisticsParam['scmNo'] = $sessionByManager['scmNo'];

        $adminMain = new AdminMain();
        $tabStatistics = $adminMain->getTabStatisticsData($statisticsParam);
        $this->setData('tabStatistics', $tabStatistics);

        // 마일리지 사용 여부
        $mileageBasic = gd_policy('member.mileageBasic');
        $mileageUseFl = $mileageBasic['payUsableFl'];
        $this->setData('mileageUseFl', $mileageUseFl);

        // 예치금 사용 여부
        $depositBasic = gd_policy('member.depositConfig');
        $depositUseFl = $depositBasic['payUsableFl'];
        $this->setData('depositUseFl', $depositUseFl);


        $this->getView()->setDefine('layout', 'layout_blank_noiframe.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->addScript(
            [
                'tui/code-snippet.min.js',
                'raphael/effects.min.js',
                'raphael/raphael-min.js',
                'tui.chart-master/chart.min.js',
                'main_presentation.js',
            ]
        );
        $this->addCss(
            [
                'chart.css',
            ]
        );
    }
}
