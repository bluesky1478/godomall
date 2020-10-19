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

class ExcelGoodsUpController extends \Controller\Admin\Controller
{
    /**
     * 상품 엑셀 업로드 페이지
     * [관리자 모드] 상품 엑셀 업로드 페이지
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
        $this->callMenu('goods', 'excel', 'excelUp');

        // --- 엑셀 설정
        $excel = \App::load('\\Component\\Excel\\ExcelDataConvert');

        $excelField = $excel->excelGoods();
        $excelField = $excel->excelGoodsExclude($excelField);

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('goods/excel_goods_up.php');

        // --- 관리자 디자인 템플릿
        $this->setData('excelField', $excelField);
    }
}
