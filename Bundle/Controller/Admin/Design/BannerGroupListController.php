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
use Component\Category\CategoryAdmin;
use Request;

/**
 * 배너 그룹 리스트
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class BannerGroupListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        if ($this->menuType == 'mobile') {
            $this->callMenu('mobile', 'designSet', 'bannerGroupList');
        } else {
            $this->callMenu('design', 'designConf', 'bannerGroupList');
        }

        $getValue = Request::get()->toArray();

        // 팝업 모드체크
        if (isset($getValue['popupMode']) === false) {
            $getValue['popupMode'] = false;
        }

        // --- 페이지 데이터
        try {
            //--- DesignBanner 정의
            $designBanner = new DesignBanner();
            $getData = $designBanner->getBannerGroupListData($this->menuType);

            // 카테고리 및 브랜드 정보
            $cate = new CategoryAdmin();
            $brand = new CategoryAdmin('brand');

            // 스킨 리스트 (해외몰 포함)
            $tmpSkinList = $designBanner->getSkinSimpleOverseasList(true, false);
            foreach ($tmpSkinList as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    // 적용 스킨 구분용
                    $designSkin[$sKey][$dKey] = $dVal['skinDesc'];
                    $selected = '';

                    // 디자인 스킨 검색용
                    if ($dVal['skinLive'] === true && $dVal['skinWork'] === true){
                        $dVal['skinDesc'] .= ' - ' . __('사용/작업중인스킨');
                    } elseif ($dVal['skinLive'] === true && $dVal['skinWork'] === false){
                        $dVal['skinDesc'] .= ' - ' . __('사용중인스킨');
                    } elseif ($dVal['skinLive'] === false && $dVal['skinWork'] === true){
                        $dVal['skinDesc'] .= ' - ' . __('작업중인스킨');
                    }

                    if(!empty($dVal['skinKind']))   $dVal['skinDesc'] .= ' [' . $dVal['skinKind'] . ']';

                    $skinKey = $sKey . STR_DIVISION . $dKey;
                    $skinList[$sKey][] = '<option value="' . $skinKey . '" ' . $getData['selected']['skinName'][$skinKey] . ' ' . $selected . '>' . $dVal['skinDesc'] . '</option>';
                    $skinList['all'][] = '<option value="' . $skinKey . '" ' . $getData['selected']['skinName'][$skinKey] . ' ' . $selected . '>' . $designBanner->deviceType[$sKey] . ' : ' . $dVal['skinDesc'] . '</option>';
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        //--- 관리자 디자인 템플릿
        if (gd_isset($getValue['popupMode']) === 'yes') {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        } else {
            $this->getView()->setDefine('layoutMenu', 'menu_design.php');
        }

        $this->addCss(['design.css']);
        $this->addScript([
            'jquery/jstree/jquery.tree.js',
            'jquery/jstree/plugins/jquery.tree.contextmenu.js',
            'design/designTree.js',
            'design/design.js',
            'jquery/jquery.multi_select_box.js',
        ]);

        $this->setData($getData);
        $this->setData('skinList', $skinList);
        $this->setData('designSkin', $designSkin);
        $this->setData('cate', $cate);
        $this->setData('brand', $brand);
        $this->setData('bannerGroupTypeFl', $designBanner->bannerGroupTypeFl);
        $this->setData('bannerDeviceType', $designBanner->deviceType);
        $this->setData('popupMode', $getValue['popupMode']);
        $this->setData('req', $getValue);
    }
}
