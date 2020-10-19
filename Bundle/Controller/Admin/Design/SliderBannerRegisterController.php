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
use Globals;
use Request;

/**
 * 움직이는 배너 등록 수정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SliderBannerRegisterController extends \Controller\Admin\Controller
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

            // 스킨 리스트
            //$skinList = $designBanner->getSkinSimpleList();

            // 기본값 정의
            $getData = [];
            DBTableField::setDefaultData('tableDesignSliderBanner', $getData);

            // 데이터
            $checked = [];
            $selected = [];
            if (isset($getValue['sno']) === true) {
                $mode = 'modifySliderBanner';
                $modeTxt = __('수정');
                $getData = $designBanner->getSliderBannerDetailData($getValue['sno']);
                $getData['selectedSkinName'] = $getData['skinName'];
                $bannerInfo = json_decode($getData['bannerInfo'], true);
                $bannerButtonConf = json_decode($getData['bannerButtonConf'], true);
                $getData['bannerFolder'] = $bannerInfo['bannerFolder'];
                $getData['bannerSize'] = json_decode($getData['bannerSize'], true);
                $getData['bannerSliderConf'] = json_decode($getData['bannerSliderConf'], true);
                $getData['sideButton']['useFl'] = gd_isset($bannerButtonConf['side']['useFl']);
                $getData['sideButton']['activeColor'] = gd_isset($bannerButtonConf['side']['activeColor']);
                $getData['pageButton']['useFl'] = gd_isset($bannerButtonConf['page']['useFl']);
                $getData['pageButton']['activeColor'] = gd_isset($bannerButtonConf['page']['activeColor']);
                $getData['pageButton']['inactiveColor'] = gd_isset($bannerButtonConf['page']['inactiveColor']);
                $getData['pageButton']['size'] = gd_isset($bannerButtonConf['page']['size']);
                $getData['pageButton']['radius'] = gd_isset($bannerButtonConf['page']['radius']);
                unset($bannerInfo['bannerFolder'], $bannerButtonConf);

                $i = 0;
                $getData['bannerInfo'] = [];
                foreach ($bannerInfo as $info) {
                    $getData['bannerInfo'][$i]['bannerImage'] = $info['bannerImage'];
                    $getData['bannerInfo'][$i]['bannerLink'] = $info['bannerLink'];
                    $getData['bannerInfo'][$i]['bannerTarget'] = $info['bannerTarget'];
                    $getData['bannerInfo'][$i]['bannerImageAlt'] = $info['bannerImageAlt'];

                    $getData['bannerInfo'][$i]['bannerNavActiveImage'] = $info['bannerNavActiveImage'];
                    if (empty($info['bannerNavActiveImage']) === false) $getData['bannerInfo'][$i]['bannerNavActiveImageTmp'] = \UserFilePath::data('skin', $getData['bannerImagePath']  . DS . $getData['bannerFolder'] . DS . $info['bannerNavActiveImage'])->www();
                    $getData['bannerInfo'][$i]['bannerNavActiveW'] = $info['bannerNavActiveW'];
                    $getData['bannerInfo'][$i]['bannerNavActiveH'] = $info['bannerNavActiveH'];
                    $getData['bannerInfo'][$i]['bannerNavInactiveImage'] = $info['bannerNavInactiveImage'];
                    if (empty($info['bannerNavInactiveImage']) === false) $getData['bannerInfo'][$i]['bannerNavInactiveImageTmp'] = \UserFilePath::data('skin', $getData['bannerImagePath'] . DS . $getData['bannerFolder'] . DS . $info['bannerNavInactiveImage'])->www();
                    $getData['bannerInfo'][$i]['bannerNavInactiveW'] = $info['bannerNavInactiveW'];
                    $getData['bannerInfo'][$i]['bannerNavInactiveH'] = $info['bannerNavInactiveH'];

                    $getData['bannerInfo'][$i]['bannerImageUseFl'] = gd_isset($info['bannerImageUseFl'], 'y');
                    $getData['bannerInfo'][$i]['bannerImagePeriodFl'] = gd_isset($info['bannerImagePeriodFl'], 'n');
                    $getData['bannerInfo'][$i]['bannerImagePeriodSDate'] = gd_isset($info['bannerImagePeriodSDate'], '');
                    $getData['bannerInfo'][$i]['bannerImagePeriodEDate'] = gd_isset($info['bannerImagePeriodEDate'], '');
                    $i++;
                }

                // --- 메뉴 설정
                if ($this->menuType == 'mobile') {
                    $this->callMenu('mobile', 'designSet', 'sliderBannerModify');
                } else {
                    $this->callMenu('design', 'designConf', 'sliderBannerModify');
                }
            } else {
                $mode = 'registerSliderBanner';
                $modeTxt = '등록';

                // --- 메뉴 설정
                if ($this->menuType == 'mobile') {
                    $this->callMenu('mobile', 'designSet', 'sliderBannerRegister');
                    $getData['bannerDeviceType'] = $this->menuType;
                } else {
                    $this->callMenu('design', 'designConf', 'sliderBannerRegister');
                    $getData['bannerDeviceType'] = 'front';
                }
            }

            // 스킨 리스트
            $checkSkin = false;
            $tmpSkinList = $designBanner->getSkinSimpleOverseasList(true, false);
            foreach ($tmpSkinList as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    // selected 처리
                    $tmpSelected = '';
                    if ($mode === 'modifySliderBanner') {
                        if ($sKey === $getData['bannerDeviceType'] && $dKey === $getData['skinName']) {
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
            if ($mode === 'modifySliderBanner' && $checkSkin === false) {
                $skinList[$getData['bannerDeviceType']][] = '<option value="' . $getData['skinName'] . '" selected="selected">' . $getData['skinName'] . ' [' . __('스킨정보없음') . ']</option>';
            }

            // 기본 값 처리
            gd_isset($getData['bannerDeviceType'], 'front');
            gd_isset($getData['skinName'], Globals::get('gSkin.frontSkinWork')); // 스킨명은 현재 작업스킨명을 기준으로
            gd_isset($getData['bannerSliderConf']['speed'], 1300);
            gd_isset($getData['bannerSliderConf']['time'], 3);
            gd_isset($getData['bannerSliderConf']['effect'], 'slide');
            gd_isset($getData['sideButton']['useFl'], 'y');
            gd_isset($getData['sideButton']['activeColor'], '#ffffff');
            gd_isset($getData['pageButton']['useFl'], 'y');
            gd_isset($getData['pageButton']['activeColor'], '#ffffff');
            gd_isset($getData['pageButton']['inactiveColor'], '#ffffff');
            gd_isset($getData['pageButton']['size'], '8');
            gd_isset($getData['pageButton']['radius'], '100');

            // 기본 이미지 처리
            if (empty(gd_isset($getData['bannerSize'])) === true) {
                $getData['bannerSize']['width'] = '600';
                $getData['bannerSize']['height'] = '384';
            }
            if (empty($getData['bannerSize']['sizeType']) === true) {
                $getData['bannerSize']['sizeType'] = 'px';
            }

            // 기본 이미지 처리
            if (empty(gd_isset($getData['bannerInfo'])) === true) {
                for ($i = 0; $i <= 2; $i++) {
                    $getData['bannerInfo'][$i]['bannerImage'] = 'img/godo5_banner_0' . ($i + 1) . '.jpg';
                    $getData['bannerInfo'][$i]['bannerLink'] = '';
                    $getData['bannerInfo'][$i]['bannerTarget'] = '';
                    $getData['bannerInfo'][$i]['bannerImageAlt'] = '';
                }
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
            $checked['bannerDeviceType'][$getData['bannerDeviceType']] =
            $checked['bannerSliderConf']['effect'][$getData['bannerSliderConf']['effect']] =
            $checked['sideButton']['useFl'][$getData['sideButton']['useFl']] =
            $checked['pageButton']['useFl'][$getData['pageButton']['useFl']] =
            $checked['pageButton']['size'][$getData['pageButton']['size']] =
            $checked['pageButton']['radius'][$getData['pageButton']['radius']] =
            $checked['bannerPeriodOutputFl'][$getData['bannerPeriodOutputFl']] = 'checked="checked"';

            $selected['sizeType'][$getData['bannerSize']['sizeType']] = 'selected="selected"';
        } catch (\Exception $e) {
            //echo $e->getMessage();
            throw new AlertBackException($e->getMessage());
        }

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
            'design/design.js',
            'jquery/colorpicker-master/jquery.colorpicker.js',
        ]);

        $this->setData('mode', $mode);
        $this->setData('modeTxt', $modeTxt);
        $this->setData('skinList', $skinList);
        $this->setData('data', gd_htmlspecialchars($getData));
        $this->setData('bannerSliderTime', $designBanner->bannerSliderTime);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('bannerTargetKindFl', $designBanner->bannerTargetKindFl);
        $this->setData('bannerDeviceType', $designBanner->deviceType);
        $this->setData('adminList', UrlUtils::getAdminListUrl());
    }
}
