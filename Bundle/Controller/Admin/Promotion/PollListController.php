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

namespace Bundle\Controller\Admin\Promotion;

use Component\Promotion\Poll;
use Component\Page\Page;
use Exception;

/**
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Young-jin Bag <kookoo135@godo.co.kr>
 */
class PollListController extends \Controller\Admin\Controller
{
    // '=' . __('통합검색') . '='
    // __('설문제목')
    // __('등록자')
    private $pollSearch = [
        'all'       => '=통합검색=',
        'pollTitle'     => '설문제목',
        'managerNm' => '등록자',
    ];
    // __('진행기간')
    // __('등록일')
    private $pollDateSearch = [
        'regDt' => '등록일',
        'pollDt'     => '진행기간',
    ];

    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('promotion', 'poll', 'pollList');

        $currentPage = \Request::get()->get('page', 1);
        $pageNum = \Request::get()->get('pageNum', 10);

        $manager = \App::load('\\Component\\Member\\Manager');
        $arrManager = $manager->getManagerName();

        $poll = \App::load('\\Component\\Promotion\\Poll');
        $getRequest = \Request::get()->all();
        if (!$getRequest['date'] && (!$getRequest['regDt'][0] && !$getRequest['regDt'][1])) {
            $getReqest['regDt'][0] = date('Y-m-d', strtotime('-6 day'));
            $getReqest['regDt'][1] = date('Y-m-d');
            $getRequest['date'] = 'regDt';
        }
        $data = $poll->lists($getRequest, $currentPage, $pageNum);
        foreach ($data as $k => $v) {
            $data[$k]['joinCnt'] = $poll->getPollCnt($v['pollCode']);
        }

        $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        $checked['statusFl'][\Request::get()->get('statusFl', '')] = 'checked="checked"';

        $this->setData('data', $data);
        $this->setData('page', $page);
        $this->setData('pollSearch', $this->pollSearch);
        $this->setData('pollDateSearch', $this->pollDateSearch);
        $this->setData('checked', $checked);
        $this->setData('deviceFl', $poll->getObject('deviceFl'));
        $this->setData('groupFl', $poll->getObject('groupFl'));
        $this->setData('statusFl', $poll->getObject('statusFl'));
    }
}
