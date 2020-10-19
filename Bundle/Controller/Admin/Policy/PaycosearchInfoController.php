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

class PaycosearchInfoController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 고도 공지서버 모듈 호출
        try {
            $this->callMenu('policy', 'paycoSearch', 'info');
            $this->setData('menu', 'paycosearch_info');

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
