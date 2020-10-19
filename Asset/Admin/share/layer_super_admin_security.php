<div id="superAdminSecurity" style="position:absolute;top:30%;left:20%;z-index:999; margin:0; padding:15px 15px 20px 25px;width:480px;border: 1px solid #666; background-color:#ffffff;">
    <div style="width:430px;position:relative; overflow:hidden;">
        <div style="width:430px;">
            <div style="float:left; font-size:14px; font-weight:bold; line-height:40px;">
                대표운영자 정보 입력 안내
            </div>
            <div style="float:right; background: url(<?=PATH_ADMIN_GD_SHARE?>img/btn_layer_close.png) no-repeat 50% 50%; width: 22px;height: 22px; opacity: 1;cursor:pointer;" onclick="$('#superAdminSecurity').hide();"></div>
            <div style="clear:both; height:0px;"></div>
        </div>
        <div style="width:430px; border-bottom: 2px solid #888;"></div>

        <div style="width:420px; padding:20px 0px 0px 5px;">
            <div style="font-size:13px; color:#f91d11; line-height:16px; font-weight:bold;">
                관리자 보안을 위해 대표운영자 휴대폰/이메일 정보를 등록하세요.
            </div><br>
            <div style="font-size:11px; color:#777; line-height:16px;">
                고도몰5에서는 관리자 보안강화를 위해 보안이 필요한 화면 접근 시<br/>
                이메일/휴대폰 인증 기능을 제공하고 있습니다.<br/>
                운영자의 휴대폰/이메일정보가 없는 경우 보안인증 기능을 사용할 수 없으므로<br/>
                <b>관리자 보안을 위해 대표운영자 정보에 휴대폰/이메일 정보를<br/>
                등록 및 인증하시기 바랍니다.</b><br/><br/>

                * 이 팝업은 대표운영자 정보에 휴대폰 번호 또는 이메일이 등록 및 인증되면 노출되지 않습니다.
            </div>
        </div>
        <div style="text-align:center;margin-top:30px;">
            <button class="btn btn-white btn-lg" onclick="$('#superAdminSecurity').hide();" style="margin-right:10px;">닫기</button>
            <button class="btn btn-black btn-lg" onclick="window.location.href='<?=$manageModifyUrl?>'">대표운영자 정보 설정</button>
        </div>
    </div>
</div>
