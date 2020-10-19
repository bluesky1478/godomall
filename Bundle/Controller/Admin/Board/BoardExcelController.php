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

use Component\Board\ArticleListAdmin;
use Framework\Debug\Exception\AlertBackException;
use Request;

class BoardExcelController extends \Controller\Admin\Controller
{

    /**
     * Description
     */
    public function index()
    {
        try {
            $this->streamedDownload('board_' . Request::get()->get('bdId') . '.xls');
            $arrSnos = null;
            $downloadtype = Request::get()->get('downloadtype');
            if($downloadtype == 2) {
                $snos = Request::get()->get('snos');
                if ($snos) {
                    $arrSnos = explode('-', $snos);
                }
            }
            $articleList = new ArticleListAdmin(Request::get()->toArray());
            $getData = $articleList->getExcelList($arrSnos);
            $bdList = gd_isset($getData);
            include "_BoardExcelHtml.php";
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
