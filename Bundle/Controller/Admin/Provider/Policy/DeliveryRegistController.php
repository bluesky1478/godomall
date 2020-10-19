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

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\Except;
use Session;
use Request;
use Exception;
use Component\Category\CategoryAdmin;

/**
 * 배송 정책 설정 관리 페이지
 * [관리자 모드] 배송 정책 설정 관리 페이지
 *
 * @package Bundle\Controller\Admin\Provider\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryRegistController extends \Controller\Admin\Policy\DeliveryRegistController
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
