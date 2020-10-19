<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Provider\Order;

use App;
use Exception;
use Globals;
use Request;
use Component\Member\Manager;

/**
 * Class AjaxOrderViewStatusController
 *
 * @package Bundle\Controller\Admin\Order
 * @author su
 */
class AjaxOrderViewStatusReturnController extends \Controller\Admin\Order\AjaxOrderViewStatusReturnController
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // 공급사 정보 설정
        $isProvider = Manager::isProvider();
        $this->setData('isProvider', $isProvider);

        parent::index();
    }
}
