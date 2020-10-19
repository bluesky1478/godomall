
<div class="table-title gd-help-manual">
    <?=$pgNm;?> 설정
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tr>
        <th>PG사 모듈 버전</th>
        <td>Danal Pay Version W1.1.4.11111</td>
    </tr>
    <tr>
        <th>사용여부</th>
        <td>
            <label class="radio-inline"><input type="radio" name="useFl" value="y" <?=gd_isset($checked['useFl']['y']);?> /> 사용</label>
            <label class="radio-inline"><input type="radio" name="useFl" value="n" <?=gd_isset($checked['useFl']['n']);?> /> 사용안함</label>
        </td>
    </tr>
    <tr>
        <th>서비스 설정</th>
        <td>
            <?php if ($data['pgAutoSetting'] === 'y') { ?>
                <span class="text-blue bold">서비스 ID : <?php echo $data['pgId'];?></span> <span class="text-blue">(자동 설정 완료)</span>
            <?php } else { ?>
                <div class="font-kor text-blue">
                    <span>
                        ● 서비스 신청을 해주세요.
                        <a href="https://www.godo.co.kr/echost/power/add/payment/mobile-pg-intro.gd" target="_blank" class="btn btn-gray btn-sm"><?php echo $pgNm;?> 서비스 신청</a>
                    </span>
                </div>
            <?php }?>
        </td>
    </tr>
</table>
