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
namespace Bundle\Controller\Mobile\Share;

use Component\Payment\CashReceipt;
use Request;
use UserFilePath;
use Session;

/**
 * 영수증 출력
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ShowTaxInvoiceController extends  \Controller\Front\Share\ShowTaxInvoiceController
{

    /**
     * index
     *
     */
    public function index()
    {
        parent::index();
    }
}
