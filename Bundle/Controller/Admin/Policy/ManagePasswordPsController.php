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

namespace Bundle\Controller\Admin\Policy;

use Component\Godo\MyGodoSmsServerApi;
use Component\Member\Manager;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Request;

/**
 * 비밀번호 변경 안내 레이어 요청 처리 컨트롤러
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class ManagePasswordPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $mode = Request::post()->get('mode');
        $manager = new Manager();
        try {
            switch ($mode) {
                case 'changePassword':
                    $oldPassword = Request::post()->get('oldPassword', '');
                    $password = Request::post()->get('password');
                    $manager->changePassword($oldPassword, $password);
                    $type = Request::post()->get('type');
                    if ($type == 'reset') {
                        MyGodoSmsServerApi::deleteAuthKey();
                        MyGodoSmsServerApi::deleteAuth();
                    }
                    $userAgent = Request::getUserAgent();
                    if (preg_match("/GodoAdmin/", $userAgent)) {
                        $url = "https://mobileapp.godo.co.kr/new2/app/login.php?changePasswordLogout=Y";    // modified 2018.08.17 lastar@godo.co.kr
                        $msg = "비밀번호가 정상적으로 변경되었습니다.\n로그아웃 후 다시 로그인 해주세요.";
                    } else {
                        $url = $type == 'reset' ? URI_ADMIN : Request::getReferer();
                        $msg = "비밀번호가 정상적으로 변경되었습니다.";
                    }
                    $this->json(
                        [
                            'result'  => 'ok',
                            'url'     => $url,
                            'message' => __($msg),
                        ]
                    );
                    break;
                case 'laterPassword':
                    $manager->changePasswordLater();
                    $this->json(
                        [
                            'result' => 'ok',
                            'url'    => Request::getReferer(),
                        ]
                    );
                    break;
            }
        } catch (Exception $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if (Request::isAjax()) {
                $message = $e->getMessage();
                gd_isset($message, __('오류가 발생하였습니다.'));
                $this->json(
                    [
                        'result'  => 'fail',
                        'message' => $message,
                    ]
                );
            } else {
                throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
