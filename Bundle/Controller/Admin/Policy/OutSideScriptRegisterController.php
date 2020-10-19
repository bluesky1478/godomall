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
namespace Bundle\Controller\Admin\Policy;

use Component\OutSideScript\OutSideScriptAdmin;
use Framework\Debug\Exception\AlertBackException;
use Component\Database\DBTableField;
use Component\Design\DesignPopup;
use Component\Mall\Mall;
use Request;

/**
 * Class OutSideScriptRegisterController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class OutSideScriptRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        if (Request::get()->has('outSideScriptNo')) {
            $this->callMenu('policy', 'basic', 'outScriptModify');
        } else {
            $this->callMenu('policy', 'basic', 'outScriptRegist');
        }

        // 고유 번호
        $outSideScriptNo = Request::get()->get('outSideScriptNo');
        $mallSno = gd_isset(Request::get()->get('mallSno'), 1);

        // --- 페이지 데이터
        try {
            //--- DesignPopup 정의
            $designPopup = new DesignPopup();
            // 팝업 노출 페이지
            $getPopupPage = $designPopup->getPopupPageOutput();
            foreach ($getPopupPage as $pageKey => $pageVal) {
                if ($pageVal == 'intro/intro.php' || $pageVal == 'intro/member.php' || $pageVal == 'intro/adult.php') {
                    // 외부 스크립트에서 인트로는 제외
                } else {
                    $getOutSideScriptPage[$pageKey] = $pageVal;
                }
            }

            if ($outSideScriptNo > 0) {
                $outSideScriptAdmin = new OutSideScriptAdmin();
                $paramData['outSideScriptNo'] = $outSideScriptNo;
                $getData = $outSideScriptAdmin->getOutSideScript($paramData, 'regist', $mallSno);
                $getData = $getData[0];
                $getData['outSideScriptPage'] = json_decode($getData['outSideScriptPage'], true);
                $getData['mode'] = 'modify';
            } else {
                DBTableField::setDefaultData('tableOutSideScript', $getData);
                $getData['mode'] = 'insert';
            }

            $checked['outSideScriptUse'][$getData['outSideScriptUse']] =
            $checked['outSideScriptUseHeader'][$getData['outSideScriptUseHeader']] =
            $checked['outSideScriptUseFooter'][$getData['outSideScriptUseFooter']] =
            $checked['outSideScriptUsePage'][$getData['outSideScriptUsePage']] = 'checked="checked"';

            $mall = new Mall();
            $mallSno = gd_isset(Request::get()->get('mallSno'), 1);

            $mallList = $mall->getListByUseMall();
            if (count($mallList) > 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);
                $this->setData('mallSno', $mallSno);
            }

            // --- 관리자 디자인 템플릿
            $this->setData('data', $getData);
            $this->setData('checked', $checked);
            $this->setData('getOutSideScriptPage', $getOutSideScriptPage);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
