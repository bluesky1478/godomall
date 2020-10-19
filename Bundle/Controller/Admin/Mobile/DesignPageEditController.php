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

namespace Bundle\Controller\Admin\Mobile;

use Exception;
use Framework\Debug\Exception\AlertBackException;
use Component\Mall\Mall;
use Component\Validator\Validator;
use Component\Design\SkinDesign;
use Component\Design\DesignCode;
use Message;
use Globals;
use Request;

/**
 * 스킨 화일 수정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignPageEditController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        // GET 파라메터
        $getValue = Request::get()->toArray();

        // 페이지 ID
        $getPageID = $getValue['designPageId'];

        //--- 메뉴 설정
        if ($getPageID === 'default') {
            $this->callMenu('mobile', 'designConf', 'defaultPage');
        } else {
            $this->callMenu('mobile', 'designConf', 'designPage');
        }

        // 팝업 모드체크
        if (isset($getValue['popupMode']) === false) {
            $getValue['popupMode'] = false;
        }

        //--- 페이지 데이터
        try {
            //-- Validation
            if (Validator::designLinkid($getPageID, true) === false) {
                throw new Except(__('잘못된 디자인 화일 입니다.'));
            }

            //--- SkinDesign 정의
            $skinDesign = new SkinDesign('mobile');
            $skinInfo = $skinDesign->getSkinInfo(Globals::get('gSkin.mobileSkinWork'));

            // 디자인 페이지의 모든 정보
            $designInfo = $skinDesign->getDesignPageInfo($getPageID);
            $designUrl = $skinDesign->getDesignPageUrl($designInfo['file']['form_type'], $getPageID);

            // 히스토리 화일 정보
            $designHistory = $skinDesign->getDesignHistoryFile($getPageID);

            // 디자인 치환코드 excel load
            $excel = \App::load('\\Component\\Design\\DesignCode');
            $fileName = str_replace('/', '^', str_replace('.html', '.xls', $getPageID));
            $commonFuncCode = $commonVarCode = $designCode = '';

            // 디자인 치환코드
            $dCode = \App::load('\\Component\\Design\\DesignCode');
            $fileName = str_replace('.html', '', $getPageID);
            $commonFuncCode = $commonVarCode = $designCode = '';

            // 공통으로 사용되는 치환코드 load
            if (strpos($getPageID, '.html') !== false) {
                // 공통함수
                $getCommonFuncCode = $dCode->getDesignCode('common_function');
                $commonFuncCode = '
                    <table width="100%" class="design-code-tbl">
                        <tr>
                            <th>'.__('공통함수').' '. __('치환코드').'</th>
                        </tr>
                        ' . @implode($getCommonFuncCode, '') . '
                    </table>
                ';
                $this->setData('commonFuncCode', $commonFuncCode);

                //공통변수
                $getCommonVarCode = $dCode->getDesignCode('common_variable');
                $commonVarCode = '
                    <table width="100%" class="design-code-tbl">
                        <tr>
                            <th>'.__('공통변수').' '. __('치환코드').'</th>
                        </tr>
                        ' . @implode($getCommonVarCode, '') . '
                    </table>
                ';
                $this->setData('commonVarCode', $commonVarCode);

                $getDesignCode = $dCode->getDesignCode($fileName);
                $designCode = '
                    <table width="100%" class="design-code-tbl">
                        <tr>
                            <th>' . $getPageID .' '. __('치환코드').' </th>
                        </tr>
                        ' . @implode($getDesignCode, '') . '
                    </table>
                ';
                $this->setData('designCode', $designCode);

                $selected['searchKeyFl'][$getValue['key']] = 'selected="selected"';
            }
        } catch (Exception $e) {
            if ($e->ectName == 'DESIGN_PAGE_INVALID' || $e->ectName == 'FILE_NOT_FOUND') {
                throw new AlertBackException($e->ectMessage);
            } else {
                echo $e->ectMessage;
            }
            exit;
        }

        //--- 관리자 디자인 템플릿
        if ($getValue['popupMode'] === 'yes') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        } else {
            $this->getView()->setDefine('layout', 'layout_basic.php');
            $this->getView()->setDefine('layoutHeader', 'header.php');
            $this->getView()->setDefine('layoutMenu', 'menu_design_mobile.php');
        }
        if ($getPageID === 'default') {
            $this->getView()->setDefine('layoutContent', 'design/design_page_default.php');
        } else {
            $this->getView()->setDefine('layoutContent', 'design/' . Request::getFileUri());
        }
        $this->getView()->setDefine('layoutTitleBar', 'design/layout_title_bar.php');
        $this->getView()->setDefine('layoutCurrentSkin', 'design/layout_current_skin.php');
        $this->getView()->setDefine('layoutDesignMap', 'design/layout_design_map_' . $skinDesign->skinType . '.php');
        $this->getView()->setDefine('layoutDesignForm', 'design/layout_design_form_' . $designInfo['file']['form_type'] . '.php');
        $this->getView()->setDefine('layoutDesignEditor', 'design/layout_design_editor.php');
        $this->getView()->setDefine('layoutFooter', 'footer.php');

        $this->addCss(
            [
                'design.css',
                '../script/jquery/colorpicker-master/jquery.colorpicker.css',
            ]
        );
        $this->addScript(
            [
                'jquery/jstree/jquery.tree.js',
                'jquery/jstree/plugins/jquery.tree.contextmenu.js',
                'design/designTree.js',
                'design/design.js',
                'jquery/colorpicker-master/jquery.colorpicker.js',
            ]
        );

        // 멀티프론트 정보
        $mall = new Mall();
        if ($mall->isUsableMall() === true) {
            $mallSno = gd_isset(\Session::get('mallSno'), 1);
            $mallData = $mall->getMall($mallSno, 'sno');

            $skinData = gd_policy('design.skin', $mallSno);

            //--- SkinDesign 정의
            $skinDesign = new SkinDesign('mobile');
            $skinInfo = $skinDesign->getSkinInfo($skinData['mobileWork']);
        } else {
            $skinData['mobileLive'] = Globals::get('gSkin.mobileSkinLive');
            $skinData['mobileWork'] = Globals::get('gSkin.mobileSkinWork');
        }

        $this->setData('getPageID', $getPageID);
        $this->setData('designInfo', $designInfo);
        $this->setData('designUrl', $designUrl);
        $this->setData('designHistory', $designHistory);
        $this->setData('popupMode', $getValue['popupMode']);
        $this->setData('skinType', $skinDesign->skinType);
        $this->setData('skinWorkName', $skinInfo['skin_name']);
        $this->setData('mallData', $mallData);
        $this->setData('currentLiveSkin', $skinData['mobileLive']);
        $this->setData('currentWorkSkin', $skinData['mobileWork']);
    }
}
