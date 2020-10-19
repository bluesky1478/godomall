<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var useFlPlusReview = '<?=$useFlPlusReview?>';
        if(useFlPlusReview != 'y') {
            $('[name=naverReviewChannel][value=plusReview]').prop('disabled',true);
            $('[name=naverReviewChannel][value=both]').prop('disabled',true);
        }

        // 등급설정을 실행한다.
        naverGradeChange(<?php echo $data['naverGrade'];?>);

        $(".js-layer-naver-stats").click(function(e){
            layer_add_info('naver_stats');
        });

        $("input[name='naverVersion']").click(function () {
            if($(this).val() =='3') {
                $(".js-naver-summary-url").hide();
                $("input[name='nv_pcard']").val('');
            } else {
                $(".js-naver-summary-url").show();
            }
        });

        <?php if($data['naverVersion'] =='3') { ?>
        $(".js-naver-summary-url").hide();
        <?php } ?>

        //등급변경 처리
        $('#naverGrade').change(function(event) {
            var value = $(this).val();

            naverGradeChange(value);
        });

    });

    function naverGradeChange(value){
        if (value == '1') {
            gval='9,900';
        } else if (value == '2'){
            gval='9,900';
        } else if (value == '3'){
            gval='49,900';
        } else if (value == '4'){
            gval='99,000';
        } else if (value == '5'){
            gval='499,000';
        } else {
            gval='9,900';
        }
        $('#naverGradeCount').text(gval);
    }

    //-->
</script>

