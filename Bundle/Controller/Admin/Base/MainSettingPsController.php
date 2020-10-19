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
use Component\Member\Manager;
use Component\Order\OrderStatics;
use Exception;
use Framework\Debug\Exception\DatabaseException;
use Framework\Debug\Exception\LayerException;
use Message;
use Request;
use Session;

/**
 * Class MainSettingPsController
 *
 * @package Bundle\Controller\Admin\Base
 * @author  lee nam ju <lnjts@godo.co.kr>
 */
class MainSettingPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws LayerException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // 요청
        $post = Request::post()->toArray();
        $mainPolicy = \App::load('Component\\Policy\\MainSettingPolicy');
        $managerBySession = Session::get(Manager::SESSION_MANAGER_LOGIN);
        $post['managerSno'] = $managerBySession['sno'];
        switch ($post['mode']) {
            case 'presentation':
                try {
                    // 주요현황 권한 체크
                    $adminMenu = \App::load('Component\\Admin\\AdminMenu');
                    if (method_exists($adminMenu, 'setAccessWriteEnabledMenu') && method_exists($adminMenu, 'getAccessMenuStatus')) {
                        $adminMenu->setAccessWriteEnabledMenu(Session::get('manager.sno'));
                        $mainServicePresentationAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServicePresentation', gd_is_provider());
                        if ($mainServicePresentationAccess !== 'writable') {
                            $this->json(
                                ['fail' => '주요현황 설정 권한이 없습니다.']
                            );
                        }
                    }

                    $params = [
                        'period'     => $post['period'],
                        'managerSno' => $managerBySession['sno'],
                    ];
                    $mainPolicy->savePresentation($params);
                    $this->json(
                        ['success' => 'OK']
                    );
                } catch (Exception $e) {
                    \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'boardPeriod':
                try {
                    // 문의/답변관리 권한 체크
                    $adminMenu = \App::load('Component\\Admin\\AdminMenu');
                    if (method_exists($adminMenu, 'setAccessWriteEnabledMenu') && method_exists($adminMenu, 'getAccessMenuStatus')) {
                        $adminMenu->setAccessWriteEnabledMenu(Session::get('manager.sno'));
                        $mainServiceBoardAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceBoard', gd_is_provider());
                        if ($mainServiceBoardAccess !== 'writable') $this->layerNotReload(__('문의/답변관리 설정 권한이 없습니다.'));
                    }

                    $mainPolicy->saveBoardPeriod($post);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'memo':
                try {
                    // 관리메모 권한 체크
                    $adminMenu = \App::load('Component\\Admin\\AdminMenu');
                    if (method_exists($adminMenu, 'setAccessWriteEnabledMenu') && method_exists($adminMenu, 'getAccessMenuStatus')) {
                        $adminMenu->setAccessWriteEnabledMenu(Session::get('manager.sno'));
                        $mainServiceMemoAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceMemo', gd_is_provider());
                        if ($mainServiceMemoAccess !== 'writable') $this->layerNotReload(__('관리메모의 쓰기 권한이 없습니다.'));
                    }

                    $manager = \App::load('Component\\Member\\Manager');
                    if ($manager->saveMemo($managerBySession, $post)) {
                        $this->layerNotReload(__('저장이 완료되었습니다.'));
                    } else {
                        $this->layerNotReload(__('저장이 실패되었습니다.'));
                    }
                } catch (DatabaseException $e) {
                    $this->layerNotReload($e->getMessage());
                }
                break;
            case 'orderSetting':    // 주문관리 조회설정
                try {
                    // 주문관리 권한 체크
                    $adminMenu = \App::load('Component\\Admin\\AdminMenu');
                    if (method_exists($adminMenu, 'setAccessWriteEnabledMenu') && method_exists($adminMenu, 'getAccessMenuStatus')) {
                        $adminMenu->setAccessWriteEnabledMenu(Session::get('manager.sno'));
                        $mainServiceOrderAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServiceOrder', gd_is_provider());
                        if ($mainServiceOrderAccess !== 'writable') $this->layerNotReload(__('주문관리 설정 권한이 없습니다.'));
                    }

                    $mainPolicy->saveOrderMainSetting($post);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'orderPresentation':   // 주문현황 조회설정 저장
                try {
                    // 주요현황 권한 체크
                    $adminMenu = \App::load('Component\\Admin\\AdminMenu');
                    if (method_exists($adminMenu, 'setAccessWriteEnabledMenu') && method_exists($adminMenu, 'getAccessMenuStatus')) {
                        $adminMenu->setAccessWriteEnabledMenu(Session::get('manager.sno'));
                        $mainServicePresentationAccess = $adminMenu->getAccessMenuStatus('base', 'index', 'mainServicePresentation', gd_is_provider());
                        if ($mainServicePresentationAccess !== 'writable') $this->layerNotReload(__('주요현황 설정 권한이 없습니다.'));
                    }

                    $params = [
                        'period'      => $post['period'],
                        'orderStatus' => $post['orderStatus'],
                        'managerSno'  => $managerBySession['sno'],
                        'orderCountFl'  => $post['orderCountFl'],
                    ];
                    $mainPolicy->saveOrderPresentation($params);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'getOrderPresentation':   // 주문현황 조회
                try {
                    $policy = $mainPolicy->getOrderPresentation(\Session::get(Manager::SESSION_MANAGER_LOGIN . '.sno'));
                    $order = new OrderStatics();

                    $result = $order->getOrderPresentation($policy['period'], $policy['orderStatus'], $policy['orderCountFl']);
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $result,
                        ]
                    );
                } catch (Exception $e) {
                    $this->json(
                        [
                            'fail'    => 'ERROR',
                            'message' => $e->getMessage(),
                        ]
                    );
                }
                break;
            case 'orderPresentationNew': // 주문현황 NEW 아이콘 체크
                try {
                    $policy = $mainPolicy->getOrderPresentation(\Session::get(Manager::SESSION_MANAGER_LOGIN . '.sno'));
                    $order = new OrderStatics();
                    $result = $order->checkOrderPresentationCount($policy['period'], $policy['orderStatus']);
                    \Logger::debug(__METHOD__ . ', ' . $result);
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $result,
                        ]
                    );
                } catch (Exception $e) {
                    $this->json(
                        [
                            'fail'    => 'ERROR',
                            'message' => $e->getMessage(),
                        ]
                    );
                }
                break;
            case 'favoriteMenu':
                try {
                    $params = [
                        'menus'      => $post['menus'],
                        'managerSno' => $managerBySession['sno'],
                    ];
                    $mainPolicy->saveFavoriteMenu($params);
                    $this->json(
                        [
                            'success' => 'OK',
                            'message' => __('저장이 완료되었습니다.'),
                        ]
                    );
                } catch (Exception $e) {
                    $this->json(
                        [
                            'fail'    => 'ERROR',
                            'message' => $e->getMessage(),
                        ]
                    );
                }
                break;
            case 'getFavoriteMenu':
                try {
                    $adminMenu = new AdminMenu();
                    $adminMeuType = 'd';
                    if (Manager::isProvider()) {
                        $adminMeuType = 's';
                    }
                    $menuLists = $adminMenu->getAdminMenuList($adminMeuType);
                    $favoriteMenu = $mainPolicy->getFavoriteMenu(\Session::get(Manager::SESSION_MANAGER_LOGIN . '.sno'));
                    $adminMain = new AdminMain();
                    $favoriteMenu = $adminMain->getHeaderFavoriteMenu($menuLists, $favoriteMenu);
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $favoriteMenu,
                        ]
                    );
                } catch (Exception $e) {
                    $this->json(
                        [
                            'fail'    => 'ERROR',
                            'message' => $e->getMessage(),
                        ]
                    );
                }
                break;
            case 'searchMenu':
                try {
                    $adminMeuType = 'd';
                    if (Manager::isProvider()) {
                        $adminMeuType = 's';
                    }
                    $adminMenu = new AdminMenu();
                    $menuLists = $adminMenu->getAdminMenuList($adminMeuType);
                    $adminMain = new AdminMain();
                    $keyword = Request::post()->get('keyword');
                    $result = $adminMain->searchAdminMenuByMenuName($menuLists, $keyword);
                    $this->json(
                        [
                            'success' => 'OK',
                            'result'  => $result,
                        ]
                    );
                } catch (Exception $e) {
                    $this->json(
                        [
                            'fail'    => 'ERROR',
                            'message' => $e->getMessage(),
                        ]
                    );
                }
                break;
        }
        exit;
    }
}
