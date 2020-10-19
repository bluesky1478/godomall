<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-10-31
 * Time: 오후 5:18
 */

namespace Bundle\Controller\Admin\Scm;

use Request;
use Component\Member\Manager;

/**
 * 공급사 수수료 관리 > 공급사 수수료 설정 팝업
 * @author KimYeonKyung <kyeonk@godo.co.kr>
 */
class PopupScmCommissionSetController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        //공급사만 출력
        Request::get()->set('scmCommissionSet', 'p');

        $getValue = Request::get()->toArray();
        // --- 모듈 호출
        $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);

        // --- 출력데이터
        try {
            $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
            $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
            $getData = $scmAdmin->getScmAdminList();
            //판매 수수료, 배송비 수수료 동일적용
            foreach ($getData['data'] as $key => &$val) {
                $val['scmSameCommission'] = '';
                $commissionSameFl = $scmCommission->compareWithScmCommission($val['addCommissionData']);
                if ($commissionSameFl && $val['scmCommission'] == $val['scmCommissionDelivery']) {
                    $val['scmSameCommission'] = '판매수수료 동일 적용';
                }
            }
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정


            $this->addScript([
                'jquery/validation/jquery.validate.js'
            ]);

        } catch (\Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('list', gd_isset($getData['list']));
            $this->setData('search', $getData['search']);
            $this->setData('page', $page);
            $this->getView()->setPageName('scm/popup_scm_commission_set.php');
            $this->getView()->setDefine('layout', 'layout_blank_noiframe.php');
            $this->setData('popupMode', isset($getValue['popupMode']));
        }
    }
}