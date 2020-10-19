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

use Framework\Debug\Exception\Except;
use Exception;
use Globals;
use Request;

class DisplayConfigGoodsController extends \Controller\Admin\Controller
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
        $this->callMenu('goods', 'displayConfig', 'goods');

        try {

            $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');
            $data = $display->getDateGoodsDisplay();

            // 취소선 추가 설정 값이 아예 없는경우
            if (!$data['data']['goodsDisplayStrikeField']) {
                $data['data']['goodsDisplayStrikeField']['pc'][] = 'default';
                $data['data']['goodsDisplayStrikeField']['pc'][] = 'fixedPrice';
                $data['data']['goodsDisplayStrikeField']['mobile'][] = 'default';
                $data['data']['goodsDisplayStrikeField']['mobile'][] = 'fixedPrice';
            }
        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('data', $data['data']);
        $this->setData('fieldList', $data['fieldList']);
        $this->setData('addFieldList', $data['addFieldList']);
        $this->setData('strikeFieldList', $data['strikeFieldList']);
        $this->setData('themeGoodsDiscount', $data['themeGoodsDiscount']);
        $this->setData('checked', $data['checked']);

    }
}
