<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#frmConfig").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
                return false;
            },
            rules: {},
            messages: {}
        });
    });
    //-->
</script>
<form id="frmConfig" action="dburl_ps.php" method="post">
    <input type="hidden" name="type" value="config"/>
    <input type="hidden" name="company" value="daumcpc"/>
    <input type="hidden" name="mode" value="config"/>
    <div>
        <div class="page-header js-affix">
            <h3><?php echo end($naviMenu->location); ?>
                <small></small>
            </h3>
            <div class="btn-group">
                <input type="button" value="DB등록 방법 확인" class="btn btn-white" id="goodsListForCopy" onclick="window.open('https://marketing.godo.co.kr/board/view.gd?postNo=204&tab=2');"/>
                <input type="submit" value="저장" class="btn btn-red">
            </div>
        </div>

        <div class="table-title">
            설정 하기
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>사용 설정</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="useFl" value="y" <?php echo gd_isset($checked['useFl']['y']) ?> /> 사용</label>
                    <label class="radio-inline"><input type="radio" name="useFl" value="n" <?php echo gd_isset($checked['useFl']['n']) ?> /> 미사용</label>
                </td>
            </tr>
        </table>

        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>무이자할부정보</th>
                <td>
                    <input type="text" name="pcard" class="form-control" style="width:250px;" value="<?php echo gd_isset($data['pcard']) ?>"/>
                    <div class="notice-info">예) 삼성2~3/롯데3/현대6</div>
                </td>
            </tr>
            <tr>
                <th>상품명 머릿말 설정</th>
                <td>
                    <input type="text" name="goodshead" class="form-control" style="width:250px;" value="<?php echo gd_isset($data['goodshead']) ?>"/>
                    <div class="notice-info">
                         상품명 머리말 설정을 위한 치환코드<br/>
                        - 머리말 상품에 입력된 "제조사"를 넣고 싶을 때 : {_maker}<br/>
                        - 머리말 상품에 입력된 "브랜드"를 넣고 싶을 때 : {_brand}
                    </div>
                </td>
            </tr>
            <tr>
                <th>상품가격 설정</th>
                <td>
                    <b><?php echo $joinGroup['name'] ?></b> 할인율(<b><?php echo $joinGroup['dc'] ?></b>)이 상품가격에 적용되어 쇼핑하우에 노출 됩니다.
                    <div class="notice-info">
                        쇼핑하우에 노출되는 상품가격은 적용된 쿠폰과 가입시 회원그룹의 할인율이 적용된 가격이 됩니다.<br/>
                    </div>
                    <div class="notice-info">
                        다음 쇼핑하우 정책에 따라 다음 유형의 쿠폰은 상품가격에 적용되지 않습니다.<br/>
                        -발급수량 제한 쿠폰 (최대 10장 발급가능 쿠폰 등)<br/>
                        -발급가능 회원등급이 설정된 쿠폰<br/>
                        -최소 상품구매금액 제한이 설정된 쿠폰 (5만원 이상 구매 시에만 사용할 수 있는 쿠폰 등)<br/><br/>

                        가입 시 회원등급 설정과 회원등급 할인율 변경은
                        <a href="<?php echo URI_ADMIN ?>member/member_group_list.php" target="_blank" class="snote btn-link">회원>회원관리>회원등급관리</a> 에서 변경 가능합니다

                    </div>
                </td>
            </tr>
        </table>
    </div>
</form>

<div class="mgt10">
    <div class="table-title">
        DB URL
    </div>
    <table class="table table-cols">
        <thead>
        <tr>
            <th>업체</th>
            <th colspan="2">상품 DB URL</th>
        </tr>
        </thead>
        <tbody>
        <tr class="center">
            <td class="width-md" rowspan="3">다음쇼핑하우<br/>상품DB URL페이지</td>
            <td class="left">
                <?php
                $dbUrlFile = UserFilePath::data('dburl', 'daum', 'daum_all');

                echo '[전체]&nbsp;<a class="btn-link" href="' . $mallDomain . 'partner/daum_all.php" target="_blank">' . $mallDomain . 'partner/daum_all.php</a>';
                echo '&nbsp;<a class="btn btn-gray btn-sm btn-link" target="_blank" href="' . $mallDomain . 'partner/daum_all.php">미리보기</a>';
                ?>
            </td>
        </tr>
        <tr>
            <td class="left">
                <?php
                echo '[요약상품] <a class="btn-link" href="' . $mallDomain . 'partner/daum_some.php" target="_blank">' . $mallDomain . 'partner/daum_some.php</a>';
                echo '&nbsp;<a class="btn btn-gray btn-sm btn-link" target="_blank" href="' . $mallDomain . 'partner/daum_some.php">미리보기</a>';
                ?>
            </td>
        </tr>
        <tr>
            <td class="left">
                <?php
                echo '[상품평] <a class="btn-link" href="' . $mallDomain . 'partner/daum_review.php" target="_blank">' . $mallDomain . 'partner/daum_review.php</a>';
                echo '&nbsp;<a class="btn btn-gray btn-sm btn-link" target="_blank" href="' . $mallDomain . 'partner/daum_review.php">미리보기</a>';
                ?>
            </td>
        </tr>
        </tbody>
    </table>
</div>
