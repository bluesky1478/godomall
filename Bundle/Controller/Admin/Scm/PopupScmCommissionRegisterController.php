<?php

namespace Bundle\Controller\Admin\Scm;

use Globals;
use Request;
use Session;
use Component\Member\Manager;

/**
 * 공급사수수료일정등록(팝업)
 * @author tomi <tomi@godo.co.kr>
 */
class PopupScmCommissionRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        // --- 모듈 호출
        $scmCommission = \App::load('\\Component\\Scm\\ScmCommission');

        // --- 출력데이터
        try {
            // 공급사 정보 설정
            $isProvider = Manager::isProvider();
            $this->setData('isProvider', $isProvider);
            // 수수료일정등록 페이지 데이터 호출
            $getData = $scmCommission->setScmCommissionScheduleRegister($getValue['scmNo'], $getValue['scmScheduleSno']);

            $this->setData('scmNo', $getValue['scmNo']);
            $this->setData('scmScheduleSno', $getValue['scmScheduleSno']);
            $this->setData('data', $getData);
            $this->setData('checked', $getData['checked']);
            $this->setData('selected', $getData['selected']);

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
                'jquery/validation/jquery.validate.js'
            ]);

        } catch (\Exception $e) {
            throw $e;
        }

        // 회원그룹리스트
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $groupList = $memberGroup->getGroupListSelectBox(['key'=>'sno', 'value'=>'groupNm']);
        $this->setData('groupList', $groupList['data']);

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
            $this->getView()->setPageName('scm/popup_scm_commission_register.php');
            $this->setData('popupMode', isset($getValue['popupMode']));
        }
    }
}
