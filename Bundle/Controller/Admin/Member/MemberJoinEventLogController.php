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
 * @link      http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Member;

use App;
use DateTime;
use Request;
use Framework\Debug\Exception\AlertBackException;

/**
 * Class 회원가입항목설정
 * @package Controller\Admin\Policy
 * @author  yjwee
 */
class MemberJoinEventLogController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        try {
            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $member = \App::load('\\Component\\Member\\MemberAdmin');

            $getValue = Request::get()->all();
            $this->callMenu('member', 'member', 'joinEventConfig');
            gd_isset($getValue['eventType'], 'order');
            $searchPeriod = gd_isset($getValue['searchPeriod'], 6);

            gd_isset($getValue['treatDate'][0], date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
            gd_isset($getValue['treatDate'][1], date('Y-m-d'));

            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 20);


            $total = $getData = [];
            $eventType = $getValue['eventType'];

            $getData = $member->getSimpleJoinLog($getValue, $searchPeriod, $eventType);
            $total['memberCount'] = count($getData['data']);
            if($eventType == 'order') {
                $getValue['memFl'] = 'n';
                $total['noMemberCount'] = $order->getCountNoMemberOrder($getValue);
                $total['memberConversionRate'] = (int)(($total['memberCount'] / ($total['noMemberCount'] + $total['memberCount'])) * 100);
            } else {
                $tmp =  $member->getSimpleJoinPushLog($getValue, $searchPeriod);
                $total['pushClickCount'] = $tmp['click'];
                $total['pushViewCount'] = $tmp['view'];
                $total['memberConversionRateDisplay'] = (int)(($total['memberCount'] / $total['pushViewCount']) * 100);
                $total['memberConversionRateClick'] = (int)(($total['memberCount'] / $total['pushClickCount']) * 100);
                unset($tmp);
            }
            $page = \App::load('Component\\Page\\Page');

            $search['treatDate'] = $getValue['treatDate'];

            $this->setData('eventType', $eventType);
            $this->setData('search', $search);

            $this->setData('total', $total);
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            $this->setData('data', $getData['data']);
            $this->setData('groups', \Component\Member\Group\Util::getGroupName());
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
