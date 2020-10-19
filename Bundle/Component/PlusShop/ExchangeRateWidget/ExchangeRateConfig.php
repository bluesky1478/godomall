<?php

namespace Bundle\Component\PlusShop\ExchangeRateWidget;

use Bundle\Component\Storage\Storage;
use UserFilePath;

/**
 * 환율계산 위젯 설정
 */
class ExchangeRateConfig
{
    const IMAGE_PATH = 'plusshop/nhngodo/exchange_rate_widget_free/skins/images/custom';
    const SKIN_PATH = 'plusshop/nhngodo/exchange_rate_widget_free/skins/default';
    const MOBILE_SKIN_PATH = 'plusshop/nhngodo/exchange_rate_widget_free/skins/m.default';

    /**
     * 설정 저장
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function save($data)
    {
        if ($data['widget_icon_type'] == 'self_icon') {
            $path = $this->getImagePath();
            $iconPc = glob("$path/self_icon_pc.*");
            $iconMb = glob("$path/self_icon_mb.*");
            $hasIconPc = count($iconPc) == 1;
            $hasIconMb = count($iconMb) == 1;
            $format = ['gif', 'png', 'jpg', 'jpeg'];

            if ($data['self_icon_pc']) {
                $typePc = explode('/', $data['self_icon_pc']['type'])[1];
                if (!in_array($typePc, $format) || intval($data['self_icon_pc']['size']) > 1024 * 1024 * 5) {
                    $data['widget_icon_type'] = 'basic_icon';
                }
            }
            if ($data['self_icon_mb']) {
                $typeMb = explode('/', $data['self_icon_pc']['type'])[1];
                if (!in_array($typeMb, $format) || intval($data['self_icon_mb']['size']) > 1024 * 1024 * 5) {
                    $data['widget_icon_type'] = 'basic_icon';
                }
            }

            if ($data['widget_icon_use_both'] == 'true') {
                if (empty($data['self_icon_pc']) && !$hasIconPc) {
                    $data['widget_icon_type'] = 'basic_icon';
                }
            } else {
                if (empty($data['self_icon_pc']) && !$hasIconPc || empty($data['self_icon_mb']) && !$hasIconMb) {
                    $data['widget_icon_type'] = 'basic_icon';
                    $data['widget_icon_use_both'] = 'true';
                }
            }

            if ($data['self_icon_pc'] || $data['self_icon_mb']) {
                $resultImage = $this->saveImages($data['self_icon_pc'], $data['self_icon_mb']);

                if (!$resultImage) {
                    return false;
                }
            }
        }

        $exchangeRateDao = \App::load('\\Component\\PlusShop\\ExchangeRateWidget\\ExchangeRateDao');

        $appData = $exchangeRateDao->getAppData();
        $appData['widget_display'] = ($data['widget_display'] == 'true');
        $appData['base_cur_type'] = $data['base_cur_type'];
        $appData['exchange_cur_type'] = $data['exchange_cur_type'];
        $appData['widget_type'] = $data['widget_type'];
        $appData['widget_icon_type'] = $data['widget_icon_type'];
        $appData['widget_icon_use_both'] = $data['widget_icon_use_both'];

        $exchangeRateDao->update($appData);

        return true;
    }

    /**
     * 아이콘 이미지 업로드
     *
     * @param array $selfIconPc
     * @param array $selfIconMb
     * @return bool
     * @throws \Exception
     */
    private function saveImages($selfIconPc, $selfIconMb)
    {
        $files = [];
        if ($selfIconPc) {
            $files = array_merge($files, $this->getIconPc());
        }
        if ($selfIconMb) {
            $files = array_merge($files, $this->getIconMb());
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $resultPc = true;
        $resultMb = true;

        if ($selfIconPc) {
            $typePc = explode('/', $selfIconPc['type'])[1];
            $resultPc = Storage::disk(Storage::PATH_CODE_DEFAULT, 'local')->upload(
                $selfIconPc['tmp_name'], self::IMAGE_PATH . "/self_icon_pc.$typePc");
        }
        if ($selfIconMb) {
            $typeMb = explode('/', $selfIconMb['type'])[1];
            $resultMb = Storage::disk(Storage::PATH_CODE_DEFAULT, 'local')->upload(
                $selfIconMb['tmp_name'], self::IMAGE_PATH . "/self_icon_mb.$typeMb");
        }

        return $resultPc !== false && $resultMb !== false;
    }

    /**
     * 아이콘 저장 위치 반환
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->getPath(self::IMAGE_PATH);
    }

    public function getSkinPath()
    {
        return $this->getPath(self::SKIN_PATH);
    }

    public function getMobileSkinPath()
    {
        return $this->getPath(self::MOBILE_SKIN_PATH);
    }

    public function getImageWebPath()
    {
        return $this->getWebPath(self::IMAGE_PATH);
    }

    public function getSkinWebPath()
    {
        return $this->getWebPath(self::SKIN_PATH);
    }

    public function getSkinMobilePath()
    {
        return $this->getWebPath(self::MOBILE_SKIN_PATH);
    }

    private function getPath($path)
    {
        return UserFilePath::data(...explode('/', $path))->getRealPath();
    }

    public function getWebPath($path)
    {
        return UserFilePath::data(...explode('/', $path))->www();
    }

    /**
     * PC 아이콘 저장 위치 반환
     *
     * @return string
     */
    public function getIconPc()
    {
        return glob($this->getImagePath() . '/self_icon_pc.*')[0];
    }

    /**
     * 모바일 아이콘 저장 위치 반환
     *
     * @return mixed
     */
    public function getIconMb()
    {
        return glob($this->getImagePath() . '/self_icon_mb.*')[0];
    }
}
