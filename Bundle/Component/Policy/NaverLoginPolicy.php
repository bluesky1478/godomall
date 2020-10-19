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

use Component\Godo\GodoNaverServerApi;
use Framework\Object\StorageInterface;
use Session;

class NaverLoginPolicy extends \Component\Policy\Policy
{
    const KEY = 'member.naverLogin';
    const NAVER = 'naver';
    protected $currentPolicy;

    public function __construct(StorageInterface $storage = null)
    {
        parent::__construct($storage);
        $this->currentPolicy = $this->getValue(self::KEY);
    }

    public function useNaverLogin()
    {
        return $this->currentPolicy['useFl'] == 'y';
    }

    public function getCategory()
    {
        $categoryCode = [
            '001' => '건강/운동',
            '002' => '교육',
            '003' => '교통',
            '004' => '금융',
            '005' => '날씨',
            '006' => '뉴스/잡지',
            '007' => '도구',
            '008' => '도서/참고자료',
            '009' => '라이프스타일',
            '010' => '런처/폰꾸미기',
            '011' => '만화',
            '012' => '멀티미디어/동영상',
            '013' => '비즈니스',
            '014' => '사진',
            '015' => '생산성',
            '016' => '소셜네트워크',
            '017' => '쇼핑',
            '018' => '스포츠',
            '019' => '엔터테인먼트',
            '020' => '여행/지역정보',
            '021' => '음악/오디오',
            '022' => '의료',
            '023' => '커뮤니케이션',
            '024' => '게임',
            '025' => '기타',
        ];
        return $categoryCode;
    }

    public function save($data = [])
    {
        $this->currentPolicy['useFl'] = $data['useFl'];
        $this->currentPolicy['clientName'] = $data['client_name'];
        $this->currentPolicy['clientId'] = $data['client_id'];
        $this->currentPolicy['clientSecret'] = $data['client_secret'];
        $this->currentPolicy['serviceName'] = $data['service_name'];
        $this->currentPolicy['imageURL'] = $data['consumer_image_url'];
        $this->currentPolicy['serviceURL'] = $data['pcweb'];
        $this->currentPolicy['servicemURL'] = $data['mobileweb'];
        $this->currentPolicy['categoryCode'] = $data['category_code'];
        $this->currentPolicy['simpleLoginFl'] = $data['simpleLoginFl'];
        $this->currentPolicy['baseInfo'] = gd_isset($data['baseInfo'], 'y');
        $this->currentPolicy['additionalInfo'] = gd_isset($data['additionalInfo'], 'n');
        $this->currentPolicy['supplementInfo'] = gd_isset($data['supplementInfo'], 'n');

        return $this->setValue(self::KEY, $this->currentPolicy);
    }

    public function useSave($data = [])
    {
        $this->currentPolicy['useFl'] = $data['useFl'];
        return $this->setValue(self::KEY, $this->currentPolicy);
    }

    protected function isAllowImageExtension($filename)
    {
        $allowUploadExtension = [
            'gif', 'jpg', 'png'
        ];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowUploadExtension) === false) {
            return false;
        }

        return true;
    }
}
