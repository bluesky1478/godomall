<form id="layerForm" name="layerForm" action="naver_login_request_ps.php" method="post" enctype="multipart/form-data" target="naver_login">
    <input type="hidden" name="mode" id="mode" value="adminAuthorize" />
    <input type="hidden" name="token" id="token" value="" />
    <input type="hidden" name="confirmyn" id="confirmyn" value="n" />
    <input type="hidden" name="parentForm" id="parentForm" value="layer" />
    <input type="hidden" name="firstCheck" value="<?=$useFl?>" />
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">쇼핑몰명</th>
            <td>
                <input type="text" name="serviceName" id="serviceName" value="" placeholder="예) 고도몰" class="form-control width-lg" maxlength="40" flag="layer" />
                <div class="check-msg-layer c-gdred display-none">40자 이내의 영문, 한글, 숫자, 공백문자, "-", "_"만 입력 가능합니다.</div>
                <div class="notice-info">네이버 아이디로 로그인할 때 사용자에게 표시되는 이름입니다.</div>
                <div class="notice-info">40자 이내의 영문, 한글, 숫자, 공백문자, "-", "_"만 입력 가능합니다.</div>
            </td>
        </tr>
        <tr>
            <th class="require">대표 URL 입력</th>
            <td>
                <input type="text" name="serviceURL" id="serviceURL" value="http://" placeholder="예) www.godo.co.kr" class="form-control width-lg" />
            </td>
        </tr>
        <tr>
            <th>카테고리</th>
            <td>
                <select name="serviceCategory" class="form-control">
                    <option value="">= 카테고리 선택 =</option>
                    <?php foreach($category as $key => $val) { ?>
                        <option value="<?=$key?>"><?=$val?></option>
                    <?php } ?>
                </select>
                <span class="notice-info">네이버 아이디 로그인 애플리케이션 설정에 등록될 정보입니다.</span>
            </td>
        </tr>
        <tr>
            <th class="require">로고이미지</th>
            <td>
                <div class="form-inline">
                    <input type="file" name="serviceImage" class="form-control file" />
                </div>
                <div class="notice-info mgt5">네이버 아이디 로그인 연동 과정에서 사용자에게 노출되는 이미지입니다.</div>
                <div class="notice-info mgt5">권장 크기는 140X140 사이즈이며 jpg, png, 또는 gif만 등록 가능합니다.</div>
                <div class="notice-info mgt5">500KB 이하의 파일을 업로드 해주세요.</div>
            </td>
        </tr>
    </table>
    <div class="text-center btn-box">
        <button type="button" class="btn btn-lg btn-black" id="layerBtnConfirm">사용신청</button>
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
    </div>
</form>
