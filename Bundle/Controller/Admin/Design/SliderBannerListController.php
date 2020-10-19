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

use Component\Design\DesignBanner;
use Request;

/**
 * 움직이는 배너 리스트
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SliderBannerListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        if ($this->menuType == 'mobile') {
            $this->callMenu('mobile', 'designSet', 'sliderBannerList');
        } else {
            $this->callMenu('design', 'designConf', 'sliderBannerList');
        }

        // --- 페이지 데이터
        try {
            //--- DesignBanner 정의
            $designBanner = new DesignBanner();
            // 노출기간 설정시 날자가 지난경우 미노출로 업데이트
            $designBanner->updateUseFl();
            $getData = $designBanner->getSliderBannerListData($this->menuType);
            $req = Request::get()->toArray();


            // 스킨 리스트
            $tmpSkinList = $designBanner->getSkinSimpleOverseasList(true, false);
            foreach ($tmpSkinList as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    // 적용 스킨 구분용
                    $designSkin[$sKey][$dKey] = $dVal['skinDesc'];
                    $skinKey = $sKey . STR_DIVISION . $dKey;
                    $selected = '';

                    if ($dVal['skinLive'] === true && $dVal['skinWork'] === true){
                        $dVal['skinDesc'] .= ' - ' . __('사용/작업중인스킨');
                        $selected = 'selected = "selected"';
                        $selectVal[$sKey] = $skinKey;
                    } elseif ($dVal['skinLive'] === true && $dVal['skinWork'] === false){
                        $dVal['skinDesc'] .= ' - ' . __('사용중인스킨');
                    } elseif ($dVal['skinLive'] === false && $dVal['skinWork'] === true){
                        $dVal['skinDesc'] .= ' - ' . __('작업중인스킨');
                        $selected = 'selected = "selected"';
                        $selectVal[$sKey] = $skinKey;
                    }

                    if(!empty($dVal['skinKind']))   $dVal['skinDesc'] .= ' [' . $dVal['skinKind'] . ']';

                    $skinList[$sKey][] = '<option value="' . $skinKey . '" ' . $getData['selected']['skinName'][$skinKey] . ' ' . $selected . '>' . $dVal['skinDesc'] . '</option>';
                    $skinList['all'][] = '<option value="' . $skinKey . '" ' . $getData['selected']['skinName'][$skinKey] . ' ' . $selected . '>' . $designBanner->deviceType[$sKey] . ' : ' . $dVal['skinDesc'] . '</option>';
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layoutMenu', 'menu_design.php');

        $this->addCss(['design.css']);
        $this->addScript([
            'jquery/jstree/jquery.tree.js',
            'jquery/jstree/plugins/jquery.tree.contextmenu.js',
            'design/designTree.js',
            'design/design.js',
        ]);

        $this->setData($getData);
        $this->setData('skinList', $skinList);
        $this->setData('selectVal', $selectVal);
        $this->setData('getSkinName', \Request::get()->get('skinName'));
        $this->setData('designSkin', $designSkin);
        $this->setData('bannerDeviceType', $designBanner->deviceType);
        $this->setData('bannerTargetKindFl', $designBanner->bannerTargetKindFl);
        $this->setData('req', $req);
    }
}
