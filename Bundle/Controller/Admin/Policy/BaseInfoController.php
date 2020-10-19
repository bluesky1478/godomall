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

use Component\Mall\Mall;
use Component\Mall\MallDAO;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Component\Policy\Policy;
use Exception;
use Request;

/**
 * 기본 정보 설정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class BaseInfoController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'basic', 'info');

        // --- 기본 정보
        try {
            $mallSno = gd_isset(\Request::get()->get('mallSno'), DEFAULT_MALL_NUMBER);

            $globalsInfo = \Globals::get('gGlobal.mallList');

            $mallName = $globalsInfo[$mallSno]['mallName'];

            $domainFl = $globalsInfo[$mallSno]['domainFl'];

            $this->setData('mallInputDisp', $mallSno == 1 ? false : true);

            // 모듈 설정
            $unstoring = \App::load('\\Component\\Delivery\\Unstoring');
            $policy = new Policy();
            $data = $policy->getValue('basic.info', $mallSno);

            $mall = new Mall();

            $mallList = $mall->getListByUseMall();
            if (count($mallList) > 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);
                $this->setData('mallSno', $mallSno);
                if ($mallSno > 1) {
                    $defaultData = gd_policy('basic.info', DEFAULT_MALL_NUMBER);
                    foreach ($defaultData as $key => $value) {
                        if (in_array($key, Mall::GLOBAL_MALL_BASE_INFO) === true) $data[$key] = $value;
                    }

                    $disabled = ' disabled = "disabled"';
                    $readonly = ' readonly = "readonly"';
                    $this->setData('disabled', $disabled);
                    $this->setData('readonly', $readonly);
                }
            }

            // 기본 값 설정
            foreach ($policy->basicInfoData as $val) {
                gd_isset($data[$val]);
            }

            $data['businessNo'] = explode('-', $data['businessNo']);
            $data['email'] = explode('@', $data['email']);
            $data['phone'] = str_replace('-','',$data['phone']);
            $data['fax'] = str_replace('-','',$data['fax']);
            $data['centerPhone'] = str_replace('-','',$data['centerPhone']);
            $data['centerSubPhone'] = str_replace('-','',$data['centerSubPhone']);
            $data['centerFax'] = str_replace('-','',$data['centerFax']);
            $data['centerEmail'] = explode('@', $data['centerEmail']);
            $data['robotsFl'] = gd_isset($data['robotsFl'], 'n');


            // 이전 세금계산서 설정에서 인감 이미지를 등록한 경우
            $taxData = gd_policy('order.taxInvoice');
            if (gd_isset($taxData['taxStampIamge'])) {
                $data['stampImage'] = gd_isset($taxData['taxStampIamge']);
            }
            unset($taxData);

            $checked = [];
            $checked['robotsFl'][$data['robotsFl']] = 'checked="checked"';

            // 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);

            // key값을 sno로 변경한 주소 데이터
            $unstoringNoKeyData = $unstoring->getKeyChangedUnstoringList($domainFl);

            $setNormalUnstoring = [];
            $setNormalReturn = [];

            // 출고지 주소 처리
            if (empty($data['unstoringNo'])) {  //  패치 전
                if ($data['unstoringZonecode'] === $data['zonecode'] && $data['unstoringAddress'] === $data['address'] && $data['unstoringAddressSub'] === $data['addressSub']) {
                    $data['unstoringFl'] = 'same';
                } else {
                    $data['unstoringFl'] = 'new';
                    // 일반 출고지 주소 치환코드
                    $setNormalUnstoring = $unstoring->setNormalUnstoringAddress($data, $domainFl, $mallName);
                }
            } else {    //  패치 후 '주소 등록'을 통해 주소 적용
                if (empty($data['unstoringNoList'])/* && ($data['unstoringZonecode'] === $data['zonecode'] && $data['unstoringAddress'] === $data['address'] && $data['unstoringAddressSub'] === $data['addressSub'])*/) {
                    $data['unstoringFl'] = 'same';
                } else {
                    $data['unstoringFl'] = 'new';
                    $data = $unstoring->getUnstoringInfo($data, $unstoringNoKeyData);
                }
            }


            // 반품/교환지 주소 처리
            if (empty($data['returnNo']) && empty($data['returnNoList'])) {  //  패치 전
                if (($data['returnZonecode'] === $data['zonecode'] && $data['returnAddress'] === $data['address'] && $data['returnAddressSub'] === $data['addressSub'])) {
                    $data['returnFl'] = 'same';
                } elseif ($data['returnZonecode'] === $data['unstoringZonecode'] && $data['returnAddress'] === $data['unstoringAddress'] && $data['returnAddressSub'] === $data['unstoringAddressSub']) {
                    $data['returnFl'] = 'unstoring';
                } else {
                    $data['returnFl'] = 'new';
                    // 일반 반품/교환지 주소 치환코드 주소
                    $setNormalReturn = $unstoring->setNormalReturnAddress($data, $domainFl, $mallName);
                }
            } else {        //  패치 후 '주소 등록'을 통해 주소 적용
                if (empty($data['returnNoList'])) {
                    $data['returnFl'] = 'same';
                } elseif ($data['unstoringNoList'] == $data['returnNoList']) {
                    $data['returnFl'] = 'unstoring';
                } else {
                    $data['returnFl'] = 'new';
                    $data = $unstoring->getReturnInfo($data, $unstoringNoKeyData);
                }
            }

            // 출고지(반품/교환지) 주소 sort
            $unstoring->sortUnstoringInfo($data);

            //  패치 전 일반 출고지(반품/교환지) 주소 치환코드 적용
            if (empty($data['unstoringNo']) || empty($data['returnNo'])) {
                gd_set_policy('basic.info', array_merge($setNormalUnstoring, $setNormalReturn), true, $mallSno);
            }

            $checked['unstoringFl'][$data['unstoringFl']] =
            $checked['returnFl'][$data['returnFl']] = 'checked="checked"';

            // 회사소개
            $inform = new BuyerInform();
            $companyData = $inform->getInformData(BuyerInformCode::COMPANY, $mallSno);
            $data['company'] = $companyData['content'];

            // 검색로봇 설정
            $data['robotsTxt'] = gd_policy('basic.robotsTxt');
            if (empty($data['robotsTxt']['front']) === true) {
                $data['robotsTxt']['front'] = 'User-agent: *' . PHP_EOL . 'Disallow: /' . PHP_EOL . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Googlebot' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Cowbot' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: NaverBot' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Yeti' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'User-agent: Daumoa' . PHP_EOL;
                $data['robotsTxt']['front'] .= 'Disallow: /admin/' . PHP_EOL . 'Disallow: /config/' . PHP_EOL . 'Disallow: /data/' . PHP_EOL . 'Disallow: /module/' . PHP_EOL . 'Disallow: /tmp/' . PHP_EOL;
            }
            if (empty($data['robotsTxt']['mobile']) === true) {
                $data['robotsTxt']['mobile'] = 'User-agent: *' . PHP_EOL . 'Disallow: /' . PHP_EOL . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Googlebot' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Cowbot' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: NaverBot' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Yeti' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'User-agent: Daumoa' . PHP_EOL;
                $data['robotsTxt']['mobile'] .= 'Disallow: /admin/' . PHP_EOL . 'Disallow: /config/' . PHP_EOL . 'Disallow: /data/' . PHP_EOL . 'Disallow: /module/' . PHP_EOL . 'Disallow: /tmp/' . PHP_EOL;
            }

            // 사이트맵 설정
            $data['sitemap'] = gd_policy('basic.sitemap');

            foreach (MallDAO::getInstance()->selectCountries() as $key => $val) {
                $countryAddress['+' . $val['callPrefix']] = $val['countryNameKor'] . '(+' . $val['callPrefix'] . ')';
            }
        } catch (Exception $e) {
            //echo $e->getMessage();
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('mallName', $mallName);
        $this->setData('mallFl', $domainFl);
        $this->setData('emailDomain', $emailDomain);
        $this->setData('checked', $checked);
        $this->setData('countryAddress', $countryAddress);
    }
}
