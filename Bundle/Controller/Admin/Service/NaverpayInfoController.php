<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Service;

use Component\Godo\GodoGongjiServerApi;

class NaverpayInfoController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('service','pg','naverCheckout');

        $godo = new GodoGongjiServerApi();
        $info = $godo->godoRemotePage('marketing_naver_checkout');
        $this->setData('info',$info);
    }
}
