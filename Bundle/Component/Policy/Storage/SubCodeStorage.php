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

/**
 * Class SubCodeStorage
 * @package Bundle\Component\Policy\Storage
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SubCodeStorage extends \Component\Policy\Storage\DatabaseStorage
{
    /**
     * @inheritdoc
     *
     */
    public function getValue($name, $mallSno = 1)
    {
        $codes = explode('.', $name);
        $params = [
            'groupCode' => $codes[0],
            'code'      => $codes[1],
            'mallSno'   => $mallSno,
        ];
        $config = $this->selectConfigByCode($params);
        $policy = json_decode($config['data'], true);
        $policy = $this->findSubCode($codes, $policy);
        $isShared = $config['shareFl'] == 'y';
        $hasPolicy = (empty($policy) === false);

        if ($isShared && !$hasPolicy) {
            return $this->getStandardConfig($name);
        }
        if ($isShared && $hasPolicy) {
            $standard = $this->getStandardConfig($name);
            foreach ($policy as $index => $item) {
                if (key_exists($index, $standard)) {
                    $standard[$index] = $item;
                }
            }

            return $standard;
        }

        return $policy;
    }

    /**
     * 정책 세부 내용 조회
     * 정책 조회 시 정책 내의 배열 항목을 바로 조회하는 경우 사용
     *
     * @param array $codes
     * @param       $json
     *
     * @return mixed
     */
    protected function findSubCode(array $codes, $json)
    {
        array_shift($codes);
        array_shift($codes);
        if (is_array($codes)) {    // 정책 세부 내용까지 조회할 경우의 처리
            $subLength = count($codes);
            for ($i = 2; $i < $subLength; $i++) {
                $json = $json[$codes[$i]];
            }
        }

        return $json;
    }
}
