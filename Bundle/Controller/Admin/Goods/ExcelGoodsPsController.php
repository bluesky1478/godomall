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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Goods;

use Request;

class ExcelGoodsPsController extends \Controller\Admin\Controller
{

    /**
     * 상품 엑셀 처리 페이지
     * [관리자 모드] 상품 엑셀 처리 페이지
     *
     * @author    artherot
     * @version   1.0
     * @since     1.0
     *
     * @param array $get
     * @param array $post
     * @param array $files
     *
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);

        // --- 모듈 호출
        $postValue = Request::post()->toArray();


        // --- 엑셀 class
        $excel = \App::load('Component\\Excel\\ExcelGoodsConvert');

        switch ($postValue['mode']) {
            // 상품 엑셀 샘플 다운로드
            case 'excel_sample_down':
                $this->streamedDownload('상품샘플파일.xls');
                $excel->setExcelGoodsSampleDown();
                exit();
                break;

            // 상품 엑셀 다운로드
            case 'excel_down':
                $this->streamedDownload('상품다운로드.xls');
                $excel->setExcelGoodsDown($postValue);
                exit;
                break;

            // 상품 엑셀 업로드
            case 'excel_up':
                $this->streamedDownload('상품업로드결과.xls');
                $excel->setExcelGoodsUp($postValue['modDtUse']);
                exit();
                break;
        }

    }
}
