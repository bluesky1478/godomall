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

namespace Bundle\Controller\Admin\Member;

use Exception;
use Framework\Debug\Exception\AlertBackException;
use Globals;

/**
 * Class MemberDepositConfigController
 * @package Controller\Admin\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class MemberDepositConfigController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /** page navigation */
        $this->callMenu('member', 'point', 'depositConfig');

        try {
            $data = Globals::get('gSite.member.depositConfig');

            // --- 기본값 설정
            gd_isset($data['name'], __('예치금'));
            gd_isset($data['unit'], __('원'));
            gd_isset($data['payUsableFl'], 'y');

            $checked = $selected = [];
            $checked['payUsableFl'][$data['payUsableFl']] = 'checked="checked"';

            // 예치금을 가진 회원이 1명 이상 있는지 여부 확인
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = \App::load('\\Component\\Deposit\\Deposit');
            $data['isDepositMember'] = $deposit->depositIsExists();


            /** set view data */
            $this->setData('data', $data);
            $this->setData('checked', $checked);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

