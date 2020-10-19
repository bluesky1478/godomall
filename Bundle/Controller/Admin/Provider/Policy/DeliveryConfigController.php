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
namespace Bundle\Controller\Admin\Provider\Policy;

use Framework\Debug\Exception\Except;
use Exception;
use Globals;
use Request;

/**
 * 배송비조건 관리 리스트
 *
 * @package Bundle\Controller\Admin\Provider\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryConfigController extends \Controller\Admin\Policy\DeliveryConfigController
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        parent::index();
    }
}
