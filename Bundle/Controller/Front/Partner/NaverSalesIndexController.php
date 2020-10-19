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

use Framework\Utility\FileUtils;
use UserFilePath;
use FileHandler;

class NaverSalesIndexController extends \Controller\Front\Controller
{

    /**
     * 네이버 판매지수 EP 페이지
     *
     * @author yoonar
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        if(\Request::get()->get('mode') === 'run') {
            $dbUrl = \App::load('Component\\Worker\\NaverDbUrl');
            $date = \Request::get()->get('date');
            $dbUrl->salesIndexInit($date);
            $return = $dbUrl->setSalesIndexData();
        }
        set_time_limit(RUN_TIME_LIMIT);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: text/plain; charset=utf-8");

        if (FileHandler::isFile(UserFilePath::data('dburl').'/naver/naver_sales_index.tsv') === true) {
            foreach(FileUtils::readFileStream(UserFilePath::data('dburl').'/naver/naver_sales_index.tsv') as $line)
            {
                echo $line. chr(13) . chr(10);
            }
        }
        exit;
    }

}
