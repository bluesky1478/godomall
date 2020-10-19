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

use Component\Godo\GodoGongjiServerApi;
use Request;

/**
 * GodoPage Iframe 출력
 * @package Bundle\Controller\Admin\Share
 * @author  cjb3333
 */
class IframeGodoPageController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // 고도 공지서버 모듈 호출
        $godoApi = new GodoGongjiServerApi();
        $contents = $godoApi->setGodoRemotePage(Request::get()->get('menu'));

        // 데모샵인 경우 처리 내용
        $demo = \App::load('\\Component\\Godo\\GodoDemoApi');
        $contents = $demo->setGodoPageConvert(Request::get()->get('menu'), $contents);

        echo $contents;
        exit;
    }
}

