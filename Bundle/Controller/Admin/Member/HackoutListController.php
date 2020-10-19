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

use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;

/**
 * Class HackoutListController
 * 탈퇴내역 관리 페이지
 * @package Controller\Admin\Member
 * @author  Wee Yeongjong <yeongjong.wee@godo.co.kr>
 */
class HackoutListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $policy = gd_policy('member.hackout');
        if($policy['isMigrations'] != 'y') {
            $memberSleep = \App::load('Component\\Member\\MemberSleep');
            $memberSleep->deleteSleepMemberMigrations();
        }
        $request = \App::getInstance('request');
        $globals = \App::getInstance('globals');
        try {
            $this->callMenu('member', 'member', 'hackout');
            if (!$request->get()->has('hackDt')) {
                $request->get()->set('hackDt', DateTimeUtils::getBetweenDateString('-6days'));
            }
            if (!$request->get()->has('mallSno')) {
                $request->get()->set('mallSno', '');
            }
            if (!$request->get()->has('sort')) {
                $request->get()->set('sort', 'regDt desc');
            }
            if (!$request->get()->has('page')) {
                $request->get()->set('page', 1);
            }
            if (!$request->get()->has('pageNum')) {
                $request->get()->set('pageNum', 10);
            }
            $selected['sort'][$request->get()->get('sort')] = 'selected="selected"';
            $checked['mallSno'][$request->get()->get('mallSno')] = 'checked="checked"';
            $hackOutService = \App::getInstance('HackOutService');
            if (!is_object($hackOutService)) {
                $hackOutService = new \Component\Member\HackOut\HackOutService();
            }
            $hackOutList = $hackOutService->getHackOutList($request->get()->all(), $request->get()->get('page'), $request->get()->get('pageNum'));
            $page = new \Component\Page\Page($request->get()->get('page'), $hackOutService->getFoundRows(), $hackOutService->getCount(), $request->get()->get('pageNum'));
            $page->setPage();
            $page->setUrl($request->getQueryString());
            $this->setData('_hackType', array_merge(['done' => '전체'], $globals->get('hackType')));
            $this->setData('_hackStep', ($globals->get('hackStep')));
            $this->setData('page', $page);
            $this->setData('data', $hackOutList);
            $this->setData('search', $request->get()->all());
            $this->setData('selected', $selected);
            $this->setData('checked', $checked);
            $this->addScript(['member.js']);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
