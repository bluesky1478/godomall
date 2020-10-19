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

use Component\Godo\MyGodoMailServerApi;
use Exception;
use Request;
use Session;

/**
 * Class LayerGodoMailPsController
 * GODO Mail 인증관련 처리 클래스
 *
 * @package Bundle\Controller\Admin\Share
 * @author haky <haky2@godo.co.kr>
 */
class LayerGodoMailPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // 요청 값
        $request = Request::post()->toArray();

        // 고도 mail API 클래스 호출
        $godoMailApi = new MyGodoMailServerApi();

        switch ($request['mode']) {
            // 인증번호 요청
            case 'getMailAuthKey':
                $return = $godoMailApi->getMailAuthKey();
                // 인증번호 발송시 로그 작성
                if (trim($return) == 'Y') {
                    $adminLog = \App::load('Component\\Admin\\AdminLogDAO');
                    $adminLog->setAdminLog();
                }
                break;

            // 인증번호 체크
            case 'checkMailAuth':
                $return = $godoMailApi->checkMailAuthKey($request['checkAuthKey']);
                break;

            // 남은 시간 계산
            case 'getRestTime':
                try {
                    if (Session::has(MyGodoMailServerApi::KEY_SESS_INPUT_TIME)) {
                        $return = Session::get(MyGodoMailServerApi::KEY_SESS_INPUT_TIME) - time();
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
                $return = $godoMailApi->checkGodoMemberInfo($request);
                break;
        }

        echo $return;
        exit;
    }
}
