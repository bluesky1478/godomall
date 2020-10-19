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
use Request;

/**
 * 움직이는 배너 미리보기
 * @author Bag yj <kookoo135@godo.co.kr>
 */
class SliderBannerPreviewController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        $getValue = Request::get()->toArray();
        $designBanner = new DesignBanner();
        $getData = $designBanner->getSliderBannerDetailData($getValue['sno']);

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
        $setData['bannerDeviceType'] = $getData['bannerDeviceType'];
        $setData['bannerCode'] = $getData['bannerCode'];
        $setData['bannerUseFl'] = $getData['bannerUseFl'];
        $setData['bannerSpeed'] = gd_isset($tmp['bannerSliderConf']['speed'], 300);
        $setData['bannerTime'] = gd_isset($tmp['bannerSliderConf']['time'], 3);
        $setData['bannerSliderTime'] = (gd_isset($tmp['bannerSliderConf']['time'], 3) * 1000);
        $setData['bannerEffect'] = gd_isset($tmp['bannerSliderConf']['effect'], 'slide');
        $setData['bannerSize'] = json_decode($getData['bannerSize'], true);
        $setData['bannerPageButton'] = $tmp['buttonConf']['page'];
        $setData['bannerSideButton'] = $tmp['buttonConf']['side'];
        $setData['bannerInfo'] = [];

        // 배너 내용
        foreach ($tmp['bannerInfo'] as $key => $info) {
            // 이미지가 없거나, 배너 폴더명이 없다면 제외
            if (empty($tmp['bannerFolder']) === true || empty($info['bannerImage']) === true) {
                continue;
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
        unset($getData, $tmp, $tmpImage, $tmpTarget);

        $this->setData('setData', $setData);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
