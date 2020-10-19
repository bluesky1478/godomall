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
<script type="text/javascript">
    // 중복 체크값
    var idChkFl = false;
    var pwChkFl = false;

    $(function (){
        // cs 아이디 보기 클릭 시
        $(document).on('click', '.js-cs-id-view', function(){
            var csId = $(this).data('cs-id');
            var csSno = $(this).data('sno');
            $('.js-cs-id-info-' + csSno).html(csId);
            $('.js-cs-id-block-' + csSno).html('<button class="btn btn-sm btn-black btn-copy js-clipboard" data-clipboard-text="' + csId + '" title="아이디">복사</button>');
        });

        // cs 비밀번호 보기 클릭 시
        $(document).on('click', '.js-cs-pw-view', function(){
            var csPw = $(this).data('cs-pw');
            var csSno = $(this).data('sno');
            $('.js-cs-pw-info-' + csSno).html(csPw);
            $('.js-cs-pw-block-' + csSno).html('<button class="btn btn-sm btn-black btn-copy js-clipboard" data-clipboard-text="' + csPw + '" title="비밀번호">복사</button>');
        });

        // cs 수동계정 선택 시 레이어 제어
        $(document).on('click', 'input[name=createType]', function (e) {
            if(e.target.value == 'm'){
                $('.js-manual').removeClass('display-none');
                $('.js-authorization').addClass('display-none');
                $('.js-authorization-detail').addClass('display-none');
                $('.js-manual-btn').addClass('display-none');
                $('input[name="csId"]').val('');
                $('input[name="csPw"]').val('');
                $("input:radio[name='authorization']:radio[value='select']").prop('checked', true);
            }else{
                $('.js-authorization').removeClass('display-none');
                $('.js-authorization-detail').removeClass('display-none');
                $('.js-manual').addClass('display-none');
                $('.js-create-btn').removeClass('display-none');
                $('.js-manual-btn').addClass('display-none');
            }
        });

        // cs계정 수동생성 시 '다음' 버튼 제어
        $(document).on('click', '.js-manual-next', function(){
            id_ajax();

            if($('input[name="csId"]').val().length == 0){
                alert('ID를 입력하세요.');
                return false;
            }

            // id 최소 글자 제한 체크
            if ($('input[name="csId"]').val().length < 4) {
                $('.csId_error').html('최소 4자 이상 입력해 주세요.');
                alert('입력된 ID를 확인해주세요.');
                return false;
            }

            // id 최대 글자 50자 초과 시 삭제
            if ($('input[name="csId"]').val().length > 50) {
                $('#csId').val(name.substr(0, 50));
                alert('입력된 ID를 확인해주세요.');
                return false;
            }

            // id 중복 체크
            /*if (idChkFl === false) {
                alert('입력된 ID를 확인해주세요.');
                return false;
            }
            */
            // pw 사용불가 체크
            if(pwChkFl === false){
                alert('입력된 PW를 확인해주세요.');
                return false;
            }

            if($('input[name="csPw"]').val().length == 0){
                alert('PW를 입력하세요.');
                return false;
            }

            if ($('input[name="csPw"]').val().length < 10) {
                $('.csPw_error').html('최소 10자 이상 입력해 주세요.');
                alert('입력된 PW를 확인해주세요.');
                return false;
            }

            // pw 최대 글자 16자 초과 시 삭제
            if ($('input[name="csPw"]').val().length > 16) {
                $('#csPw').val(name.substr(0, 16));
                return false;
            }

            // 수동생성일때
            var createType = $('input[name="createType"]:checked').val();
            if(createType == 'm'){
                $('.js-manual').addClass('display-none');
                $('.js-authorization').removeClass('display-none');
                $('.js-create-btn').removeClass('display-block');
                $('.js-create-btn').addClass('display-none');
                $('.js-manual-btn').removeClass('display-none');
                $("input:radio[name='authorization']:radio[value='select']").prop('checked', true).trigger('click');
            }
        });

        // cs계정 수동생성 시 '이전' 버튼 제어
        $(document).on('click', '.js-manual-back', function(){
            $('.js-authorization').addClass('display-none');
            $('.js-authorization-detail').addClass('display-none');
            $('.tbodyCsList').html('<tr class="text-center"><td colspan="2">선택된 메뉴가 없습니다.</td></tr>');
            $('#tabAccess').find('.pagination-sm').html('<li class="active"><a href="#" data-page="1">1</a></li>');
            $('#access1').prop('selected', false);
            $('#access2').empty();
            $('#access3').empty();
            $('input[name*="functionAuth"]').prop('checked', false);
            if($('[role="presentation"]').parents('a').attr('href') == '#tabFunction'){
                $('[role="presentation"]').removeClass('active');
            }
            $('.js-manual').removeClass('display-none');
            $('.js-create-btn').removeClass('display-none');
            $('.js-manual-btn').addClass('display-none');
            $("input:radio[name='authorization']:radio[value='all']").prop('checked', true);
        });

        // cs계정 수동생성 시 운영권한 제어
        $(document).on('click', 'input[name="authorization"]', function(){
            var createType = $('input[name="createType"]:checked').val();
            if($(this).val() == 'select'){  // 권한선택
                if(createType == 'a'){  // 자동생성
                    $('.js-authorization-detail').removeClass('display-none');
                }else{  // 수동생성
                    $('.js-authorization-detail').removeClass('display-none');
                    $('.tbodyCsList').html('<tr class="text-center"><td colspan="2">선택된 메뉴가 없습니다.</td></tr>');
                    $('#tabAccess').find('.pagination-sm').html('<li class="active"><a href="#" data-page="1">1</a></li>');
                    $('#access1').prop('selected', false);
                    $('#access2').prop('selected', false);
                    $('#access3').prop('selected', false);
                    $('input[name*="functionAuth"]').prop('checked', false);
                    if($('[role="presentation"]').parents('a').attr('href') == '#tabFunction'){
                        $('[role="presentation"]').removeClass('active');
                    }
                    $("option:selected").prop("selected", false);
                    $('.js-create-btn').addClass('display-none');
                    $('.js-manual-btn').removeClass('display-none');
                }

            }else{  // 전체권한
                if(createType == 'a'){  // 자동생성
                    $('.js-authorization-detail').removeClass('display-none');
                    $('.js-manual-btn').addClass('display-none');
                    $('.js-create-btn').addClass('display-block');
                }else { // 수동생성
                    $('.js-authorization-detail').addClass('display-none');
                    $('.js-create-btn').removeClass('display-block');
                }
            }
        });

        // cs계정 수동생성 시 id, pw 체크
        $(document).on('focusout', 'input[name="csId"]', function() {
            csValidChk('csId', 'csId_error');

            var id = $(this).val();
            var regExp1 = /[^a-zA-Z\u119E\u11A20-9\@{1,1}\_.-]/gi;
            var newId = '';
            id = id.replace(regExp1, '');
            tmp = id.split('@');
            for (var i in tmp) {
                newId += tmp[i];
                if (i == 0 && tmp.length > 1) newId += '@';
            }
            $(this).val(newId);
        });
        $(document).on('keyup', 'input[name="csId"]', function() {
            csValidChk('csId', 'csId_error');

            var id = $(this).val();
            var regExp1 = /[^a-zA-Z\u119E\u11A20-9\@{1,1}\_.-]/gi;
            var newId = '';
            id = id.replace(regExp1, '');
            tmp = id.split('@');
            for (var i in tmp) {
                newId += tmp[i];
                if (i == 0 && tmp.length > 1) newId += '@';
            }
            $(this).val(newId);
        });

        $(document).on('blur', 'input[name="csPw"]', function() {
            csValidChk('csPw', 'csPw_error');
            if(pwChkFl === false){
                return false;
            }

            var pw = $(this).val();
            var chk = 0;
            if(pw.search(/[0-9]/g) != -1 ) chk ++;
            if(pw.search(/[a-z]/ig)  != -1 ) chk ++;
            if(pw.search(/[!@#$%^&*()?_~]/g)  != -1  ) chk ++;
            if(chk < 2) {
                $('.csPw_error').html('사용불가! 영문대/소문자, 숫자, 특수문자 중 2가지 이상 조합하세요.');
                pwChkFl = false;
                return false;
            }else{
                $(this).val(pw);
                pwChkFl = true;
            }
        });

    });

    /**
     * cs 아이디, 비밀번호 validate
     */
    function csValidChk(csName, csMark) {
        var name = $('#'+csName).val();
        var length = name.length;

        if(csName == 'csId') {
            // id 최소 글자 제한 체크
            if (length < 4) {
                $('.' + csMark).html('최소 4자 이상 입력해 주세요.');
                return false;
            }

            // id 최대 글자 50자 초과 시 삭제
            if (length > 50) {
                $('#' + csName).val(name.substr(0, 50));
                return false;
            }

            // id 중복 체크
            id_ajax();
        }else{
            // pw 최소 글자 제한 체크
            if (length < 10) {
                $('.' + csMark).html('최소 10자 이상 입력해 주세요.');
                pwChkFl = false;
            }else{
                pwChkFl = true;
                $('.csPw_error').html('');
            }

            // pw 최대 글자 16자 초과 시 삭제
            if (length > 16) {
                $('#' + csName).val(name.substr(0, 16));
                return false;
            }
        }
    }

    /**
     * cs 계정 수동생성 아이디 중복체크 ajax
     */
    function id_ajax(){
        var id = $('input[name="csId"]').val();
        $.ajax({
            url: './layer_manager_cs_ps.php',
            data: {mode: 'overlap', csId: id},
            method: 'post',
            dataType: 'json',
            cache: false,
        }).success(function (result) {
            if (result['result'] == 'fail') {
                $('.csId_error').html('이미 등록된 아이디입니다. 다른 아이디를 입력해 주세요.');
                idChkFl = false;
                return false;
            }else if(result['result'] == 'empty'){
                $('.csId_error').html('');
                idChkFl = true;
            }
        }).error(function (e){
            console.log(e);
        });
    }

</script>
<script type="text/template" id="layerCsList">
    <div class="notice-box mgb20">
        <h2 class="font12 mgt5">CS 계정이란?</h2>
        <p>
            고도 직원만이 로그인할 수 있는 기간제한제 계정으로 사용기한이 종료되면 자동으로 삭제됩니다. <br/> CS 계정은 어드민 관리자의 추가 보안 설정에 대한 권한이 모두 부여된 상태로 생성됩니다.
            <br/> 오류 및 기술 문의 시 쇼핑몰 운영자의 정보가 아닌, CS 계정을 생성하여 전달하시기를 권장합니다.
            <br/> CS 계정은 정확한 답변을 위해 [1:1문의하기 > 추가정보 작성]의 "관리자 정보" 항목에 남겨주세요.
            <br/><span class="text-red">[기본설정 > 기본정책 > 기본 정보 설정]에 "쇼핑몰 도메인 / 대표 이메일" 정보가 등록되어 있어야 인증번호가 정상적으로 발송됩니다.</span>
        </p>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col class="width-2xl"/>
        </colgroup>
        <tbody>
        <tr>
            <th>공급사 구분</th>
            <td>
                <label>
                    <select id="selectScm">
                        <option value="0">=공급사 선택=</option>
                    </select>
                </label>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="text-center mgb20">
        <button type="button" class="btn btn-lg btn-white btn-red-line btn-cs-create">CS 계정 생성</button>
        <button type="submit" class="btn btn-lg btn-black btn-cs-search">CS 계정 검색</button>
    </div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-md"/>
            <col class="width-md"/>
            <col class="width-md"/>
            <col class="width-md"/>
        </colgroup>
        <thead>
        <tr>
            <th>공급사명</th>
            <th>아이디</th>
            <th>비밀번호</th>
            <th>정보수정</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <nav aria-label="Page navigation" class="text-center">
        <ul class="pagination pagination-sm">
            <li class="active"><a href="#" data-page=1>1</a></li>
        </ul>
    </nav>
</script>
<script type="text/template" id="tbodyCsList">
    <%
    _.each(list, function(item, key) {
    %>
    <tr class="text-center trCsList" data-sno="<%= item.sno %>" data-permission-fl="<%= item.permission_fl %>">
        <td><%= item.scm_name %></td>
        <td>
            <span class="display-inline-block js-cs-id-info-<%= item.sno%>" style="float:left; word-break: break-all; white-space: normal; width:200px;">**********</span>
            <span class="display-inline-block js-cs-id-block-<%= item.sno%>" style="float:right; width:45px; margin:0 auto;"><button class="btn btn-sm btn-gray js-cs-id-view" data-sno="<%= item.sno %>" data-cs-id="<%= item.cs_id %>" title="아이디">보기</button></span>
            <!--<span class="js-cs-id-info"><%= item.cs_id %></span>
            <span class="js-cs-id-btn"><button class="btn btn-black btn-copy js-clipboard" data-clipboard-text="<%= item.cs_id %>" title="아이디">복사</button></span>-->
        </td>
        <td>
            <span class="display-inline-block js-cs-pw-info-<%= item.sno%>" style="float:left;word-break: break-all; white-space: normal; width:200px;"">**********</span>
            <span class="display-inline-block js-cs-pw-block-<%= item.sno%>" style="float:right;"><button class="btn btn-sm btn-gray js-cs-pw-view" style="margin:0 auto;" data-sno="<%= item.sno %>" data-cs-pw="<%= item.cs_pw %>" title="비밀번호">보기</button></span>
            <!--<span class="js-cs-pw-info"><%= item.cs_pw %></span>
            <span class="js-cs-pw-btn"><button class="btn btn-black btn-copy" data-clipboard-text="<%= item.cs_pw %>" title="비밀번호">복사</button></span>-->
        </td>
        <td>
            <button class="btn btn-white btn-cs-modify">수정</button>
        </td>
    </tr>
    <%
    });
    %>
</script>
<script type="text/template" id="layerCsCreate">
    <div class="table-action js-create-type-block" style="text-align: center;">
        <label class="radio-inline">
            <input type="radio" name="createType" value="a" checked="checked"/>자동생성
        </label>
        <label class="radio-inline">
            <input type="radio" name="createType" value="m" />수동생성
        </label>
    </div>
    <div class="js-manual display-none">
        <div class="text-left mgb10">
            <b class="mgr5">ID/PW 설정</b><span class="notice-info">cs계정 아이디는 어드민 운영자 아이디와 중복될 수 없습니다.</span>
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class="width-2xl"/>
            </colgroup>
            <tbody>
            <tr>
                <th>ID</th>
                <td>
                    <label class="form-inline">
                        <span>G5MCS_</span>
                        <input type="text" class="form-control" id="csId" name="csId" value="" placeholder="영문, 숫자를 사용하여 4~50자로 입력" style="width:350px;" />
                        <div class="csId_error"></div>
                    </label>
                </td>
            </tr>
            <tr>
                <th>PW</th>
                <td>
                    <label class="form-inline">
                        <input type="password" class="form-control width-2xl" id="csPw" name="csPw" value="" placeholder="영문대소문자, 숫자, 특수문자 중 2개 포함, 10~16자로 입력" />
                        <div class="csPw_error"></div>
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="table-btn">
            <button class="btn btn-sm btn-dark-gray js-manual-next">다음</button>
        </div>
    </div>
    <div class="js-authorization">
        <div class="text-left mgb10">
            <b class="mgr5">CS 계정 권한 설정</b><span class="notice-info js-auto-info">계정 생성 시 "아이디와 비밀번호"는 자동으로 생성됩니다.</span>
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class="width-2xl"/>
            </colgroup>
            <tbody>
            <tr>
                <th>운영권한</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="authorization" value="all" checked="checked"/>전체권한
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="authorization" value="select"/>권한선택
                    </label>
                    <div class="pull-right js-create-btn">
                        <button class="btn btn-red">생성</button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="js-authorization-detail display-none">
        <ul class="nav nav-tabs display-none mgb5" role="tablist" id="tabAuthorization">
            <li role="presentation" class="active">
                <a href="#tabAccess" role="tab" data-toggle="tab" aria-controls="tabAccess">접근 권한</a></li>
            <li role="presentation">
                <a href="#tabFunction" role="tab" data-toggle="tab" aria-controls="tabFunction">기능 권한</a>
            </li>
        </ul>
        <div class="tab-content display-none">
            <div class="tab-pane active text-center" role="tabpanel" id="tabAccess">
                <div class="form-inline mgb5">
                    <select class="form-control multiple-select mgr5" id="access1" data-depth=1 size="5">
                        <option>=메뉴 선택=</option>
                    </select> <select class="form-control multiple-select mgr5" id="access2" data-depth=2 size="5">
                        <option>=메뉴 선택=</option>
                    </select> <select class="form-control multiple-select" id="access3" data-depth=3 size="5">
                        <option>=메뉴 선택=</option>
                    </select>
                </div>
                <div class="mgb10">
                    <button class="btn btn-black btn-select-access">선택</button>
                </div>
                <div class="table-header text-left">선택된 메뉴
                    <span class="notice-info">접근 권한 설정은 소메뉴 중 최소 1개 이상 설정하셔야 합니다.</span></div>
                <table class="table table-rows">
                    <colgroup>
                        <col class="width-xl"/>
                        <col class="width-xs"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>메뉴명</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="2">선택된 메뉴가 없습니다.</td>
                    </tr>
                    </tbody>
                </table>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm">
                        <li class="active"><a href="#" data-page=1>1</a></li>
                    </ul>
                </nav>
            </div>
            <div class="tab-pane" role="tabpanel" id="tabFunction">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-2xs"/>
                        <col class="width-2xl"/>
                    </colgroup>
                    <% if (scm.functionAuth === null) { %>
                    <tr class="text-center empty-function-auth"><td colspan="2">기능 권한이 없습니다.</td></tr>
                    <% } else { %>
                    <tr>
                        <th>관리자 기본</th>
                        <td>
                            <div class="row">
                                <% if (scm.functionAuth.mainStatisticsSales === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[mainStatisticsSales]" value="y"/>주요현황 - 매출
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.mainStatisticsOrder === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[mainStatisticsOrder]" value="y"/>주요현황 - 주문
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.mainStatisticsVisit === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[mainStatisticsVisit]" value="y"/>주요현황 - 방문자
                                    </label>
                                </div>
                                <% } %>
                            </div>
                            <div class="row">
                                <% if (scm.functionAuth.mainStatisticsMember === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-12">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[mainStatisticsMember]" value="y"/>주요현황 - 신규회원
                                    </label>
                                </div>
                                <% } %>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>상품</th>
                        <td>
                            <div class="row">
                                <% if (scm.functionAuth.goodsDelete === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[goodsDelete]" value="y"/>상품삭제
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.goodsExcelDown === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[goodsExcelDown]" value="y"/>엑셀다운로드
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.goodsCommission === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline" style="letter-spacing: -1px;">
                                        <input type="checkbox" name="functionAuth[goodsCommission]" value="y"/>판매수수료 등록/수정
                                    </label>
                                </div>
                                <% } %>
                            </div>
                            <div class="row">
                                <% if (scm.functionAuth.goodsNm === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[goodsNm]" value="y"/>상품명 수정
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.goodsSalesDate === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[goodsSalesDate]" value="y"/>판매기간 등록/수정
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.goodsPrice === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline ">
                                        <input type="checkbox" name="functionAuth[goodsPrice]" value="y"/>판매가 수정
                                    </label>
                                </div>
                                <% } %>
                            </div>
                            <div class="row">
                                <% if (scm.functionAuth.goodsStockModify === 'y') { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[goodsStockModify]" value="y"/>상품 재고 수정
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.goodsStockExceptView === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4" style="padding-right:0px">
                                    <label class="checkbox-inline" style="letter-spacing: -1.5px;">
                                        <input type="checkbox" name="functionAuth[goodsStockExceptView]" value="y"/>상품 상세 재고 수정 제외
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.goodsSortTop === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline ">
                                        <input type="checkbox" name="functionAuth[goodsSortTop]" value="y"/>상단 고정진열 적용
                                    </label>
                                </div>
                                <% } %>
                            </div>
                            <div class="row">
                                <% if (scm.functionAuth.addGoodsCommission === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-6">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[addGoodsCommission]" value="y"/>추가상품 판매수수료 등록/수정
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.addGoodsNm === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-5">
                                    <label class="checkbox-inline ">
                                        <input type="checkbox" name="functionAuth[addGoodsNm]" value="y"/>추가상품 상품명 수정
                                    </label>
                                </div>
                                <% } %>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>주문/배송</th>
                        <td>
                            <div class="row">
                                <% if (scm.functionAuth.orderState === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[orderState]" value="y"/>주문상태 변경
                                    </label>
                                </div>
                                <% } %> <% if (scm.functionAuth.orderExcelDown === 'y' || scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[orderExcelDown]" value="y"/>엑셀다운로드
                                    </label>
                                </div>
                                <% } %> <% if (scm.scmNo === 1) { %>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[orderBank]" value="y"/>입금은행 변경
                                    </label>
                                </div>
                                <% } %>
                            </div>
                            <% if (use_bankda === 'y' && scm.scmNo === 1) { %>
                            <div class="row">
                                <div class="col-xs-12">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[bankdaManual]" value="y"/>입금내역 주문서 수동매칭
                                    </label>
                                </div>
                            </div>
                            <% } %> <% if (scm.scmNo === 1) { %>
                            <div class="row">
                                <div class="col-xs-12">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[orderReceiptProcess]" value="y"/>현금영수증 처리(발급/거절/취소/삭제)
                                    </label>
                                </div>
                            </div>
                            <% } %>
                        </td>
                    </tr>
                    <% if (scm.scmNo === 1) { %>
                    <tr>
                        <th>회원</th>
                        <td>
                            <div class="row">
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[memberHack]" value="y"/>회원탈퇴
                                    </label>
                                </div>
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[memberExcelDown]" value="y"/>엑셀다운로드
                                    </label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <% } %> <% if (scm.functionAuth.boardDelete === 'y' || scm.scmNo === 1) { %>
                    <tr>
                        <th>게시판</th>
                        <td>
                            <div class="row">
                                <div class="col-xs-4">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="functionAuth[boardDelete]" value="y"/>게시글 삭제
                                    </label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <% } %> <% } %>
                </table>
            </div>
        </div>
    </div>
    <div class="table-btn js-manual-btn display-none">
        <button class="btn btn-sm btn-white js-manual-back">이전</button>
        <button class="btn btn-sm btn-red js-manual-create">생성</button>
    </div>
</script>
<script type="text/template" id="pagination">
    <% if (total_page > limit_page && current > limit_page) { %>
    <li class="display-none front-page front-page-first">
        <a href="#" aria-label="First" data-page="1">
            <img src="/admin/gd_share/img/icon_arrow_page_ll.png" class="img-page-arrow"> </a>
    </li>
    <li class="display-none front-page front-page-prev">
        <a href="#" aria-label="Previous"
           data-page="<%= start - limit_page %>">
            <img src="/admin/gd_share/img/icon_arrow_page_l.png" class="img-page-arrow"></a>
    </li>
    <% } %>
    <% for (var i = start; i < end; i++) { %>
    <% if (i === current) { %>
    <li class="active"><a href="#" data-page="<%= i %>"><%= i %></a></li><% } else { %>
    <li><a href="#" data-page="<%= i %>"><%= i %></a></li><% } %>
    <% } %>
    <% if ((start + 5) < total_page) {%>
    <li class="display-none front-page front-page-next">
        <a href="#" aria-label="Next" data-page="<%= start + limit_page %>">
            <img src="/admin/gd_share/img/icon_arrow_page_r.png" class="img-page-arrow"></a>
    </li>
    <li class="display-none front-page front-page-last">
        <a href="#" aria-label="Last" data-page="<%= total_page %>">
            <img src="/admin/gd_share/img/icon_arrow_page_rr.png" class="img-page-arrow"> </a></li>
    <% } %>
</script>
