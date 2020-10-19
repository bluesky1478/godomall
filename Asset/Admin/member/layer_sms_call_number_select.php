<table class="table table-rows table-fixed">
    <colgroup>
        <col class="width10p"/>
        <col class="width10p"/>
        <col class="width30p"/>
        <col class="width30p"/>
        <col class="width30p"/>
    </colgroup>
    <thead>
    <tr>
        <th>선택</th>
        <th>번호</th>
        <th>발신번호</th>
        <th>관리메모</th>
        <th>승인일</th>
    </tr>
    <?php
    if (empty($smsCallNumberData) === false) {
        $tableNo = count($smsCallNumberData);
        foreach ($smsCallNumberData as $key => $getData) {
    ?>
    <tr class="text-center">
        <td class="form-inline">
            <input type="radio" name="selectNum" id="selectNum<?php echo $key;?>" value="<?php echo $getData['callback'];?>" text="<?php echo gd_number_to_phone($getData['callback']);?>" />
        </td>
        <td class="form-inline"><?php echo $tableNo--;?></td>
        <td>
            <label for="selectNum<?php echo $key;?>">
                <span class="number text-darkblue bold"><?php echo gd_number_to_phone($getData['callback']);?></span>
            </label>
        </td>
        <td>
            <label for="selectNum<?php echo $key;?>">
            <?php echo $getData['title'];?>
            </label>
        </td>
        <td>
            <label for="selectNum<?php echo $key;?>">
            <?php echo $getData['apvdtime'];?>
            </label>
        </td>
    </tr>
    <?php
        }
    }
    ?>
    </thead>
    <tbody>
    </tbody>
</table>

<div class="text-center">
    <a href="https://www.godo.co.kr/mygodo/sms/intro.gd" target="_blank" class="btn btn-white btn-lg">발신번호 관리</a>
    <button type="button" class="btn btn-black btn-lg js-submit">발신번호 선택하기</button>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        // 발신번호 선택하기
        $(".js-submit").click(function (e) {
            var selectNum = $('input:radio[name=selectNum]:checked').val();
            var selectNumText = $('input:radio[name=selectNum]:checked').attr('text');
            if (typeof selectNum == 'undefined') {
                alert('발신번호를 선택해 주세요.');
            } else {
                var params = {
                    mode: 'smsCallNumSave',
                    smsCallNum: selectNum
                };
                $.post('../member/sms_ps.php', params, function (data) {
                    if (data == 'OK') {
                        $('input[name=<?php echo $returnInput;?>]').val(selectNum);
                        $('.smsCallNumText').html(selectNumText);
                        $('.smsCallNumText').addClass('number text-darkblue bold');
                        alert('선택한 발신번호가 저장 완료되었습니다.');
                    } else {
                        alert('선택한 발신번호 저장에 실패하였습니다.');
                    }
                });
                layer_close();
            }
        });
    });
    //-->
</script>
