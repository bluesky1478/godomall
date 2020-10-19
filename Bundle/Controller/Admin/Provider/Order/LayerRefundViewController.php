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

use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;
use Exception;

/**
 * 주문상세의 환불접수 리스트내 수정 레이어
 * [관리자 모드] 환불접수내용 수정
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerRefundViewController extends \Controller\Admin\Order\LayerRefundViewController
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        parent::index();
    }
}