<form id="frmConfig" action="dburl_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="type" value="config"/> <input type="hidden" name="company" value="naver"/>
    <input type="hidden" name="mode" value="config"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small></small>
        </h3>
        <div class="btn-group">
            <input type="button" value="DB등록 방법 확인" class="btn btn-white" id="goodsListForCopy" onclick="window.open('https://marketing.godo.co.kr/board/view.gd?postNo=203&tab=1');"/>
            <input type="submit" value="저장" class="btn btn-red">
        </div>
    </div>
    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location);?>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>사용 여부</th>
            <td>
                <label class="radio-inline" title="네이버 공통유입 스크립트를 사용하시려면 &quot;사용함&quot;을 체크를 하시면 됩니다.!">
                    <input type="radio" name="naverFl" value="y" <?php echo gd_isset($checked['naverFl']['y']); ?> />사용함
                </label>
                <label class="radio-inline" title="네이버 공통유입 스크립트를 사용하지 않으려면 &quot;사용안함&quot;을 체크를 하시면 됩니다.!">
                    <input type="radio" name="naverFl" value="n" <?php echo gd_isset($checked['naverFl']['n']); ?> />사용안함
                </label>
            </td>
        </tr>
        <tr>
            <th>CPA 주문수집<br/> 동의여부</th>
            <td>
                <label class="checkbox-inline">
                    <input type="checkbox" name="cpaAgreement" value="y" <?php echo gd_isset($checked['cpaAgreement']['y']); ?> />사용함
                    <?php if ($data['cpaAgreement'] == 'y') { ?>
                        <span class="notice-ref notice-sm">(동의 일시 : <?= $data['cpaAgreementDt'] ?>)</span> <?php } ?>
                </label>
                <div class="notice-info mgb10">
                    네이버에서 CPA 주문수집에 동의하신 경우에만 주문완료시 주문정보를 네이버측으로 전송합니다.<br/> CPA 주문수집이 정상적으로 이루어 져야만 차후 CPA로의 과금전환이 이루어질수 있습니다.<br/> 주문수집에 동의하신뒤에는 반드시 체크하여주시기 바라며, CPA 주문수집에대한 문의는 네이버 쇼핑파트너존으로 문의주시기 바랍니다.<br/> 네이버 쇼핑파트너존 고객센터 : 1588-3819<br/>

                </div>
            </td>
        </tr>
        <tr>
            <th>네이버 쇼핑<br/>버전 설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="naverVersion" value="3" <?php echo gd_isset($checked['naverVersion']['3']); ?> />v3.0 (신규)
                </label>
                <label class="radio-inline" >
                    <input type="radio" name="naverVersion" value="2" <?php echo gd_isset($checked['naverVersion']['2']); ?> />v2.0 (기존)
                </label>
                <div class="notice-info mgb10">
                    네이버 쇼핑 버전 설정 정보와 네이버 쇼핑 파트너존>상품관리>상품정보수신현황>쇼핑몰 상품DB URL에 설정된 EP버전이 동일해야 합니다. <br/>
                    동일하게 설정되지 않은 경우 상품 정보를 수신할 수 없습니다.  <a href="http://adcenter.shopping.naver.com" target="_blank" class="btn-link">네이버 쇼핑파트너존</a>
                </div>
            </td>
        </tr>
        <tr>
            <th>네이버 쇼핑몰<br>몰등급 설정</th>
            <td>
                <div class="form-inline">
                    <label>
                        <?= gd_select_box('naverGrade', 'naverGrade', $naverGrade, null, $data['naverGrade']); ?>
                        <span class="notice-ref notice-sm">EP최대 상품수 : <span id="naverGradeCount"></span> 개</span>
                    </label>
                </div>
                <div class="notice-info">
                    선택한 네이버 쇼핑몰 몰등급에 따라 EP 생성이 제한됩니다
                </div>
                <div class="notice-info">
                    <span class="text-danger">몰등급 설정은 네이버 쇼핑파트너존의 쇼핑몰 몰등급과 동일</span>해야 합니다. 동일하지 않은 경우 <span class="text-danger">EP 수신에 실패</span>할 수 있습니다. . <a href="https://adCenter.shopping.naver.com" class="snote btn-link" target="_blank">네이버 쇼핑파트너존</a>

                </div>
                <div class="notice-info">
                    변경된 쇼핑몰 몰등급은 변경 후 다음 EP생성 시 반영됩니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>네이버 쇼핑<br/>상품 노출 설정</th>
            <td>
                <a href="/marketing/naver_goods_config.php" class="btn btn-gray btn-sm" target="_blank">네이버 쇼핑 상품 설정</a>
                <input type="button" value="노출상품 현황 확인" class="btn btn-gray btn-sm js-layer-naver-stats"/>
            </td>
        </tr>
        <tr>
            <th>상품가격 설정</th>
            <td>
                <div class="notice-info">
                    네이버 쇼핑에 노출되는 가격정보를 설정합니다. 설정 사항을 체크할 경우 쿠폰 및 할인율이 적용되어 노출됩니다.
                </div>
                <div class="notice-danger">
                    해당 기능 이용 시 상품가격이 실제 판매가랑 달라져 네이버 검수 시 반려사유가 될 수 있으므로 유의 바랍니다.
                </div>
                <br/>
                <?php if($isPlusShopTimeSale) {?>
                <div>
                    <label class="checkbox-inline"><input type="checkbox" name="dcTimeSale" value="y" <?php echo gd_isset($checked['dcTimeSale']['y']); ?>>타임세일 판매가</label>
                </div>
                <br/>
                <?php }?>
                <div>
                    <label class="checkbox-inline"><input type="checkbox" name="dcGoods" value="y" <?php echo gd_isset($checked['dcGoods']['y']); ?>>상품할인</label>
                </div>
                <br/>
                <div>
                    <span class="noline"><label class="checkbox-inline"><input type="checkbox" name="dcCoupon" value="y" <?php echo gd_isset($checked['dcCoupon']['y']); ?>>쿠폰적용</label></span>
                    <div class="notice-info">
                        쿠폰은 <a href="../promotion/coupon_list.php"  class="snote btn-link">프로모션 &gt; 쿠폰리스트 </a>에서 관리 가능합니다.
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품명 머릿말 설정</th>
            <td>
                <input type="text" name="goodsHead" class="form-control" style="width:250px;" value="<?php echo gd_isset($data['goodsHead']) ?>"/>
                <div class="notice-info">
                    상품명 머리말 설정을 위한 치환코드<br/> 머리말 상품에 입력된 "상품번호"를 넣고 싶을 때 : {_goodsNo}<br/> 머리말 상품에 입력된 "제조사"를 넣고 싶을 때 : {_maker}<br/> 머리말 상품에 입력된 "브랜드"를 넣고 싶을 때 : {_brand}
                </div>
            </td>
        </tr>
        <tr>
            <th>네이버 쇼핑<br/> 이벤트 문구 설정</th>
            <td>
                <div class="form-inline">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="naverEventCommon" value="y" <?php echo gd_isset($checked['naverEventCommon']['y']); ?>>공통 문구
                        <input type="text" name="naverEventDescription" class="form-control width-2xl js-maxlength" maxlength="100" value="<?php echo gd_isset($data['naverEventDescription']) ?>"/>
                    </label>
                </div>
                <label class="checkbox-inline">
                    <input type="checkbox" name="naverEventGoods" value="y" <?php echo gd_isset($checked['naverEventGoods']['y']); ?>>상품별 문구
                </label>
                <div class="notice-info">
                    상품별 문구는
                    <a href="/goods/goods_register.php" target="_blank" class="btn-link">상품>상품관리>상품등록</a>의 상품상세설명에서 이벤트 문구를 입력하거나,
                    <a href="/goods/excel_goods_up.php" target="_blank" class="btn-link">상품>상품엑셀관리>상품업로드</a>에서 일괄 등록할 수 있습니다.
                </div>
                <div class="notice-info">이벤트 문구는 공통문구 + 상품별문구로 구성됩니다. 이벤트 문구에서 사용할 항목을 체크하세요</div>
                <div class="notice-info">이벤트 문구 설정 후 반드시
                    <a href="http://adcenter.shopping.naver.com" target="_blank" class="btn-link">네이버 쇼핑파트너존</a>에서 등록 요청을 하셔야 하며, 담당자가 확인 후 노출 처리 됩니다.
                </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>네이버 쇼핑<br/> 무이자 할부정보</th>
            <td>
                <input type="text" name="nv_pcard" class="form-control" style="width:250px;" value="<?php echo gd_isset($data['nv_pcard']) ?>"/>
                <div class="notice-info" >
                    예) 기존 버전(v2.0) : 삼성3/현대3/국민6<br/>
                    신규 버전(v3.0) : 삼성카드^2~3|현대카드^2~3|KB국민카드^2~6
                </div>
            </td>
        </tr>
        <tr>
            <th>네이버 쇼핑<br/>상품평 개수 설정</th>
            <td>(
                <?php if($isPlusReview) {?>
                <label class="radio-inline">
                    <input type="radio" name="naverReviewChannel" value="plusReview" class="form-control"  <?php echo gd_isset($checked['naverReviewChannel']['plusReview']) ?>/>
                플러스리뷰
                </label>
                <?php }?>
                <label class="radio-inline">
                    <input type="radio" name="naverReviewChannel" value="board" class="form-control"  <?php echo gd_isset($checked['naverReviewChannel']['board']) ?>/>
                    상품후기 게시판
                </label>
                <?php if($isPlusReview) {?>
                <label class="radio-inline">
                    <input type="radio" name="naverReviewChannel" value="both" class="form-control"  <?php echo gd_isset($checked['naverReviewChannel']['both']) ?>/>
                    플러스리뷰+상품후기 게시판
                </label>
                <?php }?>
                ) 의 상품평 개수로 EP 생성
                <div class="pdt10 pdb10">
                    <label class="checkbox-inline"><input type="checkbox" name="onlyMemberReviewUsed" value="y"  <?php echo gd_isset($checked['onlyMemberReviewUsed']['y']) ?>> 비회원 상품평 미포함</label>
                </div>
                <div class="notice-info" >
                    네이버 쇼핑EP 칼럼 중 상품평 개수 값을 어떤 게시판 기준으로 생성하는지 여부를 설정합니다.
                </div>
                <div class="notice-info" >
                    플러스리뷰 상품평 개수 설정은 플러스리뷰 사용 시에만 설정이 가능합니다.
                </div>
                <div class="notice-info">
                    비회원이 작성한 상품평을 전달하는 경우 네이버쇼핑 리뷰 어뷰징 모니터링에 적발되어 강제 가퇴점 조치될 수 있는 점 참고 바랍니다.
                </div>
            </td>
        </tr>
    </table>
