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
 * 배너 리스트
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class BannerListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        if ($this->menuType == 'mobile') {
            $this->callMenu('mobile', 'designSet', 'bannerList');
        } else {
            $this->callMenu('design', 'designConf', 'bannerList');
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
            $getData = $designBanner->getBannerListData($this->menuType);

            // 배너 그룹 리스트
            $tmpGroupData = $designBanner->getBannerGroupData();
            foreach ($tmpGroupData as $sKey => $gVal) {
                $tmpDeviceType = $designBanner->deviceType[$gVal['bannerGroupDeviceType']];
                $tmpGroupType = $designBanner->bannerGroupTypeFl[$gVal['bannerGroupType']];
                $tmpGroupDesc1 = '[' . $gVal['skinName'] . '] ' . $tmpGroupType . ' - ' .  $gVal['bannerGroupName'];
                $tmpGroupDesc2 = $tmpDeviceType . ' : [' . $gVal['skinName'] . '] ' . $tmpGroupType . ' - ' .  $gVal['bannerGroupName'];
                $bannerGroup[$gVal['bannerGroupDeviceType']][] = '<option value="' . $gVal['sno'] . '" ' . $getData['selected']['bannerGroup'][$gVal['sno']] . '>' . $tmpGroupDesc1 . '</option>';
                $bannerGroup['all'][] = '<option value="' . $gVal['sno'] . '" ' . $getData['selected']['bannerGroup'][$gVal['sno']] . '>' . $tmpGroupDesc2 . '</option>';
            }

            // 스킨 리스트
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
                    if($skinKey == $getValue['skinName']) {
                        $selected = ' selected = "selected"';
                    }
                    $skinList[$sKey][] = '<option value="' . $skinKey . '" ' . $getData['selected']['skinName'][$skinKey] . ' ' . $selected . '>' . $dVal['skinDesc'] . '</option>';
                    $skinList['all'][] = '<option value="' . $skinKey . '" ' . $getData['selected']['skinName'][$skinKey] . ' ' . $selected . '>' . $designBanner->deviceType[$sKey] . ' : ' . $dVal['skinDesc'] . '</option>';
                }
            }

            // 적용 스킨에 따른 배너 그룹 json data
            $setData = [];
            $escapeStrings = ['\\', '"'];
            $replacements = ['\\\\', '\"'];
            foreach ($tmpGroupData as $pKey => $pVal) {
                if (empty($pVal['skinName']) === true || empty($pVal['bannerGroupDeviceType']) === true) {
                    continue;
                }
                $gKey = $pVal['bannerGroupDeviceType'] . STR_DIVISION . $pVal['skinName'];
                $setData[$gKey][] = [
                    'bannerGroupCode' => $pVal['bannerGroupCode'],
                    'bannerGroupName' => str_replace($escapeStrings, $replacements, $designBanner->bannerGroupTypeFl[$pVal['bannerGroupType']] . ' - ' . $pVal['bannerGroupName']),
                ];
            }
            $jsonGroupData = json_encode($setData, JSON_HEX_APOS | JSON_HEX_QUOT);
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
        $this->setData('jsonGroupData', $jsonGroupData);
        $this->setData('bannerGroupTypeFl', $designBanner->bannerGroupTypeFl);
        $this->setData('bannerDeviceType', $designBanner->deviceType);
        $this->setData('bannerTargetKindFl', $designBanner->bannerTargetKindFl);
        $this->setData('popupMode', $getValue['popupMode']);
        $this->setData('req', $getValue);
    }
}
