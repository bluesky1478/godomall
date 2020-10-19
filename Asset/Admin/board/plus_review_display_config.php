<style>
    .plus_review_config .js-number {
        width: 70px !important;
    }

    .plus_review_config ul li {
        margin-bottom: 5px;
    }
</style>
<div class="plus_review_config">
    <form id="frm" action="plus_review_ps.php" method="post" enctype="multipart/form-data" target="ifrmProcess">
        <input type="hidden" name="mode" value="save">
        <input type="hidden" name="type" value="view">
        <div class="page-header js-affix">
            <h3><?php echo end($naviMenu->location); ?>
                <small>플러스리뷰가 출력될 페이지에 대한 설정을 합니다.</small>
            </h3>
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>

        <div class="table-title">상품상세 페이지 설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>노출설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="goodsPageReviewFl" value="y" <?= $checked['goodsPageReviewFl']['y'] ?>>노출함</label>
                    <label class="radio-inline"><input type="radio" name="goodsPageReviewFl" value="n" <?= $checked['goodsPageReviewFl']['n'] ?>>노출안함</label>
                    <div class="notice-info">상품상세 페이지 상품리뷰 영역에 플러스리뷰를 노출하도록 설정합니다.</div>
                    <div class="notice-info">사용함 설정 시 기존 상품후기 게시판과 함께 노출됩니다. 상품후기 게시판을 사용안함으로 설정해주세요.</div>
                </td>
            </tr>
            <tr>
                <th>플러스리뷰 노출 개수 설정</th>
                <td class="form-inline">
                    <div>
                        <span class="display-inline-block width-3xs">PC</span><input type="text" name="goodsViewPageNum[front]" class="js-number form-control" value="<?= $data['goodsViewPageNum']['front'] ?>" maxlength="3"> 개
                    </div>
                    <div class="mgt10">
                        <span class="display-inline-block width-3xs">모바일</span><input type="text" name="goodsViewPageNum[mobile]" class="js-number form-control" value="<?= $data['goodsViewPageNum']['mobile'] ?>" maxlength="3"> 개
                    </div>
                    <div class="notice-info">상품 상세 화면에 노출될 플러스리뷰 개수를 설정합니다.</div>
                </td>
            </tr>
            <!--
            <tr>
                <th>템플릿 설정</th>
                <td style="position:relative">
                    <label><input type="radio" name="goodsPageTemplate" value="01" <?= $checked['goodsPageTemplate']['01'] ?>> 플러스리뷰 상세 템플릿</label>
                    <u class="js-btn-preview-template" data-target="goodsTemplate">미리보기</u>
                    <div class="goodsTemplate" style="top:0px;left:240px;position:absolute;display:none"><img
                                src="<?= PATH_ADMIN_GD_SHARE ?>image/plusreview_goods_template_01.png"></div>
                </td>
            </tr>
            -->
            <tr>
                <th>디스플레이 유형 설정</th>
                <td>
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width12p">
                            <col class="width45p">
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>상품상세 페이지</th>
                            <td style="position:relative">
                                <label><input type="radio" name="goodsPageTemplate" value="01" <?= $checked['goodsPageTemplate']['01'] ?>> 타입1 <u class="js-btn-preview-template" data-target="goodsTemplate">(미리보기)</u></label>
                                <div class="goodsTemplate" style="top:0px;left:240px;position:absolute;display:none"><img src="<?= PATH_ADMIN_GD_SHARE ?>image/plusreview_goods_template_01.png"></div>
                            </td>
                        </tr>
                        <tr>
                            <th>포토리뷰 상세(PC)</th>
                            <td style="position:relative">
                                <label><input type="radio" name="layerViewTemplate" value="01" <?= $checked['layerViewTemplate']['01'] ?>> 타입1 <u class="js-btn-preview-template" data-target="layerViewTemplate1">(미리보기)</u></label>
                                <div class="layerViewTemplate1" style="top:0px;left:240px;position:absolute;display:none"><img src="<?= PATH_ADMIN_GD_SHARE ?>image/plusreview_template_01.png"></div>
                                <div class="pdt5"></div>
                                <label><input type="radio" name="layerViewTemplate" value="02" <?= $checked['layerViewTemplate']['02'] ?>> 타입2 <u class="js-btn-preview-template" data-target="layerViewTemplate2">(미리보기)</u></label>
                                <div class="layerViewTemplate2" style="top:0px;left:240px;position:absolute;display:none"><img src="<?= PATH_ADMIN_GD_SHARE ?>image/plusreview_template_02.png"></div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <th>포토리뷰 모아보기</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="photoReviewCollectorFl" value="y" <?= $checked['photoReviewCollectorFl']['y'] ?>>노출함</label>
                    <label class="radio-inline"><input type="radio" name="photoReviewCollectorFl" value="n" <?= $checked['photoReviewCollectorFl']['n'] ?>>노출안함</label>
                    <textarea class="form-control width100p" name="photoReviewInfo" rows="5" style="margin-top:10px"><?= $data['photoReviewInfo'] ?></textarea>
                    <div class="notice-info">포토리뷰가 없을 경우 대신 출력될 텍스트 문구를 설정할 수 있습니다.</div>
                </td>
            </tr>
            <tr>
                <th class="form-inline">노출정보 설정
                </th>
                <td>
                    <label class="checkbox-inline"><input type="checkbox" name="showWriterFl" value="y" <?= $checked['showWriterFl']['y'] ?>>작성자</label>
                    <label class="checkbox-inline"><input type="checkbox" name="showRegDtFl" value="y" <?= $checked['showRegDtFl']['y'] ?>>작성일</label>
                    <div class="notice-info">설정 시 상품상세 플러스리뷰 영역 및 전체리뷰 게시판 리스트에 함께 적용됩니다.</div>
                </td>
            </tr>
        </table>


        <div class="table-title">메인 플러스리뷰 작성 팝업 설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>노출설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="popupFl" value="y" <?= $checked['popupFl']['y'] ?>>노출함</label>
                    <label class="radio-inline"><input type="radio" name="popupFl" value="n" <?= $checked['popupFl']['n'] ?>>노출안함</label>
                    <div class="notice-info">구매자가 구매완료 후 쇼핑몰 메인에 메인 플러스리뷰 작성 팝업을 띄우도록 설정합니다.</div>
                </td>
            </tr>
            <tr>
                <th>노출 시점</th>
                <td>
                    <?= gd_select_box('popupStatus', 'popupStatus', $popupStatus, ' 이후', $selected['popupStatus']); ?>
                    <div class="notice-info">플러스리뷰 게시판 설정 > 쓰기권한 설정에 따라 쓰기권한이 없는 구매자의 경우 작성 팝업이 노출되지 않습니다.</div>
                </td>
            </tr>
            <tr>
                <th>템플릿 설정</th>
                <td style="position:relative">
                    <label><input type="radio" name="popupTemplate" value="01" <?= $checked['popupTemplate']['01'] ?>>메인팝업 템플릿</label>
                    <u class="js-btn-preview-template" data-target="popupTemplate">미리보기</u>
                    <div class="popupTemplate" style="top:-250px;left:200px;position:absolute;display:none"><img
                                src="<?= PATH_ADMIN_GD_SHARE ?>image/plusreview_popup_template_01.png"></div>
                </td>
            </tr>

            <tr>
                <th>메인 팝업 노출기간 설정</th>
                <td class="form-inline">
                    <span id="authWriteStatusText">배송완료</span> 시점으로부터
                    <select name="popupPeriod">
                        <?php for ($i = 1; $i <= 15; $i++) { ?>
                            <option value="<?= $i ?>" <?= $selected['popupPeriod'][$i] ?>><?= $i ?>일</option>
                        <?php } ?>
                    </select>
                    이내만 리뷰 작성 팝업을 출력
                    <div class="notice-info">설정된 기간은 리뷰 작성 팝업 출력 시점을 포함합니다. (3일로 설정 시 1월 1일에 배송완료 or 구매확정 처리가 되면 1월 3일까지만 리뷰 작성 팝업 출력)</div>
                </td>
            </tr>
            <tr>
                <th class="form-inline">리뷰등록 완료 안내문구</th>
                <td>
                    <input type="text" name="popupReviewCompleteAlert" value="<?= $data['popupReviewCompleteAlert'] ?>" class="form-control" placeholder="회원님의 소중한 리뷰가 등록되었습니다.">
                    <div class="notice-info">리뷰등록 완료 시 출력될 알럿창 문구를 설정합니다.</div>
                </td>
            </tr>
            <tr>
                <th class="form-inline">창위치</th>
                <td class="form-inline">
                    상단에서 : <input type="text" name="popupPosition[top]" required value="<?= $data['popupPosition']['top'] ?>" class="js-number form-control">px
                    좌측에서 : <input type="text" name="popupPosition[left]" required value="<?= $data['popupPosition']['left'] ?>" class="js-number form-control"> px
                </td>
            </tr>
            <tr>
                <th class="form-inline">오늘하루 보이지 않음</th>
                <td class="form-inline">
                    <label><input type="checkbox" name="popupTodayCloseFl" value="y" <?= $checked['popupTodayCloseFl']['y'] ?>>`오늘 하루 보이지 않음`기능을 사용합니다.</label>
                </td>
            </tr>
        </table>
    </form>
