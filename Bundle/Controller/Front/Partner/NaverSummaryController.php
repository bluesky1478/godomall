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

/**
 * 네이버 부분상품 가져가기
 *
 * @package Bundle\Controller\Front\Partner
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class NaverSummaryController extends \Controller\Front\Controller
{

    /**
     * {@inheritdoc}
     *
     */

    public function index()
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: text/plain; charset=utf-8");

        $dbUrl = \App::load('\\Component\\Worker\\DbUrl');
        $dbUrl->run(
            [
                'site' => 'naver',
                'mode' => 'summary',
                'printFl' => true,
            ]
        );

        $dbUrl->deleteEpUpdate('naver');
        exit;
    }

}
