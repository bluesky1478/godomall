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

use Component\Excel\ExcelForm;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\GodoUtils;
use Component\PlusShop\PlusReview\PlusReviewConfig;

/**
 * 크리마 간편리뷰 설정
 *
 * @author haky <haky2@godo.co.kr>
 */
class CremaConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('service', 'serviceSetting', 'cremaConfig');

        try {
            // --- 크리마 설정
            $cremaConfig = gd_policy('service.crema');

            // --- 기본값 설정
            if (empty($cremaConfig['clientId']) || empty($cremaConfig['clientSecret'])) {
                $disabled['useCremaFl'] = 'disabled="disabled"';
                $cremaConfig['useCremaFl'] = 'n';
            }
            if ($cremaConfig['useCremaFl'] === 'y' && (empty($cremaConfig['useEpFl']) || $cremaConfig['useEpFl'] === 'n')) {
                $cremaConfig['useEpFl'] = 'n';
            } else {
                $cremaConfig['useEpFl'] = 'y';
            }
            gd_isset($cremaConfig['useCremaFl'],'n');
            gd_isset($cremaConfig['clientId'],'');
            gd_isset($cremaConfig['clientSecret'], '');

            // 파일 다운로드 이력
            $adminLogDao = \App::load('Component\\Admin\\AdminLogDAO');
            $request = [
                'baseUri' => '/service/crema_ps.php',
                'cremaFl' => true,
                'dataArray' => false
            ];
            $data = $adminLogDao->getList($request, ['regDt'], 1);
            if ($data['list']['regDt']) {
                $downloadDt = date('Y-m-d', strtotime($data['list']['regDt']));
                $this->setData('downloadDt', $downloadDt);
            }

            // 크리마 사용안함인 경우 csv 다운로드 불가
            if ($cremaConfig['useCremaFl'] == 'n') {
                $disabled['download'] = 'disabled="disabled"';
            }

            $checked['useCremaFl'][$cremaConfig['useCremaFl']] = 'checked="checked"';

            // 크리마 폴더/파일 초기화
            $crema = \App::load('Component\\Service\\Crema');
            $crema->initCremaFolder();

            $downloadReasonList = gd_code(ExcelForm::EXCEL_DOWNLOAD_REASON_CODE_MEMBER);

            // 플러스리뷰
            $plusReviewConfig = new PlusReviewConfig();
            $useFlPlusReview = $plusReviewConfig->getConfig('useFl');
            $isPlusReview = GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW);
            if ($cremaConfig['reviewCntUpdateType'] == 'updated') {
                $checked['reviewCntUpdateChannel'][$cremaConfig['reviewCntUpdateChannel']] = 'checked="checked"';
            } else if($isPlusReview === true && $useFlPlusReview === 'y') {
                $checked['reviewCntUpdateChannel']['both'] = 'checked="checked"';
            } else {
                $checked['reviewCntUpdateChannel']['board'] = 'checked="checked"';
            }

            // 크리마 사용안함 이거나 기존 상점(상품평 개수 업데이트 기능 추가 전에 크리마 사용중인 상점), 업데이트 완료 인 경우 상품평 개수 업데이트 불가
            if ($cremaConfig['useCremaFl'] == 'n' || $cremaConfig['reviewCntUpdateType'] == 'pass' || $cremaConfig['reviewCntUpdateType'] == 'updated') {
                $disabled['reviewCntUpdate'] = $disabled['reviewCntUpdateChannel'] = 'disabled="disabled"';
            }
        } catch (\Exception $e) {
            throw new LayerException($e->getMessage(), $e->getCode(), $e);
        }

        $this->setData('data', $cremaConfig);
        $this->setData('checked', $checked);
        $this->setData('disabled', gd_isset($disabled));
        $this->setData('reasonList', ArrayUtils::changeKeyValue($downloadReasonList));
        $this->setData('useEpFl', $cremaConfig['useEpFl']);
        $this->setData('isPlusReview', $isPlusReview);
    }
}
