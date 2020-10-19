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

use UserFilePath;
use FileHandler;

class DaumAllController extends \Controller\Front\Controller
{

    /**
     * 네이버 전체상품 가져가기
     *
     * @author artherot, sunny
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        set_time_limit(RUN_TIME_LIMIT);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: text/plain; charset=euc-kr");
        if (FileHandler::isFile(UserFilePath::data('dburl').'/daum/daum_all') === true) {
            readfile(UserFilePath::data('dburl').'/daum/daum_all');
        }


//        $dispatcher = \App::load('\\Framework\\Worker\\Dispatcher');  //TODO:배치로 실행?
//        $dispatcher->callWorkerBackground('\\Component\\Worker\\DbUrl', ['site' => 'daum', 'mode' => 'all']);
        exit;
    }

}
