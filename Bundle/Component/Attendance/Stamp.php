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

namespace Bundle\Component\Attendance;

use Framework\Object\SimpleStorage;

/**
 * Class Stamp
 * @package Bundle\Component\Attendance
 * @author  yjwee
 */
class Stamp
{
    private $stampDate = 'now';

    /** @var array */
    private $history;

    /** @var SimpleStorage */
    private $cssStorage;

    /** @var  array */
    private $arrStamp;

    /**
     * @inheritDoc
     */
    public function __construct($history = null)
    {
        $this->cssStorage = new SimpleStorage();
        $this->cssStorage->set('attend', 'attend');
        $this->cssStorage->set('nextMonth', 'next-month');

        if (!is_null($history)) {
            $this->setHistory($history);
            $this->makeStamp();
        }
    }

    /**
     * setHistory
     *
     * @param $history
     */
    public function setHistory($history)
    {
        $this->history = json_decode($history)->history;
    }

    /**
     * makeStamp
     *
     */
    public function makeStamp()
    {
        try {
            if (!$this->stampDate instanceof \DateTime) {
                $this->stampDate = new \DateTime($this->stampDate);
            }
            $firstDate = clone $this->stampDate;
            $firstDate->modify('first day of'); // 조회하는 월의 1일
            $lastDate = clone $this->stampDate;
            $lastDate->modify('last day of');   // 조회하는 월의 마지막일
            $firstWeek = new \DateTime($firstDate->format('Y-m-d'));
            $firstWeek->modify('last Sunday');  // 조회하는 월 1일이 있는 주의 일요일
            $lastWeek = new \DateTime($lastDate->format('Y-m-d'));
            $lastWeek->modify('next Saturday'); // 조회하는 월 마지막일이 있는 주의 토요일

            $nextMonthCss = $this->cssStorage->get('nextMonth', 'next-month');  // 전달과 다음달의 css
            $attendCss = $this->cssStorage->get('attend', 'attend');    // 출석 체크한 css

            $firstWeekJ = $firstWeek->modify('-1 days')->format('j');
            $firstWeekT = $firstWeek->format('t');
            $this->arrStamp[] = null;
            for ($i = $firstWeekJ; $i < $firstWeekT; $i++) {
                $firstWeek->modify('+1 day');
                $this->arrStamp[] = [
                    'date'  => $firstWeek->format('Y-m-d'),
                    'day'   => $firstWeek->format('j'),
                    'class' => $nextMonthCss,
                ];
            }
            $lastDateT = $lastDate->format('t');
            $firstDate->modify('-1 days');

            for ($i = 1; $i <= $lastDateT; $i++) {
                $firstDate->modify('+1 day');
                $ymd = $firstDate->format('Y-m-d');
                $this->arrStamp[] = [
                    'date'  => $ymd,
                    'day'   => $firstDate->format('j'),
                    'class' => (is_array($this->history) && in_array($ymd, $this->history)) ? $attendCss : '',
                ];
            }
            $lastWeekJ = $lastWeek->format('j');
            $lastWeek->modify('first day of');
            $lastWeek->modify('-1 days');
            for ($i = 1; $i <= $lastWeekJ; $i++) {
                $lastWeek->modify('+1 day');
                $this->arrStamp[] = [
                    'date'  => $lastWeek->format('Y-m-d'),
                    'day'   => $lastWeek->format('j'),
                    'class' => $nextMonthCss,
                ];
            }
            $this->arrStamp = array_slice($this->arrStamp, 1, null, true);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->getArrStamp();
    }

    /**
     * setStampDate
     *
     * @param $stampDate
     */
    public function setStampDate($stampDate)
    {
        $this->stampDate = $stampDate;
    }

    /**
     * setCssClassAttend
     *
     * @param $class
     */
    public function setCssClassAttend($class)
    {
        $this->cssStorage->set('attend', $class);
    }

    /**
     * setCssClassNextMonth
     *
     * @param $class
     */
    public function setCssClassNextMonth($class)
    {
        $this->cssStorage->set('nextMonth', $class);
    }

    /**
     * @return mixed
     */
    public function getArrStamp()
    {
        return $this->arrStamp;
    }

    /**
     * countByAttend
     *
     * @return int
     */
    public function countByAttend()
    {
        return count($this->history);
    }
}
