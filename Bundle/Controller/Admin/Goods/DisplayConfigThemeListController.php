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

/**
 * 테마 리스트 페이지
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class DisplayConfigThemeListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'displayConfig', 'themeList');

        // --- 모듈 호출
        $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');

        try {
            $getData = $display->getAdminListDisplayThemeConfig();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

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

            foreach ($confImage as $key => $val) {
                $useImage[$key] = $val['text'] . ' - ' . $val['size1'][0] . ' pixel';
                if($confImage['imageType'] == 'fixed') {
                    $useImage[$key] .= ' / 세로 ' . $val['size1'][1] . ' pixel';
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
        $this->setData('useImage', $useImage);
        $this->setData('themeCategory', $display->themeCategory);
        $this->setData('confImage', $confImage);
    }
}
