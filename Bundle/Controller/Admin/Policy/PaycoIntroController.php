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

namespace Bundle\Controller\Admin\Policy;

use Component\Godo\GodoGongjiServerApi;
use Request;

class PaycoIntroController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 고도 공지서버 모듈 호출
        try {
            // _GET 데이터
            $getValue = Request::get()->toArray();

            if (empty($getValue['menu']) === true) {
                throw new \Exception('NO_MEMU');
            }

            //--- 메뉴 설정
            $menu = explode('_', $getValue['menu']);
            $this->callMenu('policy', $menu[0], $menu[1]);
            unset($menu);

            // 고도 공지서버 모듈 호출
            $godoApi = new GodoGongjiServerApi();
            $contents = $godoApi->setGodoRemotePage('payco_info');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // 공용 페이지 사용
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/payco_intro.php');

        $this->setData('contents', $contents);
    }
}
