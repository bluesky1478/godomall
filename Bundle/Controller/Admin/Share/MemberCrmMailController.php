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

use Component\Mail\MailAdmin;
use Component\Page\Page;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;

/**
 * Class 관리자-CRM MAIL 내역
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class MemberCrmMailController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        $request = \App::getInstance('request');
        try {
            if ($request->get()->has('regDt') === false) {
                $request->get()->set('regDt', DateTimeUtils::getBetweenDateString('-6days'));
            }
            $request->get()->set('page', $request->get()->get('page', 1));
            $request->get()->set('pageNum', $request->get()->get('pageNum', 10));
            $request->get()->set('sort', $request->get()->get('sort', 'sno DESC'));

            $page = $request->get()->get('page', 1);
            $pageNum = $request->get()->get('pageNum', 10);

            /** @var \Bundle\Component\Mail\MailLog $mailLog */
            $mailLog = \App::load('\\Component\\Mail\\MailLog');
            $requestGetParams = $request->get()->all();
            $list = $mailLog->getCrmLogList($requestGetParams, $page, $pageNum);

            $p = new Page($page, $mailLog->foundRowsByCrmLogList(), null, $pageNum);
            $p->setPage();
            $p->setUrl($request->getQueryString());

            $checked['sendType'][$requestGetParams['sendType']] = $checked['sendFl'][$requestGetParams['sendFl']] = 'checked="checked"';

            $this->setData('list', $list);
            $this->setData('checked', $checked);
            $this->setData('requestGetParams', $requestGetParams);
            $this->setData('page', $p);
            $this->setData('keys', MailAdmin::CRM_KEYS);
            $this->setData('sorts', MailAdmin::CRM_SORTS);

            $this->addScript(['member.js']);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
