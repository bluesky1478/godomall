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

/**\
 * Class GroupCodeStorage
 * @package Bundle\Component\Policy\Storage
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class GroupCodeStorage extends \Component\Policy\Storage\DatabaseStorage
{
    /**
     * @inheritdoc
     *
     */
    public function getValue($name, $mallSno = 1)
    {
        $result = [];
        $params = [
            'groupCode' => $name,
            'mallSno'   => $mallSno,
        ];
        $config = $this->selectConfigByGroupCode($params);
        $standardConfig = $this->getStandardConfig($name);

        foreach ($config as $item) {
            $code = $item['code'];
            $policy = json_decode($item['data'], true);
            $isShared = $item['shareFl'] == 'y';
            $hasPolicy = (empty($policy) === false);
            if ($isShared && !$hasPolicy) {
                $result[$code] = $standardConfig[$code];
                continue;
            }
            if ($isShared && $hasPolicy) {
                $standard = $standardConfig[$code];
                foreach ($policy as $key => $value) {
                    if (key_exists($key, $standard)) {
                        $standard[$key] = $value;
                    }
                }
                $result[$code] = $standard;
                continue;
            }
            if ($hasPolicy) {
                $result[$code] = $policy;
            }
        }

        return $result;
    }
}
