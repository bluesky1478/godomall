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

namespace Bundle\Controller\Admin\Marketing;

use Framework\Debug\Exception\Except;
use Globals;
use Request;

/**
 * 다음 쇼핑하우 DBURL 설정
 * @author Lee Namju <lnjts@godo.co.kr>
 */
class DaumcpcConfigController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        //--- 메뉴 설정
        $this->callMenu('marketing', 'daumCpc', 'config');

        //--- 페이지 데이터
        try {
            $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
            $data = $dbUrl->getConfig('daumcpc', 'config');
            $checked['useFl'][gd_isset($data['useFl'], 'n')] = 'checked';

            $groups = gd_member_groups();
            $join = gd_policy('member.join');
            $joinGroup['name'] = gd_isset($groups[gd_isset($join['grpInit'], 1)]);

            $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
            $groupData = $memberGroup->getGroupViewToArray($join['grpInit']);
            switch ($groupData['dcType']) {
                case 'price':
                    $joinGroup['dc'] = $groupData['dcPrice'] . __('원');
                    break;

                default :
                    $joinGroup['dc'] = $groupData['dcPercent'] . '%';
                    break;
            }
            unset($groups);
            unset($memberGroup);
            unset($groupData);
            unset($join);
        } catch (\Exception $e) {
            debug($e);
        }

        //--- 관리자 디자인 템플릿
        $this->setData('data', gd_isset($data));
        $this->setData('checked', gd_isset($checked));
        $this->setData('joinGroup', gd_isset($joinGroup));

        if(gd_policy('basic.info')['mallDomain']) $this->setData('mallDomain',"http://".gd_policy('basic.info')['mallDomain']."/");
        else $this->setData('mallDomain',URI_HOME);
    }
}
