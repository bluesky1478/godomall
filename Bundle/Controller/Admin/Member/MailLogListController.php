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

use Component\Page\Page;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\SkinUtils;

/**
 * Class 메일 발송 내역
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MailLogListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        try {
            if ($request->get()->has('regdt') === false) {
                $request->get()->set('regdt', DateTimeUtils::getBetweenDateString('-6days'));
            }
            /** @var \Bundle\Controller\Admin\Controller $this */
            $this->callMenu('member', 'mail', 'log');

            $page = $request->get()->get('page', 1);
            $pageNum = $request->get()->get('pageNum', 10);

            /**  @var  \Bundle\Component\Mail\MailLog $mailLog */
            $mailLog = \App::load('\\Component\\Mail\\MailLog');
            $requestGetParams = $request->get()->all();
            $logData = $mailLog->getLogList($requestGetParams, $page, $pageNum);

            $page = new Page($page, $mailLog->foundRowsByLogList(), null, $pageNum);
            $page->setPage();
            $page->setUrl($request->getQueryString());

            $checked = SkinUtils::setChecked(['sendType'], $requestGetParams);

            $this->setData('requestGetParams', $requestGetParams);
            $this->setData('data', $logData);
            $this->setData('page', $page);
            $this->setData('checked', $checked);

            $this->addScript(['member.js']);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
