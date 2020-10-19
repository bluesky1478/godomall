<ul class="nav nav-tabs mgb20 benefit-nav">
    <li class="active" data-benefit="mileage"><a class="hand">마일리지</a></li>
    <li data-benefit="goods-discount"><a class="hand">상품할인</a></li>
    <li data-benefit="benefit-except"><a class="hand">할인/적립 제외 혜택</a></li>
</ul>

<table class="table table-rows benefit-table mileage">
    <colgroup>
        <col class="width-xs"/>
        <col/>
    </colgroup>
    <tr>
        <th>지급방법 선택</th>
        <td><?php echo $arrMileageFl[$data['mileageFl']]; ?></td>
    </tr>
    <tr>
        <th>대상 선택</th>
        <td>
            <?php
                $mileageGroup = gd_isset($data['mileageGroup'], 'all');
                echo $arrMileageGroup[$mileageGroup];
                if ($data['mileageFl'] == 'c' && $mileageGroup == 'group') {
                    $mileageGroupInfo = [];
                    foreach ($data['mileageGroupInfo'] as $val) {
                        $mileageGroupInfo[] = $groupList[$val];
                    }
                    echo '<br />(선택된 회원등급 : ' . @implode(', ', $mileageGroupInfo) . ')';
                }
            ?>
        </td>
    </tr>
    <tr>
        <th>금액 설정</th>
        <td>
            <?php
                if ($data['mileageFl'] == 'c') {
                    if ($conf['mileage']['giveType'] == 'price') {
                        echo '구매 금액의 <span>' . $conf['mileage']['goods'] . '%</span>를  마일리지로 지급';
                    } else if ($conf['mileage']['giveType'] == 'priceUnit') {
                        echo '구매금액으로 ' . number_format($conf['mileage']['goodsPriceUnit']) . '원 단위로 ' . number_format($conf['mileage']['goodsMileage']) . ' 마일리지 지급';
                    } else if ($conf['mileage']['giveType'] == 'cntUnit') {
                        echo '구매금액과 상관없이 구매상품 1개 단위로 ' . number_format($conf['mileage']['cntMileage']) . ' 마일리지 지급';
                    }
                } else {
                    $mileageGroup = gd_isset($data['mileageGroup'], 'all');
                    if ($mileageGroup == 'all') {
                        if ($data['mileageGoodsUnit'] == 'percent') {
                            echo '구매금액의 ' . $data['mileageGoods'] . '%';
                        } else {
                            echo '구매수량별 ' . gd_money_format($data['mileageGoods']) . Globals::get('gSite.member.mileageBasic.unit');
                        }
                    } else {
                        ?>
                        <table class="table table-rows">
                            <colgroup>
                                <col class="width-lg"/>
                                <col/>
                            </colgroup>
                            <tr>
                                <th class="center">회원등급</th>
                                <th class="center">지급금액</th>
                            </tr>
                            <?php foreach ($data['mileageGroupMemberInfo']['groupSno'] as $key => $value) { ?>
                                <tr>
                                    <td class="center"><?php echo $groupList[$value]; ?></td>
                                    <td>
                                        <?php
                                            if ($data['mileageGroupMemberInfo']['mileageGoodsUnit'][$key] == 'percent') {
                                                echo '구매금액의 ' . $data['mileageGroupMemberInfo']['mileageGoods'][$key] . '%';
                                            } else {
                                                echo '구매수량별 ' . gd_money_format($data['mileageGroupMemberInfo']['mileageGoods'][$key]) . Globals::get('gSite.member.mileageBasic.unit');
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                        <?php
                    }
                }
            ?>
        </td>
    </tr>
</table>

<table class="table table-rows benefit-table goods-discount display-none">
    <colgroup>
        <col class="width-xs"/>
        <col/>
    </colgroup>
    <tr>
        <th>혜택 적용방법</th>
        <td>
            <?php
            if ($data['goodsBenefitSetFl'] == 'y') {
                echo $data['benefitNm'];
            } else {
                echo '개별설정';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th>사용여부</th>
        <td>
            <?php
            if ($data['goodsDiscountFl'] == 'y') {
                echo '사용함';
            } else {
                echo '사용안함';
            }
            ?>
        </td>
    </tr>
    <?php if ($data['goodsDiscountFl'] == 'y') { ?>
    <tr>
        <th>진행유형</th>
        <td>
            <?php
            $arrNewGoodsReg        = array('regDt' => '등록일', 'modDt' => '수정일');
            $arrNewGoodsDate      = array('day' => '일', 'hour' => '시간');
            if ($data['benefitUseType'] == 'nonLimit') {
                echo '제한없음';
            }else if ($data['benefitUseType'] == 'newGoodsDiscount') {
                echo '신상품 할인 '.$arrNewGoodsReg[$data['newGoodsRegFl']].'부터 '.$data['newGoodsDate'].$arrNewGoodsDate[$data['newGoodsDateFl']].'까지';
            } else {
                echo '특정기간 할인 '.gd_date_format("Y-m-d H:i",$data['periodDiscountStart']).' ~ '.gd_date_format("Y-m-d H:i",$data['periodDiscountEnd']);
            }
            ?>
        </td>
    </tr>
    <tr>
        <th>할인금액 기준</th>
        <td><?php echo @implode('+', $data['fixedGoodsDiscount']); ?></td>
    </tr>
    <tr>
        <th>대상 선택</th>
        <td><?php echo $arrGoodsDiscountGroup[$data['goodsDiscountGroup']]; ?></td>
    </tr>
    <tr>
        <th>금액 설정</th>
        <td>
            <?php
                $goodsDiscountGroup = gd_isset($data['goodsDiscountGroup'], 'all');
                if (in_array($goodsDiscountGroup, ['all', 'member']) === true) {
                    if ($data['goodsDiscountUnit'] == 'percent') {
                        echo '구매금액의 ' . $data['goodsDiscount'] . '%';
                    } else {
                        echo '구매수량별 ' . gd_money_format($data['goodsDiscount']) . gd_currency_default();
                    }
                } else {
                    ?>
                    <table class="table table-rows">
                        <colgroup>
                            <col class="width-lg"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th class="center">회원등급</th>
                            <th class="center">지급금액</th>
                        </tr>
                        <?php foreach ($data['goodsDiscountGroupMemberInfo']['groupSno'] as $key => $value) { ?>
                            <tr>
                                <td class="center"><?php echo $groupList[$value]; ?></td>
                                <td>
                                    <?php
                                    if ($data['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'][$key] == 'percent') {
                                        echo '구매금액의 ' . $data['goodsDiscountGroupMemberInfo']['goodsDiscount'][$key] . '%';
                                    } else {
                                        echo '구매수량별 ' . gd_money_format($data['goodsDiscountGroupMemberInfo']['goodsDiscount'][$key]) . gd_currency_default();
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                    <?php
                }
            ?>
        </td>
    </tr>
    <?php } ?>
</table>

<table class="table table-rows benefit-table benefit-except display-none">
    <colgroup>
        <col class="width-xs"/>
        <col/>
    </colgroup>
    <tr>
        <th>혜택 적용방법</th>
        <td>
            <?php
            if ($data['goodsBenefitSetFl'] == 'y') {
                echo $data['benefitNm'];
            } else {
                echo '개별설정';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th>제외 혜택</th>
        <td>
            <?php
                if (empty($data['exceptBenefit']) === false) {
                    echo @implode('<br />', $data['exceptBenefit']);
                } else {
                    echo '선택된 제외 혜택이 없습니다.';
                }
            ?>
        </td>
    </tr>
    <?php if (empty($data['exceptBenefit']) === false) { ?>
    <tr>
        <th>제외 대상</th>
        <td>
            <?php
                $exceptBenefitGroup = gd_isset($data['exceptBenefitGroup'], 'all');
                echo $arrMileageGroup[$exceptBenefitGroup];
                if ($exceptBenefitGroup == 'group') {
                    $exceptBenefitGroupInfo = [];
                    foreach ($data['exceptBenefitGroupInfo'] as $val) {
                        $exceptBenefitGroupInfo[] = $groupList[$val];
                    }
                    echo '<br />(선택된 회원등급 : ' . @implode(', ', $exceptBenefitGroupInfo) . ')';
                }
            ?>
        </td>
    </tr>
    <?php } ?>
</table>

<p class="center">
    <button class="btn btn-black btn-lg js-layer-close">확인</button>
</p>

<script type="text/javascript">
    $(function(){
        $('.benefit-nav li').click(function(){
            var benefit = $(this).data('benefit');
            $('#viewInfoForm .benefit-nav li').removeClass('active');
            $(this).addClass('active');
            $('.benefit-table').addClass('display-none');
            $('.benefit-table.' + benefit).removeClass('display-none');
        });
    })
</script>
