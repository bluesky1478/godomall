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
namespace Bundle\Component\Promotion;

use Component\AbstractComponent;
use Component\Database\DBTableField;
use Component\Storage\Storage;
use Component\Validator\Validator;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Exception;
use Framework\Utility\StringUtils;
use Message;

/**
 * Class QrCode
 * @package Bundle\Component\Promotion
 * @author  yjwee
 */
class QrCode extends \Component\AbstractComponent
{
    /** @var  \Bundle\Component\Storage\LocalStorage $storage */
    protected $storage;

    protected $qrPath;

    protected $qrNamePrefix = 'qrCode_';
    protected $qrCodeExt = '.png';

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();
        $this->storage = Storage::disk(Storage::PATH_CODE_ETC);
    }

    /**
     * QR코드 저장
     *
     * @param $requestParams
     *
     * @return mixed
     */
    public function save($requestParams)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableQrcode(), $requestParams, 'insert', array_keys($requestParams));
        $this->db->set_insert_db(DB_QRCODE, $arrBind['param'], $arrBind['bind'], 'y');

        return $this->db->insert_id();
    }

    /**
     * edit QR코드 수정
     *
     * @param $requestArray
     *
     * @throws Exception
     */
    public function edit($requestArray)
    {
        if (Validator::number($requestArray['sno'], null, null, true) === false) {
            throw new Exception(__('%s 인자가 잘못되었습니다.'), sprintf(__('%s 인자가 잘못되었습니다.'), 'QR코드 키'));
        }
        $arrBind = $this->db->get_binding(DBTableField::tableQrcode(), $requestArray, 'update', array_keys($requestArray), ['sno']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $requestArray['sno']);
        $this->db->set_update_db(DB_QRCODE, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
    }

    /**
     * delete QR코드 삭제
     *
     * @param $requestParams
     */
    public function delete($requestParams)
    {
        $this->db->set_delete_db(DB_QRCODE, 'sno IN (' . implode(',', $requestParams['chk']) . ')');
    }

    /**
     * getContent QR코드 상세조회
     *
     * @param $requestParams
     *
     * @return array
     * @throws Exception
     */
    public function getContent($requestParams)
    {
        $result = DBTableField::tableModel('tableQrcode');
        if (isset($requestParams['sno']) && !empty($requestParams['sno'])) {
            if (Validator::number($requestParams['sno'], null, null, true) === false) {
                throw new Exception(__('%s 인자가 잘못되었습니다.'), sprintf(__('%s 인자가 잘못되었습니다.'), 'QR코드 키'));
            }
            $this->db->strField = "*";
            $this->db->strWhere = "sno = ?";
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 'i', $requestParams['sno']);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_QRCODE . ' ' . implode(' ', $query);
            $result = array_merge($result, $this->db->query_fetch($strSQL, $arrBind, false));

            $this->setQrPath($requestParams['sno']);

            $result['qrCodeFilePath'] = $this->isFileExists($requestParams['sno']) ? $this->storage->getHttpPath($this->qrNamePrefix . $requestParams['sno'] . $this->qrCodeExt) : '';
        } else {
            $result['qrCodeFilePath'] = '';
        }

        // --- DB 조회 데이터 외의 정보 설정
        $result['useType'] = 'url';
        $result['N'] = '';
        $result['TEL'] = '';
        $result['EMAIL'] = '';
        $result['URL'] = '';
        $result['ADR'] = '';
        $result['contentText'] = '';

        return $result;
    }

    public function setQrPath($sno)
    {
        $this->qrPath = $this->storage->getRealPath($this->qrNamePrefix . $sno . $this->qrCodeExt);
    }

    /**
     * @return mixed
     */
    public function getQrPath()
    {
        return $this->qrPath;
    }

    public function isFileExists($sno = null)
    {
        return $this->storage->isFileExists($this->qrNamePrefix . $sno . $this->qrCodeExt);
    }

    /**
     * lists QR코드 목록조회
     *
     * @param $requestArray
     *
     * @return mixed
     */
    public function lists($requestArray)
    {
        // --- 페이지 설정
        $nowPage = isset($requestArray['page']) ? $requestArray['page'] : '1';
        $pageNum = isset($requestArray['pageNum']) ? $requestArray['pageNum'] : '10';
        $page = \App::load('\\Component\\Page\\Page', $nowPage, 0, 0, $pageNum);
        $start = $page->recode['start'];
        $limit = $page->page['list'];

        // --- 목록
        $this->db->strField = '*';
        $this->db->strOrder = 'regDt desc';
        $this->db->strLimit = '?,?';
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $start);
        $this->db->bind_param_push($arrBind, 'i', $limit);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_QRCODE . ' ' . implode(' ', $query);
        $result['data'] = $this->db->query_fetch($strSQL, $arrBind);
        //list($cnt) = $this->db->fetch('SELECT FOUND_ROWS()', 'row');

        // --- 페이지 리셋
        unset($query['left'], $query['group'], $query['order'], $query['limit']);
        $page->recode['total'] = $this->db->query_count($query, DB_QRCODE, $arrBind);
        $page->recode['amount'] = $this->db->getCount(DB_QRCODE); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());
        $result['page'] = $page;

        return $result;
    }

    /**
     * create QR코드 생성
     *
     * @param $arrData
     *
     * @return EndroidQrCode
     * @throws Exception
     */
    public function create($arrData)
    {
        if (Validator::required($arrData['sno']) === false) {
            throw new Exception(__('QR코드 일련번호는 필수 입니다.'));
        }
        if (Validator::required($arrData['qrString']) === false) {
            throw new Exception(__('QR코드 내용이 없습니다.'));
        }
        if (Validator::required($arrData['qrSize']) === false) {
            throw new Exception(__('QR코드 크기를 설정해 주세요.'));
        }
        if (Validator::required($arrData['qrVersion']) === false) {
            throw new Exception(__('QR코드 정밀도를 설정해 주세요.'));
        }
        $qrCode = new EndroidQrCode();
        $qrCode->setSize($arrData['qrSize'] * 45); // S4의 1레벨 당 45px 증가를 참고함
        $qrCode->setVersion($arrData['qrVersion']);
        $qrCode->setText($arrData['qrString']);
        $qrCode = $qrCode->render();

        return $qrCode;
    }

    /**
     * preview QR코드 이미지 URI를 반환
     *
     * @param $requestArray
     *
     * @return string
     * @throws \Exception
     */
    public function preview($requestArray)
    {
        \Logger::info(__METHOD__, $requestArray);

        if (Validator::required($requestArray['qrString']) === false) {
            throw new Exception(__('QR코드 내용이 없습니다.'));
        }
        if (Validator::required($requestArray['qrSize']) === false) {
            throw new Exception(__('QR코드 크기를 설정해 주세요.'));
        }
        if (Validator::required($requestArray['qrVersion']) === false) {
            throw new Exception(__('QR코드 정밀도를 설정해 주세요.'));
        }

        $qrCode = new EndroidQrCode();
        $qrCode->setSize($requestArray['qrSize'] * 45);
        $qrCode->setVersion($requestArray['qrVersion']);
        $qrCode->setText($requestArray['qrString']);
        $uri = $qrCode->getDataUri();
        if (StringUtils::strIsSet($uri, '') === '') {
            throw new \Exception(__('QR코드 미리보기 생성을 실패하였습니다.'));
        }

        return $uri;
    }

    /**
     * download QR코드파일 다운로드
     *
     * @param $sno
     */
    public function download($sno = null)
    {
        $this->setQrPath($sno);
        if ($sno == null || $sno == '') {
            $this->db->strOrder = 'sno DESC';
            $this->db->strLimit = '1';
            $qrCode = $this->db->getData(DB_QRCODE);
            $this->setQrPath(($qrCode['sno'] + 1));
        }

        echo $this->storage->read($this->qrPath);
    }

    /**
     * setConfig QR코드 설정 저장
     *
     * @param $requestArray
     */
    public function setConfig($requestArray)
    {
        \Logger::info(__METHOD__);
        /* @formatter:off */
        $conf = [
            'useGoods' => $requestArray['useGoods']
            ,
            'useEvent' => $requestArray['useEvent']
            ,
            'qrStyle'  => $requestArray['qrStyle'],
        ];
        /* @formatter:on */

        gd_set_policy('promotion.qrcode', $conf);
    }
}
