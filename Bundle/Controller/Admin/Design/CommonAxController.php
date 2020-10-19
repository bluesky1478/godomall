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

namespace Bundle\Controller\Admin\Design;

use Component\Godo\GodoServiceServerApi;
use Component\Mall\Mall;
use Component\Design\SkinDesign;
use Cache;
use Globals;
use Request;
use UserFilePath;

/**
 * 디자인 관리 공통 저장
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CommonAxController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // skinType 설정
        if (Request::get()->has('skinType') === false) {
            $skinType = 'front';
        } else {
            $skinType = Request::get()->get('skinType');
        }

        try {
            // SkinDesign 정의
            $skinDesign = new SkinDesign($skinType);
            $mall = new Mall();
            $useableMall = $mall->isUsableMall();
            $skinWork = Globals::get('gSkin.' . $skinDesign->skinType . 'SkinWork');
            if (\Session::has('mallSno') === true && $useableMall === true) {
                $mallSkin = gd_policy('design.skin', \Session::get('mallSno'));
                $skinWork = $mallSkin[$skinDesign->skinType . 'Work'];
            }
            $skinDesign->setSkin($skinWork);

            switch (Request::get()->get('mode')) {
                case 'init': // 초기값
                    if ($mall->isUsableMall() === true) {
                        $mallCnt = $mall->getListByUseMall();
                        $mallSno = gd_isset(\Session::get('mallSno'), 1);
                        $mallData = $mall->getMall($mallSno, 'sno');
                        $skin = gd_policy('design.skin', $mallSno)[$skinDesign->skinType . 'Work'];
                    } else {
                        $skin = Globals::get('gSkin.' . $skinDesign->skinType . 'SkinWork'); // 스킨명
                    }
                    $result = ['skin' => $skin, 'mallCnt' => count($mallCnt), 'mallData' => $mallData, 'mallSno' => $mallSno];
                    echo json_encode($result);
                    exit();

                case 'getSkinName': // 스킨명
                    echo Globals::get('gSkin.' . $skinDesign->skinType . 'SkinWork');
                    break;

                case 'getDesignTreeData': // 파일트리 데이터 (JSON)
                    $getID = Request::get()->get('id');
                    $result = $skinDesign->getDesignTreeData(gd_isset($getID));
                    echo json_encode($result);
                    break;

                case 'getTextarea': // html 에디터
                    $output = '';
                    $logger = \App::getInstance('logger');
                    if ($useableMall === true) {
                        $mallSno = gd_isset(\Session::get('mallSno'), 1);
                        $skin = gd_policy('design.skin', $mallSno)[$skinDesign->skinType . 'Work'];
                    } else {
                        $skin = Globals::get('gSkin.' . $skinDesign->skinType . 'SkinWork'); // 스킨명
                    }

                    if (empty($skin) === false && empty(Request::get()->get('tplFile')) === false) {
                        // Textarea Use Skin
                        if (Request::get()->has('historyFile') === true) {
                            $tmp = UserFilePath::temporary('skin_history' . Request::get()->get('tplFile'));
                        } else {
                            if ($skinDesign->skinType === 'front'){
                                $tmp = UserFilePath::frontSkin($skin . Request::get()->get('tplFile'));
                            } else {
                                $tmp = UserFilePath::mobileSkin($skin . Request::get()->get('tplFile'));
                            }
                        }

                        if (Request::get()->get('body') === 'user_body' && $tmp->isExists()) {
                            $file = file($tmp);
                            $output = implode("", $file);
                        }

                        // Textarea Base Skin
                        if (Request::get()->get('body') === 'base_body') {
                            if ($skinDesign->skinType === 'front') {
                                $tmp = 'front/' . $skin . Request::get()->get('tplFile');
                            } else {
                                $tmp = 'mobile/' . $skin . Request::get()->get('tplFile');
                            }

                            $cacheKey = \Request::getRequestUri();
                            $expireSecond = 10 * 60; //캐시 유지시간 10분

                            try {
                                if (Cache::get($cacheKey, $expireSecond)) { //캐시가 존재하면
                                    $remoteResult = Cache::get($cacheKey, $expireSecond);
                                    if(!$remoteResult['contents'] || !$remoteResult['result'] ){    //존재하는 캐시 결과값이 실패했으면 서버에서 다시가져옴
                                        $remoteResult = $this->getOriginalSource($tmp);
                                    }
                                } else {
                                    $remoteResult = $this->getOriginalSource($tmp);
                                    if($remoteResult['contents'] && $remoteResult['result']){  //통신이 성공적이고 컨텐츠값이 존재할때만 캐시태움.
                                        Cache::set($cacheKey, $remoteResult, $expireSecond, $cacheKey);
                                    }
                                }
                            } catch(\Throwable $e){
                                $logger->info('design getTextarea',[$e->getMessage(),$e->getLine()]);
                                $remoteResult = $this->getOriginalSource($tmp);
                            }
                            $output = '';
                            if ($remoteResult['result']) {
                                $output = $remoteResult['contents'];
                            }
                            else {
                                $logger->info('design getTextarea',[$remoteResult]);
                            }
                        }
                    }


                    echo $output;
                    break;

                case 'overlapDesignFile': // 디자인 페이지 중복확인(파일명)
                    // GET 파라메터
                    $getValue = Request::get()->toArray();
                    $result = [];
                    $result['result'] = $skinDesign->overlapDesignFile($getValue);
                    $designUrl = $skinDesign->getDesignPageUrl('file', $getValue['dirPath'].'/'.$getValue['fileName']);
                    $result['designUrl'] = $designUrl;
                    echo json_encode($result);
                    break;
            }
        } catch (\Exception $e) {
            $result['code'] = 'error';
            $result['message'] = $e->getMessage();
            echo json_encode($result);
        }
        exit();
    }

    /**
     * 원본스킨소스 가져오기
     *
     * @param $path
     *
     * @return mixed
     */
    public function getOriginalSource($path)
    {
        $godoCenterServerApi = new GodoServiceServerApi();
        $remoteResult = json_decode($godoCenterServerApi->getSkinOriginalSource($path), true);

        return $remoteResult;
    }
}
