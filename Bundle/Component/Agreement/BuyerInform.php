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
namespace Bundle\Component\Agreement;

use App;
use Component\Mall\Mall;
use Component\AbstractComponent;
use Component\Database\DBTableField;
use Component\Policy\BaseAgreementPolicy;
use Component\Validator\Validator;
use Exception;
use Framework\Database\DBTool;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\LayerException;
use Framework\StaticProxy\Proxy\FileHandler;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Logger;
use Request;
use Session;

/**
 * Class 안내문 관리
 * @package Bundle\Component\Agreement
 * @author  yjwee
 */
class BuyerInform extends \Component\AbstractComponent
{
    const ECT_INVALID_ARG = 'BuyerInform.ECT_INVALID_ARG';

    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';

    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';

    const TEXT_USELESS_VALUE = '%s은(는) 사용할 수 없습니다.';

    // informCd,informNm,groupCd
    private $goodsInfo = [
        '002' => 'goodsInfoDelivery',
        '003' => 'goodsInfoAS',
        '004' => 'goodsInfoRefund',
        '005' => 'goodsInfoExchange',
    ];

    // informCd,informNm,groupCd
    private $arrBind = [];
    // 리스트 검색관련
    private $arrWhere = [];
    // 리스트 검색관련
    private $checked = [];
    // 리스트 검색관련
    private $search = [];

    // __('배송안내')
    private $goodsInfoDelivery = [
        '002000',
        '배송안내',
        '002',
    ];
    // informCd,informNm,groupCd
    // __('AS안내')
    private $goodsInfoAS = [
        '003000',
        'AS안내',
        '003',
    ];
    // informCd,informNm,groupCd
    // __('환불안내')
    private $goodsInfoRefund = [
        '004000',
        '환불안내',
        '004',
    ];
    // informCd,informNm,groupCd
    // __('교환안내')
    private $goodsInfoExchange = [
        '005000',
        '교환안내',
        '005',
    ];
    // informCd,informNm,groupCd
    // __('회사소개')
    private $company = [
        '010001',
        '회사소개',
        '010',
    ];

    private $mall;
    private $saveMallSno = 1;

    public function __construct(DBTool $db = null)
    {
        parent::__construct($db);
        $this->tableFunctionName = 'tableMember';
        $this->mall = new Mall();
    }

    /**
     * 이용약관 정보
     *
     * @author sunny
     * @return array
     * @deprecated
     * @uses   BuyerInform::getAgreementWithReplaceCode
     */
    public function getAgreementContentWithReplaceCode()
    {
        /** @var \Bundle\Component\Design\ReplaceCode $replaceCode */
        $replaceCode = App::load('\\Component\\Design\\ReplaceCode');
        $replaceCode->initWithUnsetDiff(
            [
                '{rc_mallNm}',
                '{rc_companyNm}',
            ]
        );
        $defineCode = $replaceCode->getDefinedCode();
        $mallInfo = [];
        foreach ($defineCode as $key => $value) {
            $mallInfo[$key] = $value['val'];
        }
        $inform = $this->_getAgreement();
        $inform['content'] = str_replace(array_keys($mallInfo), array_values($mallInfo), $inform['content']);

        return $inform;
    }

    /**
     * getAgreementWithReplaceCode
     *
     * @param $code
     *
     * @return string
     * @throws Exception
     */
    public function getAgreementWithReplaceCode($code, $mallSno = DEFAULT_MALL_NUMBER)
    {
        /** @var \Bundle\Component\Design\ReplaceCode $replaceCode */
        $replaceCode = App::load('\\Component\\Design\\ReplaceCode');
        $replaceCode->initWithUnsetDiff(
            [
                '{rc_mallNm}',
                '{rc_companyNm}',
            ]
        );
        $defineCode = $replaceCode->getDefinedCode();
        $mallInfo = [];
        foreach ($defineCode as $key => $value) {
            $mallInfo[$key] = $value['val'];
        }

        $databaseKey = BuyerInformCode::toArray($code);
        $agreementFl = StringUtils::contains($code, BuyerInformCode::AGREEMENT);
        $informCd = $agreementFl ? $code : $databaseKey[0];
        $inform = $this->getInformData($informCd, $mallSno);

        if ($agreementFl && $inform['modeFl'] === 'y') {
            $policy = new BaseAgreementPolicy();
            $agreementDate = $policy->getAgreementDate();
            // __('0000년 00월 00일')
            $agreementDate = $agreementDate['year'] . '년 ' . $agreementDate['month'] . '월 ' . $agreementDate['day'] . '일';
            $mallInfo['0000년 00월 00일'] = $agreementDate;
        }

        $inform['content'] = str_replace(array_keys($mallInfo), array_values($mallInfo), $inform['content']);

        return $inform;
    }

    /**
     * 사용자 정의 이용약관 데이터 반환 함수
     *
     * @return string
     */
    private function _getAgreement()
    {
        $databaseKey = BuyerInformCode::toArray(BuyerInformCode::AGREEMENT);

        return $inform = $this->getInformData($databaseKey[0]);
    }

