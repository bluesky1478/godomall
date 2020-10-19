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

namespace Bundle\Component\Worker;


use Component\Database\DBTableField;
use Component\Storage\Storage;
use Component\Storage\StorageInterface;
use Framework\Utility\ComponentUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class AbstractDbUrl
 * @package Bundle\Component\Worker
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
abstract class AbstractDbUrl
{
    /** @var null|\Component\Category\Category */
    protected $componentCategory = null;
    /** @var null|\Component\Delivery\Delivery */
    protected $componentDelivery = null;
    /** @var null|\Component\Coupon\Coupon */
    protected $componentCoupon = null;
    /** @var null|\Component\Goods\GoodsBenefit */
    protected $componentBenefit = null;

    protected $fileConfig = [];
    protected $config = [];
    protected $policy = [];
    protected $mileage = [];
    protected $couponForEpPcList = [];
    protected $couponForEpMobileList = [];
    protected $goodsCateList = [];
    protected $goodsWheres = [];
    protected $goodsQuery = [];
    protected $categoryStorage = [];
    protected $deliveryStorage = [];
    protected $brandStorage = [];
    protected $imageStorage = [];
    protected $imagePathStorage = [];
    protected $mallUrl = '';
    protected $totalDbUrlData = 0;
    protected $totalDbUrlPage = 0;
    protected $maxCount = 499000;
    protected $limit = 10000;
    /** @var null|\Monolog\Logger */
    protected $dbUrlLogger = null;

    /**
     * DbUrl 정책 호출
     *
     */
    abstract protected function loadConfig();

    /**
     * DbUrl 사용함 상태 확인
     *
     * @return mixed
     */
    abstract protected function notUseDbUrl(): bool;

