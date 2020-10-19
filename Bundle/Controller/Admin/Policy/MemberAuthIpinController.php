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

/**
 * Class MemberAuthIpinController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MemberAuthIpinController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'auth', 'authIpin');

        // --- 페이지 데이터
        $data = gd_policy('member.ipin');
        $data = gd_htmlspecialchars_stripslashes($data);

        gd_isset($data['useFl'], 'n');
        gd_isset($data['minorFl'], 'n');
        gd_isset($data['codeValue'], '6');
        gd_isset($data['id'], '');
        $checked['useFl'][$data['useFl']] = $checked['minorFl'][$data['minorFl']] = $checked['codeValue'][$data['codeValue']] = 'checked="checked"';

        // --- 관리자 디자인 템플릿

        $this->setData('data', gd_htmlspecialchars($data));
        $this->setData('checked', $checked);
    }
}
