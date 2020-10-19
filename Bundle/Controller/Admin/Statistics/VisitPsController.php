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

use Framework\Debug\Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;

/**
 * 방문통계 처리
 * @author Seung-gak Kim <surlira@godo.co.kr>
 */
class VisitPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        try {
            // @todo mysql json type - bug - json object 순서가 mysql 저장시 자동으로 변경됨.
            $getPost = gd_htmlspecialchars_addslashes(Request::post()->toArray());
            switch ($getPost['mode']) {
                case 'insertVisitConfig':
                case 'modifyVisitConfig':
                    $visitNumberTime = $getPost['visitNumberTime'];
                    $visitNewCountTime = $getPost['visitNewCountTime'];
                    $exceptPage = $getPost['exceptPage'];
                    $inflowAgent = [];
                    $osAgent = [];
                    $browserAgent = [];
                    if (count($getPost['agentInflowCode']) != count(array_unique($getPost['agentInflowCode']))) {
                        throw new LayerNotReloadException(__('중복 값이 있을 수 없습니다.'));
                    }
                    foreach ($getPost['agentInflowCode'] as $key => $val) {
                        if ((trim($val) && !trim($getPost['inflowCode'][$key])) || (!trim($val) && trim($getPost['inflowCode'][$key]))) {
                            throw new LayerNotReloadException(__('빈 값이 있을 수 없습니다.'));
                        }
                        $inflowAgent[$val] = $getPost['inflowCode'][$key];
                    }
                    if (count($getPost['agentOsCode']) != count(array_unique($getPost['agentOsCode']))) {
                        throw new LayerNotReloadException(__('중복 값이 있을 수 없습니다.'));
                    }
                    foreach ($getPost['agentOsCode'] as $key => $val) {
                        if ((trim($val) && !trim($getPost['osCode'][$key])) || (!trim($val) && trim($getPost['osCode'][$key]))) {
                            throw new LayerNotReloadException(__('빈 값이 있을 수 없습니다.'));
                        }
                        $osAgent[$val] = $getPost['osCode'][$key];
                    }
                    if (count($getPost['agentBrowserCode']) != count(array_unique($getPost['agentBrowserCode']))) {
                        throw new LayerNotReloadException(__('중복 값이 있을 수 없습니다.'));
                    }
                    foreach ($getPost['agentBrowserCode'] as $key => $val) {
                        if ((trim($val) && !trim($getPost['browserCode'][$key])) || (!trim($val) && trim($getPost['browserCode'][$key]))) {
                            throw new LayerNotReloadException(__('빈 값이 있을 수 없습니다.'));
                        }
                        $browserAgent[$val] = $getPost['browserCode'][$key];
                    }
                    $visitConfigArrData = [
                        'visitNumberTime'   => $visitNumberTime,
                        'visitNewCountTime' => $visitNewCountTime,
                        'exceptPage'        => $exceptPage,
                        'inflowAgent'      => $inflowAgent,
                        'osAgent'           => $osAgent,
                        'browserAgent'      => $browserAgent,
                    ];
                    gd_set_policy('visit.config', $visitConfigArrData);
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="visit_config.php";');
                    break;
            }
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
        }
    }
}
