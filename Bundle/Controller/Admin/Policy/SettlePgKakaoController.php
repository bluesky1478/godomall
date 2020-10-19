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
namespace Bundle\Controller\Admin\Policy;

/**
 * 카카오페이 설정/관리
 * @author JAEWON NOH <nokoon@godo.co.kr>
 */
class SettlePgKakaoController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'settle', 'kakaopay');

        // --- 페이지 데이터
        try {
            // 카카오페이 정보
            $data = gd_policy('pg.kakaopay');
            $gift = \App::load('\\Component\\Gift\\Gift');

            $radioDisabled = '';
            if (empty($data) || $data['pgId'] == '') {
                $radioDisabled = 'disabled';
            }

            // 기본 값 처리
            gd_isset($data['useYn'], 'all');
            gd_isset($data['testYn'], 'Y');

            $checked = [];
            $checked['useYn'][$data['useYn']] = 'checked="checked"';
            $checked['testYn'][$data['testYn']] = 'checked="checked"';

            if ($data['exceptGoods']) {
                $data['exceptGoodsNo'] = $gift->viewGoodsData(implode(INT_DIVISION, $data['exceptGoods']));
            }

            if($data['exceptCategory']) {
                $data['exceptCateCd'] = $gift->viewCategoryData(implode(INT_DIVISION, $data['exceptCategory']));
            }

            if($data['exceptBrand']) {
                $data['exceptBrandCd'] = $gift->viewCategoryData(implode(INT_DIVISION, $data['exceptBrand']), 'brand');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);

        $this->setData('data', $data);
        $this->setData('radioDisabled', $radioDisabled);
        $this->setData('checked', $checked);
    }
}
