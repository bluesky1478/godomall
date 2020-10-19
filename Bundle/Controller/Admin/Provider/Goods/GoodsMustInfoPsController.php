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
namespace Bundle\Controller\Admin\Provider\Goods;

use Exception;
use Framework\Debug\Exception\LayerException;
use Message;
use Request;
use Component\Validator\Validator;

class GoodsMustInfoPsController extends \Controller\Admin\Goods\GoodsMustInfoPsController
{

    /**
     * 추가상품  처리 페이지
     * [관리자 모드] 추가상품  관련 처리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @throws Except
     * @throws LayerException
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        parent::index();
    }
}
