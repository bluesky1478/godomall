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
namespace Bundle\Controller\Admin;

use App;
use Component\Admin\AdminMenu;
use Core\View\Resolver\IncludeResolver;
use Core\View\Template;
use Session;

/**
 * 모바일 앱 관리자 컨트롤러 및 인터셉터 구현 (세션이 없을시 예외처리때문에 별도로 작성)
 *
 * @author Lee Seungjoo <slowj@godo.co.kr>
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 * @author Noh JaeWon <nokoon@godo.co.kr>
 *
 */
class MobileappController extends \Core\Base\Controller\Controller
{

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        // 세션이 없으면 모바일앱페이지쪽으로 리턴처리하기위한 처리
        if (!Session::has('manager.managerId')) {
            header('location:https://mobileapp.godo.co.kr/new/app/login.php');
            exit;
        }

        // 공급사면 접근불가
        if (\Component\Member\Manager::isProvider() == true) {
            echo "<script>alert('공급사는 접근이 불가능한페이지입니다.');location.replace('https://mobileapp.godo.co.kr/new/app/login.php');</script>";
            exit;
        }

        parent::__construct();
        // @formatter:off
        $view = new Template($this->getPageName(), new IncludeResolver());
        // @formatter:on

        $this->setView($view);
    }

    /**
     * {@inheritdoc}
     */
    final protected function setUp()
    {
        parent::setUp();

        $this->setInterceptors(App::getConfig('bundle.interceptor')->getAdmin());
    }

    /**
     * 관리자 페이지 네비게이션 메뉴데이터를 생성한다.
     *
     * @param mixed $topMenu  1차 메뉴
     * @param mixed $midMenu  2차 메뉴
     * @param mixed $thisMenu 3차 메뉴
     *
     * @return object
     */
    public function callMenu($topMenu = null, $midMenu = '', $thisMenu = '')
    {
        $naviMenu = new AdminMenu();

        // 관리자 타입에 따른 메뉴 - 본사(d)/공급사(s)
        if (gd_is_provider() && AdminMenu::isProviderDirectory()) {
            $adminMenuType = 's';
        } else {
            $adminMenuType = 'd';
        }

        if ($topMenu !== null) {
            $naviMenu->callMenu($topMenu, $midMenu, $thisMenu, $adminMenuType);
            $naviMenu->setAccessMenu(Session::get('manager.sno'));
        }
        $getTopMenuArr = $naviMenu->getTopMenu($adminMenuType);
        $this->setData('mobileAppFl', true);
        $this->setData('getTopMenuArr', $getTopMenuArr);
        $this->setData('naviMenu', $naviMenu);

        return $naviMenu;
    }

    /**
     * 모바일앱 네비게이션 메뉴데이터를 생성한다.
     *
     * @param string $pageName  네비메뉴 액티브
     * @param string $navTitle 네비 타이틀
     *
     * @return void
     */
    public function setMenu($pageName, $navTitle)
    {
        $navArray = array(
            'main', 'order', 'goods', 'regist', 'member', 'board', 'visit', 'orders', 'sales', 'notice', 'config'
        );

        $navActive = array(
            '', '', '', '', '', '', '', '', '', '', ''
        );

        foreach ($navArray as $key => $val) {
            if ($val == $pageName) {
                $navActive[$key] = 'active';
            }
        }
        $this->setData('nav_active', $navActive);
        $this->setData('nav_title', $navTitle);
    }
}
