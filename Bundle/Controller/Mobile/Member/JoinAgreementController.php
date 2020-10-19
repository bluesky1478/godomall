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
namespace Bundle\Controller\Mobile\Member;

use App;
use Component\Godo\GodoWonderServerApi;

/**
 * Class 회원가입 약관동의
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class JoinAgreementController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\JoinAgreementController $front */
        $front = \App::load('\\Controller\\Front\\Member\\JoinAgreementController');
        $front->index();

        $wonderLoginPolicy = gd_policy('member.wonderLogin');
        if (empty(\Session::has(GodoWonderServerApi::SESSION_ACCESS_TOKEN)) === false && $wonderLoginPolicy['useFl'] === 'y') {
            $this->getView()->setPageName('member/join_agreement_sns');
        }

        $this->setData($front->getData());
    }
}
