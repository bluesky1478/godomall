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

use App;
use Component\Policy\PasswordChangePolicy;
use Component\Storage\Storage;
use Exception;
use Framework\Debug\Exception\LayerException;
use Message;
use Request;

/**
 * Class 관리자 회원 정책 처리
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MemberPsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */

    const ERR_CODE_GUTEST_AUTH = 904;

    public function index()
    {
        /** @var  \Bundle\Component\Policy\Policy $policy */
        $policy = App::load('\\Component\\Policy\\Policy');

        $requestPostParams = Request::post()->all();
        $mode = Request::post()->get('mode', Request::get()->get('mode'));

        switch ($mode) {
            // --- 방문/구매 및 로그아웃
            case 'member_access':
                try {
                    $policy->saveMemberAccess($requestPostParams);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    switch ($e->getCode()) {
                        case self::ERR_CODE_GUTEST_AUTH:
                            $this->js('parent.dialog_alert("' . addslashes(__($e->getMessage())) . '","' . __('경고') . '" ,{isReload:false});');
                            break;
                        default :
                            throw new LayerException($e->getMessage(), $e->getCode(), $e);
                            break;
                    }
                }
                break;

            // --- 아이핀
            case 'member_auth_ipin':
                try {
                    $policy->saveMemberAuthIpin($requestPostParams);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json($this->exceptionToArray($e));
                    } else {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                    }
                }
                break;

            // --- 휴대폰 본인확인
            case 'member_auth_cellphone':
                try {
                    $policy->saveMemberAuthCellphone($requestPostParams);
                    $kcpParam = array(
                        'useFlKcp' => $requestPostParams['useFlKcp'],
                        'useDataJoinFlKcp' => $requestPostParams['useDataJoinFlKcp'],
                        'useDataModifyFlKcp' => $requestPostParams['useDataModifyFlKcp'],
                        'serviceId' => $requestPostParams['serviceId']
                    );
                    $policy->saveKcpCellphoneAuthConfig($kcpParam);

                    $this->json(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json($this->exceptionToArray($e));
                    } else {
                        throw new LayerException($e->getMessage(), $e->getCode(), $e);
                    }
                }
                break;

            // 비밀번호 찾기
            case 'password_find':
                try {
                    $data['emailFl'] = Request::post()->get('emailFl');
                    $data['smsFl'] = Request::post()->get('smsFl');

                    $policy->saveMemberPasswordFind($data);

                    $this->json(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json($this->exceptionToArray($e));
                    } else {
                        throw new LayerException($e->getMessage(), $e->getCode(), $e);
                    }
                }
                break;

            // 비밀번호 변경
            case 'password_change':
                try {
                    $data['managerFl'] = Request::post()->get('managerFl');
                    $data['memberFl'] = Request::post()->get('memberFl');
                    $data['guidePeriod'] = Request::post()->get('guidePeriod');
                    $data['guidePeriodItem'] = Request::post()->get('guidePeriodItem');
                    $data['reGuidePeriod'] = Request::post()->get('reGuidePeriod');
                    $data['reGuidePeriodItem'] = Request::post()->get('reGuidePeriodItem');

                    $passwordPolicy = new PasswordChangePolicy();
                    $passwordPolicy->saveMemberPasswordChange($data);
                    $this->json(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json($this->exceptionToArray($e));
                    } else {
                        throw new LayerException($e->getMessage(), $e->getCode(), $e);
                    }
                }
                break;

            // 14세이하 회원가입 동의서 다운로드
            case 'under14Download':
                $downloadPath = Storage::disk(Storage::PATH_CODE_COMMON)->getDownLoadPath('under14sample.docx');
                $this->download($downloadPath, __('만14세미만회원가입동의서(샘플)').'.docx');
                break;
        }
    }
}
