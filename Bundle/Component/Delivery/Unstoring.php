<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2018 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Delivery;

use Component\Policy\Policy;


class Unstoring
{

    private $_unstoringDAO;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->_unstoringDAO = \App::load('\\Component\\Delivery\\UnstoringDAO');
    }

    public function setUnstoringNoToKey($addressData)
    {
        if (isset($addressData)) {
            foreach ($addressData as $k => $v) {    // sno를 키 값으로 변경
                $unstoringNoKeyData[$v['sno']] = $v;
            }
        }

        return $unstoringNoKeyData;
    }

    function sortUnstoringInfo(&$data)
    {
        $standardAddress = [];

        if (empty($data['unstoringInfo'] == false)) {
            foreach ($data['unstoringInfo'] as $key => $value) {
                if ($value['mainFl'] != 'n') {
                    $standardAddress = $value;
                    array_splice($data['unstoringInfo'], $key, 1);
                    break;
                }
            }
            array_unshift($data['unstoringInfo'], $standardAddress);
        }

        if (empty($data['returnInfo'] == false)) {
            $standardAddress = [];
            foreach ($data['returnInfo'] as $key => $value) {
                if ($value['mainFl'] != 'n') {
                    $standardAddress = $value;
                    array_splice($data['returnInfo'], $key, 1);
                    break;
                }
            }
            array_unshift($data['returnInfo'], $standardAddress);
        }

    }

    // 출고지주소건 패치 후 저장돼있던 출고지주소 get
    public function getUnstoringInfo($data, &$unstoringNoKeyData)
    {
        foreach ($data['unstoringNoList'] as $k => $no) {
            foreach ($unstoringNoKeyData as $sno => $value) {
                if ($sno == $no) {
                    $data['unstoringInfo'][$k] = $value;
                    unset($unstoringNoKeyData[$sno]);
                    break;
                }
            }
        }

        return $data;
    }

    // 출고지주소건 패치 후 저장돼있던 반품/교환지 주소 get
    public function getReturnInfo($data, &$unstoringNoKeyData)
    {
        foreach ($data['returnNoList'] as $k => $no) {
            foreach ($unstoringNoKeyData as $sno => $value) {
                if ($sno == $no) {
                    $data['returnInfo'][$k] = $value;
                    unset($unstoringNoKeyData[$sno]);
                    break;
                }
            }
        }

        return $data;
    }

    public function getStandardAddress($addressFl, $mallFl)
    {
        return $this->_unstoringDAO->selectStandardUnstoring($addressFl, $mallFl, $mallFl);
    }

    // 적용중인 출고지 or 반품/교환지 주소 체크처리
    public function getCheckedUnstoringInfoList($data, $addressFl, $mallFl)
    {
        $globalMall = \App::getInstance('globals')->get('gGlobal');

        $globalsMallInfo = \Globals::get('gGlobal.mallList');

        foreach ($globalMall['useMallList'] as $key => $val) {
            $beforeData[$key] = gd_policy('basic.info', $key);
        }

        $mallNo = '';

        foreach ($globalsMallInfo as $mallSno => $mallInfo) {
            if ($mallInfo['domainFl'] == $mallFl) {
                $mallNo = $mallInfo['sno'];
                break;
            }
        }

        if ($addressFl == 'unstoring') {
            foreach ($beforeData[$mallNo]['unstoringNoList'] as $k => $unstoringNo) {
                foreach ($data as $key => $val) {
                    if ($unstoringNo == $val['sno']) {
                        $data[$key]['checkedNo'] = true;
                        break;
                    }
                }
            }
        } elseif ($addressFl == 'return') {
            foreach ($beforeData[$mallNo]['returnNoList'] as $k => $returnNo) {
                foreach ($data as $key => $val) {
                    if ($returnNo == $val['sno']) {
                        $data[$key]['checkedNo'] = true;
                        break;
                    }
                }
            }
        }

        return $data;

    }

    public function getKeyChangedUnstoringList($mallFl)
    {
        $addressData = $this->_unstoringDAO->selectUnstoringListToChangeKey($mallFl);

        if (isset($addressData)) {
            foreach ($addressData as $k => $v) {    // sno를 키 값으로 변경
                $unstoringNoKeyData[$v['sno']] = $v;
            }
        }

        foreach ($unstoringNoKeyData as $i => $addressInfo) {
            $unstoringNoKeyData[$i] = $this->setFilterText($addressInfo);
        }

        return gd_htmlspecialchars($unstoringNoKeyData);
    }

    public function getUnstoringInfoListBy($addressFl = null, $mallType = null, $startPage, $endPage, $sno)
    {
        if (is_array($sno) == false) {
            $sno = json_decode($sno);
        }

        $result = $this->_unstoringDAO->selectUnstoringListBy($addressFl, $mallType, $startPage, $endPage, $sno);

        foreach($result as $k => $addressInfo) {
            $result[$k] = $this->setFilterText($addressInfo);
        }

        return gd_htmlspecialchars($result);
    }

    public function getUnstoringInfoOne($unstoringNo)
    {
        $getData = $this->_unstoringDAO->selectUnstoringInfoOne($unstoringNo);
        $getData = $this->setFilterText($getData);

        return gd_htmlspecialchars($getData);
    }

    public function setFilterText($arrData)
    {
        $addressKeys = ['unstoringNm', 'unstoringAddress', 'unstoringAddressSub', 'mainContact', 'additionalContact'];

        foreach($addressKeys as $i => $key) {
            $arrData[$key] = str_replace('\\"', '"', $arrData[$key]);
            $arrData[$key] = str_replace("\\'", "'", $arrData[$key]);
            $arrData[$key] = str_replace("\\\\", "\\", $arrData[$key]);
        }

        return $arrData;
    }

    // 패치 전 일반 출고지(반품/교환지) 주소 치환 코드 설정
    public function setNormalReturnAddress(&$data, $domainFl, $mallName)
    {

        $data['returnInfo'][0]['unstoringNm'] = '반품/교환지 주소(' . $mallName . ')';
        $data['returnInfo'][0]['unstoringAddress'] = $data['returnAddress'];
        $data['returnInfo'][0]['unstoringZipcode'] = $data['returnZipcode'];
        $data['returnInfo'][0]['unstoringZonecode'] = $data['returnZonecode'];
        $data['returnInfo'][0]['unstoringAddressSub'] = $data['returnAddressSub'];
        $data['returnInfo'][0]['mainFl'] = $domainFl;
        $data['returnInfo'][0]['mallFl'] = $domainFl;
        $data['returnInfo'][0]['addressFl'] = 'return';

        $data['returnInfo'][0]['sno'] = $this->saveUnstoringInfo($data['returnInfo'][0]);
        $setReturnInfo = [
            'returnNo' => $data['returnInfo'][0]['sno'],
            'returnNoList' => array($data['returnInfo'][0]['sno']),
            'returnZonecodeList' => array($data['returnInfo'][0]['unstoringZonecode']),
            'returnZipcodeList' => array($data['returnInfo'][0]['unstoringZipcode']),
            'returnAddressList' => array($data['returnInfo'][0]['unstoringAddress']),
            'returnAddressSubList' => array($data['returnInfo'][0]['unstoringAddressSub'])
        ];

        return $setReturnInfo;
    }

    public function setNormalUnstoringAddress(&$data, $domainFl, $mallName)
    {
        $data['unstoringInfo'][0]['unstoringNm'] = '출고지 주소(' . $mallName . ')';
        $data['unstoringInfo'][0]['unstoringAddress'] = $data['unstoringAddress'];
        $data['unstoringInfo'][0]['unstoringZipcode'] = $data['unstoringZipcode'];
        $data['unstoringInfo'][0]['unstoringZonecode'] = $data['unstoringZonecode'];
        $data['unstoringInfo'][0]['unstoringAddressSub'] = $data['unstoringAddressSub'];
        $data['unstoringInfo'][0]['mainFl'] = $domainFl;
        $data['unstoringInfo'][0]['mallFl'] = $domainFl;
        $data['unstoringInfo'][0]['addressFl'] = 'unstoring';
        $data['unstoringInfo'][0]['sno'] = $data['unstoringNo'] = $this->saveUnstoringInfo($data['unstoringInfo'][0]);

        $setUnstoringInfo = [
            'unstoringNo' => $data['unstoringInfo'][0]['sno'],
            'unstoringNoList' => array($data['unstoringInfo'][0]['sno']),
            'unstoringZonecodeList' => array($data['unstoringInfo'][0]['unstoringZonecode']),
            'unstoringZipcodeList' => array($data['unstoringInfo'][0]['unstoringZipcode']),
            'unstoringAddressList' => array($data['unstoringInfo'][0]['unstoringAddress']),
            'unstoringAddressSubList' => array($data['unstoringInfo'][0]['unstoringAddressSub'])
        ];

        return $setUnstoringInfo;
    }


    public function saveUnstoringInfo($unstoringInfo)
    {
        $mode = (empty($unstoringInfo['mode'])) ? 'register' : $unstoringInfo['mode'];
        $title = $unstoringInfo['title'];

        unset($unstoringInfo['mode']);
        unset($unstoringInfo['mallDomainType']);
        unset($unstoringInfo['title']);

        $cnt = $this->_unstoringDAO->selectCountUnstoring($unstoringInfo['addressFl'], $unstoringInfo['mallFl']);

        if ($mode == 'register' && $cnt == 30) { // 주소 등록 개수 30개 제한
            throw new \Exception(__($title . ' 주소 등록은 최대 30개 까지입니다.'));
        }
        /*if ($cnt > 0) { // 기존 주소는 관리명칭이 null 값이므로
            if (Validator::required(gd_isset($unstoringInfo['unstoringNm'])) === false) {
                throw new \Exception(__('관리명칭은 필수 항목 입니다.'), 500);
            }
            if (Validator::required(gd_isset($unstoringInfo['unstoringAddress'])) === false) {
                throw new \Exception(__($title . '주소는 필수 항목 입니다.'), 500);
            }
            if ($unstoringInfo['postFl'] != 'y' && Validator::required(gd_isset($unstoringInfo['unstoringZonecode'])) === false) {
                throw new \Exception(__($title . '주소는 필수 항목 입니다.'), 500);
            }
        }*/

        // 새로운 기본 출고지 등록/수정 시 기존의 기본 출고지 취소
        if ($cnt > 0 && $unstoringInfo['mainFl'] != 'n') {
            $this->_unstoringDAO->updateMainFl($unstoringInfo['addressFl'], $unstoringInfo['mainFl']);
        }

        if ($mode == 'register') {
            // 첫 주소 등록인 경우 기본 주소로 저장
            if ($cnt == 0) {
                $unstoringInfo['mainFl'] = $unstoringInfo['mallFl'];
            }
            return $this->_unstoringDAO->insertUnstoringInfo($unstoringInfo);
        } else if ($mode == 'modify') {
            $this->_unstoringDAO->updateUnstoringInfo($unstoringInfo);
            return null;
        }

    }

    public function deleteUnstoringInfo($unstoringInfo)
    {
        unset($unstoringInfo['mode']);
        unset($unstoringInfo['type']);

        $this->_unstoringDAO->deleteUnstoring($unstoringInfo);
    }

    public function getCountAddressRow($addressFl, $mallFl)
    {
        $result = $this->_unstoringDAO->selectCountUnstoring($addressFl, $mallFl);

        return $result;
    }

}
