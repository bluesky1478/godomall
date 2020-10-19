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

use Request;
use App;
use Component\Godo\GodoNhnServerApi;
use Component\Nhn\Paycosearch;
use Component\Nhn\PaycosearchPipAll;
use Exception;
use Component\Policy\Policy;
use Logger;

/**
 * Class 기본설정-페이코서치-페이코서치 신청/관리
 * @package Bundle\Controller\Admin\Policy
 * @author  yoonar
 */
class PaycosearchPsController extends \Controller\Admin\Controller {

    public function Index() {
        try {
            $requestPostParams = Request::post()->all();
            $mode = $requestPostParams['mode'];
            $configShopKey = new Policy();
            $paycosearchApi = new godoNhnServerApi();
            $paycosearch = new Paycosearch();

            $paycosearchConfig = $paycosearch->getConfig();
            $shopKey = $configShopKey->getNhnShopKeyConfig();

            switch ($mode) {
                case 'insertSearchPopup':
                case 'insertAgainSearchPopup':
                    if ($requestPostParams['agreementFlag'] != 'y') {
                        $this->json([
                            'error'   => true,
                            'message' => '페이코 서치 이용약관에 동의해주세요.',
                        ]);
                        exit;
                    }

                    if ($requestPostParams['searchAllDomain']) {
                        $domainSplit = explode('|', $requestPostParams['searchAllDomain']);
                    }
                    if (count(array_filter($domainSplit)) != 2) {
                        $this->json([
                            'error'   => true,
                            'message' => '대표 URL을 선택 해주시기 바랍니다.',
                        ]);
                        exit;
                    }

                    $registData = [
                        'domain' => $domainSplit[0],
                        'domainName' => iconv('UTF-8', 'EUC-KR', $requestPostParams['shopName']),
                        'retry' => $domainSplit[0] ? 'Y' : 'N',
                        'pipUrl' => $domainSplit[0] . '/data/dburl/paycosearch/paycoSearch_all.txt',
                    ];

                    if ($mode == 'insertSearchPopup') {
                        $requestMode = 'paycosearchRegist';
                    } elseif ($mode == 'insertAgainSearchPopup') {
                        $registData['shopKey'] = $shopKey;
                        $requestMode = 'paycosearchAgainRegist';
                        $registData['shopKey'] = strtoupper($shopKey);
                    }

                    $result = $paycosearchApi->request($requestMode, $registData);

                    if($result['resultCode'] === 'OK') {
                        /* db 저장 처리 */
                        $result['mode'] = 'regist';
                        $result['shopName'] = $requestPostParams['shopName'];
                        $result['displayDomain'] = $domainSplit[1];
                        $configShopKey->savePaycosearchConfig($result);
                        /* db 저장 처리 */

                        Logger::channel('paycosearch')->info('PAYCOSEARCH_REGIST_SUCCESS', [__CLASS__, $result]);
                        $this->json([
                            'message' => '사용신청이 완료되었습니다.<br />신청 후 사용까지는 최대 1일이 소요됩니다.'
                        ]);
                    } else {
                        Logger::channel('paycosearch')->info('PAYCOSEARCH_REGIST_ERROR', [__CLASS__, $result]);
                        if($result['resultCode'] === 'ERR06') {
                            $message = '선택하신 URL은 다른 상점에서 사용중인 URL 입니다. 현재 쇼핑몰에서 사용중인 URL이 맞는 경우, 고도 홈페이지 > 마이고도 > 1:1문의 를 남겨주시면 확인 후 신청 가능하도록 처리해드리겠습니다.';
                        } else {
                            $message = '페이코 서치 설정 중 오류가 발생하였습니다.<br />잠시 후 다시 시도해주세요.';
                        }
                        $this->json([
                            'error'   => true,
                            'message' => $message,
                        ]);
                    }
                    break;
                case 'configSearch':

                    $goodsSearchCfg = gd_policy('paycosearch.config');
                    $createType = $goodsSearchCfg['createType'];

                    if (empty($requestPostParams['paycosearchFl']) === true) $requestPostParams['paycosearchFl'] = 'T';
                    if ($requestPostParams['paycosearchFl'] == 'Y' || $requestPostParams['paycosearchFl'] == 'T') {
                        $goodsSearchCfg['searchPipScheduler'] = 'Y';
                        $goodsSearchCfg['searchRejectMessage'] = '';
                    } elseif ($requestPostParams['paycosearchFl'] == 'N') {
                        $goodsSearchCfg['searchPipScheduler'] = 'N';
                    } else {
                        $goodsSearchCfg['searchPipScheduler'] = 'N';
                    }
                    try {
                        if (empty($requestPostParams['paycosearchFl']) === false && empty($goodsSearchCfg['searchPipScheduler']) === false) {
                            // 고도 중앙 서버에 사용 설정 값 전달
                            if ($goodsSearchCfg['paycosearchFl'] != $requestPostParams['paycosearchFl'] & in_array($requestPostParams['paycosearchFl'], ['N', 'T', 'Y']) === true) {
                                $statusParam = [];
                                $statusParam['mode'] = 'status';
                                $statusParam['domain'] = $goodsSearchCfg['searchKeyDomain'];
                                $statusParam['shopKey'] = strtoupper($goodsSearchCfg['shopKey']);

                                if ($requestPostParams['paycosearchFl'] == 'N') $statusParam['status'] = 'deactivated';
                                else if ($requestPostParams['paycosearchFl'] == 'T') $statusParam['status'] = 'simulate';
                                else if ($requestPostParams['paycosearchFl'] == 'Y') $statusParam['status'] = 'activated';

                                $godoApiShopChangeValue = $paycosearchApi->request('godoApiShopStatusChangeRequest', $statusParam);
                            } else {
                                $godoApiShopChangeValue['resultCode'] = 'OK';
                            }
                            if ($godoApiShopChangeValue['resultCode'] == 'OK') {
                                $goodsSearchCfg['mode'] = 'config';
                                $goodsSearchCfg['searchUse'] = $requestPostParams['paycosearchFl'];
                                $goodsSearchCfg['createType'] = $requestPostParams['createType'];
                                $goodsSearchCfg['autocomplete'] = $requestPostParams['autocompleteFl'];

                                $configShopKey->savePaycosearchConfig($goodsSearchCfg);

                                if($requestPostParams['paycosearchFl'] != 'N' && $createType != $requestPostParams['createType'] && $requestPostParams['createType'] == 'hand') {
                                    if ($paycosearch->getCheckFileFlag() === 'G') {
                                        $this->json([
                                            'message' => '상품 데이터가 자동생성 중입니다. 자동생성이 완료된 후 수동생성 설정이 가능합니다. 잠시 후 다시 시도해주세요.'
                                        ]);
                                        exit;
                                    }
                                    if ($paycosearch->makePipFl() === false) {
                                        $this->json([
                                            'message' => '상품 데이터 수동생성은 1시간에 1번만 가능합니다.'
                                        ]);
                                        exit;
                                    }

                                    $ps = new PaycosearchPipAll();
                                    $ps->exec();
                                }
                                $this->json([
                                    'message' => '저장되었습니다.'
                                ]);
                            }
                        }
                    } catch (Exception $e) {

                    }
                    break;
                case 'makePip':
                    if ($paycosearch->makePipFl() === false || $paycosearch->getCheckFileFlag() === 'G') {
                        $this->json([
                            'message' => '상품 데이터 수동생성 중입니다. 상품 데이터 수동생성은 1시간에 1번만 가능합니다.'
                        ]);
                        exit;
                    }

                    $ps = new PaycosearchPipAll();
                    $ps->exec();

                    $paycosearchConfig['createType'] = $requestPostParams['createType'];
                    gd_set_policy('paycosearch.config', $paycosearchConfig);

                    $this->json([
                        'message' => '저장되었습니다.'
                    ]);
                    break;
            }
        } catch (Exception $e) {

        }
    }
}