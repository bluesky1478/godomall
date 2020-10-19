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

class PaycoAllController extends \Controller\Front\Controller
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
        header("Content-Type: text/plain; charset=utf-8");

        if (FileHandler::isFile(UserFilePath::data('dburl').'/payco/payco_all') === true) {
            foreach(FileUtils::readFileStream(UserFilePath::data('dburl').'/payco/payco_all') as $line)
            {
                echo $line. chr(13) . chr(10);
            }
        } else {
            if (FileHandler::isFile(UserFilePath::data('dburl').'/payco/payco_all_back_up') === true) {
                foreach(FileUtils::readFileStream(UserFilePath::data('dburl').'/payco/payco_all_back_up') as $line)
                {
                    echo $line. chr(13) . chr(10);
                }
            }
        }

        exit;
    }

}

