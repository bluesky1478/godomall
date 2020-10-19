<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Mypage;
use Bundle\Component\Promotion\Barcode;
use Bundle\Component\Promotion\BarcodeCoupon;
use Component\Page\Page;
use Cookie;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Session;


/**
 * ===================================================================================
 * 2019.10.15
 *  - 바코드 기능 사용 불가 (기능 제거)
 *  - 바코드 기능 제거 작업으로 기존 레거시 보장을 위해 해당 class 및 함수 유지하되 로직 제거
 * ===================================================================================
 */
class LayerViewBarcodeController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index() {
        $this->alert(__('접근할 수 없습니다.'));
    }
}
