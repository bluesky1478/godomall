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

use Component\Godo\GodoPaycoServerApi;
use Request;

/**
 * Class PaycoLoginRequestPsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class PaycoLoginRequestPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        \Logger::debug(__METHOD__, \Request::post()->all());

        if (Request::post()->get('agreementFlag', 'n') != 'y') {
            $this->json(
                [
                    'error'   => true,
                    'message' => __('페이코 로그인 서비스를 사용하려면 이용정책에 동의해 주세요.'),
                ]
            );
        }

        $godoApi = new GodoPaycoServerApi();
        $params = Request::post()->all();
        $serviceCode = $godoApi->getServiceCode($params);

        $codes = json_decode($serviceCode, true);
        if ($codes['header']['resultCode'] == 0) {
            $params['clientId'] = $codes['clientId'];
            $params['clientSecret'] = $codes['clientSecret'];
            \Session::set(GodoPaycoServerApi::SESSION_PAYCO_SERVICE_CODE, $params);
            $this->json(
                [
                    'message'      => __('신청 후 페이코 아이디 로그인 설정 화면에서 저장을 눌러주셔야 최종 사용신청이 완료됩니다.'),
                    'clientId'     => $codes['clientId'],
                    'clientSecret' => $codes['clientSecret'],
                ]
            );
        } else {
            $this->json(
                [
                    'error'   => true,
                    'message' => __('서비스 신청이 실패하였습니다.'),
                ]
            );
        }
    }
}
