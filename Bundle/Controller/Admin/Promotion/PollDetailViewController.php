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
use Request;

/**
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Young-jin Bag <kookoo135@godo.co.kr>
 */
class PollDetailViewController extends \Controller\Admin\Controller
{
    public function index()
    {
        $getValue = Request::get()->all();
        $poll = new Poll();
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        } else {
            $getValue['page'] = 1;
        }
        $currentPage = \Request::get()->get('page', $getValue['page']);
        $pageNum = \Request::get()->get('pageNum', 10);

        $data = $poll->getPollData($getValue['code']);
        $resultData = $poll->getpollResult($getValue['code'], $data, 'pr.regDt ASC');

        $resData = [];
        foreach ($resultData as $key => $value) {
            if (in_array($key, $range) === false) continue;
            $decData = json_decode(stripslashes($value[$getValue['type']]), true);
            if (is_array($decData) === true) {
                foreach ($decData as $k => $v) {
                    if ($k == $getValue['detail']) {
                        $resData[] = $v;
                    }
                }
            }
        }
        $resData = array_values(array_filter($resData));
        $total = count($resData);
        $max = ($total - ($pageNum*($currentPage - 1))) - 1;
        $min = $max - $pageNum + 1;
        $range = range($min,$max);

        $page = \App::load('\\Component\\Page\\Page', $currentPage);
        $page->page['list'] = $pageNum; // 페이지당 리스트 수
        $page->recode['amount'] = $total;
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 검색 레코드 수
        $page->recode['total'] = $total;
        $page->setPage();

        $page = \App::load('\\Component\\Page\\Page');

        /*$page = new Page($currentPage, count($resData), count($resData), $pageNum);
        $page->setPage();
        $page->setUrl(\Request::getQueryString());*/

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('data', array_reverse($resData));
        $this->setData('page', $page);
    }
}
