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

namespace Bundle\Controller\Mobile\Popup;

use Component\Design\SkinDesign;
use Component\Design\DesignMultiPopup;
use Globals;
use Request;
use Cookie;
use UserFilePath;
use FileHandler;

/**
 * 팝업창
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MultiPopupController extends \Controller\Mobile\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        //--- SkinDesign 정의 (사용 스킨 기준)
        $skinDesign = new SkinDesign();
        $skinDesign->setSkin(Globals::get('gSkin.frontSkinLive'));
        $getPopupSkin = $skinDesign->getDirDesignPageInfo('popup', true);

        //--- DesignPopup 정의 (사용 스킨 기준)
        $designMultiPopup = new DesignMultiPopup();
        $designMultiPopup->setSkin(Globals::get('gSkin.frontSkinLive'));

        // _GET 데이터
        $getValue = Request::get()->toArray();

        // 데이터
        $getData = $designMultiPopup->getPopupDetailData($getValue['sno']);

        // 해당 팝업창 스킨이 없는 경우 있는것 중 첫번째 것으로 처리
        if (array_key_exists($getData['popupSkin'], $getPopupSkin) === false) {
            $tmp = array_keys($getPopupSkin);
            $getData['popupSkin'] = $tmp[0];
        }

        // 오늘 하루 보이지 않음 정보
        $todayUnSee['todayUnSeeFl'] = $getData['todayUnSeeFl'];
        $todayUnSee['todayUnSeeBgColor'] = $getData['todayUnSeeBgColor'];
        $todayUnSee['todayUnSeeFontColor'] = $getData['todayUnSeeFontColor'];
        $todayUnSee['todayUnSeeAlign'] = $getData['todayUnSeeAlign'];

        $getData['imagePath'] = UserFilePath::data('multi_popup')->www().DS;
        $getData['widthCount'] = substr($getData['popupSlideCount'],0,1);
        $getData['heightCount'] = substr($getData['popupSlideCount'],1,1);
        $getData['popupSlideThumbW'] =  $getData['popupSlideViewW'] / $getData['widthCount'];
        $getData['image'] =json_decode($getData['popupImageInfo'], true);

        // --- 디자인 템플릿
        $this->getView()->setPageName('popup/multi/layer'); //레이어팝업고정
        $this->setData('todayUnSee', $todayUnSee);
        $this->setData('data', $getData);
        $this->setData('popupCode', $designMultiPopup->popupCodePrefix . $getData['popupKindFl'] . '_' . $getData['sno']);
        $addScript[] =  'jquery/jquery-cookie/jquery.cookie.js';
        if (FileHandler::isExists( USERPATH_SKIN.'js/bxslider/dist/jquery.bxslider.min.js')) {
            $addScript[] =  'bxslider/dist/jquery.bxslider.min.js';
        }
        if (FileHandler::isExists( USERPATH_SKIN.'js/slider/slick/slick.js')) {
            $addScript[] =  'slider/slick/slick.js';
        }
        $this->addScript($addScript);
    }
}
