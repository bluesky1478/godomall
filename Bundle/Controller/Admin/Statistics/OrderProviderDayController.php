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

namespace Bundle\Controller\Admin\Statistics;

use Exception;
use Request;

/**
 * [관리자 모드] 매출분석 > 공급사별 매출통계 페이지
 *
 * @author    Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderProviderDayController extends OrderDayController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 모드 설정 (반드시 설정해야 함)
            $this->groupType = 'day';

            // 공급사 사용 설정
            $this->useScmFl = true;

            // 부모 index 실행
            parent::index();

        } catch (Exception $e) {
            throw $e;
        }
    }
}
