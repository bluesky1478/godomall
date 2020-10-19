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
use Exception;

/**
 * 무통장 입금은행 변경 레이어 페이지
 * [관리자 모드] 무통장 입금은행 변경 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerBankSelectorController extends \Controller\Admin\Order\LayerBankSelectorController
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
