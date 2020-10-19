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

use Framework\StaticProxy\Proxy\Logger;
use UserFilePath;

class DaumSomeController extends \Controller\Front\Controller
{

    /**
     * 다음 부분상품 가져가기
     *
     * @author    lnjts
     * @version   1.0
     * @since     1.0
     * @copyright Copyright (c), Godosoft
     */

    public function index()
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: text/plain; charset=euc-kr");

        $dbUrl = \App::load('\\Component\\Worker\\DbUrl');

        $dbUrl->run(['site' => 'daum', 'mode' => 'summary', 'filename'=> 'daum_some', 'printFl' => true]);
        $dbUrl->deleteEpUpdate('daum');
        exit;
    }
/*
    public function after()
    {
        //update 후 데이터 삭제
        $dbUrl = \App::load('\\Component\\Worker\\DbUrl');



        //21시가 넘은 경우 마지막 부분상품 업데이트 이므로 전체 상품 업데이트   TODO:임시 / 배치작업 완료되면 적용용
       if(date('H') >= '21' ) {
            $dispatcher = \App::load('\\Framework\\Worker\\Dispatcher');
            $dispatcher->callWorkerBackground('\\Component\\Worker\\DbUrl', ['site' => 'daum', 'mode' => 'all']);
        }
    }*/

}
