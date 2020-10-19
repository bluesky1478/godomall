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

use Exception;
use Component\Member\Manager;

/**
 * Class LayerUserMemoController
 * 고객 신청 메모
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerSuperAdminMemoController extends \Controller\Admin\Order\LayerSuperAdminMemoController
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 공급사 정보 설정
            $isProvider = Manager::isProvider();
            $this->setData('isProvider', $isProvider);

            parent::index();

        } catch (Exception $e) {
            throw $e;
        }
    }
}
