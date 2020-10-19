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

use Framework\Debug\Exception\LayerException;

/**
 * 방문통계 설정
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('statistics', 'visit', 'visitConfig');

        try {
            // --- 설정값 정보
            $visitConfig = gd_policy('visit.config');
            $visitConfig = gd_htmlspecialchars_stripslashes($visitConfig);
            if ($visitConfig) {
                $mode = 'modifyVisitConfig';
            } else {
                $mode = 'insertVisitConfig';
            }
            // --- 기본값 설정
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        $this->setData('mode', $mode);
        $this->setData('data', $visitConfig);
    }
}
