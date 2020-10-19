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

use Framework\Debug\Exception\Except;
use Framework\Utility\ArrayUtils;
use Globals;
use App;

/**
 * 현금영수증 개별 발급 요청 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CashReceiptRegisterController extends \Controller\Admin\Order\CashReceiptRegisterController
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
