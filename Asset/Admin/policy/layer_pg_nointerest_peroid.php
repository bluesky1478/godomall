<table class="table table-cols">
    <colgroup>
        <col class="width-xs"/>
        <col/>
    </colgroup>
    <?php
    $chkPgName = ['lguplus', 'allthegate', 'nicepay'];
    if (in_array($pgName, $chkPgName) === false) {
        ?>
        <tr>
            <th>전체카드</th>
            <td><label class="checkbox-inline"><input type="checkbox" name="cardAll" value="y" onclick="checked_bold(this)"/><span>전체카드</span></label></td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <th>무이자 카드</th>
        <td>
            <?php
            $no = 0;
            foreach ($data as $key => $val) {
                echo '<label><input type="checkbox" name="card[' . $no . ']" value="' . $key . '" onclick="checked_bold(this)" /><span style="display:inline-block; width:120px">' . $val . '</span></label>';
                $no++;
                if ($no % 3 === 0) {
                    echo '<br />';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <th>무이자 기간</th>
        <td>
            <?php
            $no = 0;
            $arrNoZero = ['inicis', 'allthegate', 'nicepay'];
            for ($i = 2; $i <= $pgPeriod['noInterest']; $i++) {
                if (in_array($pgName, $arrNoZero) === true) {
                    $periodNo = $i;
                } else {
                    $periodNo = sprintf('%02d', $i);
                }
                echo '<label class="checkbox-inline"><input type="checkbox" name="period[' . $no . ']" value="' . $periodNo . '" onclick="checked_bold(this)"r /><span>' . $periodNo . '개월</span></label>';
                $no++;
                if ($no % 4 === 0) {
                    echo '<br />';
                }
            }
            ?>
        </td>
    </tr>
</table>
<div class="text-center">
    <button type="button" class="btn btn-black btn-lg" onclick="select_code();">선택</button>
</div>

<script type="text/javascript">
    <!--
    function select_code() {
        var cardAll = $('input[name=\'cardAll\']').is(':checked');
        var cardNmCode = eval(<?php echo json_encode($data);?>);

        var periodCheck = $('input[name*=\'period[\']').length;
        var cardCheck = $('input[name*=\'card[\']').length;

        var periodCode = '';
        var strPeriodCd = '';

        var cardCode = '';
        var strCardCd = '';

        var chkPeriod = false;
        var chkCard = false;

        for (var i = 0; i < periodCheck; i++) {
            if ($('input[name=\'period[' + i + ']\']').is(':checked')) {
                var periodCd = $('input[name=\'period[' + i + ']\']').val();
                if (periodCode == '') {
                    periodCode = periodCd;
                    strPeriodCd = periodCd + '개월';
                } else {
                    periodCode = periodCode + ':' + periodCd;
                    strPeriodCd = strPeriodCd + ', ' + periodCd + '개월';
                }
                chkPeriod = true;
            }
        }

        if (cardAll == true) {
            cardCode = 'ALL-' + periodCode;
            $('#noInterestPeroid').html('');            // 기존 무이자 삭제
            var addHtml = '';
            addHtml += '<div id="noInterest_Peroid_all" class="mgt3">';
            addHtml += '<input type="button" onclick="field_remove(\'noInterest_Peroid_all\');" value="삭제" class="btn btn-red-box btn-xs" />';
            addHtml += '<input type="hidden" name="noInterestPeroid[]" value="' + cardCode + '" />';
            addHtml += ' <span>전체카드 - ' + strPeriodCd + '</span>';
            addHtml += '</div>';

            $('#noInterestPeroid').append(addHtml);
            chkCard = true;
        } else {
            if (chkPeriod == true) {
                field_remove('noInterest_Peroid_all');        // 전체 무이자 삭제

                for (var i = 0; i < cardCheck; i++) {
                    if ($('input[name=\'card[' + i + ']\']').is(':checked')) {
                        var cardCd = $('input[name=\'card[' + i + ']\']').val();
                        var randCd = Math.floor(Math.random() * 899 + 100);        // 100 ~ 999 까지의 난수

                        cardCode = cardCd + '-' + periodCode;
                        strCardCd = cardNmCode[cardCd] + ' - ' + strPeriodCd;

                        // 동일 카드사 체크
                        var sameCheck = false;
                        $('input[name=\'noInterestPeroid[]\']').each(function (idx, element) {
                            var checkCardCode = $(element).val().split('-');
                            if (checkCardCode[0] == cardCd) {
                                alert('[' + cardNmCode[cardCd] + '] 이(가) 이미 등록이 되어 있습니다. 등록된 카드를 삭제후 다시 등록 바랍니다.');
                                sameCheck = true;
                            }
                        });

                        // 동일 카드사가 없는 경우 등록 처리
                        if (sameCheck == false) {
                            var addHtml = '';
                            addHtml += '<div id="noInterest_Peroid_' + randCd + '" class="mgt3">';
                            addHtml += '<input type="button" onclick="field_remove(\'noInterest_Peroid_' + randCd + '\');" value="삭제" class="btn btn-red-box btn-xs" />';
                            addHtml += '<input type="hidden" name="noInterestPeroid[]" value="' + cardCode + '" />';
                            addHtml += ' <span>' + strCardCd + '</span>';
                            addHtml += '</div>';

                            $('#noInterestPeroid').append(addHtml);
                        }

                        chkCard = true;
                    }
                }
            } else {
                alert('무이자 설정할 카드 및 기간을 선택해 주세요');
                return false;
            }
        }

        if (chkPeriod == true && chkCard == true) {
            $.unblockUI();
            return true;
        } else {
            if (chkCard == false) {
                alert('무이자 설정할 카드를 선택해 주세요');
                return false;
            }
        }
    }
    //-->
</script>
