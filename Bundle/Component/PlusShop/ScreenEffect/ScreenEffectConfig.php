<?php

namespace Bundle\Component\PlusShop\ScreenEffect;


use Bundle\Component\Storage\Storage;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\File\FileHandler;
use UserFilePath;

class ScreenEffectConfig
{
    const PATH = 'plusshop/nhngodo/screen_effect_free/skins/default/images/effect';
    const IMAGE_TYPE_DEFAULT = 'default';
    const IMAGE_TYPE_CUSTOM = 'custom';

    private $dao;

    public function __construct()
    {
        $this->dao = \App::load('\\Component\\PlusShop\\ScreenEffect\\ScreenEffectDao');
    }

    public function update($sno, $params)
    {
        $data = $this->dao->getBySno($sno);

        if ($params['imageType'] == self::IMAGE_TYPE_DEFAULT) {
            $data['effect_image'] = $params['effectImage'];
        } else if ($params['imageType'] == self::IMAGE_TYPE_CUSTOM && $params['customImageFile']['tmp_name']) {
            $data['effect_image'] = 'custom_' . strtolower($data['effect_code']) . '.png';

            if (!$this->processImage($params['customImageFile'], $data['effect_image'])) {
                throw new LayerNotReloadException('이미지 업로드 중 오류가 발생했습니다.');
            }
        }

        $data['image_type'] = $params['imageType'];
        $data['effect_name'] = $params['effectName'];
        $data['effect_limited'] = $params['effectLimited'];
        $data['effect_start_date'] = $params['effectStartDate'];
        $data['effect_start_time'] = $params['effectStartTime'];
        $data['effect_end_date'] = $params['effectEndDate'];
        $data['effect_end_time'] = $params['effectEndTime'];
        $data['effect_type'] = $params['effectType'];
        $data['effect_type_twinkle'] = $params['effectTypeTwinkle'];
        $data['effect_speed'] = $params['effectSpeed'];
        $data['effect_amount'] = $params['effectAmount'];
        $data['effect_opacity'] = $params['effectOpacity'];
        $data['admin_id'] = \Session::get('manager.managerId');

        return $this->dao->updateJsonData($sno, $data);
    }

    public function insert($params)
    {
        $data = [];
        $data['image_type'] = $params['imageType'];
        $data['effect_image'] = $params['effectImage'];
        $data['effect_code'] = $this->getNewCode();

        if ($params['imageType'] == self::IMAGE_TYPE_CUSTOM && $params['customImageFile']['tmp_name']) {
            $data['effect_image'] = 'custom_' . strtolower($data['effect_code']) . '.png';

            if (!$this->processImage($params['customImageFile'], $data['effect_image'])) {
                throw new LayerNotReloadException('이미지 업로드 중 오류가 발생했습니다.');
            }
        }

        $data['effect_name'] = $params['effectName'];
        $data['effect_limited'] = $params['effectLimited'];
        $data['effect_start_date'] = $params['effectStartDate'];
        $data['effect_start_time'] = $params['effectStartTime'];
        $data['effect_end_date'] = $params['effectEndDate'];
        $data['effect_end_time'] = $params['effectEndTime'];
        $data['effect_type'] = $params['effectType'];
        $data['effect_type_twinkle'] = $params['effectTypeTwinkle'];
        $data['image_type'] = $params['imageType'];
        $data['effect_speed'] = $params['effectSpeed'];
        $data['effect_amount'] = $params['effectAmount'];
        $data['effect_opacity'] = $params['effectOpacity'];
        $data['admin_id'] = \Session::get('manager.managerId');

        return $this->dao->insert($data);
    }

    public function init()
    {
        $code = 'ABC1';

        if ($this->dao->getByCode($code)) {
            return false;
        }

        $data = [
            'admin_id' => \Session::get('manager.managerId'),
            'image_type' => self::IMAGE_TYPE_DEFAULT,
            'effect_code' => $code,
            'effect_name' => '[예시]화면 전체 눈 내림 효과',
            'effect_type' => 1,
            'effect_image' => 'snowflake_1.png',
            'effect_speed' => 3,
            'effect_amount' => 3,
            'effect_limited' => 0,
            'effect_opacity' => 100,
            'effect_end_date' => '2000-01-01',
            'effect_end_time' => '23:59',
            'effect_start_date' => '2000-01-01',
            'effect_start_time' => '00:00',
            'effect_type_twinkle' => false
        ];

        return $this->dao->insert($data);
    }

    /**
     * Delete
     *
     * @param array $arrSno
     * @return mixed
     */
    public function delete($arrSno)
    {
        $handle = new FileHandler();

        foreach ($arrSno as $sno) {
            $data = $this->dao->getBySno($sno);

            if ($data['image_type'] == self::IMAGE_TYPE_CUSTOM) {
                $image = UserFilePath::data(...explode('/', self::PATH))->getRealPath() .
                    '/' . $data['effect_image'];
                if ($handle->isExists($image)) {
                    $handle->delete($image);
                }
            }
        }

        return $this->dao->delete($arrSno);
    }

    /**
     * 이미지 업로드
     *
     * @param array $image
     * @param string $filename
     * @return string|boolean
     * @throws \Exception
     */
    private function processImage($image, $filename)
    {
        $path = self::PATH . '/' . $filename;

        return Storage::disk(Storage::PATH_CODE_DEFAULT, 'local')->upload(
            $image['tmp_name'], $path, ['width' => 40, 'quality' => 'high', 'overwrite' => true]);
    }

    private function getNewCode()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        srand((double)microtime() * 1000000);

        for ($try = 0; $try < 5; $try++) {
            $i = 0;
            $code = '' ;

            while ($i < 4) {
                $num = rand() % 33;
                $tmp = substr($chars, $num, 1);
                $code = $code . $tmp;
                $i++;
            }

            $data = $this->dao->getByCode($code);
            if (!$data) {
                return $code;
            }
        }

        throw new LayerNotReloadException('효과 등록 중 오류가 발생했습니다.');
    }
}
