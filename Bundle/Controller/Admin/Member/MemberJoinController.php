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

/**
 * Class 회원가입 정책 관리
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MemberJoinController extends \Controller\Admin\Controller
{

    /**
     * index
     */
    public function index()
    {
        /**
        *   page navigation
        */
        $this->callMenu('member', 'member', 'join');

        /**
        *   request process
        */
        $data = gd_policy('member.join');
        gd_isset($data['appUseFl'], 'n');
        gd_isset($data['under14Fl'], 'auto');
        gd_isset($data['rejoinFl'], 'n');
        gd_isset($data['rejoin'], 0);
        gd_isset($data['snsMemberAuthFl'], 'y');

        /**
        *   set checkbox, select property
        */
        $checked['under14Fl'][$data['under14Fl']] = $checked['appUseFl'][$data['appUseFl']] = $checked['rejoinFl'][$data['rejoinFl']] = $checked['snsMemberAuthFl'][$data['snsMemberAuthFl']] = $checked['under14ConsentFl'][$data['under14ConsentFl']] = 'checked="checked"';

        /**
        *   set view data
        */
        $this->setData('data', $data);
        $this->setData('checked', $checked);

        /**
        *   add javascript
        */
        $this->addScript(['member.js']);

//        debug($data);
    }
}
