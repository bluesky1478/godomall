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

use Component\Godo\MyGodoSmsServerApi;
use Message;
use Globals;
use Request;

/**
 * 주문 정책 저장 처리 페이지
 * [관리자 모드] 주문 정책 저장 처리 페이지
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SettlePsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function index()
    {
        $postValue = Request::request()->toArray();
        $getValue = Request::get()->toArray();

        switch (Request::post()->get('mode')) {
            // --- 결제 수단 설정 저장
            case 'settle_settlekind':
                try {
                    // 휴대폰 결제 사용시
                    if (empty($postValue['mobilePgConfFl']) === false) {
                        $postValue['ph']['useFl'] = 'n';
                    }

                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveOrderSettleKind($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 무통장 입금 은행 등록 / 수정
            case 'bank_register':
            case 'bank_modify':
                try {
                    if (MyGodoSmsServerApi::getAuth() === true) {
                        $order = \App::load('\\Component\\Order\\OrderAdmin');
                        $order->saveBankPolicy($postValue);

                        // 복사 완료 후 SMS세션 제거
                        MyGodoSmsServerApi::deleteAuth();

                        $this->layer(__('저장이 완료되었습니다.'));
                    } else {
                        // 처리불가 메시지 출력
                        $this->layer(__('복사하시려면 SMS인증이 반드시 필요합니다.'));
                    }
                    exit;
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 무통장 입금 은행 복사
            case 'bank_copy':
                try {
                    if (MyGodoSmsServerApi::getAuth() === true) {
                        $order = \App::load('\\Component\\Order\\OrderAdmin');
                        foreach (Request::post()->get('sno') as $sno) {
                            $order->copyBankPolicy($sno);
                        }

                        // 복사 완료 후 SMS세션 제거
                        MyGodoSmsServerApi::deleteAuth();

                        $this->layer(__('복사가 완료 되었습니다.'));
                    } else {
                        // 처리불가 메시지 출력
                        $this->layer(__('복사하시려면 SMS인증이 반드시 필요합니다.'));
                    }
                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                break;

            // 무통장 입금 은행 삭제
            case 'bank_delete':
                try {
                    if (MyGodoSmsServerApi::getAuth() === true) {
                        $order = \App::load('\\Component\\Order\\OrderAdmin');
                        foreach (Request::post()->get('sno') as $sno) {
                            $order->deleteBankPolicy($sno);
                        }

                        // 복사 완료 후 SMS세션 제거
                        MyGodoSmsServerApi::deleteAuth();

                        // 삭제완료 메시지 출력
                        $this->layer(__('삭제 되었습니다.'));
                    } else {
                        // 처리불가 메시지 출력
                        $this->layer(__('삭제하시려면 SMS인증이 반드시 필요합니다.'));
                    }
                } catch (Exception $e) {
                    $this->json([
                        'error' => 1,
                        'message' => $e->getMessage(),
                    ]);
                }
                break;

            // --- PG 설정
            case 'pg_config':
                try {
                    // --- 결제수단 설정 config 불러온뒤 저장
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $settle = gd_policy('order.settleKind');
                    $settle['pc']['useFl'] = gd_isset($postValue['settleKind']['pc']['useFl'], 'n');
                    $settle['pb']['useFl'] = gd_isset($postValue['settleKind']['pb']['useFl'], 'n');
                    $settle['pv']['useFl'] = gd_isset($postValue['settleKind']['pv']['useFl'], 'n');
                    $settle['ph']['useFl'] = gd_isset($postValue['settleKind']['ph']['useFl'], 'n');
                    $settle['pk']['useFl'] = gd_isset($postValue['settleKind']['pk']['useFl'], gd_isset($settle['pk']['useFl'], 'n'));
                    $settle['ec']['useFl'] = gd_isset($postValue['settleKind']['ec']['useFl'], 'n');
                    $settle['eb']['useFl'] = gd_isset($postValue['settleKind']['eb']['useFl'], 'n');
                    $settle['ev']['useFl'] = gd_isset($postValue['settleKind']['ev']['useFl'], 'n');
                    $settle['fc']['useFl'] = gd_isset($postValue['settleKind']['fc']['useFl'], 'n');
                    $settle['fb']['useFl'] = gd_isset($postValue['settleKind']['fb']['useFl'], 'n');
                    $settle['fv']['useFl'] = gd_isset($postValue['settleKind']['fv']['useFl'], 'n');
                    $settle['fh']['useFl'] = gd_isset($postValue['settleKind']['fh']['useFl'], 'n');
                    $settle['fp']['useFl'] = gd_isset($postValue['settleKind']['fp']['useFl'], 'n');
                    if ($postValue['pgName'] == 'nicepay' && empty($postValue['pgCancelPassword']) === false) {
                        $postValue['pgCancelPassword'] = \Encryptor::encrypt($postValue['pgCancelPassword']);
                    }

                    $policy->saveOrderSettleKind($settle);
                    unset($postValue['settleKind']);

                    // --- PG 설정 저장
                    $pg = \App::load('\\Component\\Payment\\PG');
                    $pg->savePgInfo($postValue);

                    $this->layer(__('수정 되었습니다. - 반드시 변경된 정보에 대해 검증을 위해서 테스트 결제를 하시기 바랍니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 핸드폰 결제 PG 설정 저장
            case 'pg_mobile_config':
                try {
                    // --- PG 설정 저장
                    $pg = \App::load('\\Component\\Payment\\PG');
                    $pg->saveMobilePgInfo($postValue);

                    $this->layer(__('수정 되었습니다. - 반드시 변경된 정보에 대해 검증을 위해서 테스트 결제를 하시기 바랍니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- PAYCO 설정
            case 'payco_config':
                try {
                    // --- PAYCO 설정 저장
                    $postValue = Request::post()->toArray();
                    $paycoConfig = \App::load('\\Component\\Payment\\Payco\\PaycoConfig');
                    if($paycoConfig->saveInfoPayco($postValue) !== true) {
                        throw new \Exception(__('정보가 저장되지 않았습니다.'));
                    }
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 카카오 페이 설정
            case 'kakao_config':
                try {
                    // --- 결제수단 설정 config 불러온뒤 저장
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $settle = gd_policy('order.settleKind');
                    $pgConfig = gd_policy('pg.kakaopay');

                    if (empty($postValue['pgId']) === true) {
                        $settle['pk']['useFl'] = 'n';
                    } else {
                        if ($postValue['testYn'] == 'Y') {
                            $settle['pk']['useFl'] = 'n';
                        } else {
                            $settle['pk']['useFl'] = 'y';
                        }
                    }

                    $policy->saveOrderSettleKind($settle);
                    unset($postValue['settleKind']);

                    // --- PG 설정 저장
                    $kakaoConfig = \App::load('\\Component\\Payment\\Kakaopay\\KakaopayConfig');
                    $kakaoConfig->saveInfoKakaopay($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- 해외 PG 설정
            case 'pg_overseas_config':
                try {
                    $postValue = Request::post()->toArray();

                    // --- PG 설정 저장
                    $pg = \App::load('\\Component\\Payment\\PG');
                    $pg->savePgInfoOverseas($postValue);
                    $this->layer(__('수정 되었습니다. - 반드시 변경된 정보에 대해 검증을 위해서 테스트 결제를 하시기 바랍니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // --- PG 개별 승인 신청
            case 'pgPrefix':
            case 'pgPrefixClose':
                try {
                    $pg = \App::load('\\Component\\Payment\\PG');
                    $resultData = $pg->setPgPrefix($postValue);

                    echo $resultData;
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    echo $item;
                }
                break;

            // --- 결제수단 자동 설정 - PG 중앙화에 따른
            case 'pgAutoUpdate':
                try {
                    $pg = \App::load('\\Component\\Payment\\PG');
                    $resultData = $pg->setPgSettleKind($postValue);

                    echo $resultData;
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    echo $item;
                }
                break;
        }
        exit();
    }
}