</div>
<script>
    $(document).ready(function () {
        $('.js-btn-preview-template').mouseover(function () {
            var targetClass = $(this).data('target');
            $('.' + targetClass).show();
        })

        $('.js-btn-preview-template').mouseout(function () {
            var targetClass = $(this).data('target');
            $('.' + targetClass).hide();
        })

        //  메인팝업노출시점 text 처리(페이지 로드 시)
        $('#authWriteStatusText').text($('select[name=popupStatus]').find(':selected').text().replace(' 시',''));

        //  메인팝업노출시점 text 처리(change이벤트 시)
        $('select[name="popupStatus"]').change(function() {
            $('#authWriteStatusText').text($('select[name=popupStatus]').find(':selected').text().replace(' 시',''));
        })

        $("#frm").validate({
            submitHandler: function (form) {
                form.submit();
            },
            rules: {
                'goodsViewPageNum[front]': {
                    required: function () {
                        return $('[name=goodsPageReviewFl][value=y]').is(':checked');
                    },
                    min: 1
                },
                'goodsViewPageNum[mobile]': {
                    required: function () {
                        return $('[name=goodsPageReviewFl][value=y]').is(':checked');
                    },
                    min: 1
                }
            },
            messages: {
                'goodsViewPageNum[front]': {
                    required: '플러스리뷰 노출개수 설정(PC)를 입력해주세요.',
                    min: '플러스리뷰 노출 개수 설정의 게시물 수(PC)는 1이상 입력해주세요.'
                },
                'goodsViewPageNum[mobile]': {
                    required: '플러스리뷰 노출개수 설정(모바일)를 입력해주세요.',
                    min: '플러스리뷰 노출 개수 설정의 게시물 수(모바일)는 1이상 입력해주세요.'
                }
            }
        });
    })
</script>
