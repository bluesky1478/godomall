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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Framework\Debug\Exception\LayerException;

class ScmListController extends \Controller\Admin\Controller
{
    /**
     * 공급사 리스트
     * [관리자 모드] 공급사 리스트
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('scm', 'scm', 'scmList');

        // --- 모듈 호출
        try {
            $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
            $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
            $getData = $scmAdmin->getScmAdminList();
            foreach ($getData['data'] as $key => &$val) {
                $val['scmSameCommission'] = '';
                $commissionSameFl = $scmCommission->compareWithScmCommission($val['addCommissionData']);
                if ($commissionSameFl && $val['scmCommission'] == $val['scmCommissionDelivery']) {
                    $val['scmSameCommission'] = '판매수수료 동일 적용';
                }
            }
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', gd_isset($getData['data']));
        $this->setData('list', gd_isset($getData['list']));
        $this->setData('search', $getData['search']);
        $this->setData('checked', $getData['checked']);
        $this->setData('page', $page);
    }
}
