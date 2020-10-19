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

use Framework\Utility\ImageUtils;
use Request;

/**
 * 테마 등록
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class DisplayConfigThemeRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        if (Request::get()->has('themeCd')) {
            $this->callMenu('goods', 'displayConfig', 'themeModify');
        } else {
            $this->callMenu('goods', 'displayConfig', 'themeRegister');
        }

        // --- 모듈 호출
        $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        $getValue = Request::get()->toArray();
        $addTheme = htmlspecialchars(Request::get()->get('addTheme'), ENT_QUOTES, 'UTF-8');

        try {
            $data = $display->getDataThemeConfig(Request::get()->get('themeCd'));
            $data['data']['themeCate'] = htmlspecialchars($data['data']['themeCate'], ENT_QUOTES, 'UTF-8');

            // --- 이미지 설정 및 필요한 이미지만 추출
            $confImage = gd_policy('goods.image');
            ImageUtils::sortImageConf($confImage, ['detail', 'magnify', 'list']);

            $imageType = $confImage['imageType'];
            foreach ($confImage as $k => $v) {
                foreach ($v as $key => $value) {
                    if (stripos($key, 'size') === 0) {
                        $confImage[$k][$key] = [$value];
                        if($imageType == 'fixed') {
                            array_push($confImage[$k][$key], $confImage[$k]['h' . $key]);
                            unset($confImage[$k]['h' . $key]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        $this->setData('data',$data['data']);
        $this->setData('openerMobileFl',$getValue['mobileFl']);
        $this->setData('addTheme',$addTheme);
        $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
        $this->setData('checked', $data['checked']);
        $this->setData('confImage', $confImage);
        $this->setData('themeCategory', $display->themeCategory);
        $this->setData('themeDisplayField', $display->themeDisplayField);
        $this->setData('themeDisplayType', $display->themeDisplayType);
        $this->setData('themeGoodsDiscount', $display->themeGoodsDiscount);
        $this->setData('themePriceStrike', $display->themePriceStrike);
        $this->setData('themeDisplayAddField', $display->themeDisplayAddField);
    }
}
