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

use Bundle\Component\Godo\GodoWonderServerApi;
use Exception;
use Request;
use Logger;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertReloadException;

/**
 * Class WonderLoginRequestPsController
 * @package Bundle\Controller\Admin\Member
 * @author yoonar
 */
class WonderLoginRequestPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $value = Request::post()->all() ? Request::post()->all() : Request::get()->all();
        Logger::channel('wonderLogin')->info(__METHOD__, $value);
        $msg = $value['mode'] == 'regist' ? '신청' : '수정';

        $godoApi = new GodoWonderServerApi();
        $redirectURL = '../member/wonder_login_config.php';
        $response = $godoApi->createClient($value);

        if (empty($response['error']) === false) {
            throw new AlertRedirectException($response['error_description'], 200, null, $redirectURL);
        } else {
            throw new AlertReloadException('위메프 아이디 로그인 ' . $msg . '이 완료되었습니다.', 0, null, 'top');
        }
    }
}
