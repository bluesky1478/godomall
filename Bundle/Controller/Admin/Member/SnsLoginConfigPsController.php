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

namespace Bundle\Controller\Admin\Member;

use Facebook\Exceptions\FacebookResponseException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * 소셜 로그인 관리 요청 처리
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class SnsLoginConfigPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $logger = \App::getInstance('logger');
        try {
            $policy = \App::load('Component\\Policy\\SnsLoginPolicy');
            $beforePolicy = $policy->getValue($policy::KEY);
            if ($policy->save()) {
                $policy->removeUUID($beforePolicy, $policy->getValue($policy::KEY));
                $redirectURL = '../member/sns_login_config.php';
                $redirectTarget = 'parent';
                if ($policy->useFacebook()) {
                    $joinitem = \App::load('Component\\Policy\\JoinItemPolicy');
                    $message = __('소셜로그인 사용 상태로 변경되었습니다. 아이디/비밀번호 분실 시 본인확인을 위해 이메일을 회원가입항목으로 설정합니다.');
                    if ($joinitem->useEmail()) {
                        $message = __('저장이 완료되었습니다.');
                    }
                    $joinitem->setThirdPartyLogin();
                    throw new AlertRedirectException($message, 200, null, $redirectURL, $redirectTarget);
                } else {
                    throw new AlertRedirectException(__('저장이 완료되었습니다.'), 200, null, $redirectURL, $redirectTarget);
                }
            } else {
                throw new LayerNotReloadException(__('처리중에 오류가 발생하여 실패되었습니다.'));
            }
        } catch (FacebookResponseException $e) {
            $logger->error($e->getTraceAsString());
            throw new LayerNotReloadException(__('페이스북 로그인 사용 설정 저장 중 오류가 발생하였습니다.'), $e->getCode(), $e);
        } catch (\Throwable $e) {
            $logger->error($e->getTraceAsString());
            throw new LayerNotReloadException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
