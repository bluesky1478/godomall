<?php

namespace Bundle\Controller\Admin\Design;


use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Request;
use Bundle\Controller\Admin\Controller;
use Component\Page\Page;

class ScreenEffectListController extends Controller
{
    public function index()
    {
        $this->callMenu('design', 'designConf', 'screenEffect');
        $screenEffectDao = \App::load('\\Component\\PlusShop\\ScreenEffect\\ScreenEffectDao');

        $request = Request::get()->toArray();
        $searchDateFl = $request['searchDateFl'] ? $request['searchDateFl'] : 'reg_dt';
        $searchDate = $request['searchDate'] ? $request['searchDate'] :
            [date('Y-m-d', strtotime('-6 day')), date('Y-m-d')];

        if (DateTimeUtils::intervalDay($searchDate[0], $searchDate[1]) > 365) {
            throw new AlertBackException(__('1년이상 기간으로 검색하실 수 없습니다.'));
        }

        $effectName = $request['effectName'];
        $page = $request['page'] ? $request['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        switch ($searchDateFl) {
            case 'reg_dt':
                $items = $screenEffectDao->getByReg($offset, $searchDate[0], $searchDate[1], $effectName);
                break;
            case 'mod_dt':
                $items = $screenEffectDao->getByMod($offset, $searchDate[0], $searchDate[1], $effectName);
                break;
            case 'effect_start_date':
                $items = $screenEffectDao->getByStart($offset, $searchDate[0], $searchDate[1], $effectName);
                break;
            case 'effect_end_date':
                $items = $screenEffectDao->getByEnd($offset, $searchDate[0], $searchDate[1], $effectName);
                break;
        }
        $count = $screenEffectDao->getCount();
        $totalCount = $screenEffectDao->getTotalCount();

        $pager = new Page($page, $count, $totalCount, $perPage, 10);
        $pager->setUrl(\Request::getQueryString());

        $this->setData('totalList', $items);
        $this->setData('listCount', $count);
        $this->setData('totalCount', $totalCount);
        $this->setData('managerId', \Session::get('manager.managerId'));
        $this->setData('searchDateFl', $searchDateFl);
        $this->setData('searchDate', $searchDate);
        $this->setData('effectName', $effectName);
        $this->setData('page', $pager);
        $this->setData('getDate', function($date) {
            if (!$date) {
                return $date;
            }
            return date('Y-m-d', strtotime($date));
        });
    }
}
