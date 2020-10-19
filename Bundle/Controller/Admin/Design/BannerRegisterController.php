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
use Component\Design\DesignBanner;
use Component\Database\DBTableField;
use Framework\Utility\UrlUtils;
use Request;
use UserFilePath;
use FileHandler;

/**
 * 배너 등록 수정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class BannerRegisterController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        // 팝업 모드체크
        if (isset($getValue['popupMode']) === false) {
            $getValue['popupMode'] = false;
        }

        // --- 페이지 데이터
        try {
            //--- DesignBanner 정의
            $designBanner = new DesignBanner();
            $getGroupData = $designBanner->getBannerGroupData();

            // 기본값 정의
            $getData = [];
            DBTableField::setDefaultData('tableDesignBanner', $getData);

            // 데이터
            $checked = [];
            $selected = [];
            if (isset($getValue['sno']) === true) {
                $mode = 'modify';
                $modeTxt = '수정';
                $getData = $designBanner->getBannerDetailData($getValue['sno']);
                $getData['selectedSkinName'] = $getData['skinName'];
                $selected['bannerGroupCode'][$getData['bannerGroupCode']] =
                $selected['bannerTarget'][$getData['bannerTarget']] = 'selected="selected"';

                // 배너 정보
                if (empty($getData['bannerImage']) === false) {
                    $tmpBannerImage = UserFilePath::data('skin', $getData['bannerImagePath'] . $getData['bannerImage']);
                    if (FileHandler::isExists($tmpBannerImage)) {
                        $getImgData = getimagesize($tmpBannerImage);
                        $getData['bannerImageInfo']['width'] = $getImgData[0];
                        $getData['bannerImageInfo']['height'] = $getImgData[1];
                        $getData['bannerImageInfo']['size'] = FileHandler::getFileInfo($tmpBannerImage)->getSize();
                        $getData['bannerImageInfo']['mime'] = $getImgData['mime'] ?? FileHandler::getFileInfo($tmpBannerImage)->getExtension();
                    }
                }
                // --- 메뉴 설정
                if ($this->menuType == 'mobile') {
                    $this->callMenu('mobile', 'designSet', 'bannerModify');
                } else {
                    $this->callMenu('design', 'designConf', 'bannerModify');
                }
            } else {
                $mode = 'register';
                $modeTxt = '등록';
                // --- 메뉴 설정
                if ($this->menuType == 'mobile') {
                    $this->callMenu('mobile', 'designSet', 'bannerRegister');
                    $getData['bannerGroupDeviceType'] = $this->menuType;
                } else {
                    $this->callMenu('design', 'designConf', 'bannerRegister');
                    $getData['bannerGroupDeviceType'] = 'front';
                }
            }

            // 스킨 리스트
            $checkSkin = false;
            $tmpSkinList = $designBanner->getSkinSimpleOverseasList(true, false);
            foreach ($tmpSkinList as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    // selected 처리
                    $tmpSelected = '';
                    if ($mode === 'modify') {
                        if ($sKey === $getData['bannerGroupDeviceType'] && $dKey === $getData['skinName']) {
                            $tmpSelected = 'selected="selected"';
                            $getData['selectedSkinName'] = $dVal['skinDesc'];
                            $checkSkin = true;
                        }
                    } else {
                        if ($dVal['skinWork'] === true) {
                            $tmpSelected = 'selected="selected"';
                        }
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

            // garbage data 에 의한 스킨이 없는 경우
            if ($mode === 'modify' && $checkSkin === false) {
                $skinList[$getData['bannerGroupDeviceType']][] = '<option value="' . $getData['skinName'] . '" selected="selected">' . $getData['skinName'] . '[' . __('스킨정보없음') . ']</option>';
            }

            // 날짜 처리
            gd_isset($getData['bannerPeriodOutputFl'], 'y');
            $getData['bannerPeriodSDateY'] =
            $getData['bannerPeriodEDateY'] =
            $getData['bannerPeriodSDateT'] =
            $getData['bannerPeriodSTimeT'] =
            $getData['bannerPeriodEDateT'] =
            $getData['bannerPeriodETimeT'] = '';
            if ($getData['bannerPeriodOutputFl'] === 'y') {
                $getData['bannerPeriodSDateY'] = $getData['bannerPeriodSDate'] . ' ' . $getData['bannerPeriodSTime'];
                $getData['bannerPeriodEDateY'] = $getData['bannerPeriodEDate'] . ' ' . $getData['bannerPeriodETime'];
            } elseif ($getData['bannerPeriodOutputFl'] === 't') {
                $getData['bannerPeriodSDateT'] = $getData['bannerPeriodSDate'];
                $getData['bannerPeriodSTimeT'] = $getData['bannerPeriodSTime'];
                $getData['bannerPeriodEDateT'] = $getData['bannerPeriodEDate'];
                $getData['bannerPeriodETimeT'] = $getData['bannerPeriodETime'];
            }
            unset($getData['bannerPeriodSDate'], $getData['bannerPeriodEDate'], $getData['bannerPeriodSTime'], $getData['bannerPeriodETime']);

            $checked['bannerUseFl'][$getData['bannerUseFl']] =
            $checked['bannerGroupDeviceType'][$getData['bannerGroupDeviceType']] =
            $checked['bannerPeriodOutputFl'][$getData['bannerPeriodOutputFl']] = 'checked="checked"';

            // 적용 스킨에 따른 배너 그룹 json data
            $setData = [];
            $escapeStrings = ['\\', '"'];
            $replacements = ['\\\\', '\"'];
            foreach ($getGroupData as $pKey => $pVal) {
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
            //echo $e->getMessage();
            throw new AlertBackException($e->getMessage());
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

        $this->setData('mode', $mode);
        $this->setData('modeTxt', $modeTxt);
        $this->setData('skinList', $skinList);
        $this->setData('jsonGroupData', $jsonGroupData);
        $this->setData('data', gd_htmlspecialchars($getData));
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('bannerTargetKindFl', $designBanner->bannerTargetKindFl);
        $this->setData('bannerDeviceType', $designBanner->deviceType);
        $this->setData('popupMode', $getValue['popupMode']);
        $this->setData('adminList', UrlUtils::getAdminListUrl());
    }
}
