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

use Component\Member\Manager;
use Core\Base\View\Alert;
use Framework\Utility\StringUtils;

/**
 * 관리자 메인 페이지
 *
 * @author yjwee <yeongjong.wee@godo.co.kr>
 * @author Jont-tae Ahn <qnibus@godo.co.kr>
 * @author Lee Namju <lnjts@godo.co.kr>
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class IndexController extends \Controller\Admin\Controller
{
    const CACHE_USE = true;
    const CACHE_EXPIRE = 60 * 10;

    public function index()
    {
        $globals = \App::getInstance('globals');
        $session = \App::getInstance('session');
        $adminMain = \App::load('Component\\Admin\\AdminMain');
        $mainSettingPolicy = \App::load('Component\\Policy\\MainSettingPolicy');
        $sessionByManager = $session->get(Manager::SESSION_MANAGER_LOGIN);

        // 메인 통계의 권한 체크
        $adminMenu = \App::load('Component\\Admin\\AdminMenu');
        if ($this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
            $adminMenu->setAccessWriteEnabledMenu($sessionByManager['sno']);
        } else {
            $adminMenu->setAccessMenu($sessionByManager['sno']);
        }
        if (method_exists($adminMenu, 'getMainStatisticsFunctionAuthMenu') && $this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
            // 개편 후 : 관리자 기본 메뉴의 기능권한 검증 (통계 권한 분리)
            $mainStatisticsAccess = $adminMenu->getMainStatisticsFunctionAuthMenu(Manager::isProvider());
        } else {
            // 개편 전 : 통계 권한 검증
            $mainStatisticsAccess = $adminMenu->getMainStatisticsAccessMenu(Manager::isProvider());
        }
        $this->setData('mainStatisticsAccess', $mainStatisticsAccess);

        // 주문관리, 문의/답변관리, 관리메모, 주요현황, 주요일정 권한 체크
        $this->setData('mainServiceAccess', true);
        if ($this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
            $mainServiceOrderAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceOrder', Manager::isProvider()); // 주문관리
            $this->setData('mainServiceOrderAccess', $mainServiceOrderAccess);
            $mainServiceBoardAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceBoard', Manager::isProvider()); // 문의/답변관리
            $this->setData('mainServiceBoardAccess', $mainServiceBoardAccess);
            $mainServiceMemoAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceMemo', Manager::isProvider()); // 관리메모
            $this->setData('mainServiceMemoAccess', $mainServiceMemoAccess);
            $mainServicePresentationAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServicePresentation', Manager::isProvider()); // 주요현황
            $this->setData('mainServicePresentationAccess', $mainServicePresentationAccess);
            $mainServiceCalendarAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceCalendar', Manager::isProvider()); // 주요일정
            $this->setData('mainServiceCalendarAccess', $mainServiceCalendarAccess);
        } else {
            $this->setData('mainServiceOrderAccess', 'writable');
            $this->setData('mainServiceBoardAccess', 'writable');
            $this->setData('mainServiceMemoAccess', 'writable');
            $this->setData('mainServicePresentationAccess', 'writable');
            $this->setData('mainServiceCalendarAccess', 'writable');
        }

        $this->addScript(
            [
                'gd_board_common.js',
                'jquery/jquery.multi_select_box.js',
            ]
        );

        // --- 라이선스 관련 메시지 출력
        if (empty($globals->get('gLicense.licenseMessage')) === false) {
            $alert = new Alert();
            $alert->addMessage($globals->get('gLicense.licenseMessage'));
            $alert->display();
        }

        $order = \App::load('\\Component\\Order\\OrderStatics');

        // 진행 중인 주문
        $orderMainPolicy = $mainSettingPolicy->getOrderMainSetting($sessionByManager['sno']);
        $eachOrderStatus = [];
        if (empty($orderMainPolicy) === false) {
            $tmpEachOrderStatus = $order->getEachOrderStatusAdmin($orderMainPolicy['orderStatus'], $orderMainPolicy['period'], null, $orderMainPolicy['orderCountFl']);
            $orderStatusByMain = $orderMainPolicy['orderStatus'];
            foreach ($tmpEachOrderStatus as $orderStatus => $val) {
                if (in_array($orderStatus, $orderStatusByMain)) {
                    if ($order->notAllowOrderStatusByProvider($orderStatus)) {
                        continue;
                    }

                    // 이미지 출력을 위한 클래스 설정 (사용자 정의 상태의 경우 이미지를 1번 이미지로 강제 대체)
                    $val['imageClass'] = $orderStatus;
                    $val['codeStep'] = substr($orderStatus, -1);
                    if ($orderStatus == 'b2') {
                        $val['name'] = __('반품 반송중');
                    } else if ($orderStatus == 'e2') {
                        $val['name'] = __('교환 반송중');
                    }
                    // 검색 조건 설정
                    $queryString = '?treatDate[]=' . date('Y-m-d', strtotime('-' . $orderMainPolicy['period'] . ' day')) . '&treatDate[]=' . date('Y-m-d');
                    if ($orderStatus == 'er') {
                        $queryString .= '&view=exchange&detailSearch=y&searchFl=y&orderStatus[]=e&treatDateFl=ouh.regDt&userHandleFl[]=r';
                    } else if ($orderStatus == 'br') {
                        $queryString .= '&view=back&detailSearch=y&searchFl=y&orderStatus[]=b&treatDateFl=ouh.regDt&userHandleFl[]=r';
                    } else if ($orderStatus == 'rr') {
                        $queryString .= '&view=refund&detailSearch=y&searchFl=y&orderStatus[]=r&treatDateFl=ouh.regDt&userHandleFl[]=r';
                    } else {
                        if($orderMainPolicy['orderCountFl'] == 'order'){
                            if (in_array(substr($orderStatus,0, 1), ['','o'])) {
                                $view = 'order';
                            } elseif (in_array(substr($orderStatus,0, 1), ['p','g','d','s'])) {
                                $view = 'orderGoodsSimple';
                            }
                        }else{
                            $view = 'orderGoods';
                        }
                        $queryString .= '&view='.$view.'&detailSearch=y&searchFl=y&orderStatus[]=' . $orderStatus;
                    }

                    $val = $order->initLinkByOrderStatus($orderStatus, $val);

                    // 링크조함
                    $val['link'] .= $queryString;
                    $eachOrderStatus[] = $val;
                }
            }
        }
        $this->setData('eachOrderStatus', $eachOrderStatus);

        /**
         * 쇼핑몰 서비스 정보 & 운영서비스 사용현황
         * @var \Bundle\Component\Mall\Mall $mall
         */
        $mall = \App::load('\\Component\\Mall\\Mall');
        $svcInfos = $mall->getServiceInfo();
        $svcStates = $mall->getServiceState();

        // 쇼핑몰 이름 세팅
        if (empty($svcInfos['mallNm']) === true) {
            $svcInfos['mallNm'] = __('쇼핑몰명을 입력하세요');
        }

        // 쇼핑몰 도메인 세팅
        if (empty($svcInfos['mallDomain']) === true) {
            $svcInfos['mallDomain'] = __('쇼핑몰 도메인을 입력하세요');
        }

        //메모 세팅
        $manager = \App::load('Component\\Member\\Manager');
        $setting['memo'] = $manager->getMemo($sessionByManager);
        StringUtils::strIsSet($setting['memo']['self']['isVisible'], 'y');
        StringUtils::strIsSet($setting['memo']['self']['viewAuth'], 'all');
        $managerData = $manager->getManagerData($sessionByManager['sno'])['data'];

        //게시글 관리 정보
        $mainCsPolicy = $mainSettingPolicy->getBoard($sessionByManager['sno']);
        $boardData = $adminMain->getBoardCsCount($mainCsPolicy);
        $this->setData('boardData', $boardData);
        $boardLink = $adminMain->getBoardCsLink($mainCsPolicy);
        $this->setData('boardLink', $boardLink);

        $plusShopList = $adminMain->getPlusShopListByUsed();
        $plusShopUsedList = $adminMain->getPlusShopListByUsed(true, 'c');
        $this->setData('plusShopList', $plusShopList);
        $this->setData('plusShopUsedList', $plusShopUsedList);

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('serviceInfo', 'base/_index_service_info.php'); // 쇼핑몰 서비스 정보
        $this->getView()->setDefine('serviceOrder', 'base/_index_service_order.php'); // 주문 관리
        $this->getView()->setDefine('serviceMemo', 'base/_index_service_memo.php'); // 관리 메모
        $this->getView()->setDefine('servicePresentation', 'base/_index_service_presentation.php'); // 중앙 현황판
        $this->getView()->setDefine('serviceCalendar', 'base/_index_service_calendar.php'); // 주요 일정
        $this->getView()->setDefine('serviceState', 'base/_index_service_state.php'); // 운영 필수 서비스 현황
        $this->getView()->setDefine('serviceBoard', 'base/_index_service_board.php'); // 문의 / 답변 관리
        $this->getView()->setDefine('servicePlusShop', 'base/_index_service_plus_shop.php'); // 플러스샵 이용현황

        $this->setData('svcInfos', $svcInfos);
        $this->setData('svcStates', $svcStates);
        $this->setData('manager', $managerData);
        $this->setData('setting', $setting);
        $this->setData('godoSno', $globals->get('gLicense.godosno'));
        $this->setData('isStandard', $globals->get('gLicense.ecCode') === 'rental_mxfree_season');

        $this->getView()->setPageName('base/index.php');
    }
}
