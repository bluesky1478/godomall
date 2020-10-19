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
namespace Bundle\Widget\Front\Proc;

use App;

/**
 * 움직이는 배너
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */

class SliderBannerWidget extends \Widget\Front\Widget
{

    public function index()
    {
    	// 움직이는 배너 정보
	    $designBanner = \App::load('\\Component\\Design\\DesignBanner');
        $bannerDeviceType = $this->getRootDirecotory();
        $skinName = \Globals::get('gSkin.' . $bannerDeviceType . 'SkinName');
	    $getData = $designBanner->getSliderBannerData($this->getData('bannerCode'), $bannerDeviceType, $skinName);

        // 정보 여부 체크
        if (empty($getData) === true) {
            return false;
        }

        // 필요 정보 가공
        $tmp['buttonConf'] = json_decode($getData['bannerButtonConf'], true);
        $tmp['bannerInfo'] = json_decode($getData['bannerInfo'], true);
        $tmp['bannerSliderConf'] = json_decode($getData['bannerSliderConf'], true);
        $tmp['bannerFolder'] = $tmp['bannerInfo']['bannerFolder'];
        gd_isset($tmp['buttonConf']['side']['useFl'], 'y');
        gd_isset($tmp['buttonConf']['side']['activeColor'], '#ffffff');
        gd_isset($tmp['buttonConf']['page']['useFl'], 'y');
        gd_isset($tmp['buttonConf']['page']['activeColor'], '#ffffff');
        gd_isset($tmp['buttonConf']['page']['inactiveColor'], '#ffffff');
        gd_isset($tmp['buttonConf']['page']['size'], '8');
        gd_isset($tmp['buttonConf']['page']['radius'], '100');
        unset($tmp['bannerInfo']['bannerFolder']);

        // 정보 여부 체크
        if (empty($tmp['bannerInfo']) === true) {
            return false;
        }

        // 움직이는 배너 정보 세팅
        $setData = [];
        $setData['bannerCode'] = $getData['bannerCode'];
        $setData['bannerUseFl'] = $getData['bannerUseFl'];
        $setData['bannerSpeed'] = gd_isset($tmp['bannerSliderConf']['speed'], 300);
        $setData['bannerTime'] = gd_isset($tmp['bannerSliderConf']['time'], 3);
        $setData['bannerSliderTime'] = (gd_isset($tmp['bannerSliderConf']['time'], 3) * 1000);
        $setData['bannerEffect'] = gd_isset($tmp['bannerSliderConf']['effect'], 'slide');
        $setData['bannerSize'] = json_decode($getData['bannerSize'], true);
        $setData['bannerSize']['sizeType'] = gd_isset($setData['bannerSize']['sizeType'], 'px');
        $setData['bannerPageButton'] = $tmp['buttonConf']['page'];
        $setData['bannerSideButton'] = $tmp['buttonConf']['side'];
        $setData['bannerInfo'] = [];
        $setData['bannerImgInfo'] = [];

        // 배너 내용
        foreach ($tmp['bannerInfo'] as $key => $info) {
            // 이미지가 없거나, 배너 폴더명이 없다면 제외
            if (empty($tmp['bannerFolder']) === true || empty($info['bannerImage']) === true) {
                continue;
            }

            // 이미지별 노출설정 체크해서 제외
            if ($info['bannerImageUseFl'] == 'n') {
                continue;
            }

            // 이미지별 노출기간 설정 체크해서 제외
            if ($info['bannerImagePeriodFl'] == 'y') {
                $thisDate = date('Y-m-d H:i:s');
                $bannerImagePeriodSDate = $info['bannerImagePeriodSDate'];
                $bannerImagePeriodEDate = $info['bannerImagePeriodEDate'];
                if ($bannerImagePeriodSDate > $thisDate || $bannerImagePeriodEDate < $thisDate) {
                    continue;
                }
            }
            // 이미지 Tag 생성
            $tmpImage = gd_html_banner_image(($getData['bannerImagePath'] . $tmp['bannerFolder'] . '/' . $info['bannerImage']), $info['bannerImageAlt']);
            $tmpTarget = '';
            if (empty($info['bannerLink']) === false) {
                if (empty($info['bannerTarget']) === false) {
                    $tmpTarget = ' target="' . $info['bannerTarget'] . '" ';
                }
                $setData['bannerInfo'][] = '<a href="' . $info['bannerLink'] . '" ' . $tmpTarget . '>' . $tmpImage . '</a>';
            } else {
                $setData['bannerInfo'][] = $tmpImage;
            }

            //네비게이션 활성
            if (empty($info['bannerNavActiveImage']) === false) {
                $setData['bannerImgInfo'][$key]['act']['img'] = \UserFilePath::data('skin', $getData['bannerImagePath']  . DS . $tmp['bannerFolder'] . DS . $info['bannerNavActiveImage'])->www();
                $setData['bannerImgInfo'][$key]['act']['width'] = $info['bannerNavActiveW'];
                $setData['bannerImgInfo'][$key]['act']['height'] = $info['bannerNavActiveH'];
            }
            //네비게이션 비활성
            if (empty($info['bannerNavInactiveImage']) === false) {
                $setData['bannerImgInfo'][$key]['inact']['img'] = \UserFilePath::data('skin', $getData['bannerImagePath']  . DS . $tmp['bannerFolder'] . DS . $info['bannerNavInactiveImage'])->www();
                $setData['bannerImgInfo'][$key]['inact']['width'] = $info['bannerNavInactiveW'];
                $setData['bannerImgInfo'][$key]['inact']['height'] = $info['bannerNavInactiveH'];
            }
        }
        if (empty($setData['bannerInfo']) === true) {
            $setData['bannerUseFl'] = 'n';
        }
        unset($tmp, $tmpImage, $tmpTarget);

        // 노출 기간 체크
        if ($getData['bannerPeriodOutputFl'] === 'y') {
            $thisDate = date('Y-m-d H:i:s');
            $bannerPeriodSDate = $getData['bannerPeriodSDate'] . ' ' . $getData['bannerPeriodSTime'] . ':00';
            $bannerPeriodEDate = $getData['bannerPeriodEDate'] . ' ' . $getData['bannerPeriodETime'] . ':00';
            if ($bannerPeriodSDate > $thisDate || $bannerPeriodEDate < $thisDate) {
                $setData['bannerUseFl'] = 'n';
            }
        }

        // 데이터 세팅
        $this->setData($setData);
    }
}
