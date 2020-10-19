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

namespace Bundle\Controller\Admin\Promotion;
use Bundle\Component\Promotion\BarcodeAdmin;
use Exception;
use Request;

/**
 * ===================================================================================
 * 2019.10.15
 *  - 바코드 기능 사용 불가 (기능 제거)
 *  - 바코드 기능 제거 작업으로 기존 레거시 보장을 위해 해당 class 및 함수 유지하되 로직 제거
 * ===================================================================================
 */
class BarcodeManagePsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->json([
            'isSuccess' => false,
            'resultMessage' => __('접근할 수 없습니다.'),
        ]);
    }
}
