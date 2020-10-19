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
use Framework\Debug\Exception\LayerException;
use Message;
use Request;

/**
 * 상품 정책 저장 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class GoodsPsController extends \Controller\Admin\Policy\GoodsPsController
{

    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        parent::index();
    }
}
