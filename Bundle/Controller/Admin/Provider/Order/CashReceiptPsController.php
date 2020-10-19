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
namespace Bundle\Controller\Admin\Provider\Order;

use Component\Payment\CashReceipt;
use Message;
use Request;

/**
 * 현금영수증 처리 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CashReceiptPsController extends \Controller\Admin\Order\CashReceiptPsController
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        parent::index();
    }
}
