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

namespace Bundle\Component\GoodsStatistics;

use DateTime;
use Exception;

/**
 * Class PageViewParser
 * @package Bundle\Component\GoodsStatistics
 * @author  yjwee
 */
class PageViewParser
{
    private $logDate;
    private $logFile;
    private $siteKeys;
    private $startPageViews;
    private $endPageViews;
    private $pageViews;

    /**
     * 생성자
     *
     * @param null $callDate
     */
    public function __construct($callDate = null)
    {
        if ($callDate == null) {
            // 전달받은 파라미터가 없는 경우 오늘을 기준으로 -1일된 파일을 대상으로 지정
            $date = new \DateTime();
            $date->modify('-1days');
            $this->logDate = $date->format('Y-m-d');
        } else {
            // 전달받은 파라미터가 있는 경우 파라미터 기준으로 -1일된 파일을 대상으로 지정
            $date = new \DateTime(explode(' ', $callDate)[0]);
            $date->modify('-1days');
            $this->logDate = $date->format('Y-m-d');
        }
    }


    /**
     * loadFile
     *
     * @param null $logFilePath
     *
     * @return $this
     * @throws Exception
     */
    public function loadFile($logFilePath = null)
    {
        if ($logFilePath == null) {
            $logFilePath = SYSTEM_LOG_PATH . DS . 'pageView' . DS . 'pageView-' . $this->logDate . '.log';
        }
        if (file_exists($logFilePath) == false) {
            throw new Exception(__('로그파일이 존재하지 않습니다.') . ' filePath=' . $logFilePath);
        }
        $this->logFile = file_get_contents($logFilePath);

        return $this;
    }

    public function parseLog()
    {
        $this->parseRows();
        $this->parseSiteKey();
    }

    public function parseRows()
    {
        if ($this->logFile == null) {
            throw new Exception(__('분석할 로그파일이 없습니다.'));
        }
        $rows = explode("\n", $this->logFile);
        foreach ($rows as $row) {
            $pageView = explode('|', $row);
            $siteKey = $pageView[1];
            if (empty($siteKey)) {
                continue;
            }
            $this->siteKeys[$siteKey][] = $pageView;
        }

        return $this;
    }

    public function parseSiteKey()
    {
        if ($this->siteKeys == null) {
            throw new Exception(__('분석할 정보가 없습니다.'));
        }
        foreach ($this->siteKeys as $siteKey => $pageViews) {
            $this->_parsePageView($pageViews);
        }
    }

    /**
     * @return mixed
     */
    public function getStartPageViews()
    {
        return $this->startPageViews;
    }

    /**
     * @return mixed
     */
    public function getEndPageViews()
    {
        return $this->endPageViews;
    }

    /**
     * @return mixed
     */
    public function getPageViews()
    {
        return $this->pageViews;
    }

    private function _parsePageView($pageViews)
    {
        $this->startPageViews[$pageViews[0][3]][$pageViews[0][2]]++;
        $this->endPageViews[$pageViews[0][3]][end($pageViews)[2]]++;

        $arrayObject = new \ArrayObject($pageViews);
        $it = $arrayObject->getIterator();
        while ($it->valid()) {
            $current = $it->current();
            $it->next();
            $after = $it->current();
            $currentURI = $current[2];
            if ($after == null) {
                $this->pageViews[$currentURI]['viewSeconds']++;
                $this->pageViews[$currentURI]['viewCount']++;
                $this->pageViews[$currentURI]['viewDate'] = $this->logDate;
                $this->pageViews[$currentURI]['mallSno'] = $current[3];
                break;
            }
            $afterURI = $after[2];
            if ($currentURI == $afterURI) {
                continue;
            }
            $currentDateTime = new DateTime($current[0]);
            $afterDateTime = new DateTime($after[0]);
            $second = $afterDateTime->diff($currentDateTime)->s;
            if ($second < 1) {
                $second = 1;
            }
            $this->pageViews[$currentURI]['viewSeconds'] += $second;
            $this->pageViews[$currentURI]['viewCount']++;
            $this->pageViews[$currentURI]['viewDate'] = $this->logDate;
            $this->pageViews[$currentURI]['mallSno'] = $current[3];
        }
    }

    /**
     * @return string
     */
    public function getLogDate()
    {
        return $this->logDate;
    }
}
