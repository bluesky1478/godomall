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

namespace Bundle\Component\Policy\Storage;

use App;
use Component\Database\DBTableField;
use Component\Policy\Storage\StorageInterface;
use Exception;
use Framework\Database\DB;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use LogHandler;

/**
 * @author Seungwoo Yuk <xtac@godo.co.kr>
 */

/**
 * Class DatabaseStorage
 *
 * case1. 상점마다 정책이 다른 경우
 * -> 기준몰은 es_config, 그 외에는 es_configGlobal 을 사용한다.
 * ex) INSERT INTO `es_configGlobal` (`groupCode`, `code`, `data`, `mallSno`, `shareFl`)
 *     VALUES ('member', 'join', '{"email": {"use":"y"}, "phone": {"use":"y"}}', 2, 'n');
 *
 * case2. 기준몰 설정 공통사용인 경우
 * -> es_configGlobal 테이블에서 각 상점마다 shareFl 을 y 로 설정하면 기준몰 설정을 반환한다.
 * ex) INSERT INTO `es_configGlobal` (`groupCode`, `code`, `data`, `mallSno`, `shareFl`)
 *     VALUES ('member', 'join', '{}', 2, 'y');
 *
 * case3. 기준몰을 제외한 상점의 설정이 없는경우
 * -> 빈배열을 반환한다.
 *
 * case4. 기준몰 설정을 따르지만 특정 항목만 상점마다 정책이 다른 경우
 * -> es_configGlobal 조회 후 shareFl 이 y 일 경우 기준몰 정보에 각 상점 정보를 덮어쓴다. (존재하는 데이터만 덮어씌우는 형식)
 * ex) 기준몰 데이터 '{"email": {"use":"y"}, "phone": {"use":"y"}}', 추가상점 '{"phone": {"use":"n"}}' 의 경우
 * 반환하는 값은 '{"email": {"use":"y"}, "phone": {"use":"n"}}' 이 된다.
 *
 * @package Bundle\GlobalComponent\Policy\Storage
 * @author  Seungwoo Yuk <xtac@godo.co.kr>
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class DatabaseStorage implements StorageInterface
{
    /** @var \Framework\Log\Logger $logger 로그 클래스 */
    protected $logger;
    /** @var integer $mallSno 상점 번호 */
    protected $mallSno;
    /** @var boolean $shareFl 기준몰 정책을 공유하는지 여부 */
    protected $shareFl = false;
    /** @var array $standardConfig 기준몰 정책 */
    protected $standardConfig = [];
    /** @var array $config 상점별 정책 */
    protected $config = [];
    /** @var string $configName 정책이름 */
    protected $configName;
    /** @var string $storageKey 정책 임시저장소 키값 */
    protected $storageKey;

    /**
     * @var \Framework\Database\DBTool $db
     */
    protected $db;

    /**
     *
     */
    protected $storage = [];

    /**
     * @param DB $db
     */
    public function __construct(DB $db)
    {
        $this->db = $db;
        $this->logger = App::getInstance('logger');
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($name, $value, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if ($mallSno == DEFAULT_MALL_NUMBER) {
            return $this->setDefaultValue($name, $value);
        } else {
            return $this->setGlobalValue($name, $value, $mallSno);
        }
    }

    /**
     * cofnig 테이블 정책 저장함수
     *
     * @param $name
     * @param $value
     *
     * @return bool
     */
    public function setDefaultValue($name, $value)
    {
        $arName = explode('.', $name);
        $fieldType = DBTableField::getFieldTypes('tableConfig');

        $strSQL = 'SELECT data FROM ' . DB_CONFIG . ' WHERE groupCode = ? AND code = ?';
        $this->db->bind_param_push($arrBind, $fieldType['groupCode'], $arName[0]);
        $this->db->bind_param_push($arrBind, $fieldType['code'], $arName[1]);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if (empty($getData['data']) === false || $this->db->num_rows() > 0) {
            $dbSQL = 'update';
            $length = count($arName);
            $json = json_decode($getData['data'], true);
            $tmp = &$json;

            for ($i = 2; $i < $length; $i++) {
                if (is_array($tmp) && array_key_exists($arName[$i], $tmp)) {
                    $tmp = &$tmp[$arName[$i]];
                } else {
                    $tmp = [];
                    $tmp[$arName[$i]] = '';
                    $tmp = &$tmp[$arName[$i]];
                }
            }

            $arrVal = &$tmp;
            $arrVal = $value;
            $json = gd_htmlspecialchars_decode($json);
        } else {
            $dbSQL = 'insert';
            $json = $value;
        }

        if (is_array($json)) {
            $json = gd_htmlspecialchars($json);
        }

        $arrData['data'] = json_encode($json, JSON_UNESCAPED_UNICODE);
        if ($dbSQL == 'insert') {
            $arrData['groupCode'] = $arName[0];
            $arrData['code'] = $arName[1];
            $arrBind = $this->db->get_binding(DBTableField::tableConfig(), $arrData, $dbSQL);
            $this->db->set_insert_db(DB_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
        } else {
            if ($dbSQL == 'update') {
                $arrBind = $this->db->get_binding(DBTableField::tableConfig(), $arrData, $dbSQL, ['data']);

                $this->db->bind_param_push($arrBind['bind'], $fieldType['groupCode'], $arName[0]);
                $this->db->bind_param_push($arrBind['bind'], $fieldType['code'], $arName[1]);
                //                debug($arrBind);
                //                debug($arrData);
                //    exit;
                $this->db->set_update_db(DB_CONFIG, $arrBind['param'], 'groupCode = ? AND code = ?', $arrBind['bind']);
            }
        }
        unset($arrBind);

        // 저장소에 데이터가 없는 경우만 저장 처리
        if (array_key_exists($name, $this->storage)) {
            $this->storage[$name] = $json;
        }

        return true;
    }

    /**
     * 글로벌 정책 저장 함수
     *
     * @param     $name
     * @param     $value
     * @param int $mallSno
     *
     * @return bool
     * @throws Exception
     */
    public function setGlobalValue($name, $value, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $this->mallSno = $mallSno;
        $this->validateMallSno();
        $this->storageKey = $name;
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $this->storageKey = $name . '' . $mallSno;
        }
        $arName = explode('.', $name);
        $fieldType = DBTableField::getFieldTypes('tableConfigGlobal');
        $getData = $this->selectConfig($mallSno, $fieldType, $arName);
        $this->logger->info(__METHOD__, $getData);

        if (empty($getData['data']) === false || $this->db->num_rows() > 0) {
            $dbSQL = 'update';
            $length = count($arName);
            $json = json_decode($getData['data'], true);
            $tmp = &$json;

            for ($i = 2; $i < $length; $i++) {
                if (is_array($tmp) && array_key_exists($arName[$i], $tmp)) {
                    $tmp = &$tmp[$arName[$i]];
                } else {
                    $tmp = [];
                    $tmp[$arName[$i]] = '';
                    $tmp = &$tmp[$arName[$i]];
                }
            }

            $arrVal = &$tmp;
            $arrVal = $value;
            $json = StringUtils::htmlSpecialCharsDecode($json);
        } else {
            $dbSQL = 'insert';
            $json = $value;
        }

        if (is_array($json)) {
            $json = StringUtils::htmlSpecialChars($json);
        }

        $arrData['data'] = json_encode($json, JSON_UNESCAPED_UNICODE);
        $shareFl = in_array($name, \Component\Mall\Mall::GLOBAL_MALL_SHARE) === true ? 'y' : 'n';
        if ($dbSQL == 'insert') {
            $this->insertConfig($arrData, $arName, $mallSno, $shareFl);
        } elseif ($dbSQL == 'update') {
            $this->updateConfig($arrData, $fieldType, $arName, $mallSno, $shareFl);
        }

        // 캐시된 데이터가 존재하는 경우 갱신
        if (array_key_exists($this->storageKey, $this->storage)) {
            $this->storage[$this->storageKey] = $json;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($name, $mallSno = null)
    {
        if ($mallSno === null) {
            $mallSno = DEFAULT_MALL_NUMBER;
        }
        if ($mallSno == DEFAULT_MALL_NUMBER) {
            return $this->getDefaultValue($name);
        } else {
            return $this->getGlobalValue($name, $mallSno);
        }
    }

    public function getValueWithGlobal($name)
    {
    }

    /**
     * 기존 config 테이블 조회 함수
     *
     * @param $name
     *
     * @return string
     */
    public function getDefaultValue($name)
    {
        $logger = \App::getInstance('logger');
        // 스토리지에 DB 중복호출하지 않도록 별도 저장 처리
        if (isset($this->storage[$name])) {
            //            $logger->debug(sprintf('Return storage. %s', $name));

            return $this->storage[$name];
        }

        $arrName = explode('.', $name);
        $arrResult = [];


        if (!is_array($arrName)) {
            $logger->error(sprintf('Not found config name. %s', $name));
        }
        $length = count($arrName);

        if ($length == 1) {
            $strSQL = 'SELECT code, data FROM ' . DB_CONFIG . ' WHERE groupCode = \'' . $arrName[0] . '\'';
            $getData = $this->db->slave()->query_fetch($strSQL);

            if (empty($getData) === false) {
                foreach ($getData as $val) {
                    if (empty($val['data']) === false) {
                        $arrResult[$val['code']] = json_decode($val['data'], true);
                    }
                }
            }
        } else {
            if ($length > 1) {
                $strSQL = 'SELECT code, data FROM ' . DB_CONFIG . ' WHERE groupCode = \'' . $arrName[0] . '\' AND code = \'' . $arrName[1] . '\'';
                $getData = $this->db->slave()->query_fetch($strSQL, null, false);
                if (empty($getData['data']) === false) {
                    $json = json_decode(($getData['data']), true);
                    for ($i = 2; $i < $length; $i++) {
                        $json = $json[$arrName[$i]];
                    }

                    if (is_array($json)) {
                        $arrResult = $json;
                    } else {
                        $arrResult = $json;
                    }
                }
            }
        }

        // 저장소에 데이터가 없는 경우만 저장 처리
        if (!array_key_exists($name, $this->storage)) {
            $this->storage[$name] = $arrResult;
        }

        //        \App::getInstance('logger')->debug(sprintf('get default mall, name %s', $name), $arrResult);

        return ArrayUtils::arrayEmpty($arrResult);
    }


    /**
     * 글로벌 정책 테이블 조회 함수
     *
     * @param $params
     *
     * @return array
     */
    public function selectConfigByGroupCode($params)
    {
        $strSQL = 'SELECT code, data, mallSno, shareFl FROM ' . DB_CONFIG_GLOBAL . ' WHERE groupCode = ? AND mallSno= ?';
        $this->db->bind_param_push($binds, 's', $params['groupCode']);
        $this->db->bind_param_push($binds, 'i', $params['mallSno']);
        $getData = $this->db->query_fetch($strSQL, $binds);

        return $getData;
    }

    /**
     * 글로벌 정책 테이블 조회 함수
     *
     * @param $params
     *
     * @return array
     */
    public function selectConfigByCode($params)
    {
        $strSQL = 'SELECT code, data, mallSno, shareFl FROM ' . DB_CONFIG_GLOBAL . ' WHERE groupCode = ? AND code = ? AND mallSno = ?';
        $this->db->bind_param_push($binds, 's', $params['groupCode']);
        $this->db->bind_param_push($binds, 's', $params['code']);
        $this->db->bind_param_push($binds, 'i', $params['mallSno']);
        $getData = $this->db->slave()->query_fetch($strSQL, $binds, false);

        return $getData;
    }

    /**
     * @param int $mallSno 해외상점 번호 설정 함수
     */
    public function setMallSno($mallSno)
    {
        $this->mallSno = $mallSno;
    }

    /**
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * 기준몰 설정을 반환하는 함수
     *
     * @param $name
     *
     * @return mixed|string
     */
    protected function getStandardConfig($name)
    {
        return $this->getDefaultValue($name);
    }

    /**
     * 글로벌 정책 반환 함수
     *
     * @param     $name
     * @param int $mallSno
     *
     * @return string
     */
    public function getGlobalValue($name, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $logger = \App::getInstance('logger');
        $this->configName = $name;
        $this->storageKey = $name;
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $this->storageKey = $name . '' . $mallSno;
        }
        // 스토리지에 DB 중복호출하지 않도록 별도 저장 처리
        if (isset($this->storage[$this->storageKey])) {
            //            $logger->debug(sprintf('Return storage. %s', $name));

            return $this->storage[$this->storageKey];
        }

        $arrName = explode('.', $name);
        $arrResult = [];

        if (!is_array($arrName)) {
            $this->logger->error('환경설정 파라메터가 없음');
        }
        $length = count($arrName);
        if ($length == 1) {
            $storage = new GroupCodeStorage($this->db);
            $arrResult = $storage->getValue($name, $mallSno);
        } elseif ($length == 2) {
            $storage = new CodeStorage($this->db);
            $arrResult = $storage->getValue($name, $mallSno);
        } elseif ($length > 2) {
            $storage = new SubCodeStorage($this->db);
            $arrResult = $storage->getValue($name, $mallSno);
        }

        // 저장소에 데이터가 없는 경우만 저장 처리
        if (!array_key_exists($this->storageKey, $this->storage)) {
            $this->storage[$this->storageKey] = $arrResult;
        }
        // \App::getInstance('logger')->debug(sprintf('get mall no %d, name %s', $mallSno, $name), $arrResult);

        return ArrayUtils::arrayEmpty($arrResult);
    }


    /**
     * validateMallSno
     *
     * @throws Exception
     */
    protected function validateMallSno()
    {
        if ($this->mallSno < DEFAULT_MALL_NUMBER) {
            throw new Exception(__('상점번호는 필수입니다.'));
        }
    }

    /**
     * selectConfig
     *
     * @param $mallSno
     * @param $fieldType
     * @param $arName
     *
     * @return array
     */
    protected function selectConfig($mallSno, $fieldType, $arName)
    {
        $arrBind = [];
        $strSQL = 'SELECT data, mallSno, shareFl FROM ' . DB_CONFIG_GLOBAL . ' WHERE groupCode = ? AND code = ? AND mallSno = ?';
        $this->db->bind_param_push($arrBind, $fieldType['groupCode'], $arName[0]);
        $this->db->bind_param_push($arrBind, $fieldType['code'], $arName[1]);
        $this->db->bind_param_push($arrBind, 'i', $mallSno);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        return $getData;
    }

    /**
     * insertConfig
     *
     * @param $arrData
     * @param $arName
     * @param $mallSno
     */
    protected function insertConfig($arrData, $arName, $mallSno, $shareFl = 'n')
    {
        $this->logger->info(__METHOD__);
        $arrData['groupCode'] = $arName[0];
        $arrData['code'] = $arName[1];
        $arrData['mallSno'] = $mallSno;
        $arrBind = $this->db->get_binding(DBTableField::tableConfig(), $arrData);
        $arrBind['param'][] = 'mallSno';
        $this->db->bind_param_push($arrBind['bind'], 'i', $mallSno);
        $arrBind['param'][] = 'shareFl';
        $this->db->bind_param_push($arrBind['bind'], 's', $shareFl);
        $this->logger->debug(__METHOD__, $arrData);
        $this->db->set_insert_db(DB_CONFIG_GLOBAL, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * updateConfig
     *
     * @param $arrData
     * @param $fieldType
     * @param $arName
     * @param $mallSno
     */
    protected function updateConfig($arrData, $fieldType, $arName, $mallSno, $shareFl = 'n')
    {
        $arrBind = $this->db->get_binding(DBTableField::tableConfig(), $arrData, 'update', ['data']);
        $arrBind['param'][] = 'shareFl=?';
        $this->db->bind_param_push($arrBind['bind'], 's', $shareFl);
        $this->db->bind_param_push($arrBind['bind'], $fieldType['groupCode'], $arName[0]);
        $this->db->bind_param_push($arrBind['bind'], $fieldType['code'], $arName[1]);
        $this->db->bind_param_push($arrBind['bind'], 'i', $mallSno);
        $this->db->set_update_db(DB_CONFIG_GLOBAL, $arrBind['param'], 'groupCode = ? AND code = ? AND mallSno = ?', $arrBind['bind']);
    }
}
