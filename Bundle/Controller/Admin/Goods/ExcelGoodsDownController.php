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

use Globals;

class ExcelGoodsDownController extends \Controller\Admin\Controller
{

    /**
     * 상품 엑셀 다운로드 페이지
     * [관리자 모드] 상품 엑셀 다운로드 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @param array $get
     * @param array $post
     * @param array $files
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {

        // --- 메뉴 설정
        $this->callMenu('goods', 'excel', 'excelDown');

        // --- 상품 설정
        $cate = \App::load('\\Component\\Category\\CategoryAdmin');
        $brand = \App::load('\\Component\\Category\\BrandAdmin');
        $excel = \App::load('\\Component\\Excel\\ExcelDataConvert');

        $excelField = $excel->excelGoods();

        // --- 관리자 디자인 템플릿
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('goods/excel_goods_down.php');

        $this->setData('cate', $cate);
        $this->setData('brand', $brand);
        $this->setData('excelField', $excelField);
    }
}
