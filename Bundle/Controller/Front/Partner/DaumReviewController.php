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

namespace Bundle\Controller\Front\Partner;


use Component\Board\Board;
use Component\Board\BoardList;
use Framework\StaticProxy\Proxy\UserFilePath;

class DaumReviewController extends \Controller\Front\Controller
{
    public function index()
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: text/plain; charset=euc-kr");

        $dbUrl = \App::load('\\Component\\Worker\\DbUrl');
        $path = UserFilePath::data('dburl').'/daum/daum_review';
        $isBegin = file_exists($path) == false; //첫데이터 전달 시 전체 데이터
        $dbUrl->run(['site' => 'daum', 'mode' => 'review', 'filename' => 'daum_review', 'printFl' =>$isBegin == false,'isBegin' => $isBegin]);
        exit;
       // $dbUrl->deleteEpUpdate('daum');
    }
}
