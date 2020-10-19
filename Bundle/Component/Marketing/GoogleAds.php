<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Marketing;

use App;
use UserFilePath;

/**
 * Class GoogleAds
 * @author  Sunny <bluesunh@godo.co.kr>
 */
class GoogleAds
{
    public $config;
    public $feedDirPath;
    public $feedFilePath;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $dburl = App::load('\\Component\\Marketing\\DBUrl');
        $this->config = $dburl->getConfig('google', 'config');
        $this->isEnabled = $this->config['feedUseFl'];

        $this->feedDirPath = UserFilePath::data('dburl', 'google');
        $this->feedFilePath = UserFilePath::data('dburl', 'google', 'google.txt');
    }

    /**
     * 설정값 리턴
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 상품 피드 실행조건 체크
     * @return bool
     */
    public function enabledCheck()
    {
        return $this->isEnabled === 'n' ? false : true;
    }

    /**
     * txt 파일 생성 여부 체크 & 생성
     * @return bool
     */
    public function checkTxtFile()
    {
        if(!file_exists($this->feedFilePath)) { // google.txt 파일 없는 경우에만 최초 생성
            $result = true;
            $logger = \App::getInstance('logger')->channel('google');
            $logger->info(__CLASS__ . ', initiate start');

            ini_set('memory_limit', '-1');
            set_time_limit(RUN_TIME_LIMIT);

            $dbUrl = \App::load('Component\\Worker\\GoogleDbUrl');
            $dbUrl->setFileConfig(
                [
                    'site' => 'google',
                    'mode' => 'all',
                    'filename' => 'google.txt'
                ]
            );
            $dbUrl->run();

            if(file_exists($this->feedFilePath)) {
                @chmod($this->feedFilePath, 0777);
            } else {
                $result = false;
                $logger->error(__CLASS__ . ', Txt File creation failed.');
            }
            if(file_exists($this->feedDirPath)) {
                @chmod($this->feedDirPath, 0777);
            }

            $logger->info(__CLASS__ . ', initiate end');

            return $result;
        } else { // google.txt 파일 있는 경우(스케줄러에서 자동갱신)
            return true;
        }
    }

    /**
     * 피드 정보 리턴
     * @return array
     */
    public function getFeedFileInfo() {
        $result = [];

        if(file_exists($this->feedFilePath)) {
            $result['time'] = date('Y-m-d H:i:s', filemtime($this->feedFilePath));

            $openFile = fopen($this->feedFilePath,"r");
            $cntLine = 0;
            while(!feof($openFile)){
                fgets($openFile);
                $cntLine++;
            }
            fclose($openFile);
            $result['cntLine'] = $cntLine - 2; // 헤더 필드, 마지막 공백 핃드 제거한 총 라인수
        }

        return $result;
    }
}
