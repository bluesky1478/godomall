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

namespace Bundle\Controller\Admin\Policy;


use Component\Admin\AdminLogDAO;

class AdminLogListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('policy', 'management', 'adminLogList');
        $adminLog = new AdminLogDAO();
        $request = \Request::get()->toArray();
        gd_isset($request['view'], 'default');

        $request['searchDate'][0] = $request['searchDate'][0] ? $request['searchDate'][0] : date('Y-m-d', strtotime('-6 day'));
        $request['searchDate'][1] = $request['searchDate'][1] ? $request['searchDate'][1] : date('Y-m-d');

        // 페이지 구분
        $request['adminAccessPage'] = 'adminInfo';
        if ($request['view'] == 'adminAccess') {
            $request['adminAccessFl'] = true;
        }

        $fields = ['sno','regDt', 'ip','managerId','menu','page','action','baseUri','data'];
        $data = $adminLog->getList($request,$fields,\Request::get()->get('pageNum',20));

        // 정규식 패턴 view 파라미터 제거
        $pattern = '/view=[^&]+$|view=[^&]+&/';
        // view 제거된 쿼리 스트링
        $queryString = preg_replace($pattern, '', \Request::getQueryString());
        if (empty($queryString) === false) {
            $queryString = '&' . $queryString;
        }

        $this->setData('req',$data['request']);
        $this->setData('data',$data);
        $this->setData('view', $request['view']);
        $this->setData('queryString', $queryString);
    }
}