</form>

<?php if ($data['naverFl'] == 'y') { ?>
    <form id="frmGen" action="dburl_ps.php" method="post">
        <input type="hidden" name="type" value="gen"/> <input type="hidden" name="company" value="naver"/>
        <table class="table table-rows">
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th>상품 DB URL [페이지 미리보기]</th>
            </tr>
            </thead>
            <tbody>
            <tr class="center">
                <td class="width-md">네이버 쇼핑<br/>상품 DB URL</td>
                <td class="left">
                    <?php
                    $dbUrlFile = UserFilePath::data('dburl', 'naver', 'naver_all');

                    echo '<div>[전체상품] <a href="' . $mallDomain . 'partner/naver_all.php" target="_blank">' . $mallDomain . 'partner/naver_all.php</a> <a href="' . $mallDomain . 'partner/naver_all.php" target="_blank" class="btn btn-gray btn-sm">미리보기</a></div>';
                    echo '<div>[판매지수] <a href="' . $mallDomain . 'partner/naver_sales_index.php" target="_blank">' . $mallDomain . 'partner/naver_sales_index.php</a> <a href="' . $mallDomain . 'partner/naver_sales_index.php" target="_blank" class="btn btn-gray btn-sm">미리보기</a></div>';
                    ?>
                    <?php

                    echo '<div class="mgt5 js-naver-summary-url">[요약상품] <a href="' . $mallDomain . 'partner/naver_summary.php" target="_blank">' . $mallDomain . 'partner/naver_summary.php</a>  <a href="' . $mallDomain . 'partner/naver_summary.php" target="_blank" class="btn btn-gray btn-sm">미리보기</a></div>';

                    ?>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
<?php } ?>
<div class="center" style="margin:20px;">
    <a href="https://adcenter.shopping.naver.com/" target="_blank"/><img src="<?= PATH_ADMIN_GD_SHARE ?>img/marketing/btn_naver_go.gif"/></a>
</div>

