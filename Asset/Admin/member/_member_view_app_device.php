<div class="table-title">
    모바일앱 사용정보
</div>
<div class="form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>앱설치여부</th>
            <td>
                <table class="table table-rows">
                    <colgroup>
                        <col class="width-md"/>
                        <col class="width-md"/>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>디바이스</th>
                            <th>앱 로그인 상태</th>
                            <th>앱 푸시 수신 상태</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($appDeviceInfo as $val) { ?>
                        <tr>
                            <td class="center"><?=$val['model'];?></td>
                            <td class="center"><?=$val['loggedIn'] == 'y' ? '로그인' : '로그아웃';?></td>
                            <td class="center"><?=$val['pushEnabled'] == 1 ? '수신허용' : '수신거부';?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div class="notice-info">회원님이 모바일앱 설치 후 로그인한 단말기 목록이며, 앱 삭제 시 목록에서도 삭제됩니다.</div>
                <div class="notice-info">
                    앱에 로그인된 상태이고, 앱 푸시 수신이 '수신허용'인 회원만 푸시 발송 대상으로 처리됩니다.<br/>
                    (단, 전체 푸시 발송은 로그인과 관계업이 모든 앱 설치 단말기를 대상으로 합니다.)
                </div>
            </td>
        </tr>
        <tr>
            <th>앱설치혜택<br/>제공여부</th>
            <td>
                <?=$installBenefitFl;?>
                <span class="notice-info mgl10">앱설치 혜택 지급 여부에 대한 정보입니다. (앱 설치 혜택은 회원당 1회만 지급 가능)</span>
            </td>
        </tr>
    </table>
</div>
