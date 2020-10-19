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

use Component\Godo\GodoSmsServerApi;
use Globals;
use Request;

/**
 * SMS 발신 번호 선택하기
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerSmsCallNumberSelectController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        //--- 페이지 데이터
        if (Request::get()->has('returnInput') === true) {
            $returnInput = Request::get()->get('returnInput');
        } else {
            throw new LayerException(__('로딩이 실패했습니다.'));
        }

        // --- SMS 발신번호 사전 등록 번호 정보
        $godoSms = new GodoSmsServerApi();
        $smsCallNumberData = $godoSms->getSmsCallNumberData();

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('smsCallNumberData', gd_htmlspecialchars($smsCallNumberData));
        $this->setData('returnInput', $returnInput);
    }
}
