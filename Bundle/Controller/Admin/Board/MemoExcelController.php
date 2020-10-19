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
namespace Bundle\Controller\Admin\Board;

use Framework\Debug\Exception\AlertBackException;
use Component\Memo\MemoAdmin;
use Request;
use Framework\Utility\Strings;

class MemoExcelController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {

        /**
         * 게시물엑셀
         *
         * @author sj
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // 화일명 prefix
        $this->streamedDownload('memo_' . Request::get()->get('bdId').'.xls');
        // --- 페이지 데이터
        try {
            $memoAdmin = new MemoAdmin(Request::get()->toArray());
            $arrSnos = null;
            $downloadtype = Request::get()->get('downloadtype');
            if($downloadtype == 4) {
                $snos = Request::get()->get('snos');
                if ($snos) {
                    $arrSnos = explode('-', $snos);
                }
            }

            $getData = $memoAdmin->getExcelList($arrSnos);
            $bdList = gd_isset($getData);
            include "_MemoExcelHtml.php";
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
