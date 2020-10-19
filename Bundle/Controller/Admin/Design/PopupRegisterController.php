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

use Framework\Debug\Exception\AlertBackException;
use Component\Design\SkinDesign;
use Component\Design\DesignPopup;
use Component\Database\DBTableField;
use Component\Mall\Mall;
use Framework\Utility\UrlUtils;
use Globals;
use Request;

/**
 * 팝업 등록 / 수정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class PopupRegisterController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        //--- SkinDesign 정의 (사용 스킨 기준)
        $skinDesign = new SkinDesign();
        $skinDesign->setSkin(Globals::get('gSkin.frontSkinLive'));
        $getPopupSkin = $skinDesign->getDirDesignPageInfo('popup', true);

        //--- SkinDesign 정의 (모바일 사용 스킨 기준)
        $skinDesign = new SkinDesign('mobile');
        $skinDesign->setSkin(Globals::get('gSkin.mobileSkinLive'));
        $getMobilePopupSkin = $skinDesign->getDirDesignPageInfo('popup', true);

        /*$skinDesign->setSkin(Globals::get('gSkin.mobileSkinLive'));
        $getMobilePopupSkin = $skinDesign->getDirDesignPageInfo('popup', true);*/

        //--- DesignPopup 정의
        $designPopup = new DesignPopup();

        // _GET 데이터
        $getValue = Request::get()->toArray();

        // 기본값 정의
        $getData = [];
        DBTableField::setDefaultData('tableDesignPopup', $getData);
        if ($this->menuType == 'mobile') {
            $getData['pcDisplayFl'] = 'n';
            $getData['mobileDisplayFl'] = 'y';
        }

        // 데이터
        $checked = [];
        $selected = [];
        if (isset($getValue['sno']) === true) {
            $mode = 'modify';
            $modeTxt = '수정';
            $getData = $designPopup->getPopupDetailData($getValue['sno']);
            //--- 메뉴 설정
            if ($this->menuType == 'mobile') {
                $this->callMenu('mobile', 'designSet', 'popupModify');
            } else {
                $this->callMenu('design', 'designConf', 'popupModify');
            }

            $gGlobal = Globals::get('gGlobal');
            $mallDisplay = explode(',', $getData['mallDisplay']);
            if (count($gGlobal['useMallList']) == count($mallDisplay)) {
                $checked['mallDisplay']['all'] = 'checked="checked"';
            } else {
                foreach ($mallDisplay as $val) {
                    $checked['mallDisplay'][$val] = 'checked="checked"';
                }
            }
        } else {
            $mode = 'register';
            $modeTxt = '등록';
            //--- 메뉴 설정
            if ($this->menuType == 'mobile') {
                $this->callMenu('mobile', 'designSet', 'popupRegister');
            } else {
                $this->callMenu('design', 'designConf', 'popupRegister');
            }

            $checked['mallDisplay']['all'] = 'checked="checked"';
        }

        // 날짜 처리
        $getData['popupPeriodSDateY'] =
        $getData['popupPeriodSTimeY'] =
        $getData['popupPeriodEDateY'] =
        $getData['popupPeriodETimeY'] =
        $getData['popupPeriodSDateT'] =
        $getData['popupPeriodSTimeT'] =
        $getData['popupPeriodEDateT'] =
        $getData['popupPeriodETimeT'] = '';
        if ($getData['popupPeriodOutputFl'] === 'y') {
            $getData['popupPeriodSDateY'] = $getData['popupPeriodSDate'];
            $getData['popupPeriodSTimeY'] = $getData['popupPeriodSTime'];
            $getData['popupPeriodEDateY'] = $getData['popupPeriodEDate'];
            $getData['popupPeriodETimeY'] = $getData['popupPeriodETime'];
        } elseif ($getData['popupPeriodOutputFl'] === 't') {
            $getData['popupPeriodSDateT'] = $getData['popupPeriodSDate'];
            $getData['popupPeriodSTimeT'] = $getData['popupPeriodSTime'];
            $getData['popupPeriodEDateT'] = $getData['popupPeriodEDate'];
            $getData['popupPeriodETimeT'] = $getData['popupPeriodETime'];
        }
        unset($getData['popupPeriodSDate'], $getData['popupPeriodEDate'], $getData['popupPeriodSTime'], $getData['popupPeriodETime']);

        $checked['pcDisplayFl'][$getData['pcDisplayFl']] =
        $checked['mobileDisplayFl'][$getData['mobileDisplayFl']] =
        $checked['popupUseFl'][$getData['popupUseFl']] =
        $checked['popupKindFl'][$getData['popupKindFl']] =
        $checked['mobilePopupKindFl'][$getData['mobilePopupKindFl']] =
        $checked['popupPeriodOutputFl'][$getData['popupPeriodOutputFl']] =
        $checked['todayUnSeeFl'][$getData['todayUnSeeFl']] =
        $checked['todayUnSeeAlign'][$getData['todayUnSeeAlign']] =
        $checked['contentImgFl'][$getData['contentImgFl']] =
        $checked['mobileContentImgFl'][$getData['mobileContentImgFl']] = 'checked="checked"';

        $selected['popupSkin'][$getData['popupSkin']] =
        $selected['mobilePopupSkin'][$getData['mobilePopupSkin']] =
        $selected['popupPageUrl'][$getData['popupPageUrl']] =
        $selected['mobilePopupPageUrl'][$getData['mobilePopupPageUrl']] = 'selected="selected"';

        // 팝업 노출 페이지
        $getPopupPage = $designPopup->getPopupPageOutput();

        // 팝업 노출 위치 페이지
        $getPcPopupPage = $designPopup->getPopupPage(['pcDisplayFl' =>'y'], ['sno', 'pageName', 'pageUrl'], false);
        $getMobilePopupPage = $designPopup->getPopupPage(['mobileDisplayFl' =>'y'], ['sno', 'pageName', 'pageUrl'], false);

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layoutMenu', 'menu_design.php');

        $this->addCss([
            'design.css',
            '../script/jquery/colorpicker-master/jquery.colorpicker.css',
        ]);
        $this->addScript([
            'jquery/jstree/jquery.tree.js',
            'jquery/jstree/plugins/jquery.tree.contextmenu.js',
            'design/designTree.js',
            'jquery/colorpicker-master/jquery.colorpicker.js',
            'design/design.js',
        ]);

        $this->setData('mode', $mode);
        $this->setData('modeTxt', $modeTxt);
        $this->setData('data', gd_htmlspecialchars($getData));
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('getPopupKindFl', $designPopup->popupKindFl);
        $this->setData('getPopupSkin', $getPopupSkin);
        $this->setData('getMobilePopupSkin', $getMobilePopupSkin);
        $this->setData('getPopupPage', $getPopupPage);
        $this->setData('getPcPopupPage', $getPcPopupPage['data']);
        $this->setData('getMobilePopupPage', $getMobilePopupPage['data']);
        $this->setData('sizeType', $designPopup->sizeType);
        $this->setData('adminList', UrlUtils::getAdminListUrl());
    }
}
