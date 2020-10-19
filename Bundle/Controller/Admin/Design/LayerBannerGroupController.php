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
use Component\Design\DesignBanner;
use Component\Category\CategoryAdmin;
use Globals;
use Request;

/**
 * 팝업 리스트
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerBannerGroupController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 페이지 데이터
        try {
            //--- DesignBanner 정의
            $designBanner = new DesignBanner();

            // GET 데이터
            $getValue = Request::get()->toArray();

            // 데이터
            $checked = [];
            $selected = [];
            if (isset($getValue['sno']) === true) {
                $mode = 'banner_group_modify';
                $modeTxt = '수정';
                $getData = $designBanner->getBannerGroupDetailData($getValue['sno']);
                $getData['selectedSkinName'] = $getData['skinName'];
            } else {
                $mode = 'banner_group_register';
                $modeTxt = '등록';
                $getData = [];
                if (empty($this->menuType) === true) {
                    $getData['bannerGroupDeviceType'] = 'front';
                } else {
                    $getData['bannerGroupDeviceType'] = $this->menuType;
                }
                $getData['bannerGroupType'] = 'banner';
            }

            // 카테고리 및 브랜드 정보
            $cate = new CategoryAdmin();
            $brand = new CategoryAdmin('brand');

            // 스킨 리스트
            $tmpSkinList = $designBanner->getSkinSimpleOverseasList(true, false);
            foreach ($tmpSkinList as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    // 수정인 경우 선택된 스킨 정보만 필요
                    if ($mode === 'banner_group_modify') {
                        if ($sKey === $getData['bannerGroupDeviceType'] && $dKey === $getData['skinName']) {
                            $getData['selectedSkinName'] = $dVal['skinDesc'];
                        }
                    } else {
                        // selected 처리
                        $tmpSelected = '';
                        if ($dVal['skinWork'] === true) {
                            $tmpSelected = 'selected="selected"';
                        }

                        // 사용스킨 / 작업스킨 구분
                        if ($dVal['skinLive'] === true && $dVal['skinWork'] === true){
                            $dVal['skinDesc'] .= ' - ' . __('사용/작업중인스킨');
                        } elseif ($dVal['skinLive'] === true && $dVal['skinWork'] === false){
                            $dVal['skinDesc'] .= ' - ' . __('사용중인스킨');
                        } elseif ($dVal['skinLive'] === false && $dVal['skinWork'] === true){
                            $dVal['skinDesc'] .= ' - ' . __('작업중인스킨');
                        }

                        if(!empty($dVal['skinKind']))   $dVal['skinDesc'] .= ' [' . $dVal['skinKind'] . ']';

                        // select option
                        $skinList[$sKey][] = '<option value="' . $dKey . '" ' . $tmpSelected . '>' . $dVal['skinDesc'] . '</option>';
                    }
                }
            }

            $checked['bannerGroupDeviceType'][$getData['bannerGroupDeviceType']] = 'checked="checked"';

            $selected['bannerGroupType'][$getData['bannerGroupType']] = 'selected="selected"';
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('mode', $mode);
        $this->setData('modeTxt', $modeTxt);
        $this->setData('skinList', $skinList);
        $this->setData('data', gd_htmlspecialchars($getData));
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('cate', $cate);
        $this->setData('brand', $brand);
        $this->setData('bannerGroupTypeFl', $designBanner->bannerGroupTypeFl);
        $this->setData('bannerDeviceType', $designBanner->deviceType);
        $this->setData('popupMode', 'yes');
    }
}
