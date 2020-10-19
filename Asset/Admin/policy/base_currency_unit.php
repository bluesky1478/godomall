<form id="frmUnit" name="frmUnit" action="base_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="currency_unit"/>
    <input type="hidden" name="mallFaviconTmp" value="<?php echo $data['mallFavicon']; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>쇼핑몰의 기본 금액 / 단위 기준 설정을 변경하실 수 있습니다.</small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        국가별 금액 / 무게 단위 표기 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>기준 국가</th>
            <td>
                <div class="form-inline">
                    <select name="country" class="form-control width-sm">
                        <?php
                        foreach ($setCountryUnit as $key => $val) {
                            echo '<option value="' . $key . '" ' . $selected['country'][$key] . '>' . $val['country'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <th>금액 표시 방법</th>
            <td>
                <div class="form-inline">
                    <select name="currency" class="form-control width-sm"></select>
                    <div class="notice-info">표시예제 : <span class="viewExercise"></span></div>
                </div>
            </td>
        </tr>
        <tr>
            <th>무게 단위</th>
            <td>
                <div class="form-inline">
                    <?php echo gd_select_box('weight', 'weight', $setWightUnit, null, $weight['unit'], null, null, 'form-control width-sm'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>용량 단위</th>
            <td>
                <div class="form-inline">
                    <?php echo gd_select_box('volume', 'volume', $setVolumeUnit, null, $volume['unit'], null, null, 'form-control width-sm'); ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        금액 절사 기준 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <?php
        foreach ($unitType as $uKey => $uVal) {
            if ($uKey === 'scm_calculate' && !gd_is_plus_shop(PLUSSHOP_CODE_SCM)) {
                continue;
            }
        ?>
        <tr>
            <th><?php echo $uVal['name'];?></th>
            <td>
                <div class="form-inline">
                    <?php
                    // 설정
                    if ($uKey === 'scm_calculate') {
                        echo '<input type="hidden" name="'. $uKey . '[unitPrecision]" value="0.1"/>';
                        echo '<input type="hidden" name="'. $uKey . '[unitRound]" value="floor"/>';
                        echo '<span>' . $unitPrecisionScm[$trunc[$uKey]['unitPrecision']] . '</span> <span>단위</span> <span>' . $unitRoundScm[$trunc[$uKey]['unitRound']] . '</span>';
                    } else {
                        echo gd_select_box($uKey . '[unitPrecision]', $uKey . '[unitPrecision]', $unitPrecision, null, $trunc[$uKey]['unitPrecision'], null, null, 'form-control width-sm');
                        echo ' <span class="mgr30">단위</span>';
                        echo gd_select_box($uKey . '[unitRound]', $uKey . '[unitRound]', $unitRound, null, $trunc[$uKey]['unitRound'], null, null, 'form-control width-sm');
                    }

                    // 설명
                    if ($uKey === 'goods') {
                        echo '<span class="notice-info mgl10"> 상품 할인이나 일괄 가격 수정 시 적용되는 절사 기준입니다.</span>';
                    }
                    if ($uKey === 'member_group') {
                        echo '<span class="notice-info mgl10"> 회원 등급에 주어지는 추가할인 / 중복할인 혜택에 적용되는 절사 기준입니다.</span>';
                    }
                    if ($uKey === 'scm_calculate') {
                        echo '<span class="notice-info mgl10"> 공급사에 수수료 정산 시 적용되는 절사 기준입니다. (기준 변경 불가)</span>';
                    }
                    ?>
                </div>
            </td>
        </tr>
        <?php }?>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 금액/단위 기준설정 정보 저장
        $("#frmUnit").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
            },
            messages: {
            }
        });

        // 국가별 통화 설정
        setCurrency();
        $('#frmUnit select[name=country]').change(function () {
            setCurrency();
        });

        <?php if (empty($currency['symbol']) === false) { echo '$(\'#frmUnit select[name=currency] option[value=' . $currency['symbol'] . ']\').attr(\'selected\',\'selected\');';}?>
        <?php if (empty($currency['string']) === false) { echo '$(\'#frmUnit select[name=currency] option[value=' . $currency['string'] . ']\').attr(\'selected\',\'selected\');';}?>

        // 통화 설정시 예제 보기
        viewExercise();
        $('#frmUnit select[name=currency]').change(function () {
            viewExercise();
        });
    });

    /**
     * 국가별 통화
     */
    <?php
    foreach ($setCountryUnit as $key => $val) {
        foreach ($val['currency'] as $cKey => $cVal) {
            $arrCurrency[$key][] = $cKey;
            $arrSymbol[$key][] = $cVal;
        }
        $varCurrency = 'var ' . $key . '_currency = ' . json_encode($arrCurrency[$key], JSON_UNESCAPED_UNICODE) . ';';
        $varSymbol = 'var ' . $key . '_symbol = ' . json_encode($arrSymbol[$key], JSON_UNESCAPED_UNICODE) . ';';
        $varDecimals = 'var ' . $key . '_decimal = ' . $val['decimal'] . ';';
        echo $varCurrency . PHP_EOL;
        echo $varSymbol . PHP_EOL;
        echo $varDecimals . PHP_EOL;
    }
    ?>
    function setCurrency() {
        var select_country = $('#frmUnit select[name=country]').val();
        var select_currency = eval(select_country + '_currency');
        var select_symbol = eval(select_country + '_symbol');
        var select_decimal = eval(select_country + '_decimal');

        var option = new Array();

        for (var i = 0; i < select_currency.length; i++)
        {
            if (select_currency[i]) {
                option[i] =  '<option value="' + select_currency[i] + '" ex="' + select_symbol[i] + '_' + select_decimal + '">' + select_currency[i] + '</option>';
            }
        }
        $('#frmUnit select[name=currency]').html(option);
        viewExercise();
    }

    /**
     * 표시 예제
     */
    function viewExercise() {
        var select_currency = $('#frmUnit select[name=currency]').val();
        var select_ex = $('#frmUnit select[name=currency] option:selected').attr('ex');
        var arr = select_ex.split('_');
        var testInt = '1,300.13';
        var intArr = testInt.split('.');
        var exInt = '';

        if (arr[1] == '0') {
            exInt = intArr[0];
        } else {
            exInt = testInt;
        }

        if (arr[0] == 'symbol') {
            exInt = select_currency + ' ' + exInt;
        } else {
            exInt = exInt + ' ' + select_currency;
        }

        $('.viewExercise').html(exInt);
    }
    //-->
</script>
