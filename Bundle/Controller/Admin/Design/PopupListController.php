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

use Component\Design\SkinDesign;
use Component\Design\DesignPopup;
use Component\Design\DesignBanner;
use Component\Page\Page;
use Globals;
use Request;
/**
 * 팝업 리스트
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class PopupListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        if ($this->menuType == 'mobile') {
            $this->callMenu('mobile', 'designSet', 'popupList');
        } else {
            $this->callMenu('design', 'designConf', 'popupList');
        }

        // --- 페이지 데이터
        try {
            //--- SkinDesign 정의 (사용 스킨 기준)
            $skinDesign = new SkinDesign();
            $skinDesign->setSkin(Globals::get('gSkin.frontSkinLive'));
            $getPopupSkin = $skinDesign->getDirDesignPageInfo('popup', true);
            $req = Request::get()->toArray();

            //--- DesignPopup 정의
            $designPopup = new DesignPopup();
            $getData = $designPopup->getPopupListData($this->menuType);

            $designBanner = new DesignBanner();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layoutMenu', 'menu_design.php');

        $this->addCss([
            'design.css',
        ]);
        $this->addScript([
            'jquery/jstree/jquery.tree.js',
            'jquery/jstree/plugins/jquery.tree.contextmenu.js',
            'design/designTree.js',
            'design/design.js',
        ]);

        $this->setData($getData);
        $this->setData('getPopupKindFl', $designPopup->popupKindFl);
        $this->setData('getPopupSkin', $getPopupSkin);
        $this->setData('displayFl', $designBanner->deviceType);
        $this->setData('req', $req);
    }
}
