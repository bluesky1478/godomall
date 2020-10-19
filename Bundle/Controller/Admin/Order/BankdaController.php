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

use Framework\Debug\Exception\Except;
use Globals;
use Request;
/**
 * 이나무 신청페이지와 경로 싱크
 *
 * @author    cjb333
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class BankdaController extends \Controller\Admin\Controller
{

    public function index()
    {
        try {
            $this->redirect('/order/bankda_service.php');
        } catch (Except $e) {
            echo ($e->ectMessage);
        }

    }
}
