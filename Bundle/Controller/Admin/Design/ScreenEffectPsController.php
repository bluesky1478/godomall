<?php

namespace Bundle\Controller\Admin\Design;


use Bundle\Component\PlusShop\ScreenEffect\ScreenEffectConfig;
use Bundle\Controller\Admin\Controller;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

class ScreenEffectPsController extends Controller
{
    private $dao;
    private $config;

    public function index()
    {
        $this->dao = \App::load('\\Component\\PlusShop\\ScreenEffect\\ScreenEffectDao');
        $this->config = \App::load('\\Component\\PlusShop\\ScreenEffect\\ScreenEffectConfig');

        $mode = Request::post()->get('mode');
        $sno = Request::post()->get('sno');

        if ($mode == 'stop') {
            $this->stop($sno);
        } else if ($mode == 'delete') {
            $this->delete($sno);
        } else if ($mode == 'validate') {
            $this->json([
                'result' => [
                    'count' => $this->getDuplicationCount()
                ]
            ]);
        } else if ($mode == 'upsert') {
            if ($sno) {
                $this->validate();
                $this->update($sno);
            } else {
                $this->validate();
                $this->insert();
            }
        } else {
            $this->layer(__('처리 중에 오류가 발생하였습니다.'), 'top.location.reload()');
        }
    }

    /**
     * 효과 종료
     *
     * @param array $sno
     * @throws LayerNotReloadException
     */
    private function stop($sno)
    {
        if (!$this->dao->stopEffect($sno)) {
            throw new LayerNotReloadException('처리 중에 오류가 발생하였습니다.');
        }

        $this->layer(__('종료되었습니다.'), 'top.location.reload()');
    }

    /**
     * 효과 삭제
     *
     * @param array $sno
     * @throws LayerNotReloadException
     */
    private function delete($sno)
    {
        if (!$this->config->delete($sno)) {
            throw new LayerNotReloadException('처리 중에 오류가 발생하였습니다.');
        }

        $this->layer(__('삭제되었습니다.'), 'top.location.reload()');
    }

    /**
     * 효과 수정
     *
     * @param int $sno
     */
    private function update($sno)
    {
        $data = $this->generateData();
        $this->config->update($sno, $data);
        $this->layer(__('저장되었습니다.'), 'top.location.href="screen_effect_list.php"');
    }

    /**
     * 효과 추가
     */
    private function insert()
    {
        $data = $this->generateData();
        $this->config->insert($data);
        $this->layer(__('저장되었습니다.'), 'top.location.href="screen_effect_list.php"');

    }

    private function generateData()
    {
        $effectLimited = intval(Request::post()->get('effect_limited'));
        $effectStartDate = null;
        $effectStartTime = null;
        $effectEndDate = null;
        $effectEndTime = null;

        if ($effectLimited == 1) {
            list($effectStartDate, $effectStartTime, $effectEndDate, $effectEndTime) = $this->sanitizeDate();
        }

        $data = [
            'effectName' => htmlentities(trim(Request::post()->get('effect_name'))),
            'effectLimited' => $effectLimited,
            'effectStartDate' => $effectStartDate,
            'effectStartTime' => $effectStartTime,
            'effectEndDate' => $effectEndDate,
            'effectEndTime' => $effectEndTime,
            'effectType' => intval(Request::post()->get('effect_type')),
            'effectTypeTwinkle' => Request::post()->get('effect_type_twinkle') !== null,
            'imageType' => Request::post()->get('image_type'),
            'effectImage' => Request::post()->get('effect_image'),
            'customImageFile' => Request::files()->get('effect_image_custom'),
            'effectSpeed' => intval(Request::post()->get('effect_speed')),
            'effectAmount' => intval(Request::post()->get('effect_amount')),
            'effectOpacity' => intval(Request::post()->get('effect_opacity')),
            'adminId' => \Session::get('manager.managerId')
        ];

        return $data;
    }

    private function sanitizeDate()
    {
        $effectStartDate = Request::post()->get('effect_start_date');
        $effectStartTime = Request::post()->get('effect_start_time');
        $effectEndDate = Request::post()->get('effect_end_date');
        $effectEndTime = Request::post()->get('effect_end_time');
        $effectStartDate = date('Y-m-d', strtotime("$effectStartDate $effectStartTime"));
        $effectStartTime = date('H:i', strtotime("$effectStartDate $effectStartTime"));
        $effectEndDate = date('Y-m-d', strtotime("$effectEndDate $effectEndTime"));
        $effectEndTime = date('H:i', strtotime("$effectEndDate $effectEndTime"));

        return [$effectStartDate, $effectStartTime, $effectEndDate, $effectEndTime];
    }

    /**
     * Validation checks for this request
     *
     * @throws LayerNotReloadException
     */
    private function validate()
    {
        $effectLimited = intval(Request::post()->get('effect_limited'));
        if ($effectLimited == 1) {
            $effectStartDate = Request::post()->get('effect_start_date');
            $effectStartTime = Request::post()->get('effect_start_time');
            $effectEndDate = Request::post()->get('effect_end_date');
            $effectEndTime = Request::post()->get('effect_end_time');

            if (!preg_match('/^[\d]{4}-[\d]{1,2}-[\d]{1,2}$/', $effectStartDate) ||
                !preg_match('/^[\d]{4}-[\d]{1,2}-[\d]{1,2}$/', $effectEndDate) ||
                !preg_match('/^[\d]{1,2}:[\d]{1,2}$/', $effectStartTime) ||
                !preg_match('/^[\d]{1,2}:[\d]{1,2}$/', $effectEndTime)) {
                throw new LayerNotReloadException('적용기간을 정확히 입력해주세요.');
            }
        }

        $imageType = Request::post()->get('image_type');
        if ($imageType == ScreenEffectConfig::IMAGE_TYPE_CUSTOM) {
            $image = Request::files()->get('effect_image_custom');

            if ($image['tmp_name'] && (
                $image['size'] > 1024 * 100 ||
                !preg_match_all('/^image\/([a-z]+)/', $image['type'], $matches) ||
                !in_array($matches[1][0], ['jpg', 'jpeg', 'gif', 'png']))
            ) {
                throw new LayerNotReloadException('100KB 이하의 JPG, GIF, PNG 확장자의 이미지 파일만 등록 가능합니다.');
            }
        }

//        if ($this->getDuplicationCount() >= 10) {
//            throw new LayerNotReloadException('적용기간 중복 개수 초과로 선택하신 기간에는 등록하실 수 없습니다.');
//        }
    }

    /**
     * 효과 적용기간 중복 카운트
     *
     * @return int
     */
    private function getDuplicationCount()
    {
        return $this->dao->getTotalCount();
    }
}
