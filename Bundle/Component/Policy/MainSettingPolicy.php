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

use Component\Member\Manager;
use Exception;
use Message;

/**
 * Class MainSettingPolicy
 * @package Bundle\Component\Policy
 * @author  yjwee
 */
class MainSettingPolicy extends \Component\Policy\Policy
{
    const GROUP_CODE = 'main';
    const PRESENTATION_CODE = 'presentation';
    const ORDER_PRESENTATION_CODE = 'orderPresentation';
    const FAVORITE_MENU_CODE = 'favoriteMenu';
    const BOARD_CODE = 'cs';
    const ORDER_CODE = 'order';

    /**
     * 메인 주요현황 설정 저장 함수
     * 관리자 별로 기간이 저장된다.
     *
     * @param array $params [관리자번호, 기간]
     *
     * @throws \Exception
     */
    public function savePresentation(array $params)
    {
        $key = self::GROUP_CODE . '.' . self::PRESENTATION_CODE;
        $policyByDB = $this->getValue($key);
        $policyByDB[$params['managerSno']] = ['period' => $params['period']];
        if ($this->setValue($key, $policyByDB) === false) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        };
    }

    /**
     * 메인 주요현황 설정 조회 함수
     * 현재 로그인된 관리자 번호를 기준으로 조회한다.
     *
     * @param integer $managerSno 관리자번호
     *
     * @return int
     */
    public function getPresentation($managerSno)
    {
        $policyByDB = $this->getValue(self::GROUP_CODE . '.' . self::PRESENTATION_CODE);
        if (key_exists($managerSno, $policyByDB)) {
            return $policyByDB[$managerSno]['period'];
        } else {
            return 7;
        }
    }

    /**
     * 메인 상단 주문현황 주문상태 설정 저장 함수
     * 관리자 별로 설정이 저장된다.
     *
     * @param array $params [관리자번호, 기간, 노출항목]
     *
     * @throws \Exception
     */
    public function saveOrderPresentation(array $params)
    {
        $key = self::GROUP_CODE . '.' . self::ORDER_PRESENTATION_CODE;
        $policyByDB = $this->getValue($key);
        $policyByDB[$params['managerSno']] = [
            'period'      => $params['period'],
            'orderStatus' => $params['orderStatus'],
            'orderCountFl' => $params['orderCountFl'],
        ];
        if ($this->setValue($key, $policyByDB) === false) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        };
    }

    /**
     * 메인 상단 주문현황 설정 조회 함수
     *
     * @param integer $managerSno
     *
     * @return array [조회기간, 노출항목]
     */
    public function getOrderPresentation($managerSno)
    {
        $policyByDB = $this->getValue(self::GROUP_CODE . '.' . self::ORDER_PRESENTATION_CODE);
        if (key_exists($managerSno, $policyByDB)) {
            $policyByManager = $policyByDB[$managerSno];
            if (empty($policyByManager['period']) && $policyByManager['period'] !== '0') {
                $policyByManager['period'] = 7;
            }
            if (empty($policyByManager['orderStatus'])) {
                $policyByManager['orderStatus'] = [];
            }
            if (empty($policyByManager['orderCountFl'])) {
                $policyByManager['orderCountFl'] = 'goods';
            }

            return $policyByManager;
        } else {
            return [
                'period'      => 7,
                'orderStatus' => explode(',', 'o1,p1,g1,d1,d2,b1,e1,r1'),
                'orderCountFl' => 'goods',
            ];
        }
    }

    /**
     * 메인 상단 자주쓰는메뉴 설정 저장 함수
     * 관리자 별로 설정이 저장된다.
     *
     * @param array $params [관리자번호, 메뉴번호]
     *
     * @throws Exception
     */
    public function saveFavoriteMenu(array $params)
    {
        $key = self::GROUP_CODE . '.' . self::FAVORITE_MENU_CODE;
        $policyByDB = $this->getValue($key);
        $policyByDB[$params['managerSno']] = [
            'menus' => $params['menus'],
        ];
        if ($this->setValue($key, $policyByDB) === false) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        };
    }

    /**
     * 메인 상단 자주쓰는 메뉴 설정 조회 함수
     *
     * @param integer $managerSno 관리자번호
     *
     * @return array [메뉴번호]
     */
    public function getFavoriteMenu($managerSno)
    {
        $policyByDB = $this->getValue(self::GROUP_CODE . '.' . self::FAVORITE_MENU_CODE);
        if (key_exists($managerSno, $policyByDB)) {
            $policyByManager = $policyByDB[$managerSno];

            return $policyByManager;
        } else {
            return [];
        }
    }

    /**
     * 메인 문의/답변관리 설정 저장 함수
     *
     * @param array $params
     *
     * @throws Exception
     */
    public function saveBoardPeriod(array $params)
    {
        $key = self::GROUP_CODE . '.' . self::BOARD_CODE;
        $policyByDB = $this->getValue($key);
        $policyByDB[$params['managerSno']]['period'] = $params['period'];
        $policyByDB[$params['managerSno']]['sno'] = $params['sno'];
        $policyByDB[$params['managerSno']]['id'] = $params['id'];
        $policyByDB[$params['managerSno']]['bdKind'] = $params['bdKind'];
        $policyByDB[$params['managerSno']]['bdNm'] = $params['bdNm'];
        if ($this->setValue($key, $policyByDB) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    /**
     * 메인 문의/답변관리 설정 조회 함수
     *
     * @param $managerSno
     *
     * @return array
     */
    public function getBoard($managerSno)
    {
        $policyByDB = $this->getValue(self::GROUP_CODE . '.' . self::BOARD_CODE);
        if (key_exists($managerSno, $policyByDB)) {
            $policyByManager = $policyByDB[$managerSno];
            if (empty($policyByManager['period']) && $policyByManager['period'] !== '0') {
                $policyByManager['period'] = 6;
            }
            if (Manager::isProvider()) {
                $policyByManager = [
                    'period' => $policyByManager['period'],
                    'sno'    => explode(',', '1,2,9999'),
                    'id'     => explode(',', 'goodsreview,goodsqa,scm'),
                    'bdKind' => explode(',', 'gallery,qa,default'),
                    'bdNm'   => explode(',', '상품후기,상품문의,공급사문의'),
                ];
            }

            return $policyByManager;
        } else {
            if (Manager::isProvider()) {
                $default = [
                    'period' => 6,
                    'sno'    => explode(',', '1,2,9999'),
                    'id'     => explode(',', 'goodsreview,goodsqa,scm'),
                    'bdKind' => explode(',', 'gallery,qa,default'),
                    'bdNm'   => explode(',', '상품후기,상품문의,공급사문의'),
                ];
            } else {
                $default = [
                    'period' => 6,
                    'sno'    => explode(',', '1,2,3,9999'),
                    'id'     => explode(',', 'goodsreview,goodsqa,qa,scm'),
                    'bdKind' => explode(',', 'gallery,qa,qa,default'),
                    'bdNm'   => explode(',', '상품후기,상품문의,1:1문의,공급사문의'),
                ];
            }

            return $default;
        }
    }

    public function saveOrderMainSetting(array $params)
    {
        $key = self::GROUP_CODE . '.' . self::ORDER_CODE;
        $policyByDB = $this->getValue($key);
        $policyByDB[$params['managerSno']]['period'] = $params['period'];
        $policyByDB[$params['managerSno']]['orderStatus'] = $params['orderStatus'];
        $policyByDB[$params['managerSno']]['orderCountFl'] = gd_isset($params['orderCountFl'],'goods');
        if ($this->setValue($key, $policyByDB) != true) {
            throw new \Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
        }
    }

    public function getOrderMainSetting($managerSno)
    {
        $policyByDB = $this->getValue(self::GROUP_CODE . '.' . self::ORDER_CODE);
        if (key_exists($managerSno, $policyByDB)) {
            $policyByManager = $policyByDB[$managerSno];
            if (empty($policyByManager['period']) && $policyByManager['period'] !== '0') {
                $policyByManager['period'] = 7;
            }
            if (empty($policyByManager['orderCountFl'])) {
                $policyByManager['orderCountFl'] = 'goods';
            }
            return $policyByManager;
        } else {
            $default = [
                'period'      => 7,
                'orderStatus' => [
                    'o1',
                    'p1',
                    'g1',
                    'd1',
                    'd2',
                    's1',
                    'r1',
                    'e1',
                ],
                'orderCountFl'      => 'goods',
            ];
            if (Manager::isProvider()) {
                unset($default['orderStatus']['o1']);
                array_push($default['orderStatus'], 'b1');
            }

            return $default;
        }
    }
}
