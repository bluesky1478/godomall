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
?>
<style>
    .layer_administrator_add{position:absolute;top:0;left:0;border:#FA2828 1px solid;background-color:#FFF}.administrator_add_top{position:relative}.administrator_add_top .btn_administrator_add_close{position:absolute;top:19px;right:19px}.administrator_add_bot{padding:29px 29px 0}.tit{font-size:14px;font-weight:700;color:#333;padding:0 20px}.box_gray{background:#F7F7F7;padding:20px}.setting_step{font-size:13px;color:#666}.setting_step li{margin-top:13px;letter-spacing:-.5px;line-height:1.5em}.setting_step li:first-child{margin-top:0}.txt_infor{padding:0 22px 0 20px;margin:10px 0 30px}.line_red{color:#FA2828;display:inline-block;border-bottom:#FA2828 1px solid;text-decoration:none}.line_red:hover{text-decoration:none;color:#FA2828}.etc_bot_con{margin-bottom:29px}.line_gray_con{color:#FA2828;text-align:center;padding:0 20px}.line_gray_con p{border-top:#DADADA 1px solid;padding-top:20px;letter-spacing:-.5px}.line_gray_con .type2{font-size:13px;text-align:left}.check_form{text-align:center;margin-top:10px}.check_form > *{vertical-align:middle}.check_form input[type="checkbox"]:checked{background-image:url(/admin/gd_share/img/panel/checkbox_red_on.png)}.check_form label{font-size:14px;font-weight:700;margin-left:6px;position:relative;top:1px}.btn_administrator_add{margin-top:15px;overflow:hidden}.btn_administrator_add > *{float:right}.btn_administrator_add > :first-child{float:left}.btn_administrator_add .btn-gray{background-color:#8B8B8B;font-weight:700}.bot_gray_close{width:100%;height:20px;line-height:19px;font-size:11px;color:#7C7C7C;background-color:#E7E7E7;text-align:right;padding-right:15px}.bot_gray_close input[type="checkbox"]{vertical-align:middle}
</style>
<div class="layer_administrator_add">
    <div class="administrator_add_top">
        <a href="http://www.law.go.kr/%EB%B2%95%EB%A0%B9/%EC%A0%95%EB%B3%B4%ED%86%B5%EC%8B%A0%EB%A7%9D%EC%9D%B4%EC%9A%A9%EC%B4%89%EC%A7%84%EB%B0%8F%EC%A0%95%EB%B3%B4%EB%B3%B4%ED%98%B8%EB%93%B1%EC%97%90%EA%B4%80%ED%95%9C%EB%B2%95%EB%A5%A0" target="_blank" class="vis_area">
            <img src="/admin/gd_share/img/panel/vis_pc_top.png" alt="관리자 추가 인증수단 설정 안내(필수) - 안전한 쇼핑몰 운영을 위하여 관리자 접속 시 추가 인증수단을 반드시 설정하여야 합니다. 미조치 시 정보통신망법(제28조 및 제73조) 에 따라 2년 이하의 징역 또는 2천만 원 이하의 과태료가 부과될 수 있습니다. 법령 자세히보기"/>
        </a>
        <a href="#" class="btn_administrator_add_close btn-close"><img src="/admin/gd_share/img/panel/btn_close.png" class="btn-close" alt="닫기"/></a>
    </div>
    <!-- // administrator_add_top -->
    <div class="administrator_add_bot">
        <p class="tit">설정방법</p>
        <div class="box_gray">
            <ul class="setting_step">
                <li>① [기본설정 > 운영 보안 설정 > 관리자 보안인증 설정]<br/>&nbsp;&nbsp;&nbsp;&nbsp;인증수단 : SMS인증 또는 이메일 인증 설정<br/>&nbsp;&nbsp;&nbsp;&nbsp;이메일 인증은 대량발송 등으로 지연될 수 있어 SMS 인증 사용 권장
                </li>
                <li>② [기본설정> 운영 보안 설정 > 관리자 보안인증 설정]<br/>&nbsp;&nbsp;&nbsp;&nbsp;보안로그인 : 사용함 설정</li>
                <li>③ [기본설정> 운영 보안 설정 > IP 접속제한 설정]<br/>&nbsp;&nbsp;&nbsp;&nbsp;관리자 IP 접속제한 : 사용함 설정 및 IP추가</li>
            </ul>
        </div>
        <div class="txt_infor">
            <p>관리자 추가 인증수단 설정에 대한 자세한 내용은<br/><a href="https://www.godo.co.kr/customer/manual/view.gd?idx=138" target="_blank" class="line_red">나를 위한 이용가이드</a>에서 확인하실 수 있습니다.
            </p>
        </div>
        <?php if (isset($isSuper)) { ?>
            <?php if ($isSuper === 'y') { ?>
                <!-- [D] 관리자 메인 최고운영자 일 경우 사용 -->
                <div class="etc_bot_con super-manager-guide">
                    <div class="line_gray_con">
                        <p>
                            추가 인증수단 미조치로 발생하는 문제는 전적으로 쇼핑몰에 있으니,<br/>부득이한 경우가 아닌 한 반드시 설정해 주시기 바랍니다.
                        </p>
                    </div>
                    <div class="check_form"><input type="checkbox" id="administratorAdd" class="input-lg" value="y"/>
                        <label for="administratorAdd">관리자 추가 인증수단 설정 안내 내용을 확인하였습니다.</label>
                    </div>
                    <div class="btn_administrator_add">
                        <button class="btn btn-gray btn-lg btn-guide-end">더 이상 안내받지 않겠습니다</button>
                        <button class="btn btn-red btn-lg btn-guide">미설정 시 재안내 받겠습니다(권장)</button>
                    </div>
                </div>
            <?php } else { ?>
                <!-- [D] 관리자 메인 부운영자 일 경우 사용 -->
                <div class="etc_bot_con manager-guide">
                    <div class="line_gray_con">
                        <p class="type2">
                            <strong>쇼핑몰 '최고운영자'가 관리자 추가 인증수단 설정 안내 내용을 확인하지<br/>않았습니다. '최고운영자'로 로그인 후 메인 페이지에서 안내 팝업을<br/>확인하시기 바랍니다.</strong>
                        </p>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
    <?php if (!isset($isSuper)) { ?>
    <!-- [D] 페이지 하단 일주일동안 열지 않기, 닫기 영역으로 관리자 로그인 일 때 사용 -->
    <div class="bot_gray_close">
        <input type="checkbox" class="check-week-close" title="일주일동안 열지않기"/>일주일동안 열지않기
        <input type="checkbox" class="check-close" title="닫기">닫기
    </div>
    <?php } ?>
    <!-- // administrator_add_bot -->
</div>

