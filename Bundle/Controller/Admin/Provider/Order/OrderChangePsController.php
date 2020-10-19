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

use App;
use Exception;
use Request;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * 주문 상태 변경 처리 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  su
 */
class OrderChangePsController extends \Controller\Admin\Order\OrderChangePsController
{
    /**
     * @inheritdoc
     *
     * @throws LayerException
     */
    public function index()
    {
        parent::index();
    }
}