    /**
     * 아이템 데이터 조회
     *
     * @param string $informCd
     * @param integer $mallSno
     *
     * @return mixed
     * @throws Exception
     */
    public function getInformData($informCd, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $postValue = Request::post()->all();
        if (Validator::pattern('/^[0-9]{6,9}$/', $informCd, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), 'informCd'));
        }

        $mallBySession = App::getInstance('session')->get(SESSION_GLOBAL_MALL);
        if ($mallBySession['sno'] > DEFAULT_MALL_NUMBER) {
            $mallSno = $mallBySession['sno'];
        }
        // 테이블명 반환
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $mallSno);
        $excludeField = $arrBind = null;
        switch ($postValue['mode']) {
            case 'list' :
                $excludeField = ['content'];
                break;
            case 'select' :
                $arrWhere[] = "displayShopFl = ?";
                $this->db->bind_param_push($arrBind, 's', 'y');
                break;
            default :
                $this->db->strLimit = '1';
                break;
        }
        $arrField = DBTableField::setTableField('tableBuyerInform', null, $excludeField);
        $arrField[] = 'sno, informNm, regDt, modDt';
        if (Request::request()->get('code') > 0 && $postValue['mode'] != 'select') {
            $arrWhere[] = "informCd = ?";
        } else {
            $arrWhere[] = "informCd like concat(?, '%')";
        }
        $this->db->bind_param_push($arrBind,'s',$informCd);
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $arrWhere[] = 'mallSno = ?';
            $this->db->bind_param_push($arrBind,'i',$mallSno);
        }
        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'sno desc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL,$arrBind, false);

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * 이용약관 저장
     *
     * @param $content
     * @param $modeFl
     *
     * @throws Except
     * @throws Exception
     */
    public function saveAgreement($content, $modeFl, $mallSno = DEFAULT_MALL_NUMBER)
    {
        // 테이블명 반환
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $mallSno);
        $this->saveMallSno = $mallSno;

        $postValue = Request::post()->toArray();
        $newInformFl = gd_isset($postValue['newInformFl'], 'n');
        if ($newInformFl === 'y' && empty($postValue['informNm'])) {
            throw new LayerException(__('약관명을 입력해주세요.'));
        }
        $vo = new BuyerInformVo(BuyerInformCode::AGREEMENT);
        $vo->setModeFl($modeFl);
        $vo->setContent($content);
        if (empty($postValue['informNm']) === false) {
            $vo->setInformNm(strip_tags($postValue['informNm']));
        }
        $selectResult = $this->getInformDataArray(BuyerInformCode::AGREEMENT, 'sno', false, $mallSno);
        $selectResult = ArrayUtils::getSubArrayByKey($selectResult, 'sno');
        $selectCount = count($selectResult);
        if ($newInformFl === 'y') {
            $selectCount++;
        }
        $agreementCount = str_pad($selectCount, 3, '0', STR_PAD_LEFT);
        $informCd = $agreementCount == '001' ? BuyerInformCode::AGREEMENT : BuyerInformCode::AGREEMENT . $agreementCount;
        $vo->setInformCd($informCd);
        if ($vo->getModeFl() == 'y') {
            $baseAgreementPath = \UserFilePath::adminSkin('policy', 'base_agreement.txt')->getRealPath();
            if (FileHandler::isExists($baseAgreementPath)) {
                $vo->setContent(FileHandler::read($baseAgreementPath));
            };
        }
        $strSQL = "SELECT sno, displayShopFl FROM " . $tableName . " WHERE informCd='" . gd_isset($informCd, BuyerInformCode::AGREEMENT) . "'";
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $strSQL .= " AND mallSno='" . $mallSno . "'";
        }
        $result = $this->db->fetch($strSQL);
        if ($this->db->num_rows(false) && $newInformFl !== 'y') {
            $vo->setDisplayShopFl($result['displayShopFl']);
            $this->_update($vo);
        } else {
            $vo->setDisplayShopFl('y');
            $this->_insert($vo);
        }
    }

    /**
     * 약관 / 개인정보처리방침 타이틀 및 노출 여부 수정
     *
     * @throws Exception
     */
    public function modifyInform()
    {
        $postValue = Request::post()->toArray();
        $mallSno = gd_isset($postValue['mallSno'], 1);
        $config = null;
        // 이전 약관 노출 여부 저장
        if (empty($postValue['displayAgreementFl']) === false) {
            $config['displayAgreementFl'] = $postValue['displayAgreementFl'];
        } elseif (empty($postValue['displayPrivateFl']) === false) {
            $config['displayPrivateFl'] = $postValue['displayPrivateFl'];
        }
        if (empty($config) === false) {
            gd_set_policy('basic.agreement', $config, true, $mallSno);
        }
        // 쇼핑몰 노출 여부 저장
        $diffDisplayShopFl = array_diff_assoc($postValue['displayShopFl'], $postValue['beforeDisplayShopFl']);
        if (is_array($diffDisplayShopFl)) {
            foreach ($diffDisplayShopFl as $informCd => $val) {
                $this->modifyInformDisplayFl($informCd, $val, $mallSno);
            }
        }
        // 약관명 저장
        if (empty($postValue['title']) === false) {
            $this->modifyInformTitle($postValue['informCd'], $postValue['title'], $mallSno);
        }
    }

    /**
     * 약관 노출 여부 수정
     *
     * @param $informCd
     * @param $displayShopFl
     * @param int $mallSno
     * @throws Exception
     */
    public function modifyInformDisplayFl($informCd, $displayShopFl, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $this->saveMallSno = $mallSno;
        $vo = new BuyerInformVo(BuyerInformCode::AGREEMENT);
        $vo->setInformCd($informCd);
        $vo->setDisplayShopFl($displayShopFl);
        $this->_update($vo, ['displayShopFl']);
    }

    /**
     * 약관명 수정
     * 
     * @param $informCd
     * @param $informNm
     * @param int $mallSno
     * @throws Exception
     */
    public function modifyInformTitle($informCd, $informNm, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if (empty($informNm)) {
            throw new LayerException(__('약관명을 입력해주세요.'));
        }
        $this->saveMallSno = $mallSno;
        $vo = new BuyerInformVo(BuyerInformCode::AGREEMENT);
        $vo->setInformCd($informCd);
        $vo->setInformNm($informNm);
        $this->_update($vo, ['informNm']);
    }

    /**
     * _update
     *
     * @param BuyerInformVo $vo
     * @param array $includeField
     *
     * @throws Exception
     */
    private function _update(BuyerInformVo $vo, $includeField = null)
    {
        Logger::info(__METHOD__);
        BuyerInform::validateUpdate($vo);
        $excludeField = 'scmNo,informCd,groupCd,regDt';
        if (empty($vo->getInformNm())) {
            $excludeField .= ',informNm';
        }

        // 테이블명 반환
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $this->saveMallSno);

        $bindArray = $this->db->get_binding(DBTableField::tableBuyerInform(), $vo->toArray(), 'update', $includeField, explode(',', $excludeField));
        if ($vo->getSno() === null) {
            $this->db->bind_param_push($bindArray['bind'], 'i', $vo->getInformCd());
            $whereIs = 'informCd = ?';
            if ($this->saveMallSno > DEFAULT_MALL_NUMBER) {
                $this->db->bind_param_push($bindArray['bind'], 'i', $this->saveMallSno);
                $whereIs .= ' AND mallSno = ?';
            }
            $this->db->set_update_db($tableName, $bindArray['param'], $whereIs, $bindArray['bind'], false);
        } else {
            $this->db->bind_param_push($bindArray['bind'], 'i', $vo->getSno());
            $whereIs = 'sno = ?';
            if ($this->saveMallSno > DEFAULT_MALL_NUMBER) {
                $this->db->bind_param_push($bindArray['bind'], 'i', $this->saveMallSno);
                $whereIs .= ' AND mallSno = ?';
            }
            $this->db->set_update_db($tableName, $bindArray['param'], $whereIs, $bindArray['bind'], false);
        }
    }

    /**
     * 약관 추가 시 검증 함수
     *
     * @static
     *
     * @param BuyerInformVo $vo
     *
     * @return Validator
     * @throws Exception
     */
    public static function validateInsert(BuyerInformVo $vo)
    {
        $v = new Validator();
        $v->init();
        $v->add('informCd', 'pattern', true, '', '/^[0-9]{6,9}$/');
        $v->add('groupCd', 'pattern', true, '', '/^[0-9]{3}$/');
        $v->add('informNm', '');
        $v->add('content', '');
        $v->add('modeFl', 'yn');
        $v->add('scmNo', '');

        if ($v->act($vo->toArray(), true) === false) {
            throw new Exception(implode("\n", $v->errors));
        }

        return $v;
    }

    /**
     * 수정
     *
     * @static
     *
     * @param BuyerInformVo $vo
     *
     * @return Validator
     * @throws Exception
     */
    public static function validateUpdate(BuyerInformVo $vo)
    {
        $v = new Validator();
        $v->init();
        $v->add('sno', 'number', true);
        $v->add('informNm', '');
        $v->add('content', '');
        $v->add('modeFl', 'yn');
        $v->add('displayShopFl', 'yn');

        if ($v->act($vo->toArray(), true) === false) {
            throw new Exception(implode("\n", $v->errors));
        }

        return $v;
    }

    /**
     * 등록
     *
     * @param BuyerInformVo $vo
     *
     * @throws Exception
     */
    private function _insert(BuyerInformVo $vo)
    {
        Logger::info(__METHOD__);
        BuyerInform::validateInsert($vo);

        // 테이블명 반환
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $this->saveMallSno);

        $arrBind = $this->db->get_binding(DBTableField::tableBuyerInform(), $vo->toArray(), 'insert');
        if ($this->saveMallSno > DEFAULT_MALL_NUMBER) {
            $arrBind['param'][] = 'mallSno';
            $arrBind['bind'][0] .= 'i';
            $arrBind['bind'][] = $this->saveMallSno;
        }
        $this->db->set_insert_db($tableName, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 기본정책 약관, 개인정보관련 동의사항, 이용, 탈퇴 안내 저장 함수
     *
     * @param string $code    BuyerInformCode
     * @param string $content 약관 내용
     * @param integer $mallSno
     *
     * @throws Exception
     */
    public function saveInformData($code, $content, $mallSno = DEFAULT_MALL_NUMBER)
    {
        // 테이블명 반환
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $mallSno);
        $this->saveMallSno = $mallSno;

        $postValue = Request::post()->toArray();
        $newInformFl = gd_isset($postValue['newInformFl'], 'n');
        if ($newInformFl === 'y' && empty($postValue['informNm'])) {
            throw new LayerException(__('약관명을 입력해주세요.'));
        }
        $vo = new BuyerInformVo($code);
        $vo->setContent($content);
        $privateCodeFl = StringUtils::contains($code, BuyerInformCode::BASE_PRIVATE);
        if ($privateCodeFl) {
            if (empty($postValue['informNm']) === false) {
                $vo->setInformNm(strip_tags($postValue['informNm']));
            }
            $selectResult = $this->getInformDataArray(BuyerInformCode::BASE_PRIVATE, 'sno', false, $mallSno);
            $selectResult = ArrayUtils::getSubArrayByKey($selectResult, 'sno');
            $selectCount = count($selectResult);
            if ($newInformFl === 'y') {
                $selectCount++;
            }
            $informCount = str_pad($selectCount, 3, '0', STR_PAD_LEFT);
            $informCd = $informCount == '001' ? BuyerInformCode::BASE_PRIVATE : BuyerInformCode::BASE_PRIVATE . $informCount;
            $vo->setInformCd($informCd);
        }

        if($code == BuyerInformCode::PRIVATE_MARKETING) {
            $vo->setInformNm(strip_tags($postValue['privateMarketingWriteTitle']));
            $vo->setModeFl(gd_isset($postValue['privateMarketingFl'], 'n'));
        }

        $strSQL = "SELECT sno, displayShopFl FROM " . $tableName . " WHERE informCd='" . $code . "'";
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $strSQL .= " AND mallSno='" . $mallSno . "'";
        }
        $result = $this->db->fetch($strSQL);
        if ($this->db->num_rows(false) && $newInformFl !== 'y') {
            if ($privateCodeFl && empty($result['displayShopFl']) === false) {
                $vo->setDisplayShopFl($result['displayShopFl']);
            }
            $this->_update($vo);
        } else {
            if ($privateCodeFl) {
                $vo->setDisplayShopFl('y');
            }
            $this->_insert($vo);
        }
    }

    /**
     * 개인정보수집 동의항목 설정 저장(필수 외)
     *
     * @param array $requestParams 데이터
     */
    public function savePrivateApprovalOption($requestParams)
    {
        Logger::info(__METHOD__);
        $mallSno = gd_isset($requestParams['mallSno'], 1);
        $modeFl = gd_isset($requestParams['privateApprovalOptionModeFl'], 'n');
        $snoArray = $requestParams['privateApprovalOptionSno'];
        $titleArray = $requestParams['privateApprovalOptionTitle'];
        $contentArray = $requestParams['privateApprovalOption'];

        $buyerCode = BuyerInformCode::toArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION);

        $selectResult = $this->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION, 'sno', false, $mallSno);
        $selectResult = ArrayUtils::getSubArrayByKey($selectResult, 'sno');
        $selectCount = count($selectResult);

        if (count($titleArray) > 0) {
            $this->saveMallSno = $mallSno;
            foreach ($titleArray as $key => $val) {
                $vo = new BuyerInformVo();
                $vo->setModeFl($modeFl);
                $vo->setContent($contentArray[$key]);
                $vo->setGroupCd($buyerCode[2]);
                $vo->setInformNm($val);

                // 약관 존재 여부
                if (in_array($snoArray[$key], $selectResult)) {
                    $vo->setSno($snoArray[$key]);
                    $this->_update($vo);
                } else {
                    $selectCount++; // 약관 informCd 선증가 처리
                    $vo->setInformCd(BuyerInformCode::PRIVATE_APPROVAL_OPTION . str_pad($selectCount, 3, '0', STR_PAD_LEFT));
                    $this->_insert($vo);
                }

                unset($data, $vo);
            }
        }
    }

    /**
     * [선택] 개인정보 취급위탁 동의 저장
     *
     * @param array $requestParams 데이터
     */
    public function savePrivateConsign($requestParams)
    {
        Logger::info(__METHOD__);
        $modeFl = gd_isset($requestParams['privateConsignModeFl'], 'n');
        $snoArray = $requestParams['privateConsignSno'];
        $titleArray = $requestParams['privateConsignTitle'];
        $contentArray = $requestParams['privateConsign'];
        $mallSno = gd_isset($requestParams['mallSno'], 1);

        $buyerCode = BuyerInformCode::toArray(BuyerInformCode::PRIVATE_CONSIGN);

        $selectResult = $this->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN, 'sno', false, $mallSno);
        $selectResult = ArrayUtils::getSubArrayByKey($selectResult, 'sno');
        $selectCount = count($selectResult);

        if (count($titleArray) > 0) {
            $this->saveMallSno = $mallSno;
            foreach ($titleArray as $key => $val) {
                $vo = new BuyerInformVo();
                $vo->setModeFl($modeFl);
                $vo->setContent($contentArray[$key]);
                $vo->setGroupCd($buyerCode[2]);
                $vo->setInformNm($val);

                // 약관 존재 여부
                if (in_array($snoArray[$key], $selectResult)) {
                    $vo->setSno($snoArray[$key]);
                    $this->_update($vo);
                } else {
                    $selectCount++; // 약관 informCd 선증가 처리
                    $vo->setInformCd(BuyerInformCode::PRIVATE_CONSIGN . str_pad($selectCount, 3, '0', STR_PAD_LEFT));
                    $this->_insert($vo);
                }

                unset($data, $vo);
            }
        }
    }

    /**
     * [선택] 개인정보 취급위탁 동의 저장
     *
     * @param array $requestParams 데이터
     */
    public function savePrivateOffer($requestParams)
    {
        $modeFl = gd_isset($requestParams['privateOfferModeFl'], 'n');
        $snoArray = $requestParams['privateOfferSno'];
        $titleArray = $requestParams['privateOfferTitle'];
        $contentArray = $requestParams['privateOffer'];
        $mallSno = gd_isset($requestParams['mallSno'], 1);

        $buyerCode = BuyerInformCode::toArray(BuyerInformCode::PRIVATE_OFFER);

        $selectResult = $this->getInformDataArray(BuyerInformCode::PRIVATE_OFFER, 'sno', false, $mallSno);
        $selectResult = ArrayUtils::getSubArrayByKey($selectResult, 'sno');
        $selectCount = count($selectResult);

        if (count($titleArray) > 0) {
            $this->saveMallSno = $mallSno;
            foreach ($titleArray as $key => $val) {
                $vo = new BuyerInformVo();
                $vo->setModeFl($modeFl);
                $vo->setContent($contentArray[$key]);
                $vo->setGroupCd($buyerCode[2]);
                $vo->setInformNm($val);

                // 약관 존재 여부
                if (in_array($snoArray[$key], $selectResult)) {
                    $vo->setSno($snoArray[$key]);
                    $this->_update($vo);
                } else {
                    $selectCount++; // 약관 informCd 선증가 처리
                    $vo->setInformCd(BuyerInformCode::PRIVATE_OFFER . str_pad($selectCount, 3, '0', STR_PAD_LEFT));
                    $this->_insert($vo);
                }

                unset($data, $vo);
            }
        }
    }

    /**
     * 개인정보수집 동의항목 설정 선택 항목 삭제
     *
     * @param $sno
     */
    public function deletePrivateItem($sno, $mallSno = DEFAULT_MALL_NUMBER)
    {
        // 테이블명 반환
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $mallSno);

        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $sno); // 추가 bind 데이터
        $this->db->set_delete_db($tableName, 'sno = ?', $arrBind);
    }

    /**
     * 몰 기본정보를 반환하는 함수
     *
     * @param int $scmNo 공급사 번호
     *
     * @return string
     */
    public function getMallBaseInfo($scmNo = DEFAULT_CODE_SCMNO)
    {
        $arrField = DBTableField::setTableField('tableScmManage');
        $strSQL = "SELECT scmNo, " . implode(', ', $arrField) . " FROM " . DB_SCM_MANAGE . " WHERE scmNo = ?";
        $arrBind = [
            's',
            $scmNo,
        ];
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        $data['businessNo'] = explode(' - ', $data['businessNo']);
        $data['email'] = explode('@', $data['email']);
        $data['phone'] = explode(' - ', $data['phone']);
        $data['fax'] = explode(' - ', $data['fax']);

        $checked = [];
        $checked['goodsDeliveryFl'][$data['goodsDeliveryFl']] = 'checked = "checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 몰 기본정보 저장 함수
     *
     * @param $arrData
     */
    public function saveMallBaseInfo($arrData,$mallSno)
    {
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            return true;
        }
        // 공급자 아이디
        gd_isset($arrData['scmNo'], DEFAULT_CODE_SCMNO);

        // 저장할 필드
        //@formatter:off
        $arrSaveData = ['companyNm', 'businessNo', 'service', 'item', 'email', 'zonecode', 'zipcode', 'address', 'addressSub', 'phone', 'fax', 'onlineOrderSerial', 'unstoringZonecode', 'unstoringZipcode', 'unstoringAddress', 'unstoringAddressSub', 'returnZonecode', 'returnZipcode', 'returnAddress', 'returnAddressSub',];
        //@formatter:on

        // 데이터 조합
        if (isset($arrData['businessNo']) && is_array($arrData['businessNo']) === true) {
            $arrData['businessNo'] = (implode('', $arrData['businessNo']) == '' ? '' : implode(' - ', $arrData['businessNo']));
        }
        if (isset($arrData['email']) && is_array($arrData['email']) === true) {
            $arrData['email'] = (implode('', $arrData['email']) == '' ? '' : implode('@', $arrData['email']));
        }
        if (isset($arrData['phone']) && is_array($arrData['phone']) === true) {
            $arrData['phone'] = (implode('', $arrData['phone']) == '' ? '' : implode(' - ', $arrData['phone']));
        }
        if (isset($arrData['fax']) && is_array($arrData['fax']) === true) {
            $arrData['fax'] = (implode('', $arrData['fax']) == '' ? '' : implode(' - ', $arrData['fax']));
        }

        // 기본 값 설정
        $setValue = [];
        foreach ($arrSaveData as $val) {
            $setValue[$val] = gd_isset($arrData[$val]);
        }

        // 처리할 필드
        $bindField = array_keys($setValue);

        // 수정 처리
        $arrBind = $this->db->get_binding(DBTableField::tableScmManage(), $setValue, 'update', $bindField);
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['scmNo']);
        $this->db->set_update_db(DB_SCM_MANAGE, $arrBind['param'], 'scmNo = ?', $arrBind['bind']);
    }

    /**
     * getGoodsInfoList
     *
     * @return mixed
     */
    public function getGoodsInfoList($mode=null)
    {
        $getValue = Request::get()->toArray();

        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableBuyerInform');

        // --- 검색 설정
        $this->search['detailSearch'] = gd_isset($getValue['detailSearch']);
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['groupCd'] = gd_isset($getValue['groupCd']);
        $this->search['modeFl'] = gd_isset($getValue['modeFl']);
        $this->search['sort'] = gd_isset($getValue['sort'], 'modeFl asc,regDt desc');
        //검색설정
        $this->search['sortList'] = [
            'regDt desc'    => __('등록일 ↑'),
            'regDt asc'     => __('등록일 ↓'),
            'informNm desc' => __('이용안내 제목 ↑'),
            'informNm asc'  => __('이용안내 제목 ↓'),
        ];


        $this->search['scmFl'] = gd_isset($getValue['scmFl'], 'all');
        $this->search['scmNo'] = gd_isset($getValue['scmNo'], (string) Session::get('manager.scmNo'));
        $this->search['scmNoNm'] = gd_isset($getValue['scmNoNm'],(string) Session::get('manager.companyNm'));

        $this->checked['scmFl'][$this->search['scmFl']] = $this->checked['groupCd'][$this->search['groupCd']] = $this->checked['modeFl'][$this->search['modeFl']] = 'checked = "checked"';

        // 기본 검색
        $searchIn = array_keys($this->goodsInfo);
        $this->arrWhere[] = 'groupCd IN (\'' . implode('\', \'', $searchIn) . '\')';


        // 키워드 검색
        if ($this->search['keyword']) {
            if($this->search['key']) {
                $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldType[$this->search['key']], $this->search['keyword']);
            }  else {

                $tmpWhere = array('content', 'informNm');
                $arrWhereAll = array();
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind,$fieldType[$keyNm], $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            }
        }
        // 이용안내 종류 검색
        if ($this->search['groupCd']) {
            $this->arrWhere[] = 'groupCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['groupCd'], $this->search['groupCd']);
        }

        // 기본 사용 여부 검색
        if ($this->search['modeFl']) {
            $this->arrWhere[] = 'modeFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['modeFl'], $this->search['modeFl']);
        }

        //공급사
        if (Session::get('manager.isProvider')) {

            switch ($this->search['scmFl'] ) {
                case 'all':
                    $this->arrWhere[] = '((b.scmNo = '.DEFAULT_CODE_SCMNO.' AND scmDisplayFl="y") OR (b.scmNo= ?))';
                    $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);
                    break;
                case 'n':
                    $this->arrWhere[] = '(b.scmNo = '.DEFAULT_CODE_SCMNO.' AND scmDisplayFl="y")';
                    break;
                case 'y':
                    $this->arrWhere[] = 'b.scmNo= ?';
                    $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);
                    break;
            }

        } else {
            if ($this->search['scmFl'] != 'all') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'b.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else {
                    $this->arrWhere[] = 'b.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);
                }
            }
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'b.modeFl desc';
        }


        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($getValue['pagelink'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], '10');
        } else {
            // --- 페이지 기본설정
            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);
        }

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());


        $this->db->strField = "b.*,s.companyNm as scmNm,g.goodsNo";
        $join[] = 'INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = b.scmNo ';
        $join[] = 'LEFT JOIN ' . DB_GOODS . ' as g ON (g.detailInfoDelivery = b.informCd OR g.detailInfoAS = b.informCd OR g.detailInfoRefund = b.informCd OR g.detailInfoExchange = b.informCd)';
        $this->db->strJoin = implode('', $join);

        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strGroup = 'b.informCd';
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BUYER_INFORM . ' as b' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        unset($query['group'], $query['order'], $query['limit']);
        $strCntSQL = 'SELECT COUNT(DISTINCT b.informCd) AS total FROM ' . DB_BUYER_INFORM . ' AS b ' . implode(' ', $query);
        $page->recode['total'] = $this->db->query_fetch($strCntSQL, $this->arrBind, false)['total'];

        if (Session::get('manager.isProvider')) {
            $page->recode['amount'] = $this->db->getCount(DB_BUYER_INFORM, 'sno', 'WHERE '.$this->arrWhere[0].'  AND (scmNo = "' . Session::get('manager.scmNo') . '" OR (scmNo = "'.DEFAULT_CODE_SCMNO.'" AND scmDisplayFl = "y"))');
        }   // 전체 레코드 수
        else {
            $page->recode['amount'] = $this->db->getCount(DB_BUYER_INFORM, 'sno', 'WHERE '.$this->arrWhere[0]);
        }   // 전체 레코드 수


        $page->setPage();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * getGoodsInfoListLayer
     *
     * @return mixed
     */
    public function getGoodsInfoListLayer($getValue)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableBuyerInform');

        // --- 검색 설정
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['groupCd'] = gd_isset($getValue['groupCd']);
        $this->search['modeFl'] = gd_isset($getValue['modeFl']);
        $this->search['sort'] = gd_isset($getValue['sort'], 'modeFl asc,regDt desc');
        $this->search['scmFl'] = gd_isset($getValue['scmFl'], '');
        $this->search['scmNo'] = gd_isset($getValue['scmNo'], (string) Session::get('manager.scmNo'));

        // 기본 검색
        $this->arrWhere[] = 'groupCd = ?';
        $this->db->bind_param_push($this->arrBind, $fieldType['groupCd'], $this->search['groupCd']);

        // 키워드 검색
        if ($this->search['keyword']) {
            $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($this->arrBind, $fieldType[$this->search['key']], $this->search['keyword']);
        }

        // 본사 상품이면 본사 정보만
        // 공급사 상품이면 본사에서 허용한 정보 와 공급사 정보
        switch ($this->search['scmFl'] ) {
            case 'n':
                $this->arrWhere[] = '(b.scmNo = '.DEFAULT_CODE_SCMNO.')';
                $countWhere = '(scmNo = '.DEFAULT_CODE_SCMNO.')';
                break;
            case 'y':
                $this->arrWhere[] = '((b.scmNo = '.DEFAULT_CODE_SCMNO.' AND b.scmDisplayFl = "y") OR (b.scmNo = ?))';
                $this->db->bind_param_push($this->arrBind, $fieldType['scmNo'], $this->search['scmNo']);
                $countWhere = '((scmNo = '.DEFAULT_CODE_SCMNO.' AND scmDisplayFl = "y") OR (scmNo = '. $this->search['scmNo'] .'))';
                break;
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'b.modeFl desc';
        }

        // --- 페이지 기본설정
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        } else {
            $getValue['page'] = 1;
        }
        gd_isset($getValue['pageNum'], '10');

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "b.*,s.companyNm as scmNm,g.goodsNo";
        $join[] = 'INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = b.scmNo ';
        $join[] = 'LEFT JOIN ' . DB_GOODS . ' as g ON (g.detailInfoDelivery = b.informCd OR g.detailInfoAS = b.informCd OR g.detailInfoRefund = b.informCd OR g.detailInfoExchange = b.informCd)';
        $this->db->strJoin = implode('', $join);

        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strGroup = 'b.informCd';
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BUYER_INFORM . ' as b' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        $join = null;
        $this->db->strField = "count(*) as cnt";
        $join[] = 'INNER JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = b.scmNo ';
        $join[] = 'LEFT JOIN ' . DB_GOODS . ' as g ON (g.detailInfoDelivery = b.informCd OR g.detailInfoAS = b.informCd OR g.detailInfoRefund = b.informCd OR g.detailInfoExchange = b.informCd)';
        $this->db->strJoin = implode('', $join);

        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strGroup = 'b.informCd';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BUYER_INFORM . ' as b' . implode(' ', $query);

        $total = $this->db->query_fetch($strSQL, $this->arrBind,false);
        $total = count($total);

        // 검색 레코드 수
        $page->recode['total'] = $total;
        $page->recode['amount'] = $this->db->getCount(DB_BUYER_INFORM, 'sno', 'WHERE groupCd = "'.$this->search['groupCd'].'" AND '. $countWhere);

        $page->setPage();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * getGoodsInfoCode
     *
     * @param null $modeStr
     * @param null $scmNo
     *
     * @return array|string
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getGoodsInfoCode($modeStr = null, $scmNo = null)
    {
        $searchIn = array_keys($this->goodsInfo);
        $this->db->strWhere = 'groupCd IN (\'' . implode('\', \'', $searchIn) . '\')';
        if ($scmNo) $this->db->strWhere .= " AND (scmNo = '" . $scmNo . "'  OR (scmNo = ".DEFAULT_CODE_SCMNO." AND scmDisplayFl='y'))";
        $this->db->strOrder = 'scmNo ASC , informNm ASC';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BUYER_INFORM . implode(' ', $query);
        $result = $this->db->query($strSQL);

        foreach ($searchIn as $val) {
            $getData[$val][] = '=' . __('사용안함') . '=';
        }

        while ($data = $this->db->fetch($result)) {

            if (Session::get('manager.isProvider')) {

                if(($data['scmNo'] ==DEFAULT_CODE_SCMNO && $data['scmModeFl'] =='y') || ($data['scmNo'] ==Session::get('manager.scmNo')&& $data['modeFl'] =='y')   ) {
                    $key = str_replace('goods', 'detail', $this->goodsInfo[$data['groupCd']]);
                    $getData['default'][$key] = $data['informCd'];
                    $getData['defaultInformNm'][$key] = $data['informNm'];
                    $getData['defaultInformContent'][$key] = $data['content'];
                }


            } else {

                // 상품 등록시의 기본값 설정
                if ($data['modeFl'] == 'y') {
                    $key = str_replace('goods', 'detail', $this->goodsInfo[$data['groupCd']]);
                    $getData['default'][$key] = $data['informCd'];
                    $getData['defaultInformNm'][$key] = $data['informNm'];
                    $getData['defaultInformContent'][$key] = $data['content'];
                }

            }

            $getData[$data['groupCd']][$data['informCd']] = $data['informNm'];
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getGoodsInfo
     *
     * @param null $informCd
     *
     * @return array|string
     * @throws Except
     */
    public function getGoodsInfo($informCd = null, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if (is_null($informCd) == false) {
            if (Validator::pattern('/^[0-9]{6}$/', $informCd, true) === false) {
                throw new AlertBackException(sprintf(__('%s 인자가 잘못되었습니다.'), 'informCd'));
            }

            $mallBySession = App::getInstance('session')->get(SESSION_GLOBAL_MALL);
            if ($mallBySession['sno'] > DEFAULT_MALL_NUMBER) {
                $mallSno = $mallBySession['sno'];
            }
            // 테이블명 반환
            $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $mallSno);

            $arrField = "*,s.companyNm as scmNm";
            if ($mallSno > DEFAULT_MALL_NUMBER) {
                $strSQL = "SELECT " . $arrField . " FROM " . $tableName . " INNER JOIN " . DB_SCM_MANAGE . " as s ON s.scmNo = " . $tableName . ".scmNo WHERE informCd='" . $informCd . "' AND mallSno='" . $mallSno . "' LIMIT 1";
            } else {
                $strSQL = "SELECT " . $arrField . " FROM " . $tableName . " INNER JOIN " . DB_SCM_MANAGE . " as s ON s.scmNo = " . $tableName . ".scmNo WHERE informCd='" . $informCd . "' LIMIT 1";
            }
            $data = $this->db->fetch($strSQL);

            if (Session::get('manager.isProvider')) {
                if($data['scmDisplayFl'] =='n' && $data['scmNo'] != Session::get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }

                if($data['scmNo'] != Session::get('manager.scmNo')) {
                    $data['saveFl'] = "n";
                }
            }

        } else {
            // 기본값 설정
            $tmpCode = DBTableField::tableBuyerInform();
            foreach ($tmpCode as $key => $val) {
                if (isset($data[$val['val']]) === false) {
                    if ($val['typ'] == 'i') {
                        $data[$val['val']] = (int) $val['def'];
                    } else {
                        $data[$val['val']] = $val['def'];
                    }
                }
            }
            unset($tmpCode);
        }

        if ($data['scmNo'] == DEFAULT_CODE_SCMNO) $data['scmFl'] = 'n';
        else $data['scmFl'] = 'y';

        gd_isset($data['saveFl'] ,'y');

        $checked = [];
        $checked['scmModeFl'][$data['scmModeFl']] =$checked['scmDisplayFl'][$data['scmDisplayFl']] =$checked['scmFl'][$data['scmFl']] = $checked['modeFl'][$data['modeFl']] = $checked['groupCd'][$data['groupCd']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return gd_htmlspecialchars_stripslashes($getData);
    }

    public function getGoodsInfoScmRelation($goodsInfoArrData)
    {
        $arrBind = [];
        // groupCd 로 상품 상세 이용안내(배송안내, AS안내, 교환안내, 환불안내) 를 구분 처리
        if (empty($goodsInfoArrData['groupCd']) === false) {
            $arrWhere[] = "groupCd = ?";
            $this->db->bind_param_push($arrBind, 's', $goodsInfoArrData['groupCd']);
        }
        // scmCode 로 본사 안내 일 경우 , 공급사 안내일 경우 를 구분 처리
        if ($goodsInfoArrData['scmCode'] == 'center') { // 본사 안내 경우 - 공급사의 기본 설정된 안내
            $arrWhere[] = "modeFl = ? AND scmNo != ?";
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 'i', DEFAULT_CODE_SCMNO);
        } else if ($goodsInfoArrData['scmCode'] == 'scm') { // 공급사 안내 경우 - 본사의 안내 중 공급사의 기본 설정된 안내
            $arrWhere[] = "scmModeFl = ? AND scmNo = ?";
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 'i', DEFAULT_CODE_SCMNO);
        }

        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BUYER_INFORM . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $returnData = count($getData);

        return $returnData;
    }

    /**
     * saveGoodsInfo
     *
     * @param $getData
     *
     * @throws Except
     * @throws Exception
     */
    public function saveGoodsInfo($getData)
    {
        if ($getData['mallSno'] > DEFAULT_MALL_NUMBER) {
            $this->saveGoodsInfoGlobal($getData);
            return;
        }
        // 이용안내 제목
        if (Validator::required(gd_isset($getData['informNm'])) === false) {
            throw new Exception(__('이용안내 제목은 필수 항목 입니다.'));
        } else {
            if (gd_is_html($getData['informNm']) === true) {
                throw new Exception(__('이용안내 제목에 태크는 필수 항목 입니다.'));
            }
        }

        // 이용안내 내용
        if (Validator::required(gd_isset($getData['content'])) === false) {
            throw new Exception(__('이용안내 내용은 필수 항목 입니다.'));
        }

        // 이용안내 종류
        if (Validator::required(gd_isset($getData['groupCd'])) === false) {
            throw new Exception(__('이용안내 종류는 필수 항목 입니다.'));
        }


        // 기본 사용 여부
        gd_isset($getData['modeFl'], 'n');
        gd_isset($getData['scmNo'],Session::get('manager.scmNo'));

        // 테이블명 반환
        $getData['mallSno'] = gd_isset($getData['mallSno'], 1);
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $getData['mallSno']);

        // 등록의 경우 informCd 생성
        $groupCd = $this->goodsInfo[$getData['groupCd']];
        $modeInsert = false;
        if (empty($getData['informCd'])) {
            if ($getData['mallSno'] > DEFAULT_MALL_NUMBER) {
                $strSQL = "SELECT if(max(informCd), max(informCd), " . $this->{$groupCd}[0] . ") as newInformCd FROM " . $tableName . " WHERE groupCd='" . $this->{$groupCd}[2] . "' AND mallSno='" . $getData['mallSno'] . "'";
            } else {
                $strSQL = "SELECT if(max(informCd), max(informCd), " . $this->{$groupCd}[0] . ") as newInformCd FROM " . $tableName . " WHERE groupCd='" . $this->{$groupCd}[2] . "'";
            }
            list($tmp) = $this->db->fetch($strSQL, 'row');
            $getData['informCd'] = sprintf('%06d', ($tmp + 1));
            $modeInsert = true;
            unset($tmp);
        }

        // 저장할 데이터
        $arrData = [
            'informCd' => $getData['informCd'],
            'groupCd'  => $this->{$groupCd}[2],
            'informNm' => $getData['informNm'],
            'content'  => $getData['content'],
            'modeFl'   => $getData['modeFl'],
            'scmModeFl'   => $getData['scmModeFl'],
            'scmDisplayFl'   => $getData['scmDisplayFl'],
            'scmNo'    => $getData['scmNo'],
        ];

        // 기본 사용인경우 같은 코드의 것은 n 으로 변경
        if ($getData['modeFl'] == 'y' && $getData['mallSno'] == DEFAULT_MALL_NUMBER) {
            if (gd_use_provider() && gd_is_provider()) { // 공급사를 사용하고 있고 공급사 관리자이면
                $scmRelationData = [
                    'scmCode' => 'scm',
                    'groupCd' => $this->{$groupCd}[2],
                ];
                $scmRelationCount = $this->getGoodsInfoScmRelation($scmRelationData); // 본사에서 본사 안내를 공급사 허용이고 공급사 기본 체크
                if ($scmRelationCount > 0) {
                    throw new Exception(__('본사의 이용안내가 기본 설정으로 되어 있습니다.'));
                }
            }
            $tmp['modeFl'] = 'n';
            $bindField = ['modeFl']; // 처리할 필드
            $arrBind = $this->db->get_binding(DBTableField::tableBuyerInform(), $tmp, 'update', $bindField);
            $this->db->bind_param_push($arrBind['bind'], 's', $this->{$groupCd}[2]);
            $this->db->bind_param_push($arrBind['bind'], 's', $getData['scmNo']);
            $this->db->set_update_db(DB_BUYER_INFORM, $arrBind['param'], 'groupCd = ? AND scmNo = ? ', $arrBind['bind']);
            unset($arrBind);
        }

        // 공급사기본 사용인경우 같은 코드의 것은 n 으로 변경
        if ($getData['scmModeFl'] == 'y') { // 본사에서 본사 안내를 공급사 기본 안내로 설정한다면
            if (gd_use_provider() && $getData['scmFl'] == 'n') { // 공급사를 사용하고 있고 본사 안내라면
                //공급사 안내의 기본 안내를 설정 해제
                $defaultModeFl['modeFl'] = 'n';
                $defaultBindField = ['modeFl']; // 처리할 필드
                $defaultArrBind = $this->db->get_binding(DBTableField::tableBuyerInform(), $defaultModeFl, 'update', $defaultBindField);
                $this->db->bind_param_push($defaultArrBind['bind'], 's', $this->{$groupCd}[2]);
                $this->db->bind_param_push($defaultArrBind['bind'], 'i', DEFAULT_CODE_SCMNO);
                $this->db->set_update_db(DB_BUYER_INFORM, $defaultArrBind['param'], 'groupCd = ? AND scmNo != ? ', $defaultArrBind['bind']);
                unset($defaultArrBind);
            }
            $tmp['scmModeFl'] = 'n';
            $bindField = ['scmModeFl']; // 처리할 필드
            $arrBind = $this->db->get_binding(DBTableField::tableBuyerInform(), $tmp, 'update', $bindField);
            $this->db->bind_param_push($arrBind['bind'], 's', $this->{$groupCd}[2]);
            $this->db->bind_param_push($arrBind['bind'], 'i', DEFAULT_CODE_SCMNO);
            $this->db->set_update_db(DB_BUYER_INFORM, $arrBind['param'], 'groupCd = ? AND scmNo = ? ', $arrBind['bind']);
            unset($arrBind);
        }

        $v = new Validator();
        // 등록 및 수정
        if ($modeInsert === false) {
            $v->init();
            $v->add('informCd', '');
            $v->add('informNm', '');
            $v->add('content', '');
            $v->add('modeFl', 'yn');
            $v->add('scmModeFl', 'yn');
            $v->add('scmDisplayFl', 'yn');

            if ($v->act($arrData, true) === false) {
                throw new Exception(implode("\n", $v->errors));
            }

            $arrBind = $this->db->get_binding(
                DBTableField::tableBuyerInform(), $arrData, 'update', null, [
                    'scmNo',
                    'informCd',
                    'groupCd',
                ]
            );

            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['informCd']);
            if ($getData['mallSno'] > DEFAULT_MALL_NUMBER) {
                $this->db->bind_param_push($arrBind['bind'], 'i', $getData['mallSno']);
                $this->db->set_update_db($tableName, $arrBind['param'], 'informCd = ? AND mallSno = ?', $arrBind['bind'], false);
            } else {
                $this->db->set_update_db($tableName, $arrBind['param'], 'informCd = ?', $arrBind['bind'], false);
            }
        } else {
            $v->init();
            $v->add('informCd', 'pattern', true, '', '/^[0-9]{6,9}$/');
            $v->add('groupCd', 'pattern', true, '', '/^[0-9]{3}$/');
            $v->add('informNm', '');
            $v->add('content', '');
            $v->add('modeFl', 'yn');
            $v->add('scmModeFl', 'yn');
            $v->add('scmDisplayFl', 'yn');
            $v->add('scmNo', '');

            if ($v->act($arrData, true) === false) {
                throw new Exception(implode("\n", $v->errors));
            }

            // 저장
            $arrBind = $this->db->get_binding(DBTableField::tableBuyerInform(), $arrData, 'insert');
            if ($getData['mallSno'] > DEFAULT_MALL_NUMBER) {
                $arrBind['bind'][0] .= 'i';
                $arrBind['bind'][] = $getData['mallSno'];
                $arrBind['param'][] = 'mallSno';
            }
            $this->db->set_insert_db($tableName, $arrBind['param'], $arrBind['bind'], 'y');
        }
    }

    public function saveGoodsInfoGlobal($getData)
    {
        // 기본 사용 여부
        gd_isset($getData['modeFl'], 'n');
        gd_isset($getData['scmNo'],Session::get('manager.scmNo'));

        // 테이블명 반환
        $getData['mallSno'] = gd_isset($getData['mallSno'], 1);
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $getData['mallSno']);

        foreach ($getData['informNm'] as $key => $value) {
            // 등록의 경우 informCd 생성
            $groupCd = $this->goodsInfo[$key];

            $modeInsert = false;
            $strSQL = "SELECT informCd FROM " . $tableName . " WHERE groupCd='" . $this->{$groupCd}[2] . "' AND mallSno='" . $getData['mallSno'] . "'";
            list($informCd) = $this->db->fetch($strSQL, 'row');
            if (empty($informCd) === true) {
                $informCd = $this->{$groupCd}[2] . '001';
                $modeInsert = true;
            }

            // 저장할 데이터
            $arrData = [
                'informCd' => $informCd,
                'groupCd'  => $this->{$groupCd}[2],
                'informNm' => $value,
                'content'  => $getData['content'][$key],
                'modeFl'   => $getData['modeFl'],
                'scmModeFl'   => 'n',
                'scmDisplayFl'   => 'n',
                'scmNo'    => $getData['scmNo'],
            ];

            $v = new Validator();

            if ($modeInsert === false) {
                $v->init();
                $v->add('informCd', '');
                $v->add('informNm', '');
                $v->add('content', '');
                $v->add('modeFl', 'yn');
                $v->add('scmModeFl', 'yn');
                $v->add('scmDisplayFl', 'yn');

                if ($v->act($arrData, true) === false) {
                    throw new Exception(implode("\n", $v->errors));
                }

                $arrBind = $this->db->get_binding(
                    DBTableField::tableBuyerInform(), $arrData, 'update', null, [
                        'scmNo',
                        'informCd',
                        'groupCd',
                    ]
                );

                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['informCd']);
                if ($getData['mallSno'] > DEFAULT_MALL_NUMBER) {
                    $this->db->bind_param_push($arrBind['bind'], 'i', $getData['mallSno']);
                    $this->db->set_update_db($tableName, $arrBind['param'], 'informCd = ? AND mallSno = ?', $arrBind['bind'], false);
                } else {
                    $this->db->set_update_db($tableName, $arrBind['param'], 'informCd = ?', $arrBind['bind'], false);
                }
            } else {
                $v->init();
                $v->add('informCd', 'pattern', true, '', '/^[0-9]{6,9}$/');
                $v->add('groupCd', 'pattern', true, '', '/^[0-9]{3}$/');
                $v->add('informNm', '');
                $v->add('content', '');
                $v->add('modeFl', 'yn');
                $v->add('scmModeFl', 'yn');
                $v->add('scmDisplayFl', 'yn');
                $v->add('scmNo', '');

                if ($v->act($arrData, true) === false) {
                    throw new Exception(implode("\n", $v->errors));
                }

                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableBuyerInform(), $arrData, 'insert');
                if ($getData['mallSno'] > DEFAULT_MALL_NUMBER) {
                    $arrBind['bind'][0] .= 'i';
                    $arrBind['bind'][] = $getData['mallSno'];
                    $arrBind['param'][] = 'mallSno';
                }
                $this->db->set_insert_db($tableName, $arrBind['param'], $arrBind['bind'], 'y');
            }
            unset($arrData);
        }
    }

    /**
     * 상품 상세 이용안내 복사
     *
     * @param integer $intSno 복사할 informCd
     */
    public function setGoodsInfoCopy($informCd)
    {
        // informCd 생성
        $groupCd = $this->goodsInfo[substr($informCd, 0, 3)];
        $modeInsert = false;
        $strSQL = 'SELECT if(max(informCd), max(informCd), ' . $this->{$groupCd}[0] . ') as newInformCd FROM ' . DB_BUYER_INFORM . ' WHERE groupCd=\'' . $this->{$groupCd}[2] . '\'';
        list($tmp) = $this->db->fetch($strSQL, 'row');
        $newInformCd = sprintf('%06d', ($tmp + 1));

        // 상품 상세 이용안내 정보 복사
        $arrField = DBTableField::setTableField(
            'tableBuyerInform', null, [
                'informCd',
                'modeFl',
                'scmNo',
            ]
        );
        $strSQL = 'INSERT INTO ' . DB_BUYER_INFORM . ' (scmNo,informCd, ' . implode(', ', $arrField) . ', modeFl, regDt) SELECT \'' . Session::get('manager.scmNo') . '\',\'' . $newInformCd . '\', ' . implode(', ', $arrField) . ', \'n\', now() FROM ' . DB_BUYER_INFORM . ' WHERE informCd = ' . $informCd;
        $this->db->query($strSQL);
    }

    /**
     * 상품 상세 이용안내 삭제
     *
     * @param $informCd
     *
     * @internal param int $intSno 삭제할 informCd
     */
    public function setGoodsInfoDelete($informCd)
    {
        // 옵션 관리 정보 삭제
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $informCd); // 추가 bind 데이터
        $this->db->set_delete_db(DB_BUYER_INFORM, 'informCd = ?', $arrBind);
    }

    /**
     * 아이템 데이터 조회
     *
     * @param string $informCd
     *
     * @param null   $column
     * @param bool   $isStripSlashes
     *
     * @return array
     * @throws Exception
     */
    public function getInformDataArray($informCd, $column = null, $isStripSlashes = true, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if (Validator::pattern('/^[0-9]{6}$/', $informCd, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), 'informCd'));
        }

        $mallBySession = \App::getInstance('session')->get(SESSION_GLOBAL_MALL);
        if ($mallBySession['sno'] > DEFAULT_MALL_NUMBER) {
            $mallSno = $mallBySession['sno'];
        }
        // 테이블명 반환
        $tableName = $this->mall->getTableName(DB_BUYER_INFORM, $mallSno);
        $arrBind = null;
        if (is_null($column)) {
            $arrField = DBTableField::setTableField('tableBuyerInform');
            $arrField[] = 'sno';
            $strSQL = "SELECT " . implode(', ', $arrField) . " FROM " . $tableName . " WHERE informCd LIKE concat(?, '%') ";
            $this->db->bind_param_push($arrBind,'s',$informCd);
        } else {
            $strSQL = "SELECT " . $column . " FROM " . $tableName . " WHERE informCd LIKE concat(?, '%') ";
            $this->db->bind_param_push($arrBind,'s',$informCd);
        }

        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $strSQL .= " AND mallSno= ? ";
            $this->db->bind_param_push($arrBind,'i',$mallSno);
        }

        $data = $this->db->query_fetch($strSQL,$arrBind);

        return $isStripSlashes ? gd_htmlspecialchars_stripslashes($data) : $data;
    }

    /**
     * 사용자 정의 이용약관 정보 및 체크박스 처리 반환 함수
     *
     * @return array
     */
    public function getAgreementWithChecked($mallSno = DEFAULT_MALL_NUMBER)
    {
        $inform = $this->getInformData(BuyerInformCode::AGREEMENT, $mallSno);

        $modeFl = $mallSno > DEFAULT_MALL_NUMBER ? 'n' : 'y';
        gd_isset($inform['modeFl'], $modeFl);
        $checked = [];
        $checked['modeFl'][$inform['modeFl']] = 'checked="checked"';

        return [
            'content' => $inform['content'],
            'informNm' => $inform['informNm'],
            'checked' => $checked,
        ];
    }

    /**
     * 사용자 정의 이용약관 정보 및 체크박스 처리 반환 함수
     *
     * @return array
     */
    public function getPrivateWithManager($mallSno = DEFAULT_MALL_NUMBER)
    {
        $mallBySession = App::getInstance('session')->get(SESSION_GLOBAL_MALL);
        if ($mallBySession['sno'] > DEFAULT_MALL_NUMBER) {
            $mallSno = $mallBySession['sno'];
        }

        $inform = $this->getInformData(BuyerInformCode::BASE_PRIVATE, $mallSno);

        //--- 개인정보 관리 책임자
        $personalInfoManager = $this->getPersonalManagerByGlobals(true, $mallSno);

        //--- 개인정보 관리 책임자 전화번호,이메일 구분자 추가, 키 값 __ 제거
        foreach ($personalInfoManager as $key => $item) {
            $personalInfoManager[str_replace("_", '', $key)] = $item;
        }

        $personalInfoManager['privatePhone'] = str_replace('-','', $personalInfoManager['privatePhone']);
        $personalInfoManager['privateEmail'] = explode('@', $personalInfoManager['privateEmail']);

        $data['personalInfoManager'] = $personalInfoManager;

        return [
            'content'             => $inform['content'],
            'informNm'             => $inform['informNm'],
            'personalInfoManager' => $personalInfoManager,
        ];
    }


    /**
     * 개인정보정보취급방침 치환코드 처리 후 반환 함수
     *
     * @return mixed
     */
    public function getPrivateWithReplaceCode($informCd, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $inform = $this->getInformData($informCd, $mallSno);

        //--- 개인정보 관리 책임자
        $personalInfoManager = $this->getPersonalManagerByGlobals(false);

        $inform['content'] = str_replace(array_keys($personalInfoManager), array_values($personalInfoManager), $inform['content']);

        return $inform;
    }

    /**
     * 개인정보 관리 책임자 치환코드에 개인정보 관리 책임자 정보를 치환하여 반환하는 함수
     *
     * @param bool $isPrefixReplace
     *
     * @return array
     */
    private function getPersonalManagerByGlobals($isPrefixReplace = true, $mallSno = DEFAULT_MALL_NUMBER)
    {
        // 해외상점일 경우 gMall reset
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            \Globals::set('gMall', gd_policy('basic.info', $mallSno));
        }

        /** @var \Bundle\Component\Design\ReplaceCode $replaceCode */
        $replaceCode = App::load('\\Component\\Design\\ReplaceCode');
        $replaceCode->initWithUnsetDiff(
            [
                '{rc_mallNm}',
                '{rc_privateNm}',
                '{rc_privatePosition}',
                '{rc_privateDepartment}',
                '{rc_privatePhone}',
                '{rc_privateEmail}',
                '{rc_companyNm}',
            ]
        );
        $defineCode = $replaceCode->getDefinedCode();
        $managerInfo = [];
        foreach ($defineCode as $key => $value) {
            if ($isPrefixReplace === true) {
                $length = strlen($key) - 5;
                $key = substr($key, 4, $length);
            }
            $managerInfo[$key] = $value['val'];
        }

        return $managerInfo;
    }

    public function getBuyerInform($data = null, $where = null, $column = '*', $dataArray = false)
    {
        \Logger::info(__METHOD__);
        $fieldTypes = DBTableField::getFieldTypes($this->tableFunctionName);
        $arrBind = [];
        $this->db->strField = $column;
        if (is_array($data) === true && is_array($where) === true && count($data) === count($where)) {
            $arrWhere = [];

            foreach ($where as $idx => $val) {
                $arrWhere[] = $val . '=?';
                $fieldType = $fieldTypes[$val];
                $this->db->bind_param_push($arrBind, $fieldType, $data[$idx]);
            }
            $this->db->strWhere = implode(' AND ', $arrWhere);
        } else {
            if ($data !== null) {
                $this->db->strWhere = $where . ' = ?';
                $this->db->bind_param_push($arrBind, $fieldTypes[$where], $data);
            } else {
                $this->db->strWhere = $where;
            }
        }

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BUYER_INFORM . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $arrBind, $dataArray);

        unset($arrBind, $where, $strSQL);

        return $data;
    }

    /**
     * @param $informCd
     * @param int $mallSno
     * @return null|string
     * @throws Exception
     */
    public function makeSelectBoxByInform($informCd, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $result = null;
        $config = gd_policy('basic.agreement', $mallSno);
        if (StringUtils::contains($informCd, BuyerInformCode::AGREEMENT)) {
            $target = 'agreement';
            $item = 'displayAgreementFl';
            $defaultInformCd = BuyerInformCode::AGREEMENT;
        } elseif (StringUtils::contains($informCd, BuyerInformCode::BASE_PRIVATE)) {
            $target = 'private';
            $item = 'displayPrivateFl';
            $defaultInformCd = BuyerInformCode::BASE_PRIVATE;
        } else {
            return $result;
        }
        if ($config[$item] == 'y') {
            \Request::post()->set('mode', 'select');
            $informData = $this->getInformData($defaultInformCd, $mallSno);
            if (empty($informData) === false) {
                $selectData = [];
                $selectValue = null;
                if (ArrayUtils::dimension($informData) < 2) {
                    $informData = [$informData];
                }
                foreach ($informData as $key => $val) {
                    if ($key == 0) {
                        $val['informNm'] .= ' (현재시행중)';
                    }
                    $link = './' . $target . '.php?code=' . $val['informCd'];
                    $selectData[$link] = $val['informNm'];
                    if ($informCd === $val['informCd']) {
                        $selectValue = $link;
                    }
                }
                $result = gd_select_box('informList', 'informList', $selectData, '', $selectValue, '', 'onchange="location.href=this.value" style="font-size:15px;"', 'tune chosen-select');
                if (\Request::isMobile()) {
                    $result = '<div class="inp_sel">' . $result . '</div>';
                }
                $result = '<br/><br/>' . $result;
            }
        }

        return $result;
    }
}
