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

use Framework\Utility\ComponentUtils;

/**
 * 080 수신거부 리스트 컨트롤러
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Sms080ListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $page = $request->request()->get('page', 1);
        $pageNum = $request->request()->get('pageNum', 10);
        $this->callMenu('member', 'sms', 'sms080List');
        $dao = \App::load('Component\\Sms\\Sms080DAO');
        $keyword = str_replace('-', '', $request->request()->get('keyword', ''));
        $list = $dao->selectList(
            [
                'offset'  => $page,
                'limit'   => $pageNum,
                'keyword' => $keyword,
            ]
        );
        $totalCount = $dao->countList(
            [
                'keyword' => $keyword,
            ]
        );
        $amountCount = $dao->countList([]);
        $this->setData('list', $list);
        $pageComponent = \App::load('Component\\Page\\Page');
        $pageComponent->setUrl($request->getQueryString());
        $pageComponent->setCurrentPage($page);
        $pageComponent->setList($pageNum);
        $pageComponent->setTotal($totalCount);
        $pageComponent->setAmount($amountCount);
        $pageComponent->setPage();
        $this->setData('policy', ComponentUtils::getPolicy('sms.sms080'));
        $this->setData('page', $pageComponent);
        $this->setData('search', $request->request()->all());
    }
}
