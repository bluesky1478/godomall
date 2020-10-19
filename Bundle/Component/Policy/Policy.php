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

namespace Bundle\Component\Policy;

use App;
use Bundle\Component\Godo\GodoDwServerApi;
use Bundle\Component\PlusShop\PlusReview\PlusReviewConfig;
use Component\Godo\GodoCenterServerApi;
use Component\Godo\NaverPayAPI;
use Component\Goods\Goods;
use Component\Mall\Mall;
use Component\Member\MemberValidation;
use Component\Policy\Storage\DatabaseStorage;
use Component\Policy\Storage\StorageInterface;
use Component\Storage\Storage;
use Component\Validator\Validator;
use Encryptor;
use Framework\Cache\CacheableProxy;
use Framework\Cache\CacheableProxyFactory;
use Framework\File\FileInfo;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\FileUtils;
use Framework\Utility\HttpUtils;
use Framework\Utility\StringUtils;
use Globals;
use Logger;
use Request;
use UserFilePath;
use FileHandler;

/**
 * 환경설정 저장 모듈 / 저장 처리
 * @package Bundle\Component\Policy
 * @author  Wee Yeongjong <yeongjong.wee@godo.co.kr>
 * @author  Shin Donggyu <artherot@godo.co.kr>
 */
class Policy
{
    /**
     * 기본 정보 필드
     * @var array
     */
    public $basicInfoData = [
        'mallNm',
        'mallNmEng',
        'mallTitle',
        'mallFavicon',
        'mallDomain',
        'ceoNm',
        'mallCategory',
        'robotsFl',
        'mallDescription',
        'mallKeyword',
        'companyNm',
        'businessNo',
        'service',
        'item',
        'email',
        'zonecode',
        'zipcode',
        'address',
        'addressSub',
        'phone',
        'fax',
        'onlineOrderSerial',
        'unstoringNo',
        'unstoringZonecode',
        'unstoringZipcode',
        'unstoringAddress',
        'unstoringAddressSub',
        'unstoringNoList',
        'unstoringZonecodeList',
        'unstoringZipcodeList',
        'unstoringAddressList',
        'unstoringAddressSubList',
        'returnNo',
        'returnZonecode',
        'returnZipcode',
        'returnAddress',
        'returnAddressSub',
        'returnNoList',
        'returnZonecodeList',
        'returnZipcodeList',
        'returnAddressList',
        'returnAddressSubList',
        'centerPhone',
        'centerPhoneHead',
        'centerSubPhone',
        'centerSubPhoneHead',
        'centerFax',
        'centerFaxHead',
        'centerEmail',
        'centerHours',
        'privateNm',
        'privatePosition',
        'privateDepartment',
        'privatePhone',
        'privateEmail',
        'stampImage',
    ];

    /**
     * 국가별 단위 기준 (통화)
     * @var array
     */
    public $setCountryUnit = [
        'kr' => [
            'country' => '한국',
            'currency' => [
                '원' => 'string',
                '￦' => 'symbol',
                'KRW' => 'symbol',
            ],
            'decimal' => '0',
        ],
        /*
        'us' => [
            'country'  => '미국',
            'currency' => [
                '$'   => 'symbol',
                'USD' => 'symbol',
            ],
            'decimal'  => '2',
        ],
        'jp' => [
            'country'  => '일본',
            'currency' => [
                '円'   => 'string',
                '￥'   => 'symbol',
                'JPY' => 'symbol',
            ],
            'decimal'  => '0',
        ],
        'cn' => [
            'country'  => '중국',
            'currency' => [
                '元'   => 'string',
                '￥'   => 'symbol',
                'CNY' => 'symbol',
            ],
            'decimal'  => '2',
        ],
        'eu' => [
            'country'  => '유럽',
            'currency' => [
                '€'   => 'symbol',
                'EUR' => 'symbol',
            ],
            'decimal'  => '2',
        ],
        */
    ];

    /**
     * 무게 단위 기준
     * @var array
     */
    public $setWightUnit = [
        'kg' => '킬로그램(kg)',
        'g' => '그램(g)',
        /*
        'lb' => '파운드(lb)',
        'oz' => '온스(oz)',
        */
    ];

    /**
     * 용량 단위 기준
     * @var array
     */
    public $setVolumeUnit = [
        '㎖' => '밀리리터(㎖)',
        '㎗' => '데시리터(㎗)',
        'ℓ' => '리터(ℓ)',
        'cc' => '시시(cc)',
    ];

    /**
     * @var \Bundle\Component\Storage\FtpStorage|\Bundle\Component\Storage\LocalStorage
     */
    protected $storage;
    /** @var bool $useCacheProxy */
    protected $useCacheProxy = false;

    /**
     * @var StorageInterface
     */
    private $_storage;

    /**
     * @param StorageInterface|null $storage
     */
    public function __construct(StorageInterface $storage = null)
    {
        if ($storage === null) {
            $storage = new DatabaseStorage(App::getInstance('DB'));
        }
        $this->storage = Storage::disk(Storage::PATH_CODE_ETC, 'local');
        $app = \App::getInstance('application');
        $request = \App::getInstance('request');
        $this->useCacheProxy = false;
        if ($this->useCacheProxy) {
            $cache = CacheableProxyFactory::create($storage, 300, 'CacheProxy::' . $request->getServerAddress(), 'Component\Policy\Storage\DatabaseStorage');
            $this->setStorage($cache);
        } else {
            $this->setStorage($storage);
        }
    }

    /**
     * 값 저장하기
     *
     * @param string $name
     * @param mixed $value 값
     * @param int $mallSno
     *
     * @return bool
     */
    public function setValue($name, $value, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if ($this->useCacheProxy) {
            $cache = \App::getInstance('cache');
            $cache->delete('Component\Policy\Storage\DatabaseStorage');
        }

        return $this->getStorage()->setValue($name, $value, $mallSno);
    }

    /**
     * getValue
     *
     * @param string $name
     * @param int $mallSno
     *
     * @return mixed
     */
    public function getValue($name, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $mallBySession = App::getInstance('session')->get(SESSION_GLOBAL_MALL);
        if ($mallBySession['sno'] > DEFAULT_MALL_NUMBER) {
            $mallSno = $mallBySession['sno'];
        }
        $globals = \App::getInstance('globals');
        if ($globals->has('gSite.' . $name) && $mallSno == DEFAULT_MALL_NUMBER) {
            return $globals->get('gSite.' . $name);
        } else {
            return $this->getStorage()->getValue($name, $mallSno);
        }
    }

