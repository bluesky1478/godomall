<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Marketing;

use Component\Godo\GodoGongjiServerApi;
use Globals;

class PaycoStatisticsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('marketing','payco','statistics');

        // 데이터 초기화
        $paycoSellerKey = '';

        // 페이코 정보
        $data = gd_policy('pg.payco');
        if (empty($data) === false) {
            if ($data['useYn'] !== 'N' && empty($data['paycoSellerKey']) === false) {
                $paycoSellerKey = $data['paycoSellerKey'];
            }
        }

        // 데이터 설정
        $this->setData('paycoSellerKey', $paycoSellerKey);
    }
}
