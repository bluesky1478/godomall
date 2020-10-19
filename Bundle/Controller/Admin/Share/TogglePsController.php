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

namespace Bundle\Controller\Admin\Share;

use Exception;
use Globals;
use Request;

class TogglePsController extends \Controller\Admin\Controller
{
    public function index()
    {

        /**
         * DepthToggle 저장
         * [4Depth 열기닫음저장]
         * @author cjb3333
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        $getValue = Request::get()->toArray();

        $toggleKey = $getValue['toggleName']."_".$getValue['scmNo']; //toggleName+scmNo
        $toggle[$toggleKey] = $getValue['hidden'];

        try {
            gd_set_policy('display.toggle', $toggle);

        } catch (Exception $e) {
            throw $e;
        }
        exit;

    }
}
