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

namespace Bundle\Controller\Mobile\Order;

use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertReloadException;
use Component\Cart\Cart;
use Exception;
use Message;
use Request;
use Session;

/**
 * 장바구니 처리 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CartPsController extends \Controller\Front\Order\CartPsController
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        parent::index();
    }
}
