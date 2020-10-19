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
namespace Bundle\Controller\Admin\Order;

use Exception;
use Request;

class ExternalOrderRegisterController extends \Controller\Admin\Controller
{
    /**
     * 외부채널 주문 일괄등록
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @throws Except
     * @copyright ⓒ 2018, NHN godo: Corp.
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'externalOrder', 'externalOrderRegister');

    }
}
