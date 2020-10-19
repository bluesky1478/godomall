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

use Component\Member\Manager;
use Request;

/**
 * Class LoginController
 *
 * @package Bundle\Controller\Admin\Base
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LoginController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $session = \App::getInstance('session');
        $session->del(Manager::SESSION_MANAGER_LOGIN);
        // save id 처리
        $saveManagerId = '';
        if (\Cookie::has('SAVE_MANAGER_ID')) {
            $saveManagerId = \Encryptor::decrypt(\Cookie::get('SAVE_MANAGER_ID'));
        }

        // auto login check
        $this->_autoLogin();

        $this->addScript(
            [
                'jquery/jquery.countdownTimer.js',
            ]
        );
        $this->addCss(
            [
                'jquery.countdownTimer.css',
            ]
        );

        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->setData('saveManagerId', $saveManagerId);

        if (str_replace('/', '', strrchr(dirname(Request::getReferer()), '/')) && pathinfo(Request::getReferer())['filename'] != 'login_ps') {
            $adminMenu = \App::load('\\Component\\Admin\\AdminMenu');
            if($adminMenu->getAdminMenuUrl(pathinfo(Request::getReferer())['filename'].".php")) {
                $this->setData('returnUrl', Request::getReferer());
            }
        }
    }

    /**
     * Auto login
     *
     * @author Shin Donggyu <artherot@godo.co.kr>
     */
    private function _autoLogin()
    {
        // 자동 로그인 정보
        $autoLogin = \App::load('\\Component\\Godo\\GodoDemoApi');

        // 자동로그인 체크
        if ($autoLogin->checkDomain() === true) {
            try {
                // 매니저 콤포넌트 로드
                $manager = \App::getInstance('Manager');

                // 로그인 체크 후 매니저 정보 반환
                $managerInfo = $manager->validateManagerLogin(
                    [
                        'managerId' => $autoLogin->getLoginInfo('id'),
                        'managerPw' => $autoLogin->getLoginInfo('pw'),
                    ]
                );

                // 세션생성
                $manager->afterManagerLogin($managerInfo);

                // 페이지 이동
                $returnUrl = URI_ADMIN . 'base/index.php';
                $this->redirect($returnUrl);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
