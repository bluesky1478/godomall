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

namespace Bundle\Controller\Front\Partner;


use UserFilePath;
use FileHandler;

/**
 * TargetingGates
 *
 * @package Bundle\Controller\Front\Partner
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class TgController extends \Controller\Front\Controller
{
    public function index()
    {
        set_time_limit(RUN_TIME_LIMIT);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: text/plain; charset=euc-kr");

        //        $dbUrl = \App::load('\\Component\\Worker\\DbUrl');
        //        $result = $dbUrl->run(
        //            [
        //                'site' => 'targetingGates',
        //                'mode' => 'all',
        //            ]
        //        );

        ini_set('memory_limit', '-1');

        $dbUrl = \App::load('Component\\Worker\\TargetingGatesDbUrl');
        $dbUrl->setFileConfig(
            [
                'site' => 'targetingGates',
                'mode' => 'all',
            ]
        );
        $result = $dbUrl->run();

        if ($result && FileHandler::isFile(UserFilePath::data('dburl') . '/targetingGates/targetingGates_all') === true) {
            readfile(UserFilePath::data('dburl') . '/targetingGates/targetingGates_all');
        }
        exit;
    }
}
