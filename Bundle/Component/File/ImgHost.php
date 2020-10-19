<?php
/**
 * 이미지호스팅전환 클래스
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\File;

use Component\Validator\Validator;
use Framework\Debug\Exception\HttpException;
use Session;
use UserFilePath;
use Encryptor;

class ImgHost
{

    const ECT_INVALID_ARG = 'ImgHost.ECT_INVALID_ARG';

    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';

    protected $db;

    private $ftpConf = array();
    // FTP 정보
    private $dataFile = null;
    // dataFile 클래스 인스턴스
    public function __construct($conf)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        ob_start();
        if (is_string($conf) === true) {
            $conf = unserialize($conf);
            $conf['pass'] = Encryptor::decrypt($conf['pass'], 1);
        }

        $this->ftpConf['domain'] = array_key_exists('domain', $conf) ? $conf['domain'] : '';
        $this->ftpConf['user'][0] = array_key_exists('userid', $conf) ? $conf['userid'] : '';
        $this->ftpConf['user'][1] = array_key_exists('pass', $conf) ? $conf['pass'] : '';
        ob_end_clean();
    }

    /**
     * 이미지경로→호스팅경로 치환
     *
     * @param string $source 소스
     * @param string $deleteFl 원이미지삭제여부
     * @return string 결과소스
     */
    private function replace($source, $deleteFl)
    {
        if (is_object($this->dataFile) === false) $this->_connector();
        $split = $this->_split($source);
        for ($i = 1, $s = count($split); $i < $s; $i += 2) {
            $prev = &$split[($i - 1)];
            $self = &$split[$i];
            $next = &$split[($i + 1)];

            if (preg_match('@^http:\/\/@ix', $self))
                ;
            else {
                // godoOld 속성 제거
                $prev = $this->clearGodoOld('@(<(?:[^<])+$)@ix', $prev);
                $next = $this->clearGodoOld('@(^(?:[^>])+>)@ix', $next);
                // 이미지 전송
                $imgPath = $self;
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $imgPath) !== false && strpos($imgPath, '/data') !== false) {
                    // 경로 정의
                    $localPath = str_replace('/data', '', $imgPath);
                    $this->_setDir();
                    $hostPath = ($this->ftpConf['dirPath'] == '/' ? '' : $this->ftpConf['dirPath']) . '/' . basename($imgPath);
                    // 복사|이동
                    if ($deleteFl != 'y') {
                        $this->dataFile->setSrcFile('localData', $localPath);
                        $this->dataFile->setDestFile('hostData', $hostPath);
                        $res = $this->dataFile->copy(true);
                    }
                    else if ($deleteFl == 'y') {
                        $this->dataFile->setSrcFile('localData', $localPath);
                        $this->dataFile->setDestFile('hostData', $hostPath);
                        $res = $this->dataFile->move(true);
                    }
                    if ($res === true) {
                        $imgPath = 'http://' . $this->ftpConf['domain'] . $hostPath;
                    }
                }
                // godoOld 속성 추가
                $quot = substr($prev, -1, 1);
                if (in_array($quot, array('"', "'")) === false) $quot = '';
                $self = $imgPath . $quot . ' godoOld=' . $quot . $self;
            }
        }
        $source = implode('', $split);
        return $source;
    }

    /**
     * godoOld 속성 제거 후 리턴
     *
     * @param string $pattern 패턴
     * @param string $str 스트링
     * @return string 결과스트링
     */
    private function clearGodoOld($pattern, $str)
    {
        $res = preg_split($pattern, $str, 3, PREG_SPLIT_DELIM_CAPTURE);
        $res[1] = preg_replace('@ ?godoOld\=(?:"|\')(?:[^"|\'])*[^"|\']+(?:"|\')@i', '', $res[1]);
        $str = implode('', $res);
        return $str;
    }

    /**
     * 호스팅경로→이미지경로 치환
     *
     * @param string $source 소스
     * @param string $deleteFl 원이미지삭제여부
     * @return string 결과소스
     */
    private function restore($source, $deleteFl)
    {
        if (is_object($this->dataFile) === false) $this->_connector();
        $Ext = 'gif|jpg|jpeg|png';
        $Ptn = 'src\="(?:[^"])*[^"]+\.(?:' . $Ext . ')" godoOld\="(?:[^"])*[^"]+\.(?:' . $Ext . ')"' . "|src\='(?:[^'])*[^']+\.(?:" . $Ext . ")' godoOld\='(?:[^'])*[^']+\.(?:" . $Ext . ")'" . '|src\=\\\\"(?:[^"])*[^"]+\.(?:' . $Ext . ')\\\\" godoOld\=\\\\"(?:[^"])*[^"]+\.(?:' . $Ext . ')\\\\"' . "|src\=\\\\'(?:[^'])*[^']+\.(?:" . $Ext . ")\\\\' godoOld\=\\\\'(?:[^'])*[^']+\.(?:" . $Ext . ")\\\\'" . '|godoOld\="(?:[^"])*[^"]+\.(?:' . $Ext . ')" src\="(?:[^"])*[^"]+\.(?:' . $Ext . ')"' . "|godoOld\='(?:[^'])*[^']+\.(?:" . $Ext . ")' src\='(?:[^'])*[^']+\.(?:" . $Ext . ")'" . '|godoOld\=\\\\"(?:[^"])*[^"]+\.(?:' . $Ext . ')\\\\" src\=\\\\"(?:[^"])*[^"]+\.(?:' . $Ext . ')\\\\"' . "|godoOld\=\\\\'(?:[^'])*[^']+\.(?:" . $Ext . ")\\\\' src\=\\\\'(?:[^'])*[^']+\.(?:" . $Ext . ")\\\\'";
        $pattern = '@(' . $Ptn . ')@i';
        preg_match_all($pattern, $source, $matches); // print_r($matches);
        $search = $replace = array();
        foreach ($matches[0] as $str) {
            // godoOld 추출
            $Ptn = '(?<=godoOld\=")(?:[^"])*[^"]+\.(?:' . $Ext . ')(?=")' . "|(?<=godoOld\=')(?:[^'])*[^']+\.(?:" . $Ext . ")(?=')" . '|(?<=godoOld\=\\\\")(?:[^"])*[^"]+\.(?:' . $Ext . ')(?=\\\\")' . "|(?<=godoOld\=\\\\')(?:[^'])*[^']+\.(?:" . $Ext . ")(?=\\\\')";
            $pattern = '@' . $Ptn . '@ix';
            preg_match($pattern, $str, $matches2);
            $godoOld = $matches2[0];
            // src 추출
            $Ptn = '(?<=src\=")(?:[^"])*[^"]+\.(?:' . $Ext . ')(?=")' . "|(?<=src\=')(?:[^'])*[^']+\.(?:" . $Ext . ")(?=')" . '|(?<=src\=\\\\")(?:[^"])*[^"]+\.(?:' . $Ext . ')(?=\\\\")' . "|(?<=src\=\\\\')(?:[^'])*[^']+\.(?:" . $Ext . ")(?=\\\\')";
            $pattern = '@' . $Ptn . '@ix';
            preg_match($pattern, $str, $matches2);
            $src = $matches2[0];
            // 파일 복사|이동|삭제 처리
            if (strpos($src, 'http://' . $this->ftpConf['domain']) !== false && strpos($godoOld, '/data') !== false) {
                try {
                    $hostPath = str_replace('http://' . $this->ftpConf['domain'], '', $src);
                    $localPath = str_replace('/data', '', $godoOld);
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $godoOld) === false && $deleteFl != 'y') {
                        $this->dataFile->setSrcFile('hostData', $hostPath);
                        $this->dataFile->setDestFile('localData', $localPath);
                        $res = $this->dataFile->copy(true);
                    }
                    else if (file_exists($_SERVER['DOCUMENT_ROOT'] . $godoOld) === false && $deleteFl == 'y') {
                        $this->dataFile->setSrcFile('hostData', $hostPath);
                        $this->dataFile->setDestFile('localData', $localPath);
                        $res = $this->dataFile->move(true);
                    }
                    else if (file_exists($_SERVER['DOCUMENT_ROOT'] . $godoOld) === true && $deleteFl == 'y') {
                        $this->dataFile->delete('hostData', $hostPath);
                    }
                }
                catch (Except $e) {
                }
            }
            // 대입
            array_push($search, $str);
            array_push($replace, 'src="' . $godoOld . '"');
        }
        $source = str_replace($search, $replace, $source);
        return $source;
    }

    /**
     * 파일업로드할 디렉토리 정의
     */
    private function _setDir()
    {
        if (empty($this->ftpConf['dirPath']) === true) {
            // goods_XXXX 디렉토리 이동
            list($docRoot) = explode('.', basename($_SERVER['DOCUMENT_ROOT']));
            $docRoot = 'goods_' . $docRoot;
            // 하위 디렉토리 정의
            $ls = $this->dataFile->getList('hostData', $docRoot);
            $dirlist = array();
            if (count($ls) > 0) {
                foreach ($ls as $v) {
                    if ($v['type'] == 'dir') $dirlist[] = strip_tags($v['name']);
                }
                sort($dirlist, SORT_NUMERIC);
                $lastDir = end($dirlist);
                $last_ls = $this->dataFile->getList('hostData', $docRoot . '/' . $lastDir);
                if (count($last_ls) > 100) {
                    $lastDir = sprintf('%d', $lastDir + 1);
                }
            }
            else {
                $lastDir = sprintf('%d', 1);
            }
            $this->ftpConf['dirPath'] = '/' . $docRoot . '/' . $lastDir;
        }
    }

    /**
     * 이미지경로 현황
     *
     * @param string $source 소스
     * @return array 갯수
     */
    public function imgStatus($source)
    {
        $cnt = array('tot' => 0, 'in' => 0);
        if (is_string($source) === true)
            $split = $this->_split($source);
        else
            $split = $source;
        for ($i = 1, $s = count($split); $i < $s; $i += 2) {
            $cnt['tot']++;
            if (preg_match('@^http:\/\/@ix', $split[$i]))
                ;
            else {
                $cnt['in']++;
            }
        }
        return $cnt;
    }

    /**
     * 이미지경로 기준으로 분할
     *
     * @param string $source 소스
     * @return array 분할된 소스
     */
    private function _split($source)
    {
        $Ext = 'gif|jpg|jpeg|png';
        $Ext = '(?<=src\=")(?:[^"])*[^"]+\.(?:' . $Ext . ')(?=")' . "|(?<=src\=')(?:[^'])*[^']+\.(?:" . $Ext . ")(?=')" . '|(?<=src\=\\\\")(?:[^"])*[^"]+\.(?:' . $Ext . ')(?=\\\\")' . "|(?<=src\=\\\\')(?:[^'])*[^']+\.(?:" . $Ext . ")(?=\\\\')";
        $pattern = '@(' . $Ext . ')@ix';
        $split = preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE);
        return $split;
    }

    /**
     * FTP접속
     */
    private function _connector()
    {
        if (Validator::required($this->ftpConf['domain']) === false) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(__('%s인자가 잘못되었습니다.'), __('FTP 도메인')));
        }
        if (Validator::required($this->ftpConf['user'][0]) === false) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(__('%s인자가 잘못되었습니다.'), 'FTP ID'));
        }
        if (Validator::required($this->ftpConf['user'][1]) === false) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(__('%s인자가 잘못되었습니다.'), 'FTP Password'));
        }

        $this->dataFile = \App::load('\\Component\\File\\DataFile');
        $this->dataFile->setLocationLocal('localData', UserFilePath::data(), '', '');
        $this->dataFile->setLocationFtp('hostData', $this->ftpConf['domain'], $this->ftpConf['user'][0], $this->ftpConf['user'][1], '//', '', '', '21');
        $this->dataFile->connectFTP('hostData');
    }

    /**
     * FTP접속검증
     */
    public function ftpVerify()
    {
        $this->_connector();
        $ftpConf = array('domain' => $this->ftpConf['domain'], 'userid' => $this->ftpConf['user'][0], 'pass' => $this->ftpConf['user'][1]);
        $ftpConf['pass'] = Encryptor::encrypt($ftpConf['pass']);
        $ftpConf = serialize($ftpConf);
        Session::set('ftpConf', $ftpConf);
    }

    /**
     * 상품상세이미지 호스팅 전환
     *
     * @param string $goods 상품코드
     * @param string $deleteFl 쇼핑몰이미지삭제여부
     * @return array 처리결과(상품코드:전환이필요한이미지갯수)
     */
    public function goodsReplace($goods, $deleteFl)
    {
        if (Validator::required($goods) === false) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(__('%s인자가 잘못되었습니다.'), __('전환할 상품')));
        }
        $result = array();
        $goods = explode(',', $goods);
        foreach ($goods as $goodsNo) {
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            $strSQL = 'SELECT goodsno,goodsDescription FROM ' . DB_GOODS . ' WHERE goodsNo=?';
            $data = $this->db->query_fetch($strSQL, $arrBind, false);
            $data = gd_htmlspecialchars_stripslashes($data);
            if (trim($data['goodsDescription']) != '') {
                // 전환
                $goodsDescription = $this->replace($data['goodsDescription'], $deleteFl);
                // 업데이트
                unset($arrBind);
                $this->db->bind_param_push($arrBind, 's', $goodsDescription);
                $this->db->bind_param_push($arrBind, 'i', $goodsNo);
                $this->db->set_update_db(DB_GOODS, 'goodsDescription = ?', 'goodsNo = ?', $arrBind);
                // 결과
                $cnt = $this->imgStatus($goodsDescription);
                $result[$data['goodsno']] = intval($cnt['in']);
            }
        }
        return $result;
    }

    /**
     * 상품상세이미지 복원
     *
     * @param string $goods 상품코드
     * @param string $deleteFl 이미지호스팅삭제여부
     * @return array 처리결과(상품코드:전환이필요한이미지갯수)
     */
    public function goodsRestore($goods, $deleteFl)
    {
        if (Validator::required($goods) === false) {
            throw new Except(self::ECT_INVALID_ARG, sprintf(__('%s인자가 잘못되었습니다.'), __('복원할 상품')));
        }
        $result = array();
        $goods = explode(',', $goods);
        foreach ($goods as $goodsNo) {
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            $strSQL = 'SELECT goodsno,goodsDescription FROM ' . DB_GOODS . ' WHERE goodsNo=?';
            $data = $this->db->query_fetch($strSQL, $arrBind, false);
            $data = gd_htmlspecialchars_stripslashes($data);
            if (trim($data['goodsDescription']) != '') {
                // 복원
                $goodsDescription = $this->restore($data['goodsDescription'], $deleteFl);
                // 업데이트
                unset($arrBind);
                $this->db->bind_param_push($arrBind, 's', $goodsDescription);
                $this->db->bind_param_push($arrBind, 'i', $goodsNo);
                $this->db->set_update_db(DB_GOODS, 'goodsDescription = ?', 'goodsNo = ?', $arrBind);
                // 결과
                $cnt = $this->imgStatus($goodsDescription);
                $result[$data['goodsno']] = intval($cnt['in']);
            }
        }
        return $result;
    }
}
