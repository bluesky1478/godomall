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

use Component\Sms\SmsAdmin;
use Request;

/**
 * SMS 문구 선택하기
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerSmsContentsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- SMS 모듈
        $smsAdmin = new SmsAdmin();

        // mode 가 있는 경우
        if (Request::post()->has('mode')) {
            // _POST 데이터
            $postValue = Request::post()->toArray();

            switch (Request::post()->get('mode')) {
                // 등록 / 수정
                case 'registerSmsContents':
                case 'modifySmsContents':
                    $result = $smsAdmin->saveSmsContentsData($postValue);
                    if ($result === true) {
                        echo 'OK';
                    } else {
                        echo 'ERROR';
                    }
                    break;

                // 선택 삭제
                case 'deleteSmsContents':
                    $result = $smsAdmin->deleteSmsContentsData(Request::post()->get('delSno'));
                    if ($result === true) {
                        echo 'OK';
                    } else {
                        echo 'ERROR';
                    }
                    break;
            }
            exit();
        }

        // SMS 문구 그룹
        $smsContentsGroup = gd_code('01007');
        $smsContentsGroup = array_merge(['0' => '문구 선택'], $smsContentsGroup);

        // SMS 문구 리스트
        $smsContentsList = $smsAdmin->getSmsContentsList('user');

        // _GET Data
        $getValue = Request::get()->toArray();

        // page Url
        $pageUrl = '../' . Request::getDirectoryUri() . '/' . Request::getFileUri();

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('layerFormID', $getValue['layerFormID']);
        $this->setData('parentFormID', $getValue['parentFormID']);
        $this->setData('dataFormID', $getValue['dataFormID']);
        $this->setData('dataInputNm', $getValue['dataInputNm']);
        $this->setData('mode', gd_isset($getValue['mode'], 'search'));
        $this->setData('disabled', gd_isset($getValue['disabled'], ''));
        $this->setData('callFunc', gd_isset($getValue['callFunc'], ''));

        $this->setData('pageUrl', $pageUrl);
        $this->setData('smsContentsGroup', $smsContentsGroup);
        $this->setData($smsContentsList);
    }
}
