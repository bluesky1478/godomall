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

namespace Bundle\Controller\Admin\Share;

use App;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;


/**
 * Class 관리자-CRM 상담 내역
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class MemberCrmCounselController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        try {
            Request::get()->set('page', Request::get()->get('page', 0));
            Request::get()->set('pageNum', Request::get()->get('pageNum', 10));
            Request::get()->set('sort', Request::get()->get('sort', 'regDt DESC'));

            /** @var \Bundle\Component\Member\Counsel $crmAdmin */
            $crmAdmin = App::load('\\Component\\Member\\Counsel');
            $requestGetParams = Request::get()->all();
            $list = $crmAdmin->getList($requestGetParams);
            $crmAdmin->replaceList($list);
            $page = $crmAdmin->getPage($requestGetParams, Request::getQueryString());
            $checked = $crmAdmin->setChecked($requestGetParams);

            /** set view data */
            $this->setData('list', $list);
            $this->setData('checked', $checked);
            $this->setData('kinds', $crmAdmin->getKinds());
            $this->setData('sorts', $crmAdmin->getCrmSorts());
            $this->setData('requestGetParams', $requestGetParams);
            $this->setData('page', $page);

            /** add javascript */
            $this->addScript(['member.js']);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
