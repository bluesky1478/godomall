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
namespace Bundle\Controller\Admin\Share;

use Component\Godo\MyGodoSmsServerApi;
use Exception;
use Request;
use Session;

/**
 * Class LayerGodoSmsPsController
 * GODO SMS 인증관련 처리 클래스
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerGodoSmsPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // 요청 값
        $request = Request::post()->toArray();

        // 고도 API 클래스 호출
        $godoApi = new MyGodoSmsServerApi();

        switch ($request['mode']) {
            // 인증번호 요청
            case 'getSmsAuthKey':
                $return = $godoApi->getSmsAuthKey();
                // 인증번호 발송시 로그 작성
                if (trim($return) == 'Y') {
                    $adminLog = \App::load('Component\\Admin\\AdminLogDAO');
                    $adminLog->setAdminLog();
                }
                break;

            // 인증번호 체크
            case 'checkSmsAuth':
                $return = $godoApi->checkSmsAuthKey($request['checkAuthKey']);
                break;

            // 남은 시간 계산
            case 'getRestTime':
                try {
                    if (Session::has(MyGodoSmsServerApi::KEY_SESS_INPUT_TIME)) {
                        $return = Session::get(MyGodoSmsServerApi::KEY_SESS_INPUT_TIME) - time();
                    } else {
                        $return = -1;
//                        throw new Exception('인증 요청을 하지 않았습니다.');
                    }
                } catch (Exception $e) {
                    throw $e;
                }

                break;
            // 고도 회원정보와 회원 입력정보 검증
            case 'checkGodoMember':
                $return = $godoApi->checkGodoMemberInfo($request);
                break;
        }

        echo $return;
        exit;
    }
}
