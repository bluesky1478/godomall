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
namespace Bundle\Controller\Admin\Goods;

use Exception;
use Framework\Utility\ImageUtils;
use Globals;
use Request;

class DisplayConfigRelationController extends \Controller\Admin\Controller
{

    /**
     * 품절상품 진열 페이지
     * [관리자 모드] 메인 진열 리스트 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     */
    public function index()
    {

        // --- 메뉴 설정
        $this->callMenu('goods', 'displayConfig', 'relation');


        // --- 상품 아이콘 데이터
        try {

            $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');
            $themeDisplayType =  $display->themeDisplayType;
            unset($themeDisplayType['02']);
            unset($themeDisplayType['03']);
            unset($themeDisplayType['07']);

            $mobileThemeDisplayType =  $display->themeDisplayType;
            unset($mobileThemeDisplayType['03']);
            unset($mobileThemeDisplayType['05']);
            unset($mobileThemeDisplayType['08']);
            unset($mobileThemeDisplayType['10']);
            unset($mobileThemeDisplayType['07']);


            $data = $display->getDateRelationDisplay();

            // --- 이미지 설정 및 필요한 이미지만 추출
            $confImage = gd_policy('goods.image');
            ImageUtils::sortImageConf($confImage, array('detail', 'magnify','list'));

        } catch (Exception $e) {
            throw $e;
        }

        //복수선택형 체크 기본값 설정
        if(empty($data['checked']['detailSetButton'])){
            $data['checked']['detailSetButton']['12']['B'] = 'checked';
        }
        if(empty($data['checked']['detailSetPosition'])){
            $data['checked']['detailSetPosition']['12']['UB'] = 'checked';
        }
        if(empty($data['checked']['mobileDetailSetButton'])){
            $data['checked']['mobileDetailSetButton']['12']['B'] = 'checked';
        }
        if(empty($data['checked']['mobileDetailSetPosition'])){
            $data['checked']['mobileDetailSetPosition']['12']['UB'] = 'checked';
        }

        $this->setData('data',$data['data']);
        $this->setData('checked', $data['checked']);
        $this->setData('selected', $data['selected']);
        $this->setData('confImage', $confImage);
        $this->setData('themeDisplayField', $display->themeDisplayField);
        $this->setData('themeDisplayType', $themeDisplayType);
        $this->setData('mobileThemeDisplayType', $mobileThemeDisplayType);
        $this->setData('themeGoodsDiscount', $display->themeGoodsDiscount);
        $this->setData('themePriceStrike', $display->themePriceStrike);
        $this->setData('themeDisplayAddField', $display->themeDisplayAddField);
    }
}
