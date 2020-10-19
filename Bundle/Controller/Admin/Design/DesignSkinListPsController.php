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

namespace Bundle\Controller\Admin\Design;

use Component\Design\SkinSave;
use Component\Policy\DesignSkinPolicy;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\StringUtils;

/**
 * 디자인 스킨 설정 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignSkinListPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     * @throws LayerException
     */
    public function index()
    {
        $request = \App::getInstance('request');
        ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);

        // skinType 설정
        if ($request->request()->has('skinType') === false) {
            $skinType = 'front';
        } else {
            $skinType = StringUtils::xssClean($request->request()->get('skinType'));
        }

        try {
            // skinSave 정의
            $skinSave = new SkinSave($skinType);
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        // _GET 처리
        switch ($request->get()->get('mode')) {
            // 스킨 다운
            case 'downSkin':
                try {
                    $skinSave->downSkin(StringUtils::xssClean(addslashes($request->get()->get('skinName'))));
                } catch (\Exception $e) {
                    throw new AlertBackException($e->getMessage());
                }
                break;
            case 'clearCache':
                if ($request->get()->get('mallSno', 0) < 1) {
                    throw new AlertBackException('상점번호를 찾을 수 없습니다.');
                }
                try {
                    $cache = \App::getInstance('cache');
                    $adaptor = $cache->getAdaptor();
                    $config = $adaptor->getConfig();
                    $config['file']['mallSno'] = $request->get()->get('mallSno');
                    $config['file']['subDomain'] = 'front';
                    $adaptor->setConfig($config);
                    $cache->setAdaptor($adaptor);
                    $cache->flush();
                } catch (\Throwable $e) {
                    throw new AlertBackException('초기화 중 오류가 발생하였습니다.');
                }
                throw new AlertBackException('초기화 되었습니다.');
                break;
        }

        // _POST 처리
        switch ($request->post()->get('mode')) {
            // 스킨 (사용스킨, 작업스킨) 변경
            case 'skinChange':
                try {
                    $postValue = $request->post()->all();
                    $requestSkinConfig = [
                        'sno'                                       => $postValue['sno'],
                        $skinSave->skinType . $postValue['skinUse'] => $postValue[$skinSave->skinType . 'Skin'],
                        'skinUse'                                   => $postValue['skinUse'],
                        'skinUseAllFl'                              => $postValue['skinUseAllFl']
                    ];
                    $designSkinPolicy = new DesignSkinPolicy();

                    if ($designSkinPolicy->saveSkin($requestSkinConfig) === false) {
                        $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.'));
                    }
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace('\n', ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 스킨 복사
            case 'copySkin':
                try {
                    $skinSave->copySkin($request->post()->xss()->all());
                    $this->layer(__('복사가 완료 되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace('\n', ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('복사시 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 스킨 업로드
            case 'uploadSkin':
                try {
                    $skinSave->uploadSkin($request->post()->xss()->all(), $request->files()->all());
                    $this->layer(__('스킨이 업로드 되었습니다. 화면보기로 확인하십시요.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace('\n', ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 6000);
                }
                break;

            // 스킨 삭제
            case 'deleteSkin':
                try {
                    $skinSave->deleteSkin($request->post()->get('skinName'));
                    echo 'ok';
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                break;

            // 스킨 정보 변경
            case 'modifySkin':
                try {
                    $skinSave->ModifySkin($request->post()->xss()->all(), $request->files()->all());
                    $this->layer(__('스킨정보가 수정되었습니다.'));
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                break;
            // 스킨 (사용스킨, 작업스킨) 변경
            case 'skinChange':
                try {
                    $postValue = $request->post()->all();
                    $requestSkinConfig = [
                        'sno'                                       => $postValue['sno'],
                        $skinSave->skinType . $postValue['skinUse'] => $postValue[$skinSave->skinType . 'Skin'],
                    ];
                    $designSkinPolicy = new DesignSkinPolicy();

                    if ($designSkinPolicy->saveSkin($requestSkinConfig) === false) {
                        $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.'));
                    }
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace('\n', ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;
            case 'mallIconConfig':
                $postValue = $request->post()->toArray();
                $fileValue = $request->files()->toArray();

                try {
                    $skinSave->mallIconConfig($postValue, $fileValue);
                } catch (\Exception $e) {
                    throw new AlertOnlyException($e->getMessage());
                }
                $this->layer(__('저장이 완료되었습니다.'));
                break;
        }
        exit();
    }
}
