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

namespace Bundle\Controller\Admin\Provider\Share;

use Framework\Debug\Exception\Except;
use Globals;
use Request;
use Exception;

class LayerDeliveryController extends \Controller\Admin\Share\LayerDeliveryController
{
    /**
     * 배송비 상품 등록 페이지
     *
     * [관리자 모드] 레이어 상품 등록 페이지
     * 설명 : 상품 정보가 필요한 페이지에서 선택할 상품의 리스트
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        parent::index();
    }
}
