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
namespace Bundle\Controller\Admin\Service;

use Component\Service\Crema;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;

/**
 * 크리마 간편리뷰
 *
 * @author haky <haky2@godo.co.kr>
 */
class CremaPsController extends \Controller\Admin\Controller
{

    public function index()
    {
        try {
            $logger = \App::getInstance('logger')->channel('crema');
            $request = \App::getInstance('request');
            $crema = \App::load('Component\\Service\\Crema');
            $postValue = $request->post()->all();
            $getValue = $request->get()->all();
            switch ($postValue['mode']) {
                // 크리마 설정 저장
                case 'config':
                    $cremaConfigData = [
                        'useCremaFl'    => $postValue['useCremaFl'],
                    ];
                    $crema->setUseCremaFl($cremaConfigData);
                    $this->layer(__('저장이 완료되었습니다.'));
                    break;
                // 크리마 EP 동의 여부 저장
                case 'setUseEpFl':
                    $cremaConfigData = [
                        'useEpFl'    => $postValue['useEpFl'],
                    ];
                    $crema->setUseEpFl($cremaConfigData);
                    $this->json(['success' => true, 'message' => '동의처리 되었습니다.']);
                    break;
                // csv 생성시 보안설정 확인
                case 'setFilePassword':
                    $policy = \App::load('Component\\Policy\\ManageSecurityPolicy');
                    if ($policy->useExcelSecurityScope('crema', 'csv')) {
                        if ($policy->useSecurityIp() && $policy->notAuthenticationIp()) {
                            $logger->warning(sprintf('use excel download security. you are ip address [%s] not registered', $request->getRemoteAddress()));
                            $this->json(['success' => false, 'message' => '파일 다운로드 보안 설정을 사용 중입니다.<br/>등록된 IP가 아니므로 파일 다운로드가 불가합니다.']);
                        }
                        if ($policy->requireAuthInfo()) {
                            $this->json(['success' => false, 'message' => '파일 다운로드 보안 인증을 사용 중입니다.<br/>로그인하신 운영자 계정은 인증정보가 없어 다운로드가 불가하오니,<br/>인증정보를 등록 후, 다시 시도해주세요.']);
                        }
                        if (!$policy->hasAuthorize()) {
                            if ($policy->requireAuthorizeExcelDownload()) {
                                $this->json(['success' => true, 'callback' => 'open_file_auth']);
                            }
                        }
                    }
                    // 고도회원 인증 결과 제거
                    \Component\Godo\MyGodoSmsServerApi::deleteAuth();
                    $this->json(['success' => true]);
                    break;
                // csv 생성
                case 'createCremaCsv':
                    $result = $crema->createCsvFile($postValue);
                    if ($result) {
                        $this->js("parent.complete_create_csv('파일 생성을 완료하였습니다.');");
                    } else {
                        $this->js("parent.hide_process('파일 생성에 실패하였습니다. 다시 시도해주시기 바랍니다.');");
                    }
                    break;
                // 상품평 개수 업데이트
                case 'reviewCntUpdate':
                    // 이미 다른 관리자 접속 화면에서 업데이트 진행 중인 경우, "업데이트가 이미 진행 중입니다" Alert 노출.
                    if ($crema->checkReviewCntUpdating() === true) {
                        $this->json(['success' => false, 'msgNo' => 'm1', 'message' => '업데이트 이미 진행 중']);
                    }
                    $result = $crema->reviewCntUpdate($postValue['reviewCntUpdateChannel']);
                    $this->json($result);
                    break;
                default:
                    break;
            }
            switch ($getValue['mode']) {
                // 생성된 csv 다운로드
                case 'download':
                    // 다운로드 파일 로그 저장
                    $request->get()->set('fileName', Crema::CREMA_CSV_ZIP_NAME);
                    $request->get()->set('downloadFileName', Crema::CREMA_CSV_ZIP_NAME);
                    $logAction = \App::load('Component\\Admin\\AdminLogDAO');
                    $logAction->setAdminLog();
                    $result = $crema->downloadCsvFile();
                    if ($result === false) {
                        throw new Exception('파일 다운로드에 실패하였습니다. 다시 시도해주시기 바랍니다.');
                    }
            }
        } catch (Exception $e) {
            throw new AlertRedirectException($e->getMessage(), null, null, '../service/crema_config.php');
        }
        exit();
    }
}
