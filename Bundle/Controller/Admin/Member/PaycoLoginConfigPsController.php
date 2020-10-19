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

namespace Bundle\Controller\Admin\Member;

use Component\Policy\JoinItemPolicy;
use Component\Policy\PaycoLoginPolicy;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Request;

/**
 * Class PaycoLoginConfigPsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class PaycoLoginConfigPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $policy = new PaycoLoginPolicy();
            $checkUseFlSame = Request::post()->get('checkUseFlSame');
            if ($policy->save(Request::post()->all())) {
                $redirectURL = '../member/payco_login_config.php';
                $redirectTarget = 'parent';
                if($checkUseFlSame == 'false') {
                    if ($policy->usePaycoLogin()) {
                        $joinitem = new JoinItemPolicy();
                        $joinitem->usePaycoLogin();
                        throw new AlertRedirectException(__('페이코 아이디 로그인 사용 상태로 변경되었습니다. 아이디/비밀번호 분실 시 본인확인을 위해 이메일을 회원가입항목으로 설정합니다.'), 200, null, $redirectURL, $redirectTarget);
                    }
                };
                throw new AlertRedirectException(__('저장이 완료되었습니다.'), 200, null, $redirectURL, $redirectTarget);
            } else {
                throw new AlertOnlyException(__('처리중에 오류가 발생하여 실패되었습니다.'));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
