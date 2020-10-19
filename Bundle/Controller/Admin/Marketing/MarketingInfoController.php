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

namespace Bundle\Controller\Admin\Marketing;

use Component\Godo\GodoGongjiServerApi;
use Request;

/**
 * 마케팅 통합 안내 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MarketingInfoController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        try {
            // _GET 데이터
            $getValue = Request::get()->toArray();

            if (empty($getValue['menu']) === true) {
                throw new \Exception('NO_MEMU');
            }

            //--- 메뉴 설정
            $menu = explode('_', $getValue['menu']);
            $this->callMenu('marketing', $menu[0], $menu[1]);
            unset($menu);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // 공용 페이지 사용
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/marketing_info.php');
        $this->setData('menu', $getValue['menu']);
    }
}
