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

namespace Bundle\Controller\Admin\Policy;

use Component\Agreement\BuyerInformCode;
use Component\Mail\MailUtil;
use Component\Policy\ManageSecurityPolicy;
use Component\Storage\Storage;
use Component\File\StorageHandler;
use Framework\Debug\Exception\HttpException;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use Message;
use Request;
use Component\Policy\Policy;
use Component\Agreement\BuyerInform;

/**
 * 기본 정책 저장 처리 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class BasePsController extends \Controller\Admin\Controller
{

    /**
     * 기본 정책 저장 처리 페이지
     *
     * @throws LayerException
     * @throws HttpException
     * @internal param array $get
     * @internal param array $post
     * @internal param array $files
     */
    public function index()
    {
        $request = \App::getInstance('request');
        // --- POST 값 처리
        $post = $request->post()->toArray();

        switch ($post['mode']) {
            // --- 쇼핑몰명 저장
            case 'base_mallnm':
                // 모듈 호출

                try {
                    // 모듈 호출
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveBasicMallnm($post['mallNm']);
                    echo $post['mallNm'];
                } catch (\Exception $e) {
                    throw new HttpException($e->ectMessage, 500);
                }
                break;

            // --- 기본 정보 저장
            case 'base_info':
                // 모듈 호출
                try {
                    $mallSno = gd_isset($post['mallSno'], 1);
                    $policy = \App::load(Policy::class);
                    $beforeBaseInfo = gd_policy('basic.info', $mallSno);
                    $saveData = $policy->saveBasicInfo($post);
                    MailUtil::saveSenderMailByBaseMail($beforeBaseInfo['email'], $saveData['email']);
                    // 본사 사업자/통신판매 정보 저장
                    $inform = \App::load(BuyerInform::class);
                    $inform->saveMallBaseInfo($saveData, $mallSno);

                    // 회사소개 저장
                    $company = StringUtils::xssClean($request->post()->get('company'));
                    $inform->saveInformData(BuyerInformCode::COMPANY, $company, $mallSno);
                    $managerSecurityPolicy = \App::load(ManageSecurityPolicy::class)->getValue();
                    StringUtils::strIsSet($managerSecurityPolicy['smsSecurity'], 'n');
                    StringUtils::strIsSet($managerSecurityPolicy['ipAdminSecurity'], 'n');
                    $securityAgreementPolicy = ComponentUtils::getPolicy('manage.securityAgreement');
                    StringUtils::strIsSet($securityAgreementPolicy['guide'], 'y');

                    if ($securityAgreementPolicy['guide'] === 'y' && ($managerSecurityPolicy['smsSecurity'] !== 'y'
                            && $managerSecurityPolicy['ipAdminSecurity'] !== 'y')) {
                        $this->js('parent.godo.layer.move_manage_security.show();');
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'));
                    }
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            // --- 코드 등록
            case 'code_register':
                // 모듈 호출

                try {
                    // 모듈 호출
                    $code = \App::load('\\Component\\Code\\Code', $post['mallSno']);
                    if ($post['stype'] == 'group') {
                        $code->codeHandle('registerGroupCode', $post['itemNm']);
                    } else if ($post['stype'] == 'code') {
                        $code->codeHandle('registerCode', $post['itemNm'], $post['groupCd']);
                    }

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;

            // --- 코드 수정
            case 'code_modify':
                // 모듈 호출

                try {
                    // 모듈 호출
                    $code = \App::load('\\Component\\Code\\Code', $post['mallSno']);
                    if ($post['stype'] == 'group') {
                        $code->codeHandle('modifyGroupCode', $post['itemNm'], $post['itemCd']);
                    } else if ($post['stype'] == 'code') {
                        $code->codeHandle('modifyCode', $post['itemNm'], $post['itemCd']);
                    }

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;

            // --- 코드 삭제
            case 'code_delete':
                // 모듈 호출
                $code = \App::load('\\Component\\Code\\Code', $post['mallSno']);
                $result = $code->codeHandle('deleteCode', $post['itemCd']);
                if ($result === false) {
                    echo __('처리중에 오류가 발생하여 실패되었습니다.');
                }
                break;
            // --- 코드 이동
            case 'code_save':
                try {
                    // 모듈 호출
                    $code = \App::load('\\Component\\Code\\Code', $post['mallSno']);
                    $code->save($post);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }

                break;
            // --- 이미지 저장소
            case 'file_storage':
                // 모듈 호출
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $data = gd_policy('basic.storage');
                    $postStorageCount = count($post['storageName']);
                    $currentStorageCount = count($data['storageName']);
                    $policy->saveFileStorage($post);

                    if ($currentStorageCount > $postStorageCount) {
                        $this->layer('파일저장소가 삭제되었습니다. 정상적으로 보이지 않는 상품이미지가 있는지 확인하시기 바랍니다. \n\n ' . __('저장이 완료되었습니다.'), null, 5000);
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'));
                    }

                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;

            // --- 이미지 저장소 체크
            case 'file_storage_checker':
                // 모듈 호출
                try {
                    Storage::checkUseStorage($post);
                    $this->json(['result' => 'ok']);
                } catch (\Exception $e) {
                    $this->json(
                        [
                            'result' => 'fail',
                            'errMsg' => $e->getMessage(),
                        ]
                    );
                }
                break;
            case 'getGroupCd' :
                //                $code = \App::load('\\Component\\Code\\Code');
                //                $data = $code->getGroupCode(Request::post()->get('categoryGroupCd'));
                //                echo $this->json($data);

                exit;
                break;

            // --- 금액/단위 기준설정 정보 저장
            case 'currency_unit':
                try {
                    // 모듈 호출
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveCurrencyUnit($post);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            // --- 대표색상 삭제 관련
            case 'search_color':
                $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
                $data = $goods->getGoodsColorCount($post['color']);

                echo $data;

                break;
            // --- 기본 정보 저장
            case 'base_seo':
                // 모듈 호출
                try {

                    // 모듈 호출
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $saveData = $policy->saveBasicSeo($post);


                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            // --- 기본 정보 저장
            case 'seo_tag_register':
            case 'seo_tag_modify':
                // 모듈 호출
                try {
                    $post['mallSno'] = DEFAULT_MALL_NUMBER;

                    // 모듈 호출
                    $seoTag = \App::load('\\Component\\Policy\\SeoTag');
                    $result = $seoTag->saveSeoTagPage($post);
                    echo json_encode($result);

                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'seo_tag_layer_list':
                // 모듈 호출
                try {
                    $post['mallSno'] = DEFAULT_MALL_NUMBER;

                    // 모듈 호출
                    $seoTag = \App::load('\\Component\\Policy\\SeoTag');
                    $result = $seoTag->getSeoTagLayerList($post);
                    echo json_encode($result);

                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
            case 'seo_tag_delete':
                // 모듈 호출
                try {

                    // 모듈 호출
                    $seoTag = \App::load('\\Component\\Policy\\SeoTag');
                    $result = $seoTag->deleteSeoTag($post);
                    echo $result;

                } catch (\Exception $e) {
                    throw new LayerException($e->getMessage());
                }
                break;
        }
        exit;
    }
}
