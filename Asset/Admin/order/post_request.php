<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
</div>

<div class="table-title">우체국택배 배송상태 자동 업데이트 </div>
<table class="table table-cols">
<tbody>
    <tr>
        <td style="padding: 15px 0;">
            <div class="notice-info" style="margin-top: 0;margin-bottom: 0">
                우체국택배 배송상태를 쇼핑몰에 2시간마다 자동으로 업데이트 합니다.<br/>
                2시간마다 자동으로 배송상태를 확인하여 배송이 완료된 주문은 ‘배송완료’로 업데이트 됩니다.
            </div>
        </td>
    </tr>
</tbody>
</table>

<div class="table-title">우체국택배 배송상태 수동 업데이트 </div>
<table class="table table-cols">
<tbody>
<tr>
    <td style="padding: 15px 0 20px 0;">
        <div class="notice-info" style="margin-top: 0;margin-bottom: 0">
            우체국택배 배송상태를 쇼핑몰에 수동 업데이트하려면, 아래 버튼을 클릭해 주세요.<br/>
            수동으로 배송상태를 확인하여 배송이 완료된 주문은 ‘배송완료’로 업데이트 됩니다.
        </div>
        <div style="padding-top: 15px;"><a href="./post_ps.php?mode=manual" class="btn btn-lg btn-black" target="ifrmProcess" style="color:#fff">배송상태 수동 업데이트</a></div>
    </td>
</tr>
</tbody>
</table>
<div class="table-title ">우체국택배 신청 </div>
<iframe name="requestPostIfrm" src="https://www.godo.co.kr/service/godopost/regist.php?shopSno=<?=$shopSno?>&shopHost=<?=$domainUrl?>" frameborder="0" height="830px" width="100%"></iframe>