    /**
     * 상품 시작과 종료 번호를 조회
     *
     * @param array $params
     *
     * @return array
     */
    protected function selectStartWithEndGoodsNo(array $params = []): array
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        $this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        $this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT min(g.goodsNo) AS startGoodsNo,max(g.goodsNo) AS endGoodsNo FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        return $resultSet;
    }

    /**
     * selectGoods
     *
     * @param int $goodsNo
     * @param int $sgoodsNo start goods no
     *
     * @return \Generator
     */
    protected function selectGoodsGenerator(int $goodsNo, int $sgoodsNo): \Generator
    {
        if (($goodsNo - $this->limit) < $sgoodsNo) {
            $startGoodsNo = $sgoodsNo;
        } else {
            $startGoodsNo = $goodsNo - $this->limit;
        }

        $db = \App::getInstance('DB');
        $db->strField = $this->getFieldsGoods() . ",GROUP_CONCAT( gi.imageNo SEPARATOR '^|^') AS imageNo,GROUP_CONCAT( gi.imageKind SEPARATOR '^|^') AS imageKind,GROUP_CONCAT( gi.imageName SEPARATOR '^|^') AS imageName";
        $db->strWhere = implode(' AND ', $this->goodsWheres) . " AND g.goodsNo between " . $startGoodsNo . " AND " . $goodsNo;
        //        $db->strWhere = implode(' AND ', $this->goodsWheres);
        $db->strOrder = 'g.goodsNo DESC';
        $db->strGroup = "g.goodsNo";
        $db->strJoin = 'INNER JOIN es_goodsImage gi on gi.goodsNo = g.goodsNo AND  gi.imageKind IN ("magnify", "detail") AND gi.imageNo = 0';
        //        $db->strLimit = ($goodsNo * $this->offset) . ', ' . $this->offset;
        $this->goodsQuery = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($this->goodsQuery) . ' FROM ' . DB_GOODS . ' as g' . implode(' ', $this->goodsQuery);

        return $db->query_fetch_generator($strSQL);
    }

    /**
     * loadGoodsLinkCategory
     *
     * @return \Generator
     */
    protected function selectGoodsLinkCategoryGenerator(): \Generator
    {
        $db = \App::getInstance('DB');
        $strSQL = 'SELECT g.goodsNo,GROUP_CONCAT( glc.cateCd SEPARATOR "^|^") AS cateCd FROM ' . DB_GOODS . ' as g INNER JOIN ' . DB_GOODS_LINK_CATEGORY . ' glc on glc.goodsNo = g.goodsNo ' . $this->goodsQuery['where'] . ' GROUP BY g.goodsNo' . $this->goodsQuery['order'];

        return $db->query_fetch_generator($strSQL);
    }

    /**
     * countGoods
     *
     * @param array $params
     *
     * @return int
     */
    protected function countGoods(array $params = []): int
    {
        $this->goodsWheres = [];
        $this->goodsWheres[] = 'g.goodsDisplayFl = \'y\'';
        $this->goodsWheres[] = 'g.delFl = \'n\'';
        $this->goodsWheres[] = 'g.applyFl = \'y\'';
        $this->goodsWheres[] = 'NOT(g.stockFl = \'y\' AND g.totalStock = 0)';
        $this->goodsWheres[] = 'g.soldOutFl = \'n\'';
        $this->goodsWheres[] = '(UNIX_TIMESTAMP(g.goodsOpenDt) IS NULL  OR UNIX_TIMESTAMP(g.goodsOpenDt) = 0 OR UNIX_TIMESTAMP(g.goodsOpenDt) < UNIX_TIMESTAMP())';
        $wheres = $this->goodsWheres;
        if (key_exists('where', $params) && count($params['where']) > 0) {
            $wheres = array_merge($wheres, $params['where']);
        }
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_GOODS . ' as g  WHERE ' . implode(' AND ', $wheres);

        $db = \App::getInstance('DB');
        $resultSet = $db->query_fetch($strSQL, null, false);

        return StringUtils::strIsSet($resultSet['cnt'], 0);
    }

    /**
     * makeDbUrl
     *
     * @param \Generator $goodsGenerator
     * @param int        $pageNumber
     *
     * @return bool
     */
    abstract protected function makeDbUrl(\Generator $goodsGenerator, int $pageNumber): bool;

    /**
     * initDbUrlLogger
     *
     */
    protected function initDbUrlLogger()
    {
        $logger = \App::getInstance('logger');
        $resolver = \App::getInstance('user.path');
        $filename = $this->fileConfig['filename'] ?? $this->fileConfig['site'] . '_' . $this->fileConfig['mode'];
        $dbUrlResolver = $resolver->data('dburl', $this->fileConfig['site'], $filename);
        $loggerPath = $dbUrlResolver->getRealPath();
        $logger->info(__CLASS__ . ', ' . $loggerPath);
        if (is_file($loggerPath)) {
            unlink($loggerPath);
        }
        $handler = new StreamHandler($loggerPath, Logger::INFO, false, 0777);
        $formatter = new LineFormatter("%message%\r\n");
        $formatter->allowInlineLineBreaks(false);
        $formatter->ignoreEmptyContextAndExtra(false);
        $handler->setFormatter($formatter);
        $this->dbUrlLogger = new Logger('dbUrl');
        $this->dbUrlLogger->pushHandler($handler);
    }

    /**
     * DbUrl 에서 필요한 정책 호출
     *
     */
    protected function loadPolicy()
    {
        $basic = ComponentUtils::getPolicy('basic');
        $this->policy['basic']['trunc'] = $basic['trunc'];
        $this->policy['basic']['info'] = $basic['info'];
        $this->mileage['give'] = ComponentUtils::getPolicy('member.mileageGive');
        $this->mileage['trunc'] = $this->policy['basic']['trunc']['mileage'];
        $this->policy['coupon']['config'] = ComponentUtils::getPolicy('coupon.config');
        $this->policy['mobile']['config'] = ComponentUtils::getPolicy('mobile.config');
    }

    /**
     * DbUrl 에서 필요한 컴포넌트 생성
     *
     */
    protected function loadComponent()
    {
        $this->componentCategory = \App::load('Component\\Category\\Category');
        $this->componentDelivery = \App::load('Component\\Delivery\\Delivery');
        $this->componentCoupon = \App::load('Component\\Coupon\\Coupon');
        $this->componentBenefit = \App::load('Component\\Goods\\GoodsBenefit');
    }

    /**
     * 쿠폰사용설정 사용함 상태 확인
     *
     * @return bool
     */
    protected function useCoupon(): bool
    {
        return $this->policy['coupon']['config']['couponUseType'] == 'y';
    }

    /**
     * 사용가능한 ep coupon 조회
     *
     * 네이버/다음 동일 적용 , 모든 사람이 사용 가능한 쿠폰기준
     * 조건가격 없음 AND 수량 무제한 AND 회원등급 제한 없음
     */
    protected function loadCoupon()
    {
        $where[] = "couponMinOrderPrice = 0 ";
        $where[] = "couponAmountType = 'n' ";
        $where[] = "(couponApplyMemberGroup = '' or  couponApplyMemberGroup is null) ";
        $this->couponForEpPcList = $this->componentCoupon->getGoodsCouponDownListAll($where, "pc");
        unset($where);
    }

    /**
     * Db Url 실행 전 멤버 변수 초기화
     *
     */
    protected function init()
    {
        if (!(key_exists('filename', $this->fileConfig) || (key_exists('site', $this->fileConfig) && key_exists('mode', $this->fileConfig)))) {
            throw new \Exception('DbUrl file config error.', $this->fileConfig);
        }
        $this->loadPolicy();
        $this->loadComponent();
        $request = \App::getInstance('request');
        $this->mallUrl = $request->getDomainUrl();
        if ($request->isCli()) {
            $this->mallUrl = 'http://' . $this->policy['basic']['info']['mallDomain'];
        }
        $this->groupConcatMaxLength();
        $this->initDbUrlLogger();
    }

    /**
     * writeDbUrl
     *
     * @param $contents
     */
    protected function writeDbUrl($contents)
    {
        $this->dbUrlLogger->info($contents);
    }

    /**
     * groupConcatMaxLength
     *
     */
    protected function groupConcatMaxLength()
    {
        $db = \App::getInstance('DB');
        $db->query("SET @@group_concat_max_len = 10000");
    }

    /**
     * calculateLastPageNumber
     *
     * @param $start
     * @param $end
     *
     * @return float|int
     */
    protected function calculateLastPageNumber($start, $end)
    {
        return ceil(($end - $start) / $this->limit) - 1;
    }

    protected function addGoodsCategoryList(array $category)
    {
        $this->goodsCateList[$category['goodsNo']] = $category['cateCd'];
    }

    protected function getFieldsGoods()
    {
        //@formatter:off
        $arrInclude = [
            'goodsNo', 'cateCd', 'fixedPrice', 'goodsPrice', 'goodsWeight', 'goodsModelNo',
            'makerNm', 'originNm', 'deliverySno', 'goodsNm', 'goodsNmPartner', 'imageStorage', 'mileageFl',
            'mileageGoods', 'mileageGoodsUnit', 'goodsPriceString', 'imagePath', 'goodsDiscountFl', 'goodsDiscount',
            'goodsBenefitSetFl','regDt','modDt','benefitUseType','newGoodsRegFl','newGoodsDate','newGoodsDateFl','periodDiscountStart','periodDiscountEnd',
            'goodsDiscountUnit', 'onlyAdultFl', 'salesStartYmd', 'salesEndYmd', 'naverImportFlag', 'naverProductFlag',
            'naverAgeGroup', 'naverGender', 'naverAttribute', 'naverCategory', 'naverProductId', 'goodsState', 'onlyAdultFl',
            'naverTag', 'minOrderCnt', 'optionFl', 'optionName', 'eventDescription', 'salesUnit', 'goodsMustInfo', 'brandCd', 'reviewCnt',
            'plusReviewCnt', 'cremaReviewCnt', 'goodsSearchWord', 'stockFl', 'soldOutFl', 'stockCnt', 'totalStock', 'shortDescription', 'modDt'
        ];
        //@formatter:on
        return implode(',', DBTableField::setTableField('tableGoods', $arrInclude, null, 'g'));
    }

    protected function getFieldsGoodsLinkBenefit()
    {
        $goodsLinkBenefitField = array('MIN(CONCAT(DATE_FORMAT(gbl.linkPeriodStart,\'%Y%m%d%H%i%s\'),\'.\', gbl.benefitSno)) as benefitSno');
        return implode(',', $goodsLinkBenefitField);
    }

    /**
     * 상품의 상품할인가 반환
     *
     * @param array $aGoodsInfo 상품정보
     *
     * @return int 상품할인가반환
     */
    public function getGoodsDcPrice($aGoodsInfo)
    {
        // 상품 할인 기준 금액 처리
        $tmp['discountByPrice'] = $aGoodsInfo['goodsPrice'];

        // 절사 내용
        $tmp['trunc'] = $this->policy['basic']['trunc']['goods'];

        if ($aGoodsInfo['goodsDiscountUnit'] === 'percent') {
            // 상품할인금액
            $discountPercent = $aGoodsInfo['goodsDiscount'] / 100;
            $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
        } else {
            // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
            $goodsDcPrice = $aGoodsInfo['goodsDiscount'];
        }

        //할인된 금액 반환
        return $goodsDcPrice;
    }

    /**
     * addDeliveryStorage
     *
     * @param int $sno
     *
     * @throws \Exception
     */
    protected function addDeliveryStorage(int $sno)
    {
        if (empty($this->deliveryStorage[$sno])) {
            $basic = $this->componentDelivery->getSnoDeliveryBasic($sno);
            $this->deliveryStorage[$sno] = [
                'basic'  => $basic,
                'charge' => $this->componentDelivery->getSnoDeliveryCharge($sno, $basic['fixFl']),
            ];
        }
    }

    /**
     * setDeliveryPrice
     *
     * @param $getData
     *
     * @return bool|int
     */
    public function setDeliveryPrice($getData)
    {
        //배송비
        if ($getData['deliverySno']) {
            //배송비 부분 상품 상세 참조
            $this->addDeliveryStorage($getData['deliverySno']);
            $deliveryData = $this->deliveryStorage[$getData['deliverySno']];

            if ($deliveryData) {
                if ($deliveryData['basic']['deliveryConfigType'] == 'etc') {
                    return -1;
                }
                $price = 0;

                if (in_array(
                    $deliveryData['basic']['fixFl'], [
                        'price',
                        'weight',
                        'count',
                    ]
                )) {

                    if ($deliveryData['basic']['rangeRepeat'] === 'y') {
                        //범위 반복 구간이 설정되어 있을 경우
                        $conditionRange = $deliveryData['charge'][0];
                        $conditionRepeat = $deliveryData['charge'][1];

                        if ($deliveryData['basic']['fixFl'] === 'count') {
                            //수량별 배송비일 경우
                            $standardInt = 1;
                        } else {
                            //금액별, 무게별 배송비일 경우
                            $standardInt = $getData['goods' . ucfirst($deliveryData['basic']['fixFl'])];
                        }

                        if ($standardInt > 0) {
                            $price = $conditionRange['price'];

                            if ($conditionRepeat['unitEnd'] > 0) {
                                $deliveryStandardNum = $standardInt - $conditionRepeat['unitStart'];
                                if (!$deliveryStandardNum) {
                                    $deliveryStandardNum = 0;
                                }

                                if ($deliveryStandardNum >= 0) {
                                    $deliveryStandardFinal = ($deliveryStandardNum / $conditionRepeat['unitEnd']);
                                    if (!$deliveryStandardFinal) {
                                        $deliveryStandardFinal = 0;
                                    }

                                    if (preg_match('/\./', (string) $deliveryStandardFinal)) {
                                        $deliveryStandardFinal = (int) ceil($deliveryStandardFinal);
                                    } else {
                                        $deliveryStandardFinal += 1;
                                    }

                                    $price += ($deliveryStandardFinal * $conditionRepeat['price']);
                                }
                            }
                        }
                    } else {
                        //범위 반복 구간이 설정되어 있지 않을 경우

                        if ($deliveryData['basic']['fixFl'] === 'count') {
                            //수량별 배송비일 경우
                            $price = $deliveryData['charge'][0]['price'];
                        } else {
                            //금액별, 무게별 배송비일 경우

                            // 비교할 필드값 설정
                            $compareField = $getData['goods' . ucfirst($deliveryData['basic']['fixFl'])];
                            foreach ($deliveryData['charge'] as $dKey => $dVal) {
                                // 금액 or 무게가 범위에 없으면 통과
                                if (intval($dVal['unitEnd']) > 0) {
                                    if (intval($dVal['unitStart']) <= intval($compareField) && intval($dVal['unitEnd']) > intval($compareField)) {
                                        $price = $dVal['price'];
                                        break;
                                    }
                                } else {
                                    if (intval($dVal['unitStart']) <= intval($compareField)) {
                                        $price = $dVal['price'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $price = $deliveryData['charge'][0]['price'];
                }

                if ($deliveryData['basic']['fixFl'] == 'free') {
                    return 0;
                } else {
                    if ($deliveryData['basic']['collectFl'] == 'pre') {
                        return $price == 0 ? 0 : $price;
                    } else if ($deliveryData['basic']['collectFl'] == 'later') {
                        return -1;
                    } else if ($deliveryData['basic']['collectFl'] == 'both') {  //선불or착불 둘다 선택가능한 배송조건인 경우 선불로 체크
                        return $price;
                    } else {
                        return 0;
                    }
                }

            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * setGoodsCoupon
     *
     * @param      $data
     * @param null $device
     *
     * @return mixed
     */
    public function setGoodsCoupon($data, $device = null)
    {
        $couponSalePrice = 0;
        // 쿠폰 할인 금액
        if ($this->policy['coupon']['config']['couponUseType'] == 'y' && $data['goodsPrice'] > 0 && empty($data['goodsPriceString']) === true) {
            // 해당 상품의 쿠폰가
            $data['cateCdArr'] = explode(STR_DIVISION, $this->goodsCateList[$data['goodsNo']]);
            if ($device == 'mobile') {
                $couponSalePrice = $this->componentCoupon->getGoodsCouponDownListPrice($data, $this->couponForEpMobileList);
            } else {
                $couponSalePrice = $this->componentCoupon->getGoodsCouponDownListPrice($data, $this->couponForEpPcList);
            }
        }

        return StringUtils::strIsSet($couponSalePrice, 0);
    }

    /**
     * setGoodsMileage
     *
     * @param $data
     *
     * @return int
     */
    public function setGoodsMileage($data)
    {
        // 마일리지 처리

        $mileage = $this->mileage;
        $data['goodsMileageFl'] = 'y';
        // 통합 설정인 경우 마일리지 설정
        if ($data['mileageFl'] == 'c' && $mileage['give']['giveFl'] == 'y') {
            if ($mileage['give']['giveType'] == 'priceUnit') { // 금액 단위별
                $mileagePrice = floor($data['goodsPrice'] / $mileage['give']['goodsPriceUnit']);
                $mileageBasic = NumberUtils::getNumberFigure($mileagePrice * $mileage['give']['goodsMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
            } else if ($mileage['give']['giveType'] == 'cntUnit') { // 수량 단위별 (추가상품수량은 제외)
                $mileageBasic = NumberUtils::getNumberFigure(1 * $mileage['give']['cntMileage'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
            } else { // 구매금액의 %
                $mileagePercent = $mileage['give']['goods'] / 100;
                $mileageBasic = NumberUtils::getNumberFigure($data['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
            }
            // 개별 설정인 경우 마일리지 설정
        } else if ($data['mileageFl'] == 'g') {
            $mileagePercent = $data['mileageGoods'] / 100;
            // 상품 기본 마일리지 정보
            if ($data['mileageGoodsUnit'] === 'percent') {
                $mileageBasic = NumberUtils::getNumberFigure($data['goodsPrice'] * $mileagePercent, $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
            } else {
                // 정액인 경우 해당 설정된 금액으로
                $mileageBasic = NumberUtils::getNumberFigure($data['mileageGoods'], $mileage['trunc']['unitPrecision'], $mileage['trunc']['unitRound']);
            }
        } else {
            $mileageBasic = 0;
        }

        return $mileageBasic;
    }

    /**
     * getGoodsImage
     *
     * @param $imageName
     * @param $imagePath
     * @param $imageStorage
     *
     * @return bool|string
     * @throws \Exception
     */
    public function getGoodsImage($imageName, $imagePath, $imageStorage)
    {
        if ((strpos(strtolower($imageName), 'http://') !== false || strpos(strtolower($imageName), 'https://') !== false) && $imageStorage != 'url') {
            return $imageName;
        } else {
            if ($imageStorage == 'url') {
                return $imageName;
            } else {
                $hasImageStorage = key_exists($imageStorage, $this->imageStorage) && $this->imageStorage[$imageStorage] instanceof StorageInterface;
                if (!$hasImageStorage) {
                    $this->imageStorage[$imageStorage] = Storage::disk(Storage::PATH_CODE_GOODS . DS, $imageStorage);
                }
                $hasImageStoragePathReal = key_exists($imageStorage, $this->imagePathStorage['real']) && !empty($this->imagePathStorage['real'][$imageStorage]);
                $hasImageStoragePathHttp = key_exists($imageStorage, $this->imagePathStorage['http']) && !empty($this->imagePathStorage['http'][$imageStorage]);
                if (!$hasImageStoragePathReal) {
                    $this->imagePathStorage['real'][$imageStorage] = $this->imageStorage[$imageStorage]->getRealPath();
                }
                if (!$hasImageStoragePathHttp) {
                    $this->imagePathStorage['http'][$imageStorage] = $this->imageStorage[$imageStorage]->getHttpPath();
                }
                if ($imageStorage == 'local') {
                    $imageRealPath = $this->imagePathStorage['real'][$imageStorage] . $imagePath . $imageName;
                    if (is_file($imageRealPath)) {
                        return $this->mallUrl . $this->imagePathStorage['http'][$imageStorage] . $imagePath . $imageName;
                    } else {
                        return false;
                    }
                } else {
                    return $this->imagePathStorage['http'][$imageStorage] . $imagePath . $imageName;
                }
            }
        }
    }

    /**
     * 이미지 경로 반환
     *
     * @param array $goods
     *
     * @return string
     */
    protected function getGoodsImageSrc(array $goods): string
    {
        $imagesSrc = '';
        if ($goods['imageName']) {
            $names = explode(STR_DIVISION, $goods['imageName']);
            $kinds = explode(STR_DIVISION, $goods['imageKind']);

            $tmpImageNo = array_search('magnify', $kinds);
            if (empty($tmpImageNo) === true) {
                $imagesSrc = $this->getGoodsImage($names[0], $goods['imagePath'], $goods['imageStorage']);
            } else {
                $imagesSrc = $this->getGoodsImage($names[$tmpImageNo], $goods['imagePath'], $goods['imageStorage']);
            }
            unset($names, $kinds);
        }

        return $imagesSrc;
    }

    protected function selectCategoryBrand($goods)
    {
        if (empty($this->brandStorage[$goods['brandCd']]) === true && $goods['brandCd']) {
            $db = \App::getInstance('DB');
            $brandQuery = "SELECT cateNm FROM " . DB_CATEGORY_BRAND . " WHERE cateCd = '" . $goods['brandCd'] . "'";
            $brandData = $db->query_fetch($brandQuery, null, false);
            $this->brandStorage[$goods['brandCd']] = $brandData['cateNm'];
            unset($brandData, $brandQuery);
        }
    }

    /**
     * DbUrl 생성 실행
     *
     * @return bool
     * @throws \Exception
     */
    public function run()
    {
        $logger = \App::getInstance('logger');

        if (empty($this->config)) {
            $this->loadConfig();
        }
        if ($this->notUseDbUrl()) {
            $logger->info(sprintf('%s DbUrl not use.', __CLASS__), $this->config);

            return false;
        }

        $this->init();

        if ($this->useCoupon()) {
            $this->loadCoupon();
        }
        $this->runBetween();

        // $this->runLimit();

        return true;
    }

    /**
     * DbUrl 생성시 상품번호를 between 을 사용하여 처리하는 함수
     * limit 방식보다 속도가 빠르다
     *
     * @return bool
     */
    protected function runBetween()
    {
        $logger = \App::getInstance('logger');
        $goodsNo = $this->selectStartWithEndGoodsNo();
        $betweenGoodsNo = [];
        $this->getBetweenGoodsNo($goodsNo['startGoodsNo'], $goodsNo['endGoodsNo'], $betweenGoodsNo);
        if ($goodsNo['startGoodsNo'] == null || $goodsNo['endGoodsNo'] == null ){
            $logger->info(__METHOD__ . sprintf(' startGoodsNo[%s] or endGoodsNo[%s] is null skip make db url', $goodsNo['startGoodsNo'], $goodsNo['endGoodsNo']));
        } else {
            $logger->info(__METHOD__, $betweenGoodsNo);
            foreach ($betweenGoodsNo as $key => $between) {
                $cnt = $this->countGoods(['where' => ['g.goodsNo BETWEEN ' . $between['startGoodsNo'] . ' AND ' . $between['endGoodsNo'],],]);
                if ($cnt < 1) {
                    $logger->info(__METHOD__ . sprintf(', unset goodsNo[%d], [%d]. count [%d]', $between['startGoodsNo'], $between['endGoodsNo'], $cnt));
                    unset($betweenGoodsNo[$key]);
                }
            }
            $logger->info(__METHOD__ . ' after unset ', $betweenGoodsNo);
            $pageNumber = 0;
            foreach ($betweenGoodsNo as $between) {
                $startGoodsNo = $between['startGoodsNo'];
                $endGoodsNo = $between['endGoodsNo'];
                $decrease = $this->limit + 1;
                for ($i = $endGoodsNo; $i >= $startGoodsNo; $i = $i - $decrease) {
                    if ($this->greaterThanMaxCount()) {
                        break;
                    }
                    $goodsGenerator = $this->selectGoodsGenerator($i,$startGoodsNo);
                    $goodsGenerator->rewind();
                    if ($goodsGenerator->valid()) {
                        if ($this->useCoupon()) {
                            $goodsLinkCategoryGenerator = $this->selectGoodsLinkCategoryGenerator();
                            $goodsLinkCategoryGenerator->rewind();
                            while ($goodsLinkCategoryGenerator->valid()) {
                                $this->addGoodsCategoryList($goodsLinkCategoryGenerator->current());
                                $goodsLinkCategoryGenerator->next();
                            }
                        }
                        if ($this->makeDbUrl($goodsGenerator, $pageNumber) === false) {
                            return false;
                        }
                        $this->goodsCateList = [];
                    }
                    unset($goodsGenerator);
                    $pageNumber++;
                }
            }
        }
    }

    /**
     * 상품의 시작, 끝 번호를 10,000,000 번씩 나누어 카운트하기 위한 배열을 리턴하는 함수
     * 시작과 끝번호가 동일한 경우 해당 값을 배열로 반환한다.
     *
     * @param $start
     * @param $end
     * @param $between
     */
    protected function getBetweenGoodsNo($start, $end, &$between)
    {
        $limit = 10000000;
        if ($start == $end) {
            $between[] = [
                'startGoodsNo' => $start,
                'endGoodsNo'   => $end,
            ];
        } else {
            while ($start < $end) {
                $tmpEnd = $start + $limit;
                if ($tmpEnd > $end) {
                    $tmpEnd = $end;
                }
                $between[] = [
                    'startGoodsNo' => $start,
                    'endGoodsNo'   => $tmpEnd,
                ];
                $start = $tmpEnd;
            }
        }
    }

    /**
     * DBUrl 생성 시 리미트 기준으로 처리되게 하는 함수
     * 리미트 방식으로 처리할 경우
     * 상속받은 클래스에서 상품조회 부분에서 수정되어야하는 로직이 있다.
     *
     * @return bool
     */
    protected function runLimit()
    {
        $totalCount = $this->countGoods();
        $lastPageNumber = 0;
        if ($this->limit < $totalCount) {
            $lastPageNumber = ceil($totalCount / $this->limit) - 1;
        }
        $pageNumber = ($this->maxCount / $this->limit) - 1;
        if ($lastPageNumber <= $pageNumber) {
            $pageNumber = $lastPageNumber;
        }

        for ($i = 0; $i < $pageNumber; $i++) {
            if ($this->greaterThanMaxCount()) {
                break;
            }
            $goodsGenerator = $this->selectGoodsGenerator($i);
            $goodsGenerator->rewind();
            if ($goodsGenerator->valid()) {
                if ($this->useCoupon()) {
                    $goodsLinkCategoryGenerator = $this->selectGoodsLinkCategoryGenerator();
                    $goodsLinkCategoryGenerator->rewind();
                    while ($goodsLinkCategoryGenerator->valid()) {
                        $this->addGoodsCategoryList($goodsLinkCategoryGenerator->current());
                        $goodsLinkCategoryGenerator->next();
                    }
                }
                if ($this->makeDbUrl($goodsGenerator, $i) === false) {
                    return false;
                }
                $this->goodsCateList = [];
            }
            unset($goodsGenerator);
        }
    }

    /**
     * @param array $fileConfig
     */
    public function setFileConfig(array $fileConfig)
    {
        $this->fileConfig = $fileConfig;
    }

    /**
     * 현재 출력한 데이터가 최대 전송 갯수 이상인지 확인
     *
     * @return bool
     */
    protected function greaterThanMaxCount(): bool
    {
        return $this->totalDbUrlData >= $this->maxCount;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param int $maxCount
     */
    public function setMaxCount(int $maxCount)
    {
        $this->maxCount = $maxCount;
    }

    /**
     * @return int
     */
    public function getMaxCount()
    {
        return $this->maxCount;
    }

    /**
     * @param int $naverGrade 설정 등급
     * @return mixed 등급별 최대 상품수
     */
    public function gatGradeMaxLimit(int $naverGrade)
    {
        $_defineMarketing = \App::load('Component\\Marketing\\DefineMarketing');
        $gradeMaxCount = $_defineMarketing->getNaverGradeMaxCount();
        $gradeSafetyCount = $_defineMarketing->getNaverGradeSafetyCount();

        $naverMaxCount = $gradeMaxCount[$naverGrade] - $gradeSafetyCount[$naverGrade];

        return $naverMaxCount;
    }


}