    /**
     * getDefaultValue 기준몰 설정 가져오기.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getDefaultValue($name) {
        return $this->getStorage()->getDefaultValue($name);
    }

    /**
     * 쇼핑몰명 저장
     *
     * @param string $mallNm 쇼핑몰명
     *
     * @throws \Exception
     */
    public function saveBasicMallnm($mallNm)
    {
        // 쇼핑몰 이름 체크
        if (Validator::required(gd_isset($mallNm)) === false) {
            throw new \Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), '쇼핑몰 이름'));
        }
        if (is_array($mallNm) === false && empty($mallNm) === false && gd_is_html($mallNm) === true) {
            throw new \Exception(sprintf(__('%s은(는) 사용할 수 없습니다.'), '쇼핑몰 이름에 태그 (HTML Tag)'));
        }

        $getValue['mallNm'] = $mallNm;
        $getValue = array_merge(gd_policy('basic.info'), $getValue);

        if ($this->setValue('basic.info', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 기본 정보 저장하기
     *
     * @param array $getValue 데이터
     *
     * @return array
     * @throws \Exception
     */
    public function saveBasicInfo($getValue)
    {
        $v = \App::load('Component\\Validator\\Validator');
        $mallSno = gd_isset($getValue['mallSno'], 1);

        $unstoring = \App::load('\\Component\\Delivery\\Unstoring');

        // 기존 저장된 정보와 같이 저장
        if (empty(gd_policy('basic.info', $mallSno)) === false) {
            $getValue = array_merge(gd_policy('basic.info', $mallSno), $getValue);
        }

        if (!$v::domainKr($getValue['mallDomain'], true)) {
            throw new \Exception('잘못된 쇼핑몰 도메인입니다. 도메인은 [ex)godomall.com] 와 같이 주소만 입력해주시기 바랍니다.');
        }

        // 파비콘 저장경로
        $faviconPath = 'favicon.ico';

        // 파비콘 삭제
        if (isset($getValue['mallFaviconDel']) && empty($getValue['mallFaviconTmp']) === false) {
            //--- 이미지 삭제
            Storage::disk(Storage::PATH_CODE_COMMON, 'local')->delete($faviconPath);
            $getValue['mallFavicon'] = '';
            unset($getValue['mallFaviconTmp']);
        }

        // 파비콘 업로드
        if (gd_file_uploadable(Request::files()->get('mallFavicon'), 'ico') === true) {
            // 이미지 저장
            $tmpImageFile = Request::files()->get('mallFavicon.tmp_name');
            list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
            Storage::disk(Storage::PATH_CODE_COMMON, 'local')->upload($tmpImageFile, $faviconPath);
            $getValue['mallFavicon'] = $faviconPath;
        } else {
            if (empty($getValue['mallFaviconTmp']) === false) {
                $getValue['mallFavicon'] = $getValue['mallFaviconTmp'];
            }
        }

        // 인감 이미지 삭제
        if (isset($getValue['stampImageDel']) && empty($getValue['stampImageDel']) === false) {
            $this->storage->delete($getValue['stampImageTmp']);
            unset($getValue['stampImageTmp']);
            unset($getValue['stampImage']);

            // 이전 세금계산서 설정에서 인감 이미지를 등록한 경우
            $taxData = gd_policy('order.taxInvoice', $mallSno);
            if (gd_isset($taxData['taxStampIamge'])) {
                $taxData['taxStampIamge'] = null;
                if ($this->setValue('order.taxInvoice', $taxData, $mallSno) != true) {
                    throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
                }
            }
            unset($taxData);
        }

        // 인감 이미지 처리
        if (gd_file_uploadable(Request::files()->get('stampImage'), 'image') === true) {
            // 인감 이미지 저장경로
            $stampPath = 'stampImage';

            $this->storage->upload(Request::files()->get('stampImage')['tmp_name'], $stampPath);

            $getValue['stampImage'] = $stampPath;

        } else {
            if (empty($getValue['stampImageTmp']) === false) {
                $getValue['stampImage'] = $getValue['stampImageTmp'];
            }
        }

        // 데이터 조합
        if (isset($getValue['businessNo']) && is_array($getValue['businessNo']) === true) {
            $getValue['businessNo'] = (implode('', $getValue['businessNo']) == '' ? '' : implode('-', $getValue['businessNo']));
        }
        if (isset($getValue['email']) && is_array($getValue['email']) === true) {
            //            $getValue['email'] = (implode('', $getValue['email']) == '' ? '' : implode('@', $getValue['email']));
            $getValue['email'] = implode('@', $getValue['email']);
        }
        if (isset($getValue['zipcode']) && is_array($getValue['zipcode']) === true) {
            $getValue['zipcode'] = (implode('', $getValue['zipcode']) == '' ? '' : implode('-', $getValue['zipcode']));
        }

        if (isset($getValue['phone']) && is_array($getValue['phone']) === true) {
            $getValue['phone'] = (implode('', $getValue['phone']) == '' ? '' : implode('-', ArrayUtils::removeEmpty($getValue['phone'])));
        } elseif (isset($getValue['phone']) && is_string($getValue['phone']) === true) {
            $getValue['phone'] = str_replace("-", "", $getValue['phone']);
            $getValue['phone'] = StringUtils::numberToPhone($getValue['phone']);
        }

        if (isset($getValue['fax']) && is_array($getValue['fax']) === true) {
            $getValue['fax'] = (implode('', $getValue['fax']) == '' ? '' : implode('-', ArrayUtils::removeEmpty($getValue['fax'])));
        } elseif (isset($getValue['fax']) && is_string($getValue['fax']) === true) {
            $getValue['fax'] = str_replace("-", "", $getValue['fax']);
            $getValue['fax'] = StringUtils::numberToPhone($getValue['fax']);
        }

        if (isset($getValue['centerPhone']) && is_array($getValue['centerPhone']) === true) {
            $getValue['centerPhone'] = (implode('', $getValue['centerPhone']) == '' ? '' : implode('-', ArrayUtils::removeEmpty($getValue['centerPhone'])));
        } elseif (isset($getValue['centerPhone']) && is_string($getValue['centerPhone']) === true) {
            $getValue['centerPhone'] = str_replace("-", "", $getValue['centerPhone']);
            $getValue['centerPhone'] = StringUtils::numberToPhone($getValue['centerPhone']);
        }

        if (isset($getValue['centerSubPhone']) && is_array($getValue['centerSubPhone']) === true) {
            $getValue['centerSubPhone'] = (implode('', $getValue['centerSubPhone']) == '' ? '' : implode('-', ArrayUtils::removeEmpty($getValue['centerSubPhone'])));
        } elseif (isset($getValue['centerSubPhone']) && is_string($getValue['centerSubPhone']) === true) {
            $getValue['centerSubPhone'] = str_replace("-", "", $getValue['centerSubPhone']);
            $getValue['centerSubPhone'] = StringUtils::numberToPhone($getValue['centerSubPhone']);
        }

        if (isset($getValue['centerFax']) && is_array($getValue['centerFax']) === true) {
            $getValue['centerFax'] = (implode('', $getValue['centerFax']) == '' ? '' : implode('-', ArrayUtils::removeEmpty($getValue['centerFax'])));
        } elseif (isset($getValue['centerFax']) && is_string($getValue['centerFax']) === true) {
            $getValue['centerFax'] = str_replace("-", "", $getValue['centerFax']);
            $getValue['centerFax'] = StringUtils::numberToPhone($getValue['centerFax']);
        }

        if (isset($getValue['centerEmail']) && is_array($getValue['centerEmail']) === true) {
            // $getValue['centerEmail'] = (implode('', $getValue['centerEmail']) == '' ? '' : implode('@', $getValue['centerEmail']));
            $getValue['centerEmail'] = implode('@', $getValue['centerEmail']);
        }

        if (isset($getValue['privatePhone']) && is_array($getValue['privatePhone']) === true) {
            $getValue['privatePhone'] = (implode('', $getValue['privatePhone']) == '' ? '' : implode('-', ArrayUtils::removeEmpty($getValue['privatePhone'])));
        } elseif (isset($getValue['privatePhone']) && is_string($getValue['privatePhone']) === true) {
            $getValue['privatePhone'] = str_replace("-", "", $getValue['privatePhone']);
            $getValue['privatePhone'] = StringUtils::numberToPhone($getValue['privatePhone']);
        }

        if (isset($getValue['privateEmail']) && is_array($getValue['privateEmail']) === true) {
            $getValue['privateEmail'] = (implode('', $getValue['privateEmail']) == '' ? '' : implode('@', ArrayUtils::removeEmpty($getValue['privateEmail'])));
        }

        // 기본 값 설정
        $setValue = [];
        foreach ($this->basicInfoData as $val) {
            $setValue[$val] = gd_isset($getValue[$val]);
        }

        foreach ($setValue as $val) {
            if (is_array($val) === false && empty($val) === false && gd_is_html($val) === true) {
                throw new \Exception(sprintf(__('%s은(는) 사용할 수 없습니다.'), '태그 (HTML Tag)'));
            }
        }

        // 주소 처리
        if (isset($getValue['unstoringFl']) && $getValue['unstoringFl'] === 'same') {   // 사업장 주소와 동일 선택
            $setValue['unstoringZonecode'] = $setValue['unstoringZonecodeList'][0] = $getValue['zonecode'];                   //  기본 출고지 주소도 사업장 주소로 변경
            $setValue['unstoringZipcode'] = $setValue['unstoringZipcodeList'][0] = $getValue['zipcode'];
            $setValue['unstoringAddress'] = $setValue['unstoringAddressList'][0] = $getValue['address'];
            $setValue['unstoringAddressSub'] = $setValue['unstoringAddressSubList'][0] = $getValue['addressSub'];
            $setValue['unstoringNoList'] = null;
//            $setValue['unstoringNoList'] = null;                                        //  기존 설정된 기본 출고지 주소 유지, 일반 출고지 주소만 사업장 주소로 변경
//            $setValue['unstoringZonecodeList'][0] = $getValue['zonecode'];
//            $setValue['unstoringZipcodeList'][0] = $getValue['zipcode'];
//            $setValue['unstoringAddressList'][0] = $getValue['address'];
//            $setValue['unstoringAddressSubList'][0] = $getValue['addressSub'];
        }
        if (isset($getValue['returnFl']) && $getValue['returnFl'] === 'same') {
            $setValue['returnZonecode'] = $setValue['returnZonecodeList'][0] = $getValue['zonecode'];                      // 기본 반품/교환지 주소도 사업장 주소로 변경
            $setValue['returnZipcode'] = $setValue['returnZipcodeList'][0] = $getValue['zipcode'];
            $setValue['returnAddress'] = $setValue['returnAddressList'][0] = $getValue['address'];
            $setValue['returnAddressSub'] = $setValue['returnAddressSubList'][0] = $getValue['addressSub'];
            $setValue['returnNoList'] = null;
            /*if (empty($getValue['returnNo'])) {                 // 아직 '주소 등록'을 통해 새로 등록한 반품/교환지 주소가 없는 경우에는 기본 반품/교환지도 수정('출고지 주소와 동일'이 선택되있는 경우 때문에)
                $setValue['returnZonecode'] = $getValue['zonecode'];                      // 기본 반품/교환지 주소도 사업장 주소로 변경
                $setValue['returnZipcode'] = $getValue['zipcode'];
                $setValue['returnAddress'] = $getValue['address'];
                $setValue['returnAddressSub'] = $getValue['addressSub'];
            }*/
//            $setValue['returnNoList'] = null;                                           //  기존 설정된 기본 반품/교환지 주소 유지, 일반 반품/교환지 주소만 사업장 주소로 변경
//            $setValue['returnZonecodeList'][0] = $getValue['zonecode'];
//            $setValue['returnZipcodeList'][0] = $getValue['zipcode'];
//            $setValue['returnAddressList'][0] = $getValue['address'];
//            $setValue['returnAddressSubList'][0] = $getValue['addressSub'];
        }

        //  출고지, 반품/교환지 주소 관련 키 값 설정
        $setValueUnstoringKey = ['unstoringNo', 'unstoringZonecode', 'unstoringZipcode', 'unstoringAddress', 'unstoringAddressSub'];
        $setValueUnstoringListKey = ['unstoringNoList', 'unstoringZonecodeList', 'unstoringZipcodeList', 'unstoringAddressList', 'unstoringAddressSubList'];
        $setValueReturnKey = ['returnNo', 'returnZonecode', 'returnZipcode', 'returnAddress', 'returnAddressSub'];
        $setValueReturnListKey = ['returnNoList', 'returnZonecodeList', 'returnZipcodeList', 'returnAddressList', 'returnAddressSubList'];
        $getValueAddressKey = ['addressNo', 'addressZonecode', 'addressZipcode', 'stdAddress', 'stdAddressSub'];
        $dbAddressKey = ['sno', 'unstoringZonecode', 'unstoringZipcode', 'unstoringAddress', 'unstoringAddressSub'];

        // 기본 출고지 주소 처리
        if (isset($getValue['unstoringFl']) && $getValue['unstoringFl'] === 'new') {
            if (empty($getValue['addressNo']) == false) {    // 새로 등록
                if (in_array('unstoring', $getValue['addressFl'])) {    // 기존 출고지 주소 관련 부분 삭제
                    /*foreach ($setValueUnstoringKey as $k => $unstoringKey) {
                        unset($setValue[$unstoringKey]);
                    }*/
                    foreach ($setValueUnstoringListKey as $k => $unstoringListKey) {
                        unset($setValue[$unstoringListKey]);
                    }
                    $i = 0;
                    foreach ($getValue['addressFl'] as $key => $value) {
                        if ($value == 'unstoring') {
                            if (empty($getValue['unstoringNo'])) { // 패치 전 주소가 없는 상태에서 패치 후 첫 주소 등록하는 경우
                                $setValue['unstoringNo'] = $getValue['addressNo'][0];
                            }
                            $setValue['unstoringNoList'][$i] = $getValue['addressNo'][$key];
                            $setValue['unstoringZonecodeList'][$i] = $getValue['addressZonecode'][$key];
                            $setValue['unstoringZipcodeList'][$i] = $getValue['addressZipcode'][$key];
                            $setValue['unstoringAddressList'][$i] = $getValue['stdAddress'][$key];
                            $setValue['unstoringAddressSubList'][$i] = $getValue['stdAddressSub'][$key];
                            $i++;
                            // getValue에 남아있던 출고지 주소 리셋(반품/교환지 주소만 존재)
                            foreach ($getValueAddressKey as $k => $v) {
                                unset($getValue[$v][$key]);
                            }
                            unset($getValue['mainFl'][$key]);
                        }
                    }
                    // getValue에 출고지 관련 unset 후 key값 정리
                    foreach ($getValueAddressKey as $k => $v) {
                        $i = 0;
                        foreach ($getValue[$v] as $gKey => $value) {
                            unset($getValue[$v][$gKey]);
                            $getValue[$v][$i] = $value;
                            $i++;
                        }

                    }
                    // 기본 출고지 주소 적용
                    $unstoringData = $unstoring->getStandardAddress('unstoring', $getValue['mallFl']);
                    if (empty($unstoringData) == false) {
                        foreach ($dbAddressKey as $k => $key) {
                            $setValue[$setValueUnstoringKey[$k]] = $unstoringData[$key];
                        }
                    }
                } elseif (empty($getValue['unstoringInfo'])) {                // 기본 출고지 주소도 사업장 주소로 변경
                    $setValue['unstoringNoList'] = $setValue['unstoringZonecodeList'] = $setValue['unstoringZipcodeList'] =
                    $setValue['unstoringAddressList'] = $setValue['unstoringAddressSubList'] = null;
                    $setValue['unstoringZonecode'] = $setValue['unstoringZonecodeList'][0] = $getValue['zonecode'];
                    $setValue['unstoringZipcode'] = $setValue['unstoringZipcodeList'][0] = $getValue['zipcode'];
                    $setValue['unstoringAddress'] = $setValue['unstoringAddressList'][0] = $getValue['address'];
                    $setValue['unstoringAddressSub'] = $setValue['unstoringAddressSubList'][0] = $getValue['addressSub'];
                }
            } elseif (empty($getValue['unstoringInfo'])) {                    // 기본 출고지 주소도 사업장 주소로 변경
                $setValue['unstoringNoList'] = $setValue['unstoringZonecodeList'] = $setValue['unstoringZipcodeList'] =
                $setValue['unstoringAddressList'] = $setValue['unstoringAddressSubList'] = null;
                $setValue['unstoringZonecode'] = $setValue['unstoringZonecodeList'][0] = $getValue['zonecode'];
                $setValue['unstoringZipcode'] = $setValue['unstoringZipcodeList'][0] = $getValue['zipcode'];
                $setValue['unstoringAddress'] = $setValue['unstoringAddressList'][0] = $getValue['address'];
                $setValue['unstoringAddressSub'] = $setValue['unstoringAddressSubList'][0] = $getValue['addressSub'];
            }

        }

        // 기본 반품/교환지 주소 처리 (출고지와 동일)
        if (isset($getValue['returnFl']) && $getValue['returnFl'] === 'unstoring') {
            // 새로 등록된 반품/교환지 주소가 없는 경우 기본 반품/교환지 주소 적용
//            if (empty($getValue['returnNo'])) {
                foreach ($setValueReturnKey as $k => $returnKey) {                  //  기본 반품/교환지 주소로 기본 출고지 주소를 반영해야 될 경우
                    $setValue[$returnKey] = $setValue[$setValueUnstoringKey[$k]];
                }
//                $setValue['returnNo'] = null;
//            }
            foreach ($setValueReturnListKey as $k => $returnListKey) {              //  일반 반품/교환지 주소로 일반 출고지 주소 반영
                $setValue[$returnListKey] = $setValue[$setValueUnstoringListKey[$k]];
            }
            if ($getValue['unstoringFl'] == 'same') {
                $setValue['returnNoList'] = null;
            }
        }

        // 새로 등록
        if (isset($getValue['returnFl']) && $getValue['returnFl'] === 'new') {
            if (empty($getValue['addressNo']) == false) {
                if (in_array('return', $getValue['addressFl'])) {
                    // 기존 반품/교환지 주소 리셋
                    /*foreach ($setValueReturnKey as $setKey => $returnKey) {
                        unset($setValue[$returnKey]);
                    }*/

                    /*foreach ($getValueAddressKey as $getKey => $addressKey) {
                        $setValue[$setValueReturnKey[$getKey]] = $getValue[$addressKey][0];
                    }*/

                    foreach ($setValueReturnListKey as $k => $returnListKey) {
                        unset($setValue[$returnListKey]);
                    }
                    foreach ($getValueAddressKey as $getKey => $addressKey) {
                        $setValue[$setValueReturnListKey[$getKey]] = $getValue[$addressKey];
                    }

                    if (empty($getValue['returnNo'])) { // 패치 전 주소가 없는 상태에서 패치 후 첫 주소 등록하는 경우
                        $setValue['returnNo'] = $getValue['addressNo'][0];
                    }

                    // 기본 반품/교환지 주소 적용
                    $returnData = $unstoring->getStandardAddress('return', $getValue['mallFl']);
                    if (empty($returnData) == false) {
                        foreach ($dbAddressKey as $k => $key) {
                            $setValue[$setValueReturnKey[$k]] = $returnData[$key];
                        }
                    }

                } elseif (empty($getValue['returnInfo'])) {            // 기본 반품/교환지 주소도 사업장 주소로 변경
                    $setValue['returnNoList'] = $setValue['returnZonecodeList'] = $setValue['returnZipcodeList'] =
                    $setValue['returnAddressList'] = $setValue['returnAddressSubList'] = null;
                    $setValue['returnZonecode'] = $setValue['returnZonecodeList'][0] = $getValue['zonecode'];
                    $setValue['returnZipcode'] = $setValue['returnZipcodeList'][0] = $getValue['zipcode'];
                    $setValue['returnAddress'] = $setValue['returnAddressList'][0] = $getValue['address'];
                    $setValue['returnAddressSub'] = $setValue['returnAddressSubList'][0] = $getValue['addressSub'];
                }
            } elseif (empty($getValue['returnInfo'])) {                // 기본 반품/교환지 주소도 사업장 주소로 변경
                $setValue['returnNoList'] = $setValue['returnZonecodeList'] = $setValue['returnZipcodeList'] =
                $setValue['returnAddressList'] = $setValue['returnAddressSubList'] = null;
                $setValue['returnZonecode'] = $setValue['returnZonecodeList'][0] = $getValue['zonecode'];
                $setValue['returnZipcode'] = $setValue['returnZipcodeList'][0] = $getValue['zipcode'];
                $setValue['returnAddress'] = $setValue['returnAddressList'][0] = $getValue['address'];
                $setValue['returnAddressSub'] = $setValue['returnAddressSubList'][0] = $getValue['addressSub'];
            }
        }

        // 기본 정보 저장
        if ($this->setValue('basic.info', $setValue, $mallSno) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }

        // 기준몰 저장시 해외몰에 공통정보 저장
        if ($mallSno == DEFAULT_MALL_NUMBER) {
            $mall = new Mall();
            $mallDefaultInfo = [];
            foreach ($setValue as $key => $value) {
                if (in_array($key, MALL::GLOBAL_MALL_BASE_INFO) === true) $mallDefaultInfo[$key] = $value;
            }
            $mallList = $mall->getListByUseMall();
            foreach ($mallList as $key => $value) {
                if ($key == $mallSno) continue;
                $mallInfo = array_merge(gd_policy('basic.info', $key), $mallDefaultInfo);
                $this->setValue('basic.info', $mallInfo, $key);
            }
        }

        // 정시개 통계 API로 데이터 전송 처리
        $dw = new GodoDwServerApi();
        $dw->sendBasicPolicyForStatistics($setValue);

        return $setValue;
    }


    /**
     * seo 정보 저장하기
     *
     * @param array $getValue 데이터
     *
     * @return array
     * @throws \Exception
     */
    public function saveBasicSeo($getValue)
    {
        gd_isset($getValue['mallSno'], DEFAULT_MALL_NUMBER);

        if ($getValue['mallSno'] == DEFAULT_MALL_NUMBER) {

            //오픈그래프/트위터 메타태그 기본설정
            $socialData = $getValue['social'];

            // 대표이미지 파일명
            $snsRepresentImagePath = 'snsRepresentImage';

            // 대표이미지 삭제
            if (isset($socialData['snsRepresentImageDel']) && empty($socialData['snsRepresentImageTmp']) === false) {
                Storage::disk(Storage::PATH_CODE_COMMON, 'local')->delete($socialData['snsRepresentImageTmp']);
                $socialData['snsRepresentImage'] = '';
                unset($socialData['snsRepresentImageTmp']);
            }


            // 넘어온 파일이 있는 경우
            if (empty(Request::files()->get('snsRepresentImage.name')) === false) {
                // 확장자 구하기
                $fileinfo = new FileInfo(Request::files()->get('snsRepresentImage.name'));
                $extension = $fileinfo->getExtension();

                // 대표이미지에 확장자 붙이기
                $snsRepresentImagePath .= '.' . $extension;

                // 대표이미지 업로드
                if (FileUtils::isImageExtension($snsRepresentImagePath)) {
                    // 이미지 저장
                    $tmpImageFile = Request::files()->get('snsRepresentImage.tmp_name');
                    list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
                    Storage::disk(Storage::PATH_CODE_COMMON, 'local')->upload($tmpImageFile, $snsRepresentImagePath);
                    $socialData['snsRepresentImage'] = $snsRepresentImagePath;
                } else {
                    if (empty($socialData['snsRepresentImageTmp']) === false) {
                        $socialData['snsRepresentImage'] = $socialData['snsRepresentImageTmp'];
                    }
                }
            }

            ComponentUtils::setPolicy('promotion.snsShare', $socialData);
            unset($getValue['social']);


            // 사이트맵 처리
            if (Request::files()->has('sitemap') === true) {
                $setSitemapData = gd_policy('basic.sitemap');
                foreach (Request::files()->get('sitemap')['tmp_name'] as $key => $val) {
                    // 사이트맵 저장 화일명
                    $sitemapFileName = 'sitemap' . ucwords($key) . '.xml';

                    // 사이트맵 삭제
                    if (isset($getValue['sitemapDel'][$key]) && empty($getValue['sitemapDel'][$key]) === false) {
                        //--- 이미지 삭제
                        Storage::disk(Storage::PATH_CODE_COMMON, 'local')->delete($sitemapFileName);
                        $setSitemapData[$key] = '';
                    } else {
                        // 사이트맵 화일이 없으면
                        if (empty($val) === true) {
                            continue;
                        }
                    }
                    // 사이트맵 업로드
                    if (empty($val) === false) {
                        if (gd_file_uploadable(Request::files()->get('sitemap'), 'xml', $key) === true) {
                            // 이미지 저장
                            $tmpImageFile = Request::files()->get('sitemap.tmp_name.' . $key);
                            Storage::disk(Storage::PATH_CODE_COMMON, 'local')->upload($tmpImageFile, $sitemapFileName);
                            $setSitemapData[$key] = $sitemapFileName;
                        } else {
                            throw new \Exception(sprintf(__('%s 파일만 업로드 가능합니다.'), 'xml'));
                        }
                    }
                }
            }

            $setSitemapData['sitemapAutoFl'] = $getValue['sitemapAutoFl'];
            // 저장
            if (isset($setSitemapData) === true) {
                if ($this->setValue('basic.sitemap', $setSitemapData) != true) {
                    throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
                }
            }
            unset($getValue['sitemapAutoFl']);


            // 로봇 배제 저장
            $robotsTxt = [];
            $robotsTxt['front'] = $getValue['robotsTxt']['front'];
            $robotsTxt['mobile'] = $getValue['robotsTxt']['mobile'];
            if ($this->setValue('basic.robotsTxt', $robotsTxt) != true) {
                throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
            }


            // rss처리
            if (Request::files()->has('rss') === true) {
                $setRssData = gd_policy('basic.seo')['rss'];
                foreach (Request::files()->get('rss')['tmp_name'] as $key => $val) {
                    // 사이트맵 저장 화일명
                    $rssFileName = 'rss' . ucwords($key) . '.xml';

                    // 사이트맵 삭제
                    if (isset($getValue['rssDel'][$key]) && empty($getValue['rssDel'][$key]) === false) {
                        //--- 이미지 삭제
                        Storage::disk(Storage::PATH_CODE_COMMON, 'local')->delete($rssFileName);
                        $setRssData[$key] = '';
                    } else {
                        // 사이트맵 화일이 없으면
                        if (empty($val) === true) {
                            continue;
                        }
                    }
                    // 사이트맵 업로드
                    if (empty($val) === false) {
                        if (gd_file_uploadable(Request::files()->get('rss'), 'xml', $key) === true) {
                            // 이미지 저장
                            $tmpImageFile = Request::files()->get('rss.tmp_name.' . $key);
                            Storage::disk(Storage::PATH_CODE_COMMON, 'local')->upload($tmpImageFile, $rssFileName);
                            $setRssData[$key] = $rssFileName;
                        } else {
                            throw new \Exception(sprintf(__('%s 파일만 업로드 가능합니다.'), 'xml'));
                        }
                    }
                }
            }

            $setRssData['useFl'] = $getValue['rss']['useFl'];
            unset($getValue['rss']);

            $seoData['rss'] = $setRssData;
            $seoData['errPage'] = $getValue['errPage'];
            $seoData['canonicalUrl'] = $getValue['canonicalUrl'];
            $seoData['relationChannel'] = $getValue['relationChannel'];

        } else {
            $seoData['errPage'] = $getValue['errPage'];
        }

        //RSS 설정 ,페이지 경로 설정 , 대표URL 설정 , 연관채널 설정 일괄 저장
        if ($this->setValue('basic.seo', $seoData, $getValue['mallSno']) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
        unset($getValue['rss'], $getValue['errPage'], $getValue['canonicalUrl'], $getValue['relationChannel']);

        //주요태그 저장
        $seoTagData = array_filter($getValue['seo']);
        $seoTag = \App::load('\\Component\\Policy\\SeoTag');

        foreach ($seoTag->seoConfig['commonPage'] as $key => $val) {
            $tmpData = $seoTagData[$val];
            unset($tmpData['sno']);
            if (empty(array_filter($tmpData))) {
                $seoTag->deleteSeoTag(
                    [
                        'path' => $key,
                        'deviceFl' => 'c',
                    ], "pageCode = ''"
                );
            } else {
                $arrData = $seoTagData[$val];
                $arrData['deviceFl'] = "c";
                $arrData['path'] = $key;
                $arrData['mallSno'] = $getValue['mallSno'];

                $seoTag->saveSeoTag($arrData);
                unset($arrData);

            }
            unset($tmpData);
        }
    }

    /**
     * 화일 저장소 관리 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveFileStorage($getValue)
    {
        unset($getValue['mode']);

        $storageDefault = $getValue['storageDefault'];
        unset($getValue['storageDefault']);

        //--- 배열 재정렬
        $chkKey = [];
        foreach ($getValue as $key => $val) {
            // 배열 키 정렬
            $getValue[$key] = array_values($getValue[$key]);

            // 저장소 명이 없는 데이터 체크후 unset 시킴
            if ($key == 'storageName') {
                foreach ($getValue[$key] as $cKey => $cVal) {
                    if (empty($cVal)) {
                        $chkKey[] = $cKey;
                    }
                }
            }
            if (!empty($chkKey)) {
                foreach ($chkKey as $uVal) {
                    unset($getValue[$key][$uVal]);
                }
            }

            // http 경로에 http:// 붙이기 및 뒤에 / 지우기
            if ($key == 'httpUrl') {
                foreach ($getValue[$key] as $hKey => $hVal) {
                    if ($hVal != 'local' && $hVal != 'url') {
                        $this->http_url($getValue[$key][$hKey]);
                    }
                }
            }

            // ftp 기본 경로 앞에 / 넣고 뒤에 / 지우기
            if ($key == 'ftpPath') {
                foreach ($getValue[$key] as $hKey => $hVal) {
                    $this->http_url($getValue[$key][$hKey], '/');
                }
            }

            // ftp 경로에 http:// 지우기 및 뒤에 / 지우기
            if ($key == 'ftpHost') {
                foreach ($getValue[$key] as $hKey => $hVal) {
                    $this->http_url($getValue[$key][$hKey], 'n');
                }
            }

            // ftp password 암호화
            if ($key == 'ftpPw') {
                foreach ($getValue[$key] as $hKey => $hVal) {
                    if ($getValue[$key][$hKey] == '******') {
                        $getValue[$key][$hKey] = $getValue['ftpPwChk'][$hKey];
                    } else {
                        $getValue[$key][$hKey] = Encryptor::encrypt($getValue[$key][$hKey]);
                    }
                }
                //   $getValue[$key][0] = $getValue[$key][1] = '';
            }

            // ftp port 기본값
            if ($key == 'ftpPort') {
                foreach ($getValue[$key] as $hKey => $hVal) {
                    if ($getValue[$key][$hKey] == '') {
                        $getValue[$key][$hKey] = '21';
                    }
                }
            }
        }


        //--- 배열 재정렬 unset 으로 key 값 정렬
        foreach ($getValue as $key => $val) {
            // 배열 키 정렬
            $getValue[$key] = array_values($getValue[$key]);

            // 배열의 키값을 변경 xml 저장을 위한 것임 (키가 숫자인 경우 xml 에러)
            foreach ($getValue[$key] as $sKey => $sVal) {
                $tmpKey = 'imageStorage' . $sKey;
                $getValue[$key][$tmpKey] = $sVal;
                unset($getValue[$key][$sKey]);
            }
        }

        // 상품기본저장소로 등록된 경우
        if (count($storageDefault['goods']) > 0) {
            $getValue['storageDefault']['imageStorage' . $storageDefault['goods']] = ['goods'];
        }

        if ($this->setValue('basic.storage', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 상품 이미지 사이즈 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveGoodsImages($getValue)
    {
        // 기본 이미지 사이즈
        gd_isset($getValue['magnify']['size1'], 500);
        gd_isset($getValue['detail']['size1'], 300);
        gd_isset($getValue['list']['size1'], 130);
        gd_isset($getValue['main']['size1'], 100);
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['magnify']['size1']);
        gd_remove_comma($getValue['detail']['size1']);
        gd_remove_comma($getValue['list']['size1']);
        gd_remove_comma($getValue['main']['size1']);

        $getValue['image']['imageType'] = $getValue['imageType'];
        $imageType = false;
        if ($getValue['imageType'] == 'auto') {
            $imageType = true;
        }

        // 추가 이미지 사이즈 정렬
        ksort($getValue);
        $i = 1;
        foreach ($getValue['image'] as $key => $val) {
            if (preg_match('/^add/', $key)) {
                $newKey = 'add' . $i;
                if ($newKey == $key) {
                    gd_isset($getValue['add' . $i]['size1'], 0);
                    //--- 숫자의 쉼표 제거
                    gd_remove_comma($getValue['add' . $i]['size1']);
                } else {
                    $getValue['add' . $i] = $getValue[$key];
                    unset($getValue[$key]);
                }
                // 사이즈가 없으면 삭제
                if (empty($getValue['add' . $i]['size1']) === true) {
                    unset($getValue['add' . $i]);
                }
                $i++;
            }

            // 이미지 사이즈 설정
            if ($imageType === true) {
                foreach ($getValue['image'][$key] as $kk => $vv) {
                    if (substr($kk, 0, 5) == 'hsize') {
                        unset($getValue['image'][$key][$kk]);
                    }
                }
            }
        }
        // 사이즈가 없는것을 삭제
        $getValue['magnify'] = ArrayUtils::removeEmpty($getValue['magnify']);
        $getValue['detail'] = ArrayUtils::removeEmpty($getValue['detail']);
        ksort($getValue);

        if ($this->setValue('goods.image', $getValue['image']) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 과세 / 비과세 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveGoodsTax($getValue)
    {
        if ($getValue['taxBasic']['goods'] == '1') {
            $getValue['taxFreeFl'] = "f";
        } else {
            $getValue['taxFreeFl'] = "t";
        }
        $getValue['taxPercent'] = $getValue['goodsTax'][$getValue['taxBasic']['goods']];


        if ($getValue['taxBasic']['delivery'] == '1') {
            $getValue['deliveryTaxFreeFl'] = "f";
        } else {
            $getValue['deliveryTaxFreeFl'] = "t";
        }
        $getValue['deliveryTaxPercent'] = $getValue['deliveryTax'][$getValue['taxBasic']['delivery']];

        // 기본값 설정
        gd_isset($getValue['taxFreeFl'], 't');
        gd_isset($getValue['taxPercent'], 10);
        gd_isset($getValue['priceTaxFl'], 'y');
        gd_isset($getValue['deliveryTaxFreeFl'], 't');
        gd_isset($getValue['deliveryTaxPercent'], 10);

        unset($getValue['mode']);
        unset($getValue['taxBasic']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['taxPercent'], '-');
        gd_remove_comma($getValue['deliveryTaxPercent'], '-');

        if ($this->setValue('goods.tax', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 사은품 증정 정책 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveGoodsGift($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['giftFl'], 'n');
        unset($getValue['mode']);

        if ($this->setValue('goods.gift', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 최근 본 상품 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveGoodsToday($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['todayHour'], 0);
        gd_isset($getValue['todayCnt'], 0);
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['todayHour'], '-');
        gd_remove_comma($getValue['todayCnt'], '-');

        // 값 체크
        if ($getValue['todayCnt'] > DEFAULT_LIMIT_TODAY_CNT) {
            $getValue['todayCnt'] = DEFAULT_LIMIT_TODAY_CNT;
        }

        if ($this->setValue('goods.today', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }


    /**
     * 상품정보 노출 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveGoodsDisplay($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['priceFl'], 'y');
        gd_isset($getValue['imageLazyFl'], 'y');
        gd_isset($getValue['goodsModDtTypeUp'], 'n');
        gd_isset($getValue['goodsModDtTypeList'], 'n');
        gd_isset($getValue['goodsModDtTypeAll'], 'n');
        gd_isset($getValue['goodsModDtTypeExcel'], 'n');
        gd_isset($getValue['goodsModDtTypeScm'], 'n');
        gd_isset($getValue['goodsModDtFl'], 'n');
        gd_isset($getValue['optionPriceFl'], 'y');
        unset($getValue['mode']);

        if ($this->setValue('goods.display', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 상품속도개선 테이블 분리
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveGoodsDivision($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['divisionFl'], 'y');

        if ($this->setValue('goods.config', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 주문 기본설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveOrderBasic($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['autoDeliveryCompleteFl'], 'n');
        gd_isset($getValue['autoDeliveryCompleteDay'], '7');
        gd_isset($getValue['autoOrderConfirmFl'], 'n');
        gd_isset($getValue['autoOrderConfirmDay'], '7');
        gd_isset($getValue['reagreeConfirmFl'], 'y');
        $getValue['userHandleAutoSettle'][] = 'c';
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['autoDeliveryCompleteDay'], '-');
        gd_remove_comma($getValue['autoOrderConfirmDay'], '-');

        if ($this->setValue('order.basic', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 장바구니 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveOrderCart($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['periodFl'], 'y');
        gd_isset($getValue['periodDay'], 7);
        gd_isset($getValue['goodsLimitFl'], 'y');
        gd_isset($getValue['goodsLimitCnt'], 100);
        gd_isset($getValue['sameGoodsFl'], 'p');
        gd_isset($getValue['zeroPriceOrderFl'], 'y');
        gd_isset($getValue['directOrderFl'], 'y');
        gd_isset($getValue['moveCartPageDeviceFl'], 'pc');
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['periodDay'], '-');
        gd_remove_comma($getValue['goodsLimitCnt'], '-');

        if ($this->setValue('order.cart', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 상품 보관함 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveOrderWish($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['goodsLimitCnt'], 30);
        gd_isset($getValue['cartToFl'], 'y');
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['goodsLimitCnt'], '-');

        if ($this->setValue('order.wish', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 주문 상태 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveOrderStatus($getValue, $mallSno = DEFAULT_MALL_NUMBER)
    {
        // 기본값 설정
        gd_isset($getValue['autoCancel'], '0');            // 자동취소 기간
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['autoCancel'], '-');

        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $setStatus = $this->setGlobalOrderStatus($this->getValue('order.status'), $getValue);

            $result = $this->setValue('order.status', $setStatus, $mallSno);
        } else {
            $result = $this->setValue('order.status', $getValue);

            $mall = new Mall();
            foreach ($mall->getListByUseMall() as $key => $mall) {
                if ($key == $mallSno) continue;

                if ($this->getValue('order.status', $key)) {
                    $setStatus = $this->setGlobalOrderStatus($getValue, $this->getValue('order.status', $key));
                    $result = $this->setValue('order.status', $setStatus, $key);
                }
            }
        }

        if ($result != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 해외몰 주문 상태 설정 기준몰 기준으로 저장
     *
     * @param array $defaultStatus
     * @param array $getvalue
     *
     * @return array
     */

    private function setGlobalOrderStatus($defaultStatus, $getvalue)
    {
        if (empty($defaultStatus) === true || empty($getvalue) === true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }

        foreach ($defaultStatus as $key => &$value) {
            foreach ($value as $k => &$v) {
                if (in_array('user', array_keys($v))) {
                    if (empty($getvalue[$key][$k]) === true) {
                        if (in_array($k, array_keys($getvalue[$key])) === true) {
                            $v['user'] = $getvalue[$key][$k]['user'];
                        }
                    } else {
                        if (is_array($getvalue[$key][$k]) === true) {
                            $v['user'] = $getvalue[$key][$k]['user'];
                        } else {
                            $v['user'] = $getvalue[$key][$k];
                        }
                    }
                }
            }
        }

        return $defaultStatus;
    }

    /**
     * 결제 수단 설정 가져하기 (기본값 매칭)
     *
     * @param array $data 데이터
     *
     * @return array|null
     * @throws \Exception
     */
    public function getDefaultSettleKind($data = null)
    {
        if ($data === null) {
            $data = gd_policy('order.settleKind');
        }

        gd_isset($data['gb']['useFl'], 'y');
        gd_isset($data['gb']['name'], '무통장 입금');
        gd_isset($data['gb']['mode'], 'general');
        gd_isset($data['pc']['useFl'], 'y');
        gd_isset($data['pc']['name'], '신용카드');
        gd_isset($data['pc']['mode'], 'general');
        gd_isset($data['pb']['useFl'], 'y');
        gd_isset($data['pb']['name'], '계좌이체');
        gd_isset($data['pb']['mode'], 'general');
        gd_isset($data['pv']['useFl'], 'y');
        gd_isset($data['pv']['name'], '가상계좌');
        gd_isset($data['pv']['mode'], 'general');
        gd_isset($data['ph']['useFl'], 'y');
        gd_isset($data['ph']['name'], '휴대폰');
        gd_isset($data['ph']['mode'], 'general');
        gd_isset($data['ec']['useFl'], 'y');
        gd_isset($data['ec']['name'], '신용카드');
        gd_isset($data['ec']['mode'], 'escrow');
        gd_isset($data['eb']['useFl'], 'y');
        gd_isset($data['eb']['name'], '계좌이체');
        gd_isset($data['eb']['mode'], 'escrow');
        gd_isset($data['ev']['useFl'], 'y');
        gd_isset($data['ev']['name'], '가상계좌');
        gd_isset($data['ev']['mode'], 'escrow');
        gd_isset($data['fc']['useFl'], 'y');
        gd_isset($data['fc']['name'], '신용카드');
        gd_isset($data['fc']['mode'], 'fintech');
        gd_isset($data['fa']['useFl'], 'y');
        gd_isset($data['fa']['name'], '무통장입금');
        gd_isset($data['fa']['mode'], 'fintech');
        gd_isset($data['fb']['useFl'], 'y');
        gd_isset($data['fb']['name'], '계좌이체');
        gd_isset($data['fb']['mode'], 'fintech');
        gd_isset($data['fv']['useFl'], 'y');
        gd_isset($data['fv']['name'], '가상계좌');
        gd_isset($data['fv']['mode'], 'fintech');
        gd_isset($data['fh']['useFl'], 'y');
        gd_isset($data['fh']['name'], '휴대폰');
        gd_isset($data['fh']['mode'], 'fintech');
        gd_isset($data['fp']['useFl'], 'y');
        gd_isset($data['fp']['name'], '포인트');
        gd_isset($data['fp']['mode'], 'fintech');
        gd_isset($data['pk']['useFl'], 'y');
        gd_isset($data['pk']['name'], '카카오페이');
        gd_isset($data['pk']['mode'], 'general');

        return $data;
    }

    /**
     * 결제 수단 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveOrderSettleKind($getValue)
    {
        // 기본값 설정
        $getValue = $this->getDefaultSettleKind($getValue);
        unset($getValue['mode']);

        if ($this->setValue('order.settleKind', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 일반 세금계산서 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveTaxInvoice($getValue)
    {
        //고도빌체크 다시한번 체크
        $tax = \App::load('\\Component\\Order\\Tax');
        if ($getValue['eTaxInvoiceFl'] == 'y' && $tax->setCheckConnection($getValue['godobillSiteId'], $getValue['godobillApiKey']) === false) {
            $getValue['eTaxInvoiceFl'] = "n";
        }

        // 기본 값 설정
        gd_isset($getValue['taxInvoiceUseFl'], 'y');
        gd_isset($getValue['taxInvoiceLimitFl'], 'y');
        gd_isset($getValue['taxInvoiceLimitDate'], '5');
        gd_isset($getValue['taxStepFl'], 'p');

        gd_isset($getValue['gTaxInvoiceFl'], 'n');
        gd_isset($getValue['eTaxInvoiceFl'], 'n');
        gd_isset($getValue['taxDeliveryFl'], 'n');
        gd_isset($getValue['TaxMileageFl'], 'n');
        gd_isset($getValue['taxDepositFl'], 'n');
        gd_isset($getValue['taxInvoiceOrderUseFl'], 'y');
        gd_isset($getValue['taxinvoiceInfoUseFl'], 'n');
        gd_isset($getValue['taxinvoiceDeadlineUseFl'], 'n');

        // 이용안내&발행신청마감 문구 저장
        $getInfoValue['taxinvoiceInfo'] = $getValue['taxinvoiceInfo'];
        $getInfoValue['taxinvoiceDeadline'] = $getValue['taxinvoiceDeadline'];

        if ($getValue['gTaxInvoiceFl'] == 'n' && $getValue['eTaxInvoiceFl'] == 'n') $getValue['taxInvoiceUseFl'] = "n";

        unset($getValue['mode'], $getValue['taxinvoiceInfo'], $getValue['taxinvoiceDeadline']);

        if ($this->setValue('order.taxInvoice', $getValue) != true || $this->setValue('order.taxInvoiceInfo', $getInfoValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 예치금 환경설정 저장하기
     *
     * @param $getValue
     *
     * @throws \Exception
     */
    public function saveDepositConfig($getValue)
    {
        //--- 기본값 설정
        gd_isset($getValue['name'], '예치금');
        gd_isset($getValue['unit'], '원');
        gd_isset($getValue['payUsableFl'], 'y');

        unset($getValue['mode']);
        if ($this->setValue('member.depositConfig', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 금액/단위 기준설정 정보 저장
     *
     * @param array $data 데이터
     *
     * @throws \Exception
     */
    public function saveCurrencyUnit($data)
    {
        $setData = [];

        //--- 통화 설정
        gd_isset($data['country'], 'kr');
        gd_isset($data['currency'], '원');

        $setData['currency']['country'] = $data['country'];
        if ($this->setCountryUnit[$data['country']]['currency'][$data['currency']] === 'symbol') {
            $setData['currency']['symbol'] = $data['currency'];
            $setData['currency']['string'] = '';
        } else {
            $setData['currency']['symbol'] = '';
            $setData['currency']['string'] = $data['currency'];
        }
        $setData['currency']['decimal'] = $this->setCountryUnit[$data['country']]['decimal'];

        // 국가별 금액 설정 저장
        if ($this->setValue('basic.currency', $setData['currency']) != true) {
            throw new \Exception(__('국가별 금액 설정 저장이 실패했습니다.'));
        }

        //--- 무게 단위 설정
        gd_isset($data['weight'], 'kg');
        $setData['weight']['unit'] = $data['weight'];

        // 무게 단위 설정 저장
        if ($this->setValue('basic.weight', $setData['weight']) != true) {
            throw new \Exception(__('무게 단위 설정 저장이 실패했습니다.'));
        }

        //--- 용량 단위 설정
        gd_isset($data['volume'], '㎖');
        $setData['volume']['unit'] = $data['volume'];

        // 용량 단위 설정 저장
        if ($this->setValue('basic.volume', $setData['volume']) != true) {
            throw new \Exception(__('용량 단위 설정 저장이 실패했습니다.'));
        }

        //--- 절사 설정
        gd_isset($data['goods']['unitPrecision'], '0.1');
        gd_isset($data['goods']['unitRound'], 'floor');
        gd_isset($data['mileage']['unitPrecision'], '0.1');
        gd_isset($data['mileage']['unitRound'], 'floor');
        gd_isset($data['coupon']['unitPrecision'], '0.1');
        gd_isset($data['coupon']['unitRound'], 'floor');
        gd_isset($data['member_group']['unitPrecision'], '0.1');
        gd_isset($data['member_group']['unitRound'], 'floor');
        gd_isset($data['scm_calculate']['unitPrecision'], '0.1');
        gd_isset($data['scm_calculate']['unitRound'], 'floor');

        if ($setData['currency']['decimal'] === '0') {
            if ($data['goods']['unitPrecision'] < '0.1') {
                $data['goods']['unitPrecision'] = '0.1';
            }
            if ($data['mileage']['unitPrecision'] < '0.1') {
                $data['mileage']['unitPrecision'] = '0.1';
            }
            if ($data['coupon']['unitPrecision'] < '0.1') {
                $data['coupon']['unitPrecision'] = '0.1';
            }
            if ($data['member_group']['unitPrecision'] < '0.1') {
                $data['member_group']['unitPrecision'] = '0.1';
            }
            if ($data['scm_calculate']['unitPrecision'] < '0.1') {
                $data['scm_calculate']['unitPrecision'] = '0.1';
            }
        }
        $setData['trunc']['goods'] = $data['goods'];
        $setData['trunc']['mileage'] = $data['mileage'];
        $setData['trunc']['coupon'] = $data['coupon'];
        $setData['trunc']['member_group'] = $data['member_group'];
        $setData['trunc']['scm_calculate'] = $data['scm_calculate'];

        // 절사 설정 저장
        if ($this->setValue('basic.trunc', $setData['trunc']) != true) {
            throw new \Exception(__('절사 설정 저장이 실패했습니다.'));
        }
    }

    /**
     * 방문/구매 및 로그아웃
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveMemberAccess($getValue)
    {
        // 비회원 주문자 본인인증 서비스 사용 시 본인인증 모듈 체크
        if (!MemberValidation::checkGuestAuthService(null, null, null, $getValue['guestUnder14Fl'] == 's')) {
            throw new \Exception(('주문자 본인인증 서비스 사용이 불가합니다. <br/> 본인인증서비스 사용설정 후 다시 시도해주세요.'), 904);
        }

        // Validation
        $validator = new Validator();
        $validator->add('introFrontUseFl', 'yn');
        $validator->add('introMobileUseFl', 'yn');
        $validator->add('introFrontAccess', '');
        $validator->add('introMobileAccess', '');
        $validator->add('buyAuthGb', '');
        $validator->add('sessTimeUseFl', 'yn');
        $validator->add('sessTime', '');
        $validator->add('chooseMileageCoupon', 'yn');
        $validator->add('guestUnder14Fl', '');
        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        $getValue = ArrayUtils::removeEmpty($getValue);

        //--- 기본값 설정
        gd_isset($getValue['introFrontUseFl'], 'n');
        gd_isset($getValue['introMobileUseFl'], 'n');
        gd_isset($getValue['introFrontAccess'], 'free');
        gd_isset($getValue['introMobileAccess'], 'free');
        gd_isset($getValue['buyAuthGb'], 'free');
        gd_isset($getValue['sessTimeUseFl'], 'n');
        gd_isset($getValue['chooseMileageCoupon'], 'n');
        gd_isset($getValue['guestUnder14Fl'], 'n');

        if ($this->setValue('member.access', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 회원가입정책관리
     *
     * @param $data
     *
     * @return bool
     * @throws \Exception
     */
    public function saveMemberJoin($data)
    {
        // 만 14세 미만 가입 설정 조건 체크
        if (!\Component\Member\MemberValidation::checkUnder14Policy($data['under14Fl'])) {
            throw new \Exception(__('<div><strong><span class="text-danger">가입연령제한 설정 사용이 불가합니다.</span><br />만 14(19)세 미만 가입연령제한 설정을 사용할 경우,<br />본인인증서비스를 사용하시거나 회원가입 항목의 \'생일\' 항목을 필수로 설정하셔야 합니다.</strong></div>
                <div class="mgl20 mgt10" style="font-weight:bold; list-style-type: disc;">
                    <ul>
                        <li style="list-style-type: disc;">본인인증서비스 설정</li>
                        <ul>
                            <li>-&nbsp;<a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link-underline">휴대폰인증</a></li> 
                            <li>-&nbsp;<a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link-underline">아이핀인증</a></li>
                        </ul>
                        <ul>
                            <li  class="mgt10" style="list-style-type: disc;">\'생일\'항목 사용, 필수 설정</li>
                        </ul>
                        <ul>
                            <li>-&nbsp;<a href="../member/member_joinitem.php" target="_blank" class="btn-link-underline">회원 가입 항목 관리</a></li> 
                        </ul>
                    </ul>
                </div>'));
        }

        // 본인 인증 제외할 수 있는 조건에 해당되는지 체크
        if (\Component\Member\MemberValidation::checkRequireSNSMemberAuth($data['snsMemberAuthFl'], $data['under14Fl']) === true) {
            throw new \Exception(__('<div class="mgb10"><strong>\'간편 로그인 본인인증 제외설정\'을 \'제외함\'으로 설정할 수 없습니다.</strong><br /><br />\'가입연령제한 설정\'을 사용하고 가입 항목 중 \'생일\'이 필수가 아닌 경우, <br />본인확인인증서비스가 필수이므로 \'제외함\'설정이 불가합니다.</div>'), 902);
        }

        // Validation
        $validator = new Validator();
        $validator->add('appUseFl', 'pattern', true, '{가입 시 인증 설정}', '/^(n|y|company)$/');
        $validator->add('under14Fl', 'pattern', true, '{만 14세 미만 가입설정}', '/^(n|y|no)$/');
        $validator->add('snsMemberAuthFl', 'pattern', true, '{본인인증 제외설정}', '/^(n|y)$/');
        $validator->add('under14ConsentFl', 'pattern', true, '{만 14세 동의 항목 설정}', '/^(y)$/');
        $validator->add('unableid', '');
        $validator->add('limitAge', '');
        $validator->add('rejoinFl', 'yn');
        $validator->add('groupSno', '');
        if ($data['rejoinFl'] == 'y') {
            $validator->add('rejoin', 'number', true, '{재가입 불가 기간}');
        }
        if ($validator->act($data, true) === false) {
            \App::getInstance('logger')->error(__METHOD__, $validator->errors);
            throw new \Exception(implode("\n", $validator->errors), 500);
        }
        $getValue = ArrayUtils::removeEmpty($data);

        return $this->setValue('member.join', $getValue);
    }

    /**
     * 회원가입 이벤트 정책관리
     *
     * @param $data
     * @param $files
     *
     * @return bool
     * @throws \Exception
     */
    public function saveMemberJoinEvent($data, $files)
    {
        $validator = new Validator();
        if($data['eventType'] == 'order') {
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            if(empty($data['couponNo']) == false && !$coupon->checkCouponType($data['couponNo'])) {
                throw new \Exception(__('발급종료 상태의 쿠폰이 선택되었습니다.<br/>쿠폰을 다시 선택해주세요.'), 903);
            }
            if ($data['bannerImagePcDel'] === 'Y') {
                Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->delete($data['bannerImagePc']);
                $data['bannerImagePc'] = '';
            }
            if ($data['bannerImageMobileDel'] === 'Y') {
                Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->delete($data['bannerImageMobile']);
                $data['bannerImageMobile'] = '';
            }
            if ($data['bannerImageType'] == 'self') {
                if (FileHandler::isDirectory(UserFilePath::data('join_event')) === false) {
                    $result = FileHandler::makeDirectory(UserFilePath::data('join_event'), 0707);
                    if ($result !== true) {
                        throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'), 903);
                    }
                }

                if ($files['bannerImagePcFile']['size'] > 0) {
                    $uploadFile = $files['bannerImagePcFile'];
                    if (gd_file_uploadable($uploadFile, 'image') === true) {
                        Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->upload($uploadFile['tmp_name'], $uploadFile['name']);
                        $data['bannerImagePc'] = $uploadFile['name'];
                    } else {
                        throw new \Exception(__('등록 불가한 확장자입니다. '), 903);
                    }
                }
                if ($files['bannerImageMobileFile']['size'] > 0) {
                    $uploadFile = $files['bannerImageMobileFile'];
                    if (gd_file_uploadable($uploadFile, 'image') === true) {
                        Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->upload($uploadFile['tmp_name'], $uploadFile['name']);
                        $data['bannerImageMobile'] = $uploadFile['name'];
                    } else {
                        throw new \Exception(__('등록 불가한 확장자입니다. '), 903);
                    }
                }
            }

            // Validation
            $validator->add('useFl', 'yn');
            $validator->add('deviceType', 'alpha', true); // 사용범위–PC+모바일(‘all’),PC(‘pc’),모바일(‘mobile’)
            $validator->add('couponNo', '');
            $validator->add('bannerFl', 'yn');
            $validator->add('bannerImageType', 'alpha', true); // 기본 배너(‘basic’),이미지 직접 등록(‘self’)
            $validator->add('bannerImagePc', '');
            $validator->add('bannerImageMobile', '');

            $configNm = 'member.joinEventOrder';
        } else {
            gd_isset($data['applySameFl'],'n');
            $data['applyPc'] = implode(STR_DIVISION, $data['applyPc']);
            if($data['applySameFl'] == 'y') {
                $data['applyMobile'] = $data['applyPc'];
            } else {
                $data['applyMobile'] = implode(STR_DIVISION, $data['applyMobile']);
            }

            if ($data['pushIconDel'] === 'Y') {
                Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->delete($data['pushIcon']);
                $data['pushIcon'] = '';
            }
            if ($data['pushDescriptionImagePcDel'] === 'Y') {
                Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->delete($data['pushDescriptionImagePc']);
                $data['pushDescriptionImagePc'] = '';
            }
            if ($data['pushDescriptionImageMobileDel'] === 'Y') {
                Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->delete($data['pushDescriptionImageMobile']);
                $data['pushDescriptionImageMobile'] = '';
            }
            if ($data['iconType'] == 'self') {
                if (FileHandler::isDirectory(UserFilePath::data('join_event')) === false) {
                    $result = FileHandler::makeDirectory(UserFilePath::data('join_event'), 0707);
                    if ($result !== true) {
                        throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'), 903);
                    }
                }
                if ($files['pushIconFile']['size'] > 0) {
                    $uploadFile = $files['pushIconFile'];
                    if (gd_file_uploadable($uploadFile, 'image') === true) {
                        Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->upload($uploadFile['tmp_name'], $uploadFile['name']);
                        $data['pushIcon'] = $uploadFile['name'];
                    } else {
                        throw new \Exception(__('등록 불가한 확장자입니다. '), 903);
                    }
                }
            }
            if ($data['pushDescriptionType'] == 'image') {
                if (FileHandler::isDirectory(UserFilePath::data('join_event')) === false) {
                    $result = FileHandler::makeDirectory(UserFilePath::data('join_event'), 0707);
                    if ($result !== true) {
                        throw new \Exception(__('파일 저장중에 오류가 발생하여 실패되었습니다.'), 903);
                    }
                }

                if ($files['pushDescriptionImagePcFile']['size'] > 0) {
                    $uploadFile = $files['pushDescriptionImagePcFile'];
                    if (gd_file_uploadable($uploadFile, 'image') === true) {
                        Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->upload($uploadFile['tmp_name'], $uploadFile['name']);
                        $data['pushDescriptionImagePc'] = $uploadFile['name'];
                    } else {
                        throw new \Exception(__('등록 불가한 확장자입니다. '), 903);
                    }
                }
                if ($files['pushDescriptionImageMobileFile']['size'] > 0) {
                    $uploadFile = $files['pushDescriptionImageMobileFile'];
                    if (gd_file_uploadable($uploadFile, 'image') === true) {
                        Storage::disk(Storage::PATH_CODE_JOIN_EVENT)->upload($uploadFile['tmp_name'], $uploadFile['name']);
                        $data['pushDescriptionImageMobile'] = $uploadFile['name'];
                    } else {
                        throw new \Exception(__('등록 불가한 확장자입니다. '), 903);
                    }
                }
            }

            // Validation
            $validator->add('pushFl', 'yn');
            $validator->add('applySameFl', 'yn');
            $validator->add('applyPc', '');
            $validator->add('applyMobile', '');
            $validator->add('pushType', 'alpha', true); // 노출시점-페이지접근시(‘all’), n 번 이상 이동시(‘cnt’)
            $validator->add('pushCnt', 'number');
            $validator->add('pushIcon', '');
            $validator->add('position', 'alpha', true); // right, left, center
            $validator->add('iconType', 'alpha', true); // false, basic, self
            $validator->add('pushDescriptionType', 'alpha', true); // text, image
            $validator->add('pushDescriptionText', '');
            $validator->add('pushDescriptionImagePc', '');
            $validator->add('pushDescriptionImageMobile', '');
            $validator->add('bgColor', '');
            $validator->add('textColor', '');

            $configNm = 'member.joinEventPush';
        }

        if ($validator->act($data, true) === false) {
            \App::getInstance('logger')->error(__METHOD__, $validator->errors);
            throw new \Exception(implode("\n", $validator->errors), 500);
        }

        $getValue = ArrayUtils::removeEmpty($data);
        return $this->setValue($configNm, $getValue);
    }

    /**
     * 회원등급 평가방법 설정 회원등급 평가방법 저장
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     * @deprecated
     * @see \Bundle\Component\Policy\GroupPolicy::save
     */
    public function saveMemberGroup($getValue)
    {
        $validator = new Validator();
        $validator->add('grpLabel', '');
        $validator->add('automaticFl', 'yn');
        $validator->add('apprSystem', '');
        $validator->add('apprPointTitle', '');
        $validator->add('apprPointLabel', '');
        $validator->add('appraisalPointOrderPriceFl', '');
        $validator->add('appraisalPointOrderRepeatFl', '');
        $validator->add('appraisalPointReviewRepeatFl', '');
        $validator->add('appraisalPointLoginRepeatFl', '');
        $validator->add('apprPointOrderPriceUnit', '');
        $validator->add('apprPointOrderPricePoint', '');
        $validator->add('apprPointOrderRepeatPoint', '');
        $validator->add('apprPointReviewRepeatPoint', '');
        $validator->add('apprPointLoginRepeatPoint', '');
        $validator->add('apprPointOrderPriceUnitMobile', '');
        $validator->add('apprPointOrderPricePointMobile', '');
        $validator->add('apprPointOrderRepeatPointMobile', '');
        $validator->add('apprPointReviewRepeatPointMobile', '');
        $validator->add('apprPointLoginRepeatPointMobile', '');
        $validator->add('downwardAdjustment', 'yn');
        $validator->add('calcPeriodFl', 'yn');
        $validator->add('calcPeriodBegin', '');
        $validator->add('calcPeriodMonth', '');
        $validator->add('calcCycleMonth', '');
        $validator->add('calcCycleDay', '');
        $validator->add('calcKeep', '');
        $validator->add('autoAppraisalDateTime', 'date');

        if ($validator->act($getValue, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }
        $getValue = ArrayUtils::removeEmpty($getValue);
        $memberGroup = $this->getValue('member.group');
        if ($memberGroup) {
            $getValue = array_merge($memberGroup, $getValue);
        }
        if ($this->setValue('member.group', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 아이핀
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveMemberAuthIpin($getValue)
    {
        // 만 14세 미만 가입 설정 조건 체크
        if (!MemberValidation::checkUnder14Policy(null, null, null, null, $getValue['useFl'] == 'y')) {
            throw new \Exception(__('<div><strong><span class="text-danger">본인인증 서비스 사용안함 설정이 불가합니다.</span><br />만 14(19)세 미만 가입연령제한 설정을 사용중입니다.<br />본인인증서비스를 사용하시거나 회원가입 항목의 \'생일\'항목을 필수로 설정하셔야 합니다.</strong></div><div class="mgl30 mgt10" style="font-weight:bold; list-style-type: disc;"><ul><li style="list-style-type: disc;">본인인증서비스 설정</li><ul><li>-&nbsp;<a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link-underline">휴대폰인증</a></li> <li>-&nbsp;<a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link-underline">아이핀인증</a></li></ul><ul><li  class="mgt10" style="list-style-type: disc;">\'생일\'항목 사용, 필수 설정</li></ul><ul><li>-&nbsp;<a href="../member/member_joinitem.php" target="_blank" class="btn-link-underline">회원 가입 항목 관리</a></li></ul></ul></div>'));
        }

        // 비회원 주문자 본인인증 서비스 사용 체크
        if (!MemberValidation::checkGuestAuthService(null, null, $getValue['useFl'] == 'y')) {
            throw new \Exception(__('본인인증 서비스 사용안함 설정이 불가합니다. <br />만 14(19)세 미만 비회원 주문 제한 설정을 사용중입니다.'));
        }

        // Validation
        $validator = new Validator();
        $validator->add('siteCode', '');            // NICE신용평가정보 아이핀 용
        $validator->add('sitePass', '');            // NICE신용평가정보 아이핀 용
        $validator->add('id', '');                // 구 한국신용정보 아이핀 용
        $validator->add('SIKey', '');            // 구 한국신용정보 아이핀 용
        $validator->add('athKeyStr', '');        // 구 한국신용정보 아이핀 용
        $validator->add('useFl', 'yn');
        $validator->add('minorFl', 'yn');
        $validator->add('codeValue', '');
        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        $getValue = ArrayUtils::removeEmpty($getValue);

        //--- 기본값 설정
        gd_isset($getValue['useFl'], 'n');
        gd_isset($getValue['minorFl'], 'n');
        gd_isset($getValue['codeValue'], '6');

        if ($this->setValue('member.ipin', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 휴대폰본인확인 정책 저장
     *
     * @param $getValue
     *
     * @throws \Exception
     */
    public function saveMemberAuthCellphone($getValue)
    {
        // 만 14세 미만 가입 설정 조건 체크
        if (!MemberValidation::checkUnder14Policy(null, null, null, $getValue['useFl']=='y', null, $getValue['useFlKcp']=='y')) {
            throw new \Exception(__('<div><strong><span class="text-danger">본인인증 서비스 사용안함 설정이 불가합니다.</span><br />만 14(19)세 미만 가입연령제한 설정을 사용중입니다.<br />본인인증서비스를 사용하시거나 회원가입 항목의 \'생일\'항목을 필수로 설정하셔야 합니다.</strong></div>
                <div class="mgl30 mgt10" style="font-weight:bold; list-style-type: disc;">
                    <ul>
                        <li style="list-style-type: disc;">본인인증서비스 설정</li>
                        <ul>
                            <li>-&nbsp;<a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link-underline">휴대폰인증</a></li> 
                            <li>-&nbsp;<a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link-underline">아이핀인증</a></li>
                        </ul>
                        <ul>
                            <li  class="mgt10" style="list-style-type: disc;">\'생일\'항목 사용, 필수 설정</li>
                        </ul>
                        <ul>
                            <li>-&nbsp;<a href="../member/member_joinitem.php" target="_blank" class="btn-link-underline">회원 가입 항목 관리</a></li> 
                        </ul>
                    </ul>
                </div>'));
        }

        // 비회원 주문자 본인인증 서비스 사용 체크
        if (!MemberValidation::checkGuestAuthService($getValue['useFl']=='y', $getValue['useFlKcp']=='y', null)) {
            throw new \Exception(__('본인인증 서비스 사용안함 설정이 불가합니다. <br />만 14(19)세 미만 비회원 주문 제한 설정을 사용중입니다.'));
        }

        // Validation
        $validator = new Validator();
        $validator->add('cpCode', '');            //  드림시큐리티의 휴대폰 본인확인 용
        $validator->add('useFl', 'yn');
        $validator->add('useDataJoinFl', 'yn');
        $validator->add('useDataModifyFl', 'yn');
        $validator->add('minorFl', 'yn');
        $validator->add('codeValue', '');
        if ($validator->act($getValue, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }
        $getValue = ArrayUtils::removeEmpty($getValue);

        //--- 기본값 설정
        gd_isset($getValue['useFl'], 'n');
        gd_isset($getValue['useDataJoinFl'], 'y');
        gd_isset($getValue['useDataModifyFl'], 'n');
        gd_isset($getValue['minorFl'], 'n');
        gd_isset($getValue['codeValue'], '6');

        //--- 모듈 호출
        $godoCenterServiceApi = new GodoCenterServerApi();
        if ($getValue['useFl'] === 'y') {
            if ($godoCenterServiceApi->checkPrefixDreamsecurity($getValue['cpCode']) === false) {
                throw new \Exception(__('회원사 코드가 정확하지 않습니다. 발급받은 코드를 확인하세요.'));
            }
        }

        if ($this->setValue('member.auth_cellphone', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }

        //--- 고도로 전송
        $godoCenterServiceApi->saveConfigDreamsecurity($getValue['cpCode']);
    }

    /**
     * saveMemberSleep 휴면회원정책 저장
     *
     * @param $getValue
     *
     * @throws \Exception
     */
    public function saveMemberSleep($getValue)
    {
        gd_isset($getValue['checkPhone'], 'n');
        gd_isset($getValue['checkEmail'], 'n');
        gd_isset($getValue['authSms'], 'n');
        gd_isset($getValue['authEmail'], 'n');
        gd_isset($getValue['authIpin'], 'n');
        gd_isset($getValue['authRealName'], 'n');
        gd_isset($getValue['wakeType'], 'normal');
        gd_isset($getValue['initMemberGroup'], 'n');

        if ($this->setValue('member.sleep', $getValue) !== true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 비밀번호 찾기 설정
     *
     * @param $data
     *
     * @return bool
     * @throws \Exception
     */
    public function saveMemberPasswordFind($data)
    {
        if ($data['emailFl'] == 'n' && $data['smsFl'] == 'n') {
            throw new \Exception(__('비밀번호 찾기 인증 방법은 1개 이상 사용하셔야 합니다.'));
        }

        // Validation
        $validator = new Validator();
        $validator->add('emailFl', 'yn', true, '{이메일 주소 인증}');
        $validator->add('smsFl', 'yn', true, '{휴대폰 번호 인증}');
        if ($validator->act($data, true) === false) {
            throw new \Exception(implode("\n", $validator->errors), 500);
        }
        $getValue = ArrayUtils::removeEmpty($data);

        return $this->setValue('member.passwordFind', $getValue);
    }

    /**
     * 운영보안설정
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveManageSecurity($getValue)
    {
        $ipAdmin = $ipFront = $ipExcel = $ipLoginTryAdmin = [];
        // Validation
        $validator = new Validator();
        $validator->add('smsSecurity', 'yn', true);
        $validator->add('screenSecurity', 'yn', true);
        $validator->add('sessionLimitUseFl', 'yn', true);
        $validator->add('sessionLimitTime', null);
        $validator->add('ipAdminSecurity', 'yn', true);
        $validator->add('ipAdmin', null);
        $validator->add('ipAdminBandWidth', null);
        $validator->add('ipFrontSecurity', 'yn', true);
        $validator->add('ipFront', null);
        $validator->add('ipFrontBandWidth', null);
        $validator->add('excel', null);
        $validator->add('ipExcel', null);
        $validator->add('ipExcelBandWidth', null);
        $validator->add('unDragFl', 'yn', true);
        $validator->add('unContextmenuFl', 'yn', true);
        $validator->add('managerUnblockFl', 'yn', true);
        $validator->add('smsSecurityFl', 'y');
        $validator->add('emailSecurityFl', 'y');
        $validator->add('authLoginPeriod', 'number', true);
        $validator->add('countryAccessBlocking', null);
        $validator->add('ipLoginTryAdmin', null);
        $validator->add('ipLoginTryAdminBandWidth', null);
        if ($validator->act($getValue, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }

        $checkIpBandWidth = function ($arrIp, $ipBandWidth) {
            foreach ($arrIp as $key => $value) {
                if (trim($ipBandWidth[$key]) !== '') {
                    if (trim($value[3]) === '' || (int)$value[3] > (int)$ipBandWidth[$key]) {
                        throw new \Exception(__('정확한 IP 대역을 입력해주세요.'));
                    }
                }
            }
        };

        $addIp = function (&$arr1, $arr2) {
            $ipKey = 0;
            for ($i = 0; $i < count($arr2); $i++) {
                $j = $i + 1;
                $arr1[$ipKey][] = $arr2[$i];
                if (!($j % 4)) {
                    $ipKey++;
                }
            }
        };

        if ($getValue['ipAdminSecurity'] == 'y') {
            if (!is_array($getValue['ipAdmin']) && count($getValue['ipAdmin']) < 4) {
                throw new \Exception(__('관리자 IP 접속제한을 사용하시려면 반드시 IP를 등록하셔야 합니다.'));
            }
            foreach ($getValue['ipAdmin'] as $ipVal) {
                if (trim($ipVal) === '') {
                    throw new \Exception(__('관리자 IP 접속제한을 사용하시려면 반드시 유효한 IP를 등록하셔야 합니다.'));
                }
            }
            $addIp($ipAdmin, $getValue['ipAdmin']);
            $checkIpBandWidth($ipAdmin, $getValue['ipAdminBandWidth']);
        }
        if ($getValue['ipFrontSecurity'] == 'y') {
            foreach ($getValue['ipFront'] as $ipVal) {
                if (trim($ipVal) === '') {
                    throw new \Exception(__('쇼핑몰 IP 접속제한을 사용하시려면 반드시 유효한 IP를 등록하셔야 합니다.'));
                }
            }
            $addIp($ipFront, $getValue['ipFront']);
            $checkIpBandWidth($ipFront, $getValue['ipFrontBandWidth']);
        }
        if (in_array('ip', $getValue['excel']['auth'])) {
            $addIp($ipExcel, $getValue['ipExcel']);
            $checkIpBandWidth($ipFront, $getValue['ipExcelBandWidth']);
            $manageSecurityVal['ipExcel'] = $ipExcel;
            $manageSecurityVal['ipExcelBandWidth'] = $getValue['ipExcelBandWidth'];
        } else {
            $manageSecurityVal['ipExcel'] = '';
            $manageSecurityVal['ipExcelBandWidth'] = '';
        }

        if ($getValue['ipLoginTryAdmin']) {
            foreach ($getValue['ipLoginTryAdmin'] as $ipVal) {
                if (trim($ipVal) === '') {
                    throw new \Exception(__('쇼핑몰 관리자 IP 접근시도 예외 등록을 사용하시려면 반드시 유효한 IP를 등록하셔야 합니다.'));
                }
            }
            $addIp($ipLoginTryAdmin, $getValue['ipLoginTryAdmin']);
            $checkIpBandWidth($ipLoginTryAdmin, $getValue['ipLoginTryAdminBandWidth']);
        }

        //--- 기본값 설정
        // 운영보안
        gd_isset($getValue['smsSecurityFl'], 'n');
        gd_isset($getValue['emailSecurityFl'], 'n');
        gd_isset($getValue['smsSecurity'], 'n');
        gd_isset($getValue['screenSecurity'], 'n');
        gd_isset($getValue['sessionLimitUseFl'], 'n');
        gd_isset($getValue['sessionLimitTime']);
        gd_isset($getValue['ipAdminSecurity'], 'n');
        gd_isset($getValue['ipFrontSecurity'], 'n');
        gd_isset($getValue['authLoginPeriod'], 0);
        gd_isset($getValue['countryAccessBlocking'], '');
        gd_isset($getValue['excel']['use'], 'y');
        if(empty($getValue['excel']['scope']['company'])) $getValue['excel']['scope']['company'][] = 'member';
        if(empty($getValue['excel']['auth'])) $getValue['excel']['auth'][] = 'sms';
        $manageSecurityVal['excel'] = $getValue['excel'];
        $manageSecurityVal['smsSecurity'] = $getValue['smsSecurity'];
        $manageSecurityVal['screenSecurity'] = $getValue['screenSecurity'];
        $manageSecurityVal['sessionLimitUseFl'] = $getValue['sessionLimitUseFl'];
        $manageSecurityVal['sessionLimitTime'] = $getValue['sessionLimitTime'];
        $manageSecurityVal['ipAdminSecurity'] = $getValue['ipAdminSecurity'];
        $manageSecurityVal['smsSecurityFl'] = $getValue['smsSecurityFl'];
        $manageSecurityVal['emailSecurityFl'] = $getValue['emailSecurityFl'];
        if ($getValue['smsSecurity'] === 'y') {
            $manageSecurityVal['authLoginPeriod'] = $getValue['authLoginPeriod'];
        }
        if ($getValue['ipAdminSecurity'] == 'y') {
            $manageSecurityVal['ipAdmin'] = $ipAdmin;
            $manageSecurityVal['ipAdminBandWidth'] = $getValue['ipAdminBandWidth'];
        } else {
            $manageSecurityVal['ipAdmin'] = $manageSecurityVal['ipAdminBandWidth'] = '';
        }
        $manageSecurityVal['ipFrontSecurity'] = $getValue['ipFrontSecurity'];
        if ($getValue['ipFrontSecurity'] == 'y') {
            $manageSecurityVal['ipFront'] = $ipFront;
            $manageSecurityVal['ipFrontBandWidth'] = $getValue['ipFrontBandWidth'];
        } else {
            $manageSecurityVal['ipFront'] = $manageSecurityVal['ipFrontBandWidth'] = '';
        }
        if ($ipLoginTryAdmin) {
            $manageSecurityVal['ipLoginTryAdmin'] = $ipLoginTryAdmin;
            $manageSecurityVal['ipLoginTryAdminBandWidth'] = $getValue['ipLoginTryAdminBandWidth'];
        } else {
            $manageSecurityVal['ipLoginTryAdmin'] = $manageSecurityVal['ipLoginTryAdminBandWidth'] = '';
        }

        // 국가별 접속 차단 설정
        $manageSecurityVal['countryAccessBlocking'] = $getValue['countryAccessBlocking'];

        // 페이지 화면 보호 설정
        gd_isset($getValue['unDragFl'], 'n');
        gd_isset($getValue['unContextmenuFl'], 'n');
        gd_isset($getValue['managerUnblockFl'], 'n');
        $pageProtectVal['unDragFl'] = $getValue['unDragFl'];
        $pageProtectVal['unContextmenuFl'] = $getValue['unContextmenuFl'];
        $pageProtectVal['managerUnblockFl'] = $getValue['managerUnblockFl'];

        $saveChk = 0;
        if ($this->setValue('design.page_protect', $pageProtectVal) != true) {
            $saveChk++;
        }
        if ($this->setValue('manage.security', $manageSecurityVal) !== true) {
            $saveChk++;
        }

        if ($saveChk > 0) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 주문 리스트 설정 저장하기
     *
     * @param array $getValue 데이터
     */
    public function saveConfigOrderList($getValue)
    {
        //--- 기본값 설정
        gd_isset($getValue['searchPeriod'], 3);
        gd_isset($getValue['searchStatus'], 'o');
        $getValue['searchStatus'] = implode(STR_DIVISION, ArrayUtils::removeEmpty(explode(STR_DIVISION, $getValue['searchStatus'])));
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['searchPeriod'], '-');

        $this->setValue('order.defaultSearch', $getValue);
    }

    /**
     * 환불 수수료 설정 저장하기
     *
     * @param array $getValue 데이터
     */
    public function saveConfigRefundCharge($getValue)
    {
        //--- 기본값 설정
        gd_isset($getValue['refundCharge'], 0);
        unset($getValue['mode']);

        //--- 숫자의 쉼표 제거
        gd_remove_comma($getValue['refundCharge'], '-');

        $this->setValue('order.refundCharge', $getValue);
    }


    /**
     * 보안서버 설정 초기화
     *
     * @param string $shopDomain 데이터
     *
     * @throws \Exception
     * @deprecated 추가도메인 기능 추가로 제거 SecureSocketLayer clearSsl 참조
     */
    public function clearSsl($shopDomain)
    {
        if ($shopDomain) {
            $ssl['pc'] = gd_policy('ssl.front');
            $ssl['mobile'] = gd_policy('ssl.mobile');
            $ssl['admin'] = gd_policy('ssl.admin');
            $ssl['api'] = gd_policy('ssl.api');

            // 유료 보안서버 셋팅여부 확인 필요
            if ($ssl['pc']['sslType'] == 'godo') $clearCheck['pc'] = true;
            if ($ssl['mobile']['sslType'] == 'godo') $clearCheck['mobile'] = true;
            if ($ssl['admin']['sslType'] == 'godo') $clearCheck['admin'] = true;
            $clearCheck['api'] = true;

            // 유료 셋팅되어 있을 경우만 도메인 비교
            // 관리자 보안서버 초기화
            if ($clearCheck['admin'] && ($ssl['admin']['sslDomain'] == 'gdadmin.' . $shopDomain)) {
                if ($this->setValue('ssl.admin', []) != true) {
                    throw new \Exception(__('관리자 보안서버 초기화 중 오류가 발생하여 실패되었습니다.'));
                }
            }
            // 모바일 보안서버 초기화
            if ($clearCheck['mobile'] && ($ssl['mobile']['sslDomain'] == 'm.' . $shopDomain)) {
                if ($this->setValue('ssl.mobile', []) != true) {
                    throw new \Exception(__('모바일 보안서버 초기화 중 오류가 발생하여 실패되었습니다.'));
                }
            }
            // API 보안서버 초기화
            if ($clearCheck['api'] && ($ssl['api']['sslDomain'] == 'api.' . $shopDomain)) {
                if ($this->setValue('ssl.api', []) != true) {
                    throw new \Exception(__('API 보안서버 초기화 중 오류가 발생하여 실패되었습니다.'));
                }
            }
            // PC 보안서버 초기화
            if ($clearCheck['pc'] && (($ssl['pc']['sslDomain'] == 'www.' . $shopDomain) || ($ssl['pc']['sslDomain'] == $shopDomain))) {
                if ($this->setValue('ssl.front', []) != true) {
                    throw new \Exception(__('PC 보안서버 초기화 중 오류가 발생하여 실패되었습니다.'));
                }
            }
        }
    }

    /**
     * PC 쇼핑몰 보안서버 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     * @deprecated 추가도메인 기능 추가로 제거 SecureSocketLayer saveSsl 참조
     */
    public function saveSsl($getValue)
    {
        if ($getValue['sslType'] == 'free') {
            $gLicense = Globals::get('gLicense');
            $getValue['sslFreeDomain'] = HttpUtils::remoteGet('http://gongji.godo.co.kr/userinterface/get.basicdomain.php?sno=' . $gLicense['godosno']);
        }
        // Validation
        $validator = new Validator();
        $validator->add('sslType', ''); // free-무료,godo-유료,direct-직접설정
        $validator->add('sslDomain', ''); // 유료/직접설정 SSL 도메인(xxx.co.kr)
        $validator->add('sslPort', ''); // 유료/직접설정 SSL 포트(000)
        $validator->add('sslSdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslEdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslStep', ''); // 유료 단계(wait,process)
        $validator->add('sslFreeDomain', ''); // 무료 SSL 도메인(xxx.co.kr)
        $validator->add('sslApplyLimit', ''); // 적용 범위 (n=개별,y=전체)페이지
        $validator->add('userSslRule', ''); // 유료 추가 보안 페이지
        $validator->add('sslFreeImageUse', ''); // 보안 이미지 노출
        $validator->add('sslGodoImageUse', ''); // 보안 이미지 노출
        $validator->add('sslGodoImageType', ''); // 보안 이미지 종류
        $validator->add('serverExists', ''); // 보안종류(kisa_ssl 등)

        // 데이터 조합
        $getValue['userSslRule'] = (implode('', $getValue['userSslRule']) == '' ? '' : implode(',', ArrayUtils::removeEmpty($getValue['userSslRule'])));

        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        $getValue = array_merge(gd_policy('ssl.front'), $getValue);
        $getValue = ArrayUtils::removeEmpty($getValue);

        if ($this->setValue('ssl.front', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * MOBILE 쇼핑몰 보안서버 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     * @deprecated 추가도메인 기능 추가로 제거 SecureSocketLayer saveSsl 참조
     */
    public function saveMobileSsl($getValue)
    {
        // Validation
        $validator = new Validator();
        $validator->add('sslType', ''); // godo-유료,direct-직접설정
        $validator->add('sslDomain', ''); // 유료/직접설정 SSL 도메인(xxx.co.kr)
        $validator->add('sslPort', ''); // 유료/직접설정 SSL 포트(000)
        $validator->add('sslSdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslEdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslStep', ''); // 유료 단계(wait,process)
        $validator->add('sslApplyLimit', ''); // 적용 범위 (n=개별,y=전체)페이지
        $validator->add('userSslRule', ''); // 유료 추가 보안 페이지
        $validator->add('serverExists', ''); // 보안종류(kisa_ssl 등)

        // 데이터 조합
        $getValue['userSslRule'] = (implode('', $getValue['userSslRule']) == '' ? '' : implode(',', ArrayUtils::removeEmpty($getValue['userSslRule'])));

        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        $getValue = array_merge(gd_policy('ssl.mobile'), $getValue);
        $getValue = ArrayUtils::removeEmpty($getValue);

        if ($this->setValue('ssl.mobile', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * ADMIN 보안서버 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     * @deprecated 추가도메인 기능 추가로 제거 SecureSocketLayer saveSsl 참조
     */
    public function saveAdminSsl($getValue)
    {
        // Validation
        $validator = new Validator();
        $validator->add('sslType', ''); // godo-유료,direct-직접설정
        $validator->add('sslDomain', ''); // 유료/직접설정 SSL 도메인(xxx.co.kr)
        $validator->add('sslPort', ''); // 유료/직접설정 SSL 포트(000)
        $validator->add('sslSdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslEdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslStep', ''); // 유료 단계(wait,process)
        $validator->add('userSslRule', ''); // 유료 추가 보안 페이지
        $validator->add('serverExists', ''); // 보안종류(kisa_ssl 등)

        // 데이터 조합
        $getValue['userSslRule'] = (implode('', $getValue['userSslRule']) == '' ? '' : implode(',', ArrayUtils::removeEmpty($getValue['userSslRule'])));

        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        $getValue = array_merge(gd_policy('ssl.admin'), $getValue);
        $getValue = ArrayUtils::removeEmpty($getValue);

        if ($this->setValue('ssl.admin', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * API 보안서버 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     * @deprecated 추가도메인 기능 추가로 제거 SecureSocketLayer saveSsl 참조
     */
    public function saveApiSsl($getValue)
    {
        // Validation
        $validator = new Validator();
        $validator->add('sslType', ''); // godo-유료,direct-직접설정
        $validator->add('sslDomain', ''); // 유료/직접설정 SSL 도메인(xxx.co.kr)
        $validator->add('sslPort', ''); // 유료/직접설정 SSL 포트(000)
        $validator->add('sslSdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslEdate', ''); // 유료/직접설정 사용기간(YYYYmmdd)
        $validator->add('sslStep', ''); // 유료 단계(wait,process)
        $validator->add('userSslRule', ''); // 유료 추가 보안 페이지
        $validator->add('serverExists', ''); // 보안종류(kisa_ssl 등)

        // 데이터 조합
        $getValue['userSslRule'] = (implode('', $getValue['userSslRule']) == '' ? '' : implode(',', ArrayUtils::removeEmpty($getValue['userSslRule'])));

        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        $getValue = array_merge(gd_policy('ssl.api'), $getValue);
        $getValue = ArrayUtils::removeEmpty($getValue);

        if ($this->setValue('ssl.api', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * WILDCARD 보안서버 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     * @deprecated 추가도메인 기능 추가로 제거 SecureSocketLayer saveSsl 참조
     */
    public function saveWildCardSsl($getValue)
    {
        $this->saveAdminSsl($getValue);
        $this->saveApiSsl($getValue);
        $this->saveMobileSsl($getValue);
        $this->saveSsl($getValue);
    }

    /**
     * 매출통계 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveStatisticsOrder($getValue)
    {
        // Validation
        $validator = new Validator();
        $validator->add('term', '');
        $validator->add('compareDtStart', '');
        $validator->add('compareDtEnd', '');
        $validator->add('graphFl', 'yn');

        // 데이터 조합
        if (isset($getValue['graphFl']) === false) {
            $getValue['graphFl'] = 'n';
        }

        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        if (empty($getValue['compareDtStart']) === false && empty($getValue['compareDtEnd']) === false) {
            $gap = gd_interval_day($getValue['compareDtStart'], $getValue['compareDtEnd']);
            if ($gap >= 7) {
                throw new \Exception(__('비교 기간은 최대 7일까지만 설정할 수 있습니다.'));
            }
        }
        $data['order'] = $getValue;
        $data = array_merge(gd_policy('basic.statistics'), $data);
        $data['order'] = ArrayUtils::removeEmpty($data['order']);

        if ($this->setValue('basic.statistics', $data) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 유입통계 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveStatisticsMember($getValue)
    {
        // Validation
        $validator = new Validator();
        $validator->add('term', '');
        $validator->add('compareDtStart', '');
        $validator->add('compareDtEnd', '');
        if ($validator->act($getValue, true) === false) {
            throw new \Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors)));
        }
        if (empty($getValue['compareDtStart']) === false && empty($getValue['compareDtEnd']) === false) {
            $gap = gd_interval_day($getValue['compareDtStart'], $getValue['compareDtEnd']);
            if ($gap >= 7) {
                throw new \Exception(__('비교 기간은 최대 7일까지만 설정할 수 있습니다.'));
            }
        }
        $data['member'] = $getValue;
        $data = array_merge(gd_policy('basic.statistics'), $data);
        $data['member'] = ArrayUtils::removeEmpty($data['member']);

        if ($this->setValue('basic.statistics', $data) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 스케줄(일정관리)설정
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveSchedule($getValue)
    {
        // Validation
        $validator = new Validator();
        $validator->add('alarmUseFl', 'yn');
        $validator->add('dDayPopup', '');
        if ($validator->act($getValue, true) === false) {
            throw new \Exception(implode("\n", $validator->errors));
        }
        $getValue = ArrayUtils::removeEmpty($getValue);

        //--- 기본값 설정
        gd_isset($getValue['alarmUseFl'], 'n');

        if ($this->setValue('basic.schedule', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 모바일샵 기본 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveMobileConfig($getValue)
    {
        // 이미지 폴더의 체크
        $imagePath = UserFilePath::data('mobile');
        if ($imagePath->isDir() === false) {
            @mkdir($imagePath);
            @chmod($imagePath, 0707);
        }

        // 모바일 바로가기 삭제
        if (isset($getValue['mobileShopIconDel']) && empty($getValue['mobileShopIconTmp']) === false) {
            //--- 이미지 삭제
            Storage::disk(Storage::PATH_CODE_MOBILE, 'local')->delete(MOBILE_SHOP_ICON);
            $getValue['mobileShopIcon'] = '';
            unset($getValue['mobileShopIconTmp']);
        }

        // 모바일 바로가기 업로드
        if (gd_file_uploadable(Request::files()->get('mobileShopIcon'), 'image') === true) {
            $tmpImageFile = Request::files()->get('mobileShopIcon.tmp_name');
            list($tmpSize['width'], $tmpSize['height']) = getimagesize($tmpImageFile);
            Storage::disk(Storage::PATH_CODE_MOBILE, 'local')->upload($tmpImageFile, MOBILE_SHOP_ICON);
            $getValue['mobileShopIcon'] = UserFilePath::data('mobile', MOBILE_SHOP_ICON)->www();
        } else {
            if (empty($getValue['mobileShopIconTmp']) === false) {
                $getValue['mobileShopIcon'] = $getValue['mobileShopIcon'];
            }
        }

        // 기본 값 설정
        gd_isset($getValue['mobileShopFl'], 'n');
        gd_isset($getValue['mobileShopGoodsFl'], 'same');
        gd_isset($getValue['mobileShopCategoryFl'], 'same');
        gd_isset($getValue['mobileShopIcon'], '/data/commonimg/' . MOBILE_SHOP_ICON);
        unset($getValue['mode']);

        if ($this->setValue('mobile.config', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 모바일샵 디자인 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveMobileDesign($getValue)
    {
        // dataFile 모듈
        $dataFile = \App::load('\\Component\\File\\DataFile');

        // 이미지 폴더의 체크
        $imagePath = UserFilePath::data('mobile');
        if ($imagePath->isDir() === false) {
            @mkdir($imagePath);
            @chmod($imagePath, 0707);
        }

        // 이미지 삭제
        if (isset($getValue['imageDel']) === true) {
            foreach ($getValue['imageDel'] as $val) {
                //$dataFile->setImageDelete('local', 'mobile', 'mobile', $getValue[$val . 'Tmp'], 'file');
                Storage::disk(Storage::PATH_CODE_MOBILE, 'local')->delete($getValue[$val . 'Tmp']);
                $getValue[$val . 'Tmp'] = '';
            }
            unset($getValue['imageDel']);
        }

        // 이미지 업로드
        foreach ($_FILES as $key => $val) {
            if (gd_file_uploadable($_FILES[$key], 'image') === true) {
                //$dataFile->setImageStorage('local', 'mobile', 'mobile');
                //$getValue[$key] = $dataFile->saveFile('mobile', $_FILES[$key]['name'], $_FILES[$key]['tmp_name']);
                $getValue[$key] = Storage::disk(Storage::PATH_CODE_MOBILE)->upload($_FILES[$key]['tmp_name'], $_FILES[$key]['name']);
            } else {
                if (empty($getValue[$key . 'Tmp']) === false) {
                    $getValue[$key] = $getValue[$key . 'Tmp'];
                }
            }
        }

        // 기본 값 설정
        gd_isset($getValue['mobileShopSkin'], 'smart');
        gd_isset($getValue['mobileShopLogo']);
        gd_isset($getValue['mobileShopIcon']);
        gd_isset($getValue['mobileShopMainImg']);
        unset($getValue['mode']);
        unset($getValue['mobileShopLogoTmp']);
        unset($getValue['mobileShopIconTmp']);
        unset($getValue['mobileShopMainImgTmp']);

        if ($this->setValue('mobile.design', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 모바일샵 각 페이지 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveMobilePageConfig($getValue)
    {
        //--- MobilePageConfig 정의
        $config = \App::load('\\Component\\Mobile\\MobilePageConfig');
        $config->setConfig($getValue['pageCode']);
        $pageCode = $getValue['pageCode'];

        // 기본 값 설정
        foreach ($config->arrFields as $key => $val) {
            $getValue[$val['CodeNm']] = gd_isset($getValue[$val['CodeNm']], $val['mustFl']);
        }
        unset($getValue['mode'], $getValue['pageCode']);

        if ($this->setValue('mobile.page_' . $pageCode, $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 네이버 공통 유입 스크립트 설정 저장하기 (고도에서 보내는것)
     *
     * @param array $getValue 데이터
     *
     * @return bool
     */
    public function saveNaverCommonInflowScriptGodoConn($getValue)
    {
        // 기본 값 설정
        gd_isset($getValue['naverCommonInflowScriptFl'], 'n');

        // 저장
        $result = $this->setValue('naver.common_inflow_script', $getValue);

        return $result;
    }

    /**
     * 네이버 공통 유입 스크립트 설정 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveNaverCommonInflowScript($getValue)
    {
        // 기본 값 설정
        gd_isset($getValue['naverCommonInflowScriptFl'], 'n');
        unset($getValue['mode']);

        $naverInfo = $this->getValue('naver.common_inflow_script');

        if ($getValue['naverCommonInflowScriptFl'] == 'y' && empty($naverInfo['accountId']) === true) {

            if (Validator::required(gd_isset($getValue['accountId'])) === false) {
                throw new \Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), '네이버 공통키'));
            }

            $godoCenterServiceApi = new GodoCenterServerApi();
            $result = $godoCenterServiceApi->saveConfigNaverCommonkey($getValue['accountId']);

            if ($result->response == 'true') {
                $getValue['whiteList'] = ArrayUtils::removeEmpty($getValue['whiteList']);
                $this->setValue('naver.common_inflow_script', $getValue);
            } else {
                switch ($result->response) {
                    case 'err_accountId_str':
                        $message = __("공통인증키가 잘못되었습니다.");
                        break;
                    case 'err_shopSno':
                        $message = __("쇼핑몰 고유번호가 유효하지 않습니다.\\n고객센터로 문의하시어 쇼핑몰 구매상태나, 라이선스상태에\\n이상이 있지는 않은지 확인하여주시기 바랍니다.");
                        break;
                    case 'err_hash':
                        $message = __("내부 요청이 잘못되었습니다.\\n네이버공통인증키 서비스관련 패치상태가\\n최신패치로 적용되어있는지 확인하여주시기 바랍니다.");
                        break;
                    case 'err_action':
                        $message = __("내부 요청이 잘못되었습니다.\\n네이버공통인증키 서비스관련 패치상태가\\n최신패치로 적용되어있는지 확인하여주시기 바랍니다.");
                        break;
                    case 'err_accountId':
                        $message = __("네이버공통인증키를 확인하여주시기 바랍니다.");
                        break;
                    case 'err_shopTrue':
                        $message = __("이미 공통인증키를 등록하셨습니다.\\n한번 등록하신 공통인증키는 쇼핑몰에서 변경이 불가능 하오니\\n네이버측으로부터 변경요청 받으셨거나, 인증키를 잘못 입력하신경우\\n해당내용을 고도고객센터로 알려주시기 바랍니다.");
                        break;
                    default:
                        $message = __("알려지지않은 에러가 발생하였습니다.\\n고도 고객센터로 문의주시기 바랍니다.");
                }

                throw new \Exception($message, 500);

            }

        } else {
            $getValue['whiteList'] = ArrayUtils::removeEmpty($getValue['whiteList']);
            $this->setValue('naver.common_inflow_script', $getValue);
        }

    }


    /**
     * Returns the config storage.
     *
     * @return \Bundle\Component\Policy\Storage\DatabaseStorage
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * Sets the config storage.
     *
     * @param StorageInterface|CacheableProxy $storage
     *
     * @return void
     */
    public function setStorage($storage)
    {
        $this->_storage = $storage;
    }

    /**
     * URL 형식 변경하기
     *
     * @param string $strUrl URL string
     * @param string $http 형식 (y : 앞에 http:// 붙이기 , n 앞에 http:// 삭제하기 , / : 앞에 / 불이기)
     *
     * @return string 변경된 URL
     */
    function http_url(&$strUrl, $http = 'y')
    {
        $prefix = 'http://';
        if (substr($strUrl, 0, 5) == 'https') {
            $prefix = 'https://';
        }
        $strUrl = str_replace($prefix, '', $strUrl);

        if ($http == 'y') {
            $strUrl = $prefix . $strUrl;
        }

        if ($http == '/') {
            if (substr($strUrl, 0, 1) == $http) {
                $strUrl = substr($strUrl, 1);
            }
            $strUrl = $http . $strUrl;
        }

        if (substr($strUrl, -1, 1) == '/') {
            $strUrl = substr($strUrl, 0, strlen($strUrl) - 1);
        }

        return $strUrl;
    }

    /**
     * saveBoardPeriod
     *
     * @param $getValue
     *
     * @throws \Exception
     */
    public function saveBoardPeriod($getValue)
    {
        $code = $getValue['code'];
        unset($getValue['mode']);
        unset($getValue['code']);

        $data = gd_policy('main.cs');
        if (gd_is_provider() === false) {
            unset($data['period']); //@TODO:기존 데이터 삭제
        }
        $data[\Session::get('manager.scmNo')]['period'] = $getValue['period'];

        if ($this->setValue('main.' . $code, $data) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * getMemoSetting
     *
     * @param $managerNo
     *
     * @return mixed
     *
     * @deprecated 해당 함수는 더 이상 사용되지 않습니다. 관리자 메모는 es_managerMemo 테이블로 변경되었습니다.
     */
    public function getMemoSetting($managerNo)
    {
        $scmNo = \Session::get('manager.scmNo');
        $memoSetting = gd_policy('main.memo');
        $data['self'] = $memoSetting['self'][$managerNo];
        $data['all'] = $memoSetting['all'][$scmNo];

        // 날짜 체크 및 new 아이콘
        $fixedDate = 3 * 24 * 60 * 60; // new 아이콘 기준 3일
        $allDate = DateTimeUtils::intervalDay($data['all']['modDt'], null, 'sec');
        $selfDate = DateTimeUtils::intervalDay($data['self']['modDt'], null, 'sec');
        $data['all']['isChanged'] = $data['self']['isChanged'] = 'n';
        if (empty($data['all']['contents']) === false && ($allDate > 0 && $allDate <= $fixedDate)) {
            $data['all']['isChanged'] = 'y';
        }
        if (empty($data['self']['contents']) === false && ($selfDate > 0 && $selfDate <= $fixedDate)) {
            $data['self']['isChanged'] = 'y';
        }

        gd_isset($data['self']['isVisible'], 'y');   //default세팅
        gd_isset($data['self']['viewAuth'], 'all');  //default세팅

        return $data;
    }

    /**
     * saveMemoSetting
     *
     * @param $getValue
     *
     * @return bool
     * @throws \Exception
     *
     * @deprecated 해당 함수는 더 이상 사용되지 않습니다. 관리자 메모는 es_managerMemo 테이블로 변경되었습니다.
     */
    public function saveMemoSetting($getValue)
    {
        $scmNo = \Session::get('manager.scmNo');
        $code = $getValue['code'];
        $managerNo = $getValue['managerNo'];
        $memoSetting = gd_policy('main.memo');

        if ($getValue['oldViewAuth']) {
            $memoSetting['viewAuth'] = $getValue['oldViewAuth'];
        }

        try {
            if ($code == 'isVisible') {
                $memoSetting['self'][$managerNo]['isVisible'] = $getValue['isVisible'];
            } else if ($code == 'save') {
                if ($getValue['viewAuth'] == 'self') {
                    $memoSetting[$getValue['viewAuth']][$managerNo]['modDt'] = date('Y-m-d H:i:s');
                    $memoSetting[$getValue['viewAuth']][$managerNo]['contents'] = $getValue['memo'][$getValue['viewAuth']];
                } else {
                    $memoSetting[$getValue['viewAuth']][$scmNo]['modManagerNo'] = $managerNo;
                    $memoSetting[$getValue['viewAuth']][$scmNo]['modDt'] = date('Y-m-d H:i:s');
                    $memoSetting[$getValue['viewAuth']][$scmNo]['contents'] = $getValue['memo'][$getValue['viewAuth']];
                }
            } else if ($code == 'viewAuth') {
                $memoSetting['self'][$managerNo]['viewAuth'] = $getValue['viewAuth'];
            }
            $result = $this->setValue('main.memo', $memoSetting);

            return $result;
        } catch (\Exception $e) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }

    }


    /**
     * 관리자 > 메인 > 주문관리 환경설정
     *
     * @param $arrData
     *
     * @throws \Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function saveOrderMainSetting($arrData)
    {
        // 넘어온 데이터 정리
        $code = $arrData['code'];
        unset($arrData['mode']);
        unset($arrData['code']);

        // main.order에 정책 저장
        if ($this->setValue('main.' . $code . \Session::get('manager.scmNo'), $arrData) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 네이버페이 설정 저장하기 (고도에서 보내는것)
     *
     * @param $getValue
     *
     * @return bool
     * @throws \Exception
     */
    public function saveNaverPaySettingGodoConn($getValue)
    {
        if (isset($getValue['naverId'])) { // 네이버페이센터 가맹점 ID 공백 제거
            gd_trim($getValue['naverId']);
        }
        if (isset($getValue['connectId'])) { // 가맹점 인증키 공백 제거
            gd_trim($getValue['connectId']);
        }
        if (isset($getValue['imageId'])) { // 버튼 인증키 공백 제거
            gd_trim($getValue['imageId']);
        }

        $arrData = gd_policy('naverPay.config');

        if (empty($getValue['useYn']) === false) { // 사용여부 값이 있을 경우에만 갱신
            $arrData['useYn'] = $getValue['useYn'];
        }

        $arrData['naverId'] = $getValue['naverId'];
        $arrData['connectId'] = $getValue['connectId'];
        $arrData['imageId'] = $getValue['imageId'];
        // 네이버페이 버튼 노출 기본값 설정 저장
        gd_isset($arrData['imgType'], 'A');
        gd_isset($arrData['imgColor'], '1');
        gd_isset($arrData['mobileImgType'], 'MA');
        gd_isset($arrData['mobileImgColor'], '1');
        gd_isset($arrData['mobileButtonTarget'], 'self');
        gd_isset($arrData['saleFl'], 'n');

        // 설정 저장
        $arrData = array_merge(gd_policy('naverPay.config'), $arrData);
        $result = $this->setValue('naverPay.config', $arrData);

        if ($result !== true) { // 저장 실패 시
            $result = 'err_naverPayConfigSave';
            return $result;
        }

        // 네이버페이 주문 API 연동 실행
        if ($getValue['naverPayApiRequest'] === 'y') {
            $naverPayConfig = $this->getNaverPaySetting();
            if ($naverPayConfig['useApi'] !== 'y') {
                $url = NaverPayAPI::RELAY_URL;
                $apiUrl = URI_API . 'godo/set_naverpay.php';
                $cryptKey = substr(md5(microtime() . rand(1, 1000)), 0, 10);

                $requestPost = ['mode' => 'register', 'shopNo' => $getValue['godosno'], 'naverId' => $getValue['naverId'], 'shopButtonKey' => $getValue['imageId'], 'apiUrl' => $apiUrl, 'cryptKey' => $cryptKey,];
                $result = HttpUtils::remotePost($url, $requestPost);

                if ($result == 'DONE') { // api 연동 성공
                    $naverPayConfig['cryptkey'] = $cryptKey;
                    $naverPayConfig['linkStock'] = 'y';
                    $this->saveNaverPaySetting($naverPayConfig);
                }
            }
            else { // 이미 주문연동 APi 사용하고 있을 경우
                $result = 'err_naverPayApiRequest';
            }
        }

        return $result;
    }

    public function saveNaverPaySetting($arrData)
    {
        unset($arrData['mode']);
        $deliveryKey = [
            'returnPrice',
            'areaDelivery',
            'area22Price',
            'area32Price',
            'area33Price',
        ];
        unset($arrData['checked']);

        $scmNo = \Session::get('manager.scmNo');
        if ($arrData['scmNo'] != null) {
            $scmNo = $arrData['scmNo'];
        }
        foreach ($deliveryKey as $val) {
            $arrData['deliveryData'][$scmNo][$val] = $arrData[$val];
            unset($arrData[$val]);
        }

        if (isset($arrData['naverId'])) { // 네이버페이센터 가맹점 ID 공백 제거
            gd_trim($arrData['naverId']);
        }
        if (isset($arrData['connectId'])) { // 가맹점 인증키 공백 제거
            gd_trim($arrData['connectId']);
        }
        if (isset($arrData['imageId'])) { // 버튼 인증키 공백 제거
            gd_trim($arrData['imageId']);
        }

        if ($this->setValue('naverPay.config', $arrData) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }

        $requestData = array(
            'naverId' => $arrData['naverId'],
            'connectId' => $arrData['connectId'],
            'imageId' => $arrData['imageId'],
        );

        $godoCenterServiceApi = new GodoCenterServerApi();
        $result = $godoCenterServiceApi->saveConfigNaverPayData($requestData);
        if ($result->response == 'true') {
            // empty
        }
        else {
            switch ($result->response) {
                case 'err_shopSno': // 상점번호 오류
                    Logger::channel('service')->info(__METHOD__ . ' 상점번호 오류', [__CLASS__]);
                    break;
                case 'err_shopTrue': // 이미 상점에 등록된 네이버페이 아이디
                    Logger::channel('service')->info(__METHOD__ . ' 이미 상점에 등록된 네이버페이 아이디', [__CLASS__]);
                    break;
                case 'err_header': // 헤더 오류
                    Logger::channel('service')->info(__METHOD__ . ' 헤더 오류', [__CLASS__]);
                    break;
                case 'err_apiKind': // 통신타입 오류
                    Logger::channel('service')->info(__METHOD__ . ' 통신타입 오류', [__CLASS__]);
                    break;
                case 'err_hash': // 해쉬데이터 오류
                    Logger::channel('service')->info(__METHOD__ . ' 해쉬데이터 오류', [__CLASS__]);
                    break;
                default: // 기타실패
                    Logger::channel('service')->info(__METHOD__ . ' 기타실패', [__CLASS__]);
                    break;
            }
        }
    }

    public function getNaverPaySetting()
    {
        $config = gd_policy('naverPay.config');
        $goods = new Goods();
        $config['exceptGoodsNo'] = null;

        gd_isset($config['presentExceptFl'], []);

        if (in_array('goods', $config['presentExceptFl']) && is_array($config['exceptGoods'])) {
            $_strExceptGoods = implode(INT_DIVISION, $config['exceptGoods']);
            $config['exceptGoodsNo'] = $goods->getGoodsDataDisplay($_strExceptGoods);
        }

        if (in_array('category', $config['presentExceptFl']) && is_array($config['exceptCategory'])) {
            // 네이버 페이에서 먼저 컨테이너로 담는 문제로 인해 request에서 요청받은 값이 goods로 강제 fix 되어버림
            $requestCateType = (\Request::get()->has('cateType') && \Request::get()->get('cateType') == 'brand') ? 'brand' : null;
            $cate = \App::load('\\Component\\Category\\Category', $requestCateType);
            foreach ($config['exceptCategory'] as $val) {
                $config['exceptCateCd']['name'][] = gd_htmlspecialchars_decode($cate->getCategoryPosition($val));
            }
            $config['exceptCateCd']['code'] = $config['exceptCategory'];
        }

        $deliveryConfig = $config['deliveryData'][\Session::get('manager.scmNo')];
        if (is_array($deliveryConfig)) {
            foreach ($deliveryConfig as $key => $val) {
                $config[$key] = $val;
            }
        }
        $config['useApi'] = 'n';
        if ($config['cryptkey']) {
            $config['useApi'] = 'y';
        }

        // 플러스샵 전역설정 TODO:임시설정 GODOUTILS::isPlusShop 수정하면 리팩토리
        $plusShopFile = UserFilePath::config('plus_shop_info.php');
        $plusShopData = [];
        if (\FileHandler::isExists($plusShopFile) === true) {
            if ($plusShopInfo = \FileHandler::read($plusShopFile)) {
                $plusShopInfo = explode(PHP_EOL, $plusShopInfo);
                if (empty($plusShopInfo[2]) === false) {
                    $plusShopData = \Encryptor::decrypt($plusShopInfo[2]);
                    if (empty($plusShopData) === false) {
                        $plusShopData = json_decode($plusShopData);
                        $plusShopData = ArrayUtils::objectToArray($plusShopData);

                        // 사용기간 체크
                        foreach ($plusShopData as $key => $val) {
                            if (date('Ymd') > $val['appExpireData']) {
                                $plusShopData[$key]['appUseFl'] = 'n';
                            }

                            if (!$val['appInstallFl']) {
                                $plusShopData[$key]['appInstallFl'] = 'y';
                            }

                            if (!$val['appBuyDate']) {
                                $plusShopData[$key]['appBuyDate'] = $val['appRegData'];
                            }
                        }
                    }
                }
            }
        }

        if($config['reviewChannel'] != 'shop') {    //플러스리뷰관련 설정인데
            $plusReviewAppUseFl= $plusShopData[PLUSSHOP_CODE_REVIEW]['appUseFl'];
            if ( $plusReviewAppUseFl != 'y' ) { //플러스리뷰 미설치인 경우
                $config['reviewChannel'] = 'shop';
            } else {    //설치인경우
                $plusReviewConfig = new PlusReviewConfig();
                if ($plusReviewConfig->getConfig('useFl') != 'y') { //사용안함인경우
                    $config['reviewChannel'] = 'shop';
                }
            }
        }

        // 테스트 기능 삭제 따른 마이그레이션
        if($config['testYn'] == 'y') {
            $config['testYn'] = 'n';
            if($config['useYn'] == 'y') {
                $config['useYn'] = 'n';
            }
        }

        gd_isset($config['useYn'], 'n');
        gd_isset($config['testYn'], 'n');
        gd_isset($config['mobileButtonTarget'], 'self');
        gd_isset($config['linkStock'], 'n');
        gd_isset($config['reviewFl'], 'y');
        gd_isset($config['saleFl'], 'n');
        gd_isset($config['autoRefund'], 'n');
        gd_isset($config['reviewChannel'], 'shop');
        unset($config['checked']);
        unset($config['selected']);
        $config['checked']['useYn'][$config['useYn']] = 'checked';
        $config['checked']['testYn'][$config['testYn']] = 'checked';
        $config['checked']['mobileButtonTarget'][$config['mobileButtonTarget']] = 'checked';
        $config['checked']['linkStock'][$config['linkStock']] = 'checked';
        $config['checked']['reviewFl'][$config['reviewFl']] = 'checked';
        $config['checked']['areaDelivery'][$config['areaDelivery']] = 'checked';
        $config['checked']['saleFl'][$config['saleFl']] = 'checked';
        $config['checked']['autoRefund'][$config['autoRefund']] = 'checked';
        $config['checked']['reviewChannel'][$config['reviewChannel']] = 'checked';

        $config['selected']['imgType'][$config['imgType']] = 'selected';
        $config['selected']['imgColor'][$config['imgColor']] = 'selected';
        $config['selected']['mobileImgType'][$config['mobileImgType']] = 'selected';
        $config['selected']['mobileImgColor'][$config['mobileImgColor']] = 'selected';

        return $config;
    }

    /**
     * 뱅크다 사용여부 저장하기
     *
     * @param array $getValue 데이터
     *
     * @throws \Exception
     */
    public function saveBankdaConf($getValue)
    {
        // 기본값 설정
        gd_isset($getValue['useFl'], 'n');

        if ($this->setValue('order.bankda', $getValue) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    public function getAcecounterSetting()
    {
        $config = gd_policy('acecounter.config');
        $config['isEnabled'] = $config['shopStatus'] === 'y' ? true : false;

        return $config;
    }

    public function saveAcecounterCommonScriptGodoConn($getValue)
    {
        $config = gd_policy('acecounter.config');

        Logger::channel('acecounter')->info(
            __METHOD__ . ' config save original data:', [
                __CLASS__,
                $getValue,
            ]
        );
        $updateData = [];
        switch ($getValue['returnKind']) {
            case 'regist': // 회원가입(고도측 신청으로 인한 환경저장)
                $updateData['aceCode'] = gd_isset($getValue['aceCode'], $config['aceCode']);
                $updateData['aceTid'] = gd_isset($getValue['aceTid'], $config['aceTid']);
                $updateData['shopStatus'] = 'y'; // gd_isset(strtolower($getValue['shopStatus']), $config['shopStatus']);
                $updateData['aceStatus'] = gd_isset(strtolower($getValue['aceStatus']), $config['aceStatus']);
                $updateData['shopKey'] = gd_isset(strtoupper($getValue['shopKey']), $config['shopKey']);
                $updateData['acePeriod'] = gd_isset($getValue['acePeriod'], $config['acePeriod']);
                $updateData['aceServiceName'] = gd_isset($getValue['aceServiceName'], $config['aceServiceName']);
                break;
            case 'modify' : // 상태변경 (에이스카운터측에서 상태변경으로 인한 환경저장)
                $updateData['aceCode'] = $getValue['aceCode'] ?? $config['aceCode'];
                $updateData['aceTid'] = $getValue['aceTid'] ?? $config['aceTid'];
                $updateData['shopStatus'] = gd_isset(strtolower($getValue['shopStatus']), $config['shopStatus']);
                $updateData['aceStatus'] = gd_isset(strtolower($getValue['aceStatus']), $config['aceStatus']);
                $updateData['shopKey'] = gd_isset(strtoupper($getValue['shopKey']), strtoupper($config['shopKey']));
                $updateData['acePeriod'] = $getValue['acePeriod'] ?? $config['acePeriod'];
                $updateData['aceServiceName'] = $getValue['aceServiceName'] ?? $config['aceServiceName'];
                break;
        }
        $result = $this->setValue('acecounter.config', $updateData);

        if ($result != true) {
            Logger::channel('acecounter')->info(
                __METHOD__ . 'config save fail : data[' . $getValue['returnKind'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        } else {
            Logger::channel('acecounter')->info(
                __METHOD__ . 'config save success : data[' . $getValue['returnKind'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        }

        return $result;
    }

    public function getPaycoSearchSetting()
    {
        $config = gd_policy('paycosearch.config');
        $config['pipDbUrlDir'] = '/data/dburl/paycosearch/paycoSearch_all.txt';
        $config['searchUse'] = $config['searchUse'] == 'T' ? 'N' : $config['searchUse']; // 테스트하기 모드 삭제

        return $config;
    }

    public function getNhnShopKeyConfig()
    {
        $ace_config = gd_policy('acecounter.config');
        $payco_config = gd_policy('paycosearch.config');
        if ($ace_config['shopKey']) {
            $key = strtoupper($ace_config['shopKey']);
        } else if ($payco_config['shopkey']) {
            $key = strtoupper($payco_config['shopkey']);
        } else {
            $key = '';
        }

        return $key;
    }

    public function savePaycosearchConfig($getValue)
    {
        $config = gd_policy('paycosearch.config');

        Logger::channel('paycosearch')->info(
            'PAYCOSEARCH_CONFIG_SAVE_ORIGINAL START : data', [
                __CLASS__,
                $getValue,
            ]
        );
        $updateData = [];
        switch ($getValue['mode']) {
            case 'regist': // 회원가입
                $updateData['shopKey'] = gd_isset(strtoupper($getValue['shopKey']), $config['shopKey']); // 샵키
                $updateData['searchUse'] = 'N'; // 사용여부: 최초 가입 시 무조건 N 상태
                $updateData['searchKeyDomain'] = $getValue['domain']; // 검색 연동 도메인
                $updateData['searchDisplayDomain'] = $getValue['displayDomain']; // 검색 연동 표시 도메인
                $updateData['searchShopName'] = $getValue['shopName']; // 검색 연동샵 이름
                $updateData['searchPipScheduler'] = 'Y'; // 스케줄러 여부
                $updateData['paycoSearchUseOnOff'] = 'on'; // 긴급 사용 값 on(페이코 검색 사용) / off(페이코 검색 중지)
                $updateData['searchRejectMessage'] = '';
                break;
            case 'modify' : // 상태변경
                $updateData['gCode'] = $config['gCode'];
                $updateData['status'] = gd_isset(strtolower($getValue['status']), $config['status']);
                $updateData['shopKey'] = strtoupper($config['shopKey']);
                $updateData['acePeriod'] = $config['acePeriod'];
                $updateData['aceServiceName'] = $config['aceServiceName'];
                break;
            case 'paycoSync': // 서비스상태변경 환경저장API
                if ($getValue['status'] == 'activated' || $getValue['status'] == 'deactivated') {
                    $updateData['shopKey'] = gd_isset(strtoupper($getValue['shopKey']), $config['shopKey']); // 샵키
                    $updateData['searchUse'] = $getValue['searchUse'];
                    $updateData['searchKeyDomain'] = $getValue['domain'];
                    $updateData['searchDisplayDomain'] = $getValue['korDomain'];
                    $updateData['searchShopName'] = $getValue['domainName'];
                    $updateData['searchPipScheduler'] = $config['searchPipScheduler'];
                    $updateData['searchRejectMessage'] = $getValue['deactivatedMsg'];
                    $updateData['paycoSearchUseOnOff'] = (!$config['paycoSearchUseOnOff']) ? "on" : $config['paycoSearchUseOnOff'];

                    if ($getValue['status'] == 'activated') {
                        if ($config['searchUse'] == 'Y' || $config['searchUse'] == 'T') {
                            $updateData['searchUse'] = $config['searchUse'];
                            $updateData['searchPipScheduler'] = 'Y';
                        } else {
                            $updateData['searchUse'] = 'T';
                            $updateData['searchPipScheduler'] = 'Y';
                        }

                        // 도메인 검증 (재신청)
                        if ($getValue['domain'] != $config['searchKeyDomain']) {
                            $updateData['searchUse'] = 'T';
                            $updateData['searchPipScheduler'] = 'Y';
                        }
                        // 거절 메시지 초기화
                        $updateData['searchRejectMessage'] = '';
                    } else if ($getValue['status'] == 'deactivated') { // 비활성 동기화 값
                        $updateData['searchUse'] = 'N';
                        $updateData['searchPipScheduler'] = 'N';
                    }
                } else if ($getValue['status'] == 'deleted') { // 정보값 삭제(초기화)
                    $updateData = [];
                }
                break;
            case 'UrgentSync': // 서비스일괄 on_off기능 환경저장API
                $updateData = $config;
                if ($getValue['resultType'] == 'on' || $getValue['resultType'] == 'off') {
                    $updateData['paycoSearchUseOnOff'] = $getValue['resultType'];
                }
                break;
            case 'config':
                $updateData['shopKey'] = gd_isset(strtoupper($getValue['shopKey']), $config['shopKey']); // 샵키
                $updateData['searchUse'] = $getValue['searchUse']; // 사용여부: 최초 가입 시 무조건 N 상태
                $updateData['searchKeyDomain'] = $getValue['searchKeyDomain']; // 검색 연동 도메인
                $updateData['searchDisplayDomain'] = $getValue['searchDisplayDomain']; // 검색 연동 표시 도메인
                $updateData['searchShopName'] = $getValue['searchShopName']; // 검색 연동샵 이름
                $updateData['searchPipScheduler'] = $getValue['searchPipScheduler']; // 스케줄러 여부
                $updateData['paycoSearchUseOnOff'] = $getValue['paycoSearchUseOnOff']; // 긴급 사용 값 on(페이코 검색 사용) / off(페이코 검색 중지)
                $updateData['searchRejectMessage'] = $getValue['searchRejectMessage'];
                $updateData['createType'] = $getValue['createType'];
                $updateData['autocomplete'] = $getValue['searchUse'] == 'N' ? 'N' : strtoupper($getValue['autocomplete']);
                break;
        }
        $result = $this->setValue('paycosearch.config', $updateData);
        unset($config);

        // 강제 중지할 경우 PIP 파일 삭제
        if($getValue['mode'] == 'paycoSync' && $getValue['status'] == 'deactivated') {
            $this->setUnusedPaycosearch($getValue);
        }

        if ($result !== true) {
            Logger::channel('paycosearch')->info(
                'PAYCOSEARCH_CONFIG_SAVE_FAIL : data', [
                    __CLASS__,
                    $updateData,
                ]
            );
        } else {
            Logger::channel('paycosearch')->info(
                'PAYCOSEARCH_CONFIG_SAVE_SUCCESS : update data', [
                    __CLASS__,
                    $updateData,
                ]
            );
        }

        return $result;
    }

    /**
     * 페이코서치 강제 사용 중지
     * @param $getValue
     * @return bool
     */
    public function setUnusedPaycosearch($getValue)
    {
        $config = gd_policy('paycosearch.config');
        $pipFilePath = USERPATH . ($config['pipDbUrlDir'] ? $config['pipDbUrlDir'] : 'data/dburl/paycosearch/');
        $pipFileName = ['paycoSearch_all.txt', 'paycoSearch_all_backup.txt', 'paycoSearch_create.txt'];
        Logger::channel('paycosearch')->info(__METHOD__ . ' 강제 사용 중지: ', [$getValue, $config]);
        $config['searchUse'] = empty($getValue['searchUse']) ? 'N' : $getValue['searchUse']; // 정시개에서 넘어온 값
        // $config['searchKeyDomain'] = ''; 2019-06-10 임시 처리 이후 주석처리
        $this->setValue('paycosearch.config', $config);
        foreach($pipFileName as $name) {
            // PIP 파일 삭제 로그
            if(is_file($pipFilePath . $name) === true) {
                @unlink($pipFilePath . $name);
                Logger::channel('paycosearch')->info(__METHOD__ . '  success file delete: ', [$pipFilePath . $name, substr(decoct(fileperms($pipFilePath)), -4)]);
                if(is_file($pipFilePath . $name) === true) {
                    Logger::channel('paycosearch')->info(__METHOD__ . '  fail file delete: ', [$pipFilePath . $name, substr(decoct(fileperms($pipFilePath)), -4)]);
                }
            } else {
                Logger::channel('paycosearch')->info(__METHOD__ . '  fail file delete(empty file): ', [$pipFilePath . $name]);
            }
        }
        return true;
    }

    /**
     * saveEventConfig 기획리스트 관련설정 저장
     *
     * @param $postValue
     *
     * @throws \Exception
     */
    public function saveEventConfig($postValue)
    {
        gd_isset($postValue['otherEventUseFl'], 'n');
        gd_isset($postValue['otherEventDefaultText'], '');
        gd_isset($postValue['otherEventDisplayFl'], 'n');
        gd_isset($postValue['otherEventBottomFirstFl'], 'n');
        gd_isset($postValue['otherEventSortType'], 'auto');

        if ($this->setValue('promotion.event', $postValue) !== true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * saveOrderPrintConfig 거래명세서/주문내역서 출력 설정 저장
     *
     * @param $postValue
     *
     * @throws \Exception
     */
    public function saveOrderPrintConfig($postValue)
    {
        //거래명세표 출력 설정
        gd_isset($postValue['orderPrintSameDisplay'], 'n');
        gd_isset($postValue['orderPrintBusinessInfo'], 'n');
        gd_isset($postValue['orderPrintBusinessInfoType'], 'companyWithOrder');
        gd_isset($postValue['orderPrintBottomInfo'], 'n');
        gd_isset($postValue['orderPrintBottomInfoType'], '');
        gd_isset($postValue['orderPrintBottomInfoText'], '');
        gd_isset($postValue['orderPrintQuantityDisplay'], 'n');
        gd_isset($postValue['orderPrintAmountDelivery'], 'n');
        gd_isset($postValue['orderPrintAmountDiscount'], 'n');
        gd_isset($postValue['orderPrintAmountMileage'], 'n');
        gd_isset($postValue['orderPrintAmountDeposit'], 'n');
        gd_isset($postValue['orderPrintGoodsNo'], 'n');
        gd_isset($postValue['orderPrintGoodsCd'], 'n');

        //주문내역서 출력 설정
        gd_isset($postValue['orderPrintOdSameDisplay'], 'n');
        gd_isset($postValue['orderPrintOdGoodsCode'], 'n');
        gd_isset($postValue['orderPrintOdSelfGoodsCode'], 'n');
        gd_isset($postValue['orderPrintOdScmDisplay'], 'y');
        gd_isset($postValue['orderPrintOdImageDisplay'], 'y');
        gd_isset($postValue['orderPrintOdSettleInfoDisplay'], 'y');
        gd_isset($postValue['orderPrintOdAdminMemoDisplay'], 'n');
        gd_isset($postValue['orderPrintOdBottomInfo'], 'n');
        gd_isset($postValue['orderPrintOdBottomInfoText'], '');

        //주문내역서 (고객용) 출력 설정
        gd_isset($postValue['orderPrintOdCsGoodsCode'], 'n');
        gd_isset($postValue['orderPrintOdCsSelfGoodsCode'], 'n');
        gd_isset($postValue['orderPrintOdCsImageDisplay'], 'y');
        gd_isset($postValue['orderPrintOdCsSettleInfoDisplay'], 'y');
        gd_isset($postValue['orderPrintOdCsAdminMemoDisplay'], 'n');
        gd_isset($postValue['orderPrintOdCsBottomInfo'], 'n');
        gd_isset($postValue['orderPrintOdCsBottomInfoText'], '');

        if ($this->setValue('order.print', $postValue) !== true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * saveShoplinkerConfig 샵링커 키값 저장
     *
     * @param $getValue
     *
     * @return boolean
     */
    public function saveShoplinkerConfig($getValue)
    {
        $config = gd_policy('shoplinker.config');

        Logger::channel('shoplinker')->info(
            __METHOD__ . ' config save original data:', [
                __CLASS__,
                $getValue,
            ]
        );
        $updateData = [];
        $updateData['shopKey'] = gd_isset($getValue['shopKey'], $config['shopKey']);
        $updateData['slinkerKey'] = gd_isset($getValue['slinkerKey'], $config['slinkerKey']);
        $result = $this->setValue('shoplinker.config', $updateData);

        if ($result != true) {
            Logger::channel('shoplinker')->info(
                __METHOD__ . 'config save fail : data[' . $getValue['shopKey'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        } else {
            Logger::channel('shoplinker')->info(
                __METHOD__ . 'config save success : data[' . $getValue['shopKey'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        }

        return $result;
    }

    /**
     * saveKcpCellphoneAuthConfig Kcp 휴대폰 본인인증 키값 저장
     *
     * @param $getValue
     *
     * @return boolean
     */
    public function saveKcpCellphoneAuthConfig($getValue)
    {
        $config = gd_policy('member.auth_cellphone_kcp');
        $config['useFlKcp'] = gd_isset($config['useFlKcp'], 'n');
        $config['useDataJoinFlKcp'] = gd_isset($config['useDataJoinFlKcp'], 'y');
        $config['useDataModifyFlKcp'] = gd_isset($config['useDataModifyFlKcp'], 'n');

        Logger::channel('service')->info(
            __METHOD__ . ' config save kcp cellphone auth original data:', [
                __CLASS__,
                $getValue,
            ]
        );
        $updateData = [];
        $updateData['serviceCode'] = gd_isset($getValue['serviceCode'], $config['serviceCode']);
        $updateData['serviceId'] = gd_isset($getValue['serviceId'], $config['serviceId']);
        $updateData['serviceStatus'] = gd_isset($getValue['serviceStatus'], $config['serviceStatus']);
        $updateData['timestamp'] = gd_isset($getValue['timestamp'], $config['timestamp']);
        $updateData['token'] = gd_isset($getValue['token'], $config['token']);
        $updateData['useFlKcp'] = gd_isset($getValue['useFlKcp'], $config['useFlKcp']);
        $updateData['useDataJoinFlKcp'] = gd_isset($getValue['useDataJoinFlKcp'], $config['useDataJoinFlKcp']);
        $updateData['useDataModifyFlKcp'] = gd_isset($getValue['useDataModifyFlKcp'], $config['useDataModifyFlKcp']);
        $result = $this->setValue('member.auth_cellphone_kcp', $updateData);

        if ($result != true) {
            Logger::channel('service')->info(
                __METHOD__ . 'config save kcp cellphone auth fail : data[' . $getValue['shopKey'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        } else {
            Logger::channel('service')->info(
                __METHOD__ . 'config save kcp cellphone auth success : data[' . $getValue['shopKey'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        }

        return $result;
    }

    /**
     * saveKakaoAlrimConfig 카카오알림톡 정보 저장
     *
     * @param $postValue
     *
     * @return $getValue
     */
    public function saveKakaoAlrimConfig($getValue)
    {
        $config = gd_policy('kakaoAlrim.config');

        Logger::channel('kakao')->info(
            __METHOD__ . ' config save original data:', [
                __CLASS__,
                $getValue,
            ]
        );
        $updateData = [];
        $updateData['plusId'] = gd_isset($getValue['plusId'], $config['plusId']);
        $updateData['phoneNumber'] = gd_isset($getValue['phoneNumber'], $config['phoneNumber']);
        $updateData['kakaoKey'] = gd_isset($getValue['kakaoKey'], $config['kakaoKey']);
        $updateData['categoryCode'] = gd_isset($getValue['categoryCode'], $config['categoryCode']);
        if ($getValue['useFlag']) {
            $updateData['useFlag'] = $getValue['useFlag'];
        } else {
            $updateData['useFlag'] = gd_isset($config['useFlag'], 'n');
        }

        $updateData['approvalFl'] = $getValue['approvalFl'] == 'y' ? 'y' : gd_isset($config['approvalFl']);
        $updateData['approvalDt'] = $getValue['approvalFl'] == 'y' ? $getValue['approvalDt'] : gd_isset($config['approvalDt']);
        $updateData['approvalId'] = $getValue['approvalFl'] == 'y' ? $getValue['approvalId'] : gd_isset($config['approvalId']);
        $result = $this->setValue('kakaoAlrim.config', $updateData);

        if ($result != true) {
            Logger::channel('kakao')->info(
                __METHOD__ . 'config save fail : data[' . $getValue['plusId'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        } else {
            Logger::channel('kakao')->info(
                __METHOD__ . 'config save success : data[' . $getValue['plusId'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        }

        return $result;
    }

    /**
     * saveKakaoAlrimConfig 카카오알림톡 정보 저장(루나)
     *
     * @param $postValue
     *
     * @return $getValue
     */
    public function saveKakaoAlrimLunaConfig($getValue)
    {
        $config = gd_policy('kakaoAlrimLuna.config');

        Logger::channel('kakao')->info(
            __METHOD__ . ' luna config save original data:', [
                __CLASS__,
                $getValue,
            ]
        );
        $updateData = [];

        $updateData['lunaCliendId'] = gd_isset($getValue['clientId'], $config['lunaCliendId']);
        $updateData['lunaClientKey'] = gd_isset($getValue['clientKey'], $config['lunaClientKey']);

        if ($getValue['useFlag']) {
            $updateData['useFlag'] = $getValue['useFlag'];
        } else {
            $updateData['useFlag'] = gd_isset($config['useFlag'], 'n');
        }

        if($getValue['lunaKeyDel'] == 'y'){
            $updateData['lunaCliendId'] = '';
            $updateData['lunaClientKey'] = '';
            $updateData['useFlag'] = 'n';
        }

        $result = $this->setValue('kakaoAlrimLuna.config', $updateData);

        if ($result != true) {
            Logger::channel('kakao')->info(
                __METHOD__ . 'luna config save fail : data[' . $getValue['lunaCliendId'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        } else {
            Logger::channel('kakao')->info(
                __METHOD__ . 'luna config save success : data[' . $getValue['lunaCliendId'] . ']', [
                    __CLASS__,
                    $updateData,
                ]
            );
        }

        return $result;
    }

    /**
     * saveDevelopmentTerms 개발소스관리 동의 정보 저장
     *
     * @param array $getValue
     *
     * @return boolean
     */
    public function saveDevelopmentTerms($getValue)
    {
        $config = gd_policy('development.terms');
        $insertData = [];
        if($getValue['agree'] && !$config) {
            $insertData['date'] =  date('Y-m-d H:i:s');
            $insertData['id'] = \Session::get('manager.managerId');
            $insertData['ip'] = \Request::server()->get('REMOTE_ADDR');
            $result = $this->setValue('development.terms', $insertData);
        }
        return $result;
    }

    /**
     * saveDeveloperInfo 개발소스관리 기술지원 쇼핑몰 개발 담당
     *
     * @param array $getValue
     *
     * @return boolean
     */
    public function saveDeveloperInfo($getValue)
    {
        $insertData = [];
        $insertData['email'] = $getValue['email'];
        $insertData['cellPhone'] = implode('-', $getValue['cellPhone']);
        $insertData['saveId'] = \Session::get('manager.managerId');
        $insertData['ip'] = \Request::server()->get('REMOTE_ADDR');
        $result = $this->setValue('development.developer', $insertData);
        return $result;
    }

    /**
     * saveSafeNumberConfig 안심번호 사용 정보 저장
     *
     * @param array $getValue
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function saveSafeNumberConfig($getValue)
    {
        $orderBasic = gd_policy('order.basic');

        // 안심번호 신청 값
        if (isset($getValue['safeNumberUseFl'])) {
            $orderBasic['safeNumberFl'] = strtolower($getValue['safeNumberUseFl']);
            $orderBasic['useSafeNumberFl'] = strtolower($getValue['safeNumberUseFl']);
        }

        // GOMS 안심번호 on/off 값
        if (isset($getValue['safeNumberServiceFl'])) {
            $orderBasic['safeNumberServiceFl'] = strtolower($getValue['safeNumberServiceFl']);
        }

        $result = $this->setValue('order.basic', $orderBasic);
        return $result;
    }

    /**
     * saveStibeeConfig 스티비 회원가입 데이터
     *
     * @param array $getValue
     *
     * @return boolean
     */
    public function saveStibeeConfig($getValue)
    {
        $insertData = [];
        $insertData['useStibeeFl'] = strtoupper($getValue['useStibeeFl']);
        $result = $this->setValue('stibee.config', $insertData);
        return $result;
    }

    /**
     * saveCremaConfig 크리마 설정 저장
     *
     * @param array $getValue
     *
     * @return boolean
     */
    public function saveCremaConfig($getValue)
    {
        $insertData = [];
        $insertData['useCremaFl'] = strtolower(gd_isset($getValue['useCremaFl'], 'n'));
        if (gd_isset($getValue['clientId'])) {
            $insertData['clientId'] = $getValue['clientId'];
        }
        if (gd_isset($getValue['clientSecret'])) {
            $insertData['clientSecret'] = $getValue['clientSecret'];
        }
        if (gd_isset($getValue['brand_id'])) {
            $insertData['brandId'] = $getValue['brand_id'];
        }
        if (empty($insertData['clientId']) === false && empty($insertData['clientSecret']) === false) {
            $insertData['useEpFl'] = 'y';
        }
        $result = $this->setValue('service.crema', $insertData);
        return $result;
    }

    /**
     * saveConfigClaimOrder 클레임 주문 운영방식 설정
     *
     * @param $getValue
     *
     * @return bool
     */
    public function saveConfiguserExchangeConfig($getValue){
        $result = $this->setValue('order.userExchangeConfig', $getValue);
        return $result;
    }

    /**
     * saveOrderSalesProcess 매출 통계 운영 방식 저장(실시간OR주기적)
     *
     * @param $getValue
     *
     * @return bool
     */
    public function saveOrderSalesProcess($getValue)
    {
        $insertData = [];
        // 튜닝 파일 여부
        $tuningFileExistFl = false;
        $tuningFilePath = \UserFilePath::module("Component/Order/OrderSalesStatistics.php")->getPathName();
        if(\FileHandler::isExists($tuningFilePath) && $getValue['salesProcess'] == 'realTime') {
            $tuningFileExistFl = true;
        }

        if ($tuningFileExistFl) {
            throw new \Exception(__('설정 변경이 불가합니다. 확인 후 재시도 해주세요'));
        } else {
            if(gd_isset($getValue)) {
                $insertData['id'] = \Session::get('manager.managerId');
                $insertData['ip'] = \Request::server()->get('REMOTE_ADDR');
                $insertData['date'] = date('Y-m-d H:i:s');
                $insertData['processSystem'] = $getValue['salesProcess'];
                $result = $this->setValue('statistics.order', $insertData);
                if ($result != true) {
                    throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
                }
            }
        }

        return $result;
    }


    /**
     * saveOrderSalesTuningArgreement 매출 통계 튜닝 파일 동의 저장
     *
     * @return bool
     */
    public function saveOrderSalesTuningArgreement()
    {
        $insertData = [];
        $insertData['id'] = \Session::get('manager.managerId');
        $insertData['ip'] = \Request::server()->get('REMOTE_ADDR');
        $insertData['date'] = date('Y-m-d');
        $insertData['fileName'] = 'Order/OrderSalesStatistics.php';
        $result = $this->setValue('development.tuning', $insertData);
        if ($result != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }

        return $result;
    }
}
