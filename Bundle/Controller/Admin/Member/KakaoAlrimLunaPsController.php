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

use Component\Sms\Sms;
use Component\Member\KakaoAlrimLuna;
use Exception;
use Framework\Debug\Exception\LayerException;
use Logger;


/**
 * 카카오 알림톡 설정 ps파일
 *
 */
class KakaoAlrimLunaPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $request = \App::getInstance('request');

        /**
         * @var \Bundle\Component\Policy\Policy      $policy
         */
        $policy = \App::load('\\Component\\Policy\\Policy');
        $oKakao = new KakaoAlrimLuna;
        $requestAllParams = array_merge($request->get()->toArray(), $request->post()->toArray());
        try {
            switch ($requestAllParams['mode']) {

                case 'saveKakaoAlrimConfig':
                    unset($requestAllParams['lunaCliendId']);
                    $result = $policy->saveKakaoAlrimLunaConfig($requestAllParams);
                    if($result === true) {
                        $oKakao->saveKakaoAuto($requestAllParams);
                        Logger::channel('kakao')->info('LUNA_COMMON_KEY_SETTING : Result', ['OK']);
                        if ($requestAllParams['return_mode'] == 'layer') {
                            $this->layer(__('저장이 완료되었습니다.'));
                        } else {
                            $this->json(array('result' => 'success'));
                        }
                    } else {
                        Logger::channel('kakao')->info('LUNA_COMMON_KEY_SETTING : Result', ['ERR']);
                        if ($requestAllParams['return_mode'] == 'layer') {
                            $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.'));
                        } else {
                            $this->json(array('result' => 'fail'));
                        }
                    }
                    break;
                case 'sendLunaId':
                    $oKakao->sendLunaId($requestAllParams);
                    break;

                //case 'saveTemplte':
                //    $oKakao->saveTemplte($requestAllParams);
                //    echo "<script>history.back();</script>";
                //    break;

                case 'deleteKakaoKey':
                    $aResult = $oKakao->deleteLunaKey();

                    if($aResult['result'] == 'success'){
                        $updateData = array();
                        $updateData['lunaKeyDel'] = 'y';
                        $result = $policy->saveKakaoAlrimLunaConfig($updateData);
                        if($result) {
                            $tmpkakaoAutoSet = gd_policy('kakaoAlrimLuna.kakaoAuto');
                            $tmpLunaSet = array_merge($tmpkakaoAutoSet, array('useFlag' => 'n'));
                            gd_set_policy('kakaoAlrimLuna.kakaoAuto', $tmpLunaSet);
                        }

                        if($result){
                            $this->json(array('result' => 'success'));
                        }else{
                            $this->json(array('result' => 'fail'));
                        }
                    }else{
                        $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.'));
                    }

                    break;

                default:
                    throw new Exception(__('요청을 처리할 페이지를 찾을 수 없습니다.'), 404);
                    break;
            }
        } catch (Exception $e) {
            if ($request->isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

}
