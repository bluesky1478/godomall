
<table class="table table-rows">
    <colgroup>
        <col style="width:20%" />
        <col style="width:30%" />
        <col style="width:20%" />
        <col style="width:30%" />
    </colgroup>
    <tbody>
        <tr>
            <th>선택된 쿠폰</th>
            <td colspan="3"><?=$data['couponNm']?></td>
        </tr>
        <tr>
            <th>사용기간</th>
            <td><?=$data['useEndDate']?></td>
            <th>사용범위</th>
            <td><?=$data['couponDeviceType']?></td>
        </tr>
        <tr>
            <th>쿠폰유형</th>
            <td><?=$data['couponUseType']?></td>
            <th>혜택구분</th>
            <td><?=$data['couponKindType']?>(<?=$data['couponBenefit']?>)</td>
        </tr>
    </tbody>
</table>

<div class="text-center">
    <button type="submit" class="btn btn-black btn-lg js-close">확인</button>
</div>

<script type="text/javascript">
    $(function(){
        // 확인 클릭시
        $('.js-close').click(function(e){
            $('div.bootstrap-dialog-close-button').click();
        });
    });
</script>

