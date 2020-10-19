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

use Component\Policy\Policy;
use Component\SiteLink\SiteLinkAdmin;
use Exception;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

/**
 * 보안서버 인증서비스 신청/관리 저장 처리 페이지
 * Class SslPsController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class SslPsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws LayerException
     * @throws LayerNotReloadException
     */
    public function index()
    {

        // --- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();
        switch (Request::request()->get('mode')) {
            // --- PC SSL 신청/관리 저장
            case 'insertSslConfig':
            case 'modifySslConfig':
                try {
                    $policy = new Policy();
                    $policy->saveSsl($postValue);
                    $this->layer(__('저장되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            // --- ADMIN SSL 신청/관리 저장
            case 'insertAdminSslConfig':
            case 'modifyAdminSslConfig':
                try {
                    $policy = new Policy();
                    $policy->saveAdminSsl($postValue);
                    $this->layer(__('저장되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            // --- MOBILE SSL 신청/관리 저장
            case 'insertMobileSslConfig':
            case 'modifyMobileSslConfig':
                try {
                    $policy = new Policy();
                    $policy->saveMobileSsl($postValue);
                    $this->layer(__('저장되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            // --- 무료SSL설치요청
            case 'checkFreeSsl':
                try {
                    $sslAdmin = new SiteLinkAdmin();
                    $sslAdmin->requestFreessl();
                    $this->layer(__('무료 SSL 신청이 완료 되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
            // --- SSL 신청/관리 저장
            case 'sslSetting':
                try {
                    $sslAdmin = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
                    $sslAdmin->saveSsl($postValue);
                    $this->json(
                        [
                            'result'  => 'ok',
                            'message' => __('저장되었습니다.'),
                        ]
                    );
                } catch (Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
        }
    }
}
