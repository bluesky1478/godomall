<form name="insgoForm" id="insgoForm" method="post" action="./insgo_widget_ps.php" target="ifrmProcess">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <input type="button" value="위젯 등록" class="btn btn-red-line js-btn-regist"/>
    </div>
    <div class="panel panel_center pd10">
        <p><b>인스고위젯이란?</b></p>
        <p>
            SNS 서비스인 인스타그램의 컨텐츠를 내 쇼핑몰 화면에 불러와 보여줄 수 있는 위젯서비스입니다.<br />
            쇼핑몰의 인스타그램 계정을 만들고, 인스타그램에 쇼핑몰을 홍보할 수 있는 컨텐츠를 등록하세요.<br />
            인스고위젯에서 쇼핑몰의 인스타그램 계정을 연동하여 컨텐츠를 노출할 수 있습니다.<br />
            쇼핑몰에 방문한 회원들과 쇼핑몰 인스타그램을 통해 소통해보세요.<br />
            (인스타그램 계정이 없으신 경우 먼저 <a href="http://www.instagram.com" target="_blank">http://www.instagram.com</a> 사이트를 방문하여 가입해주세요.)
        </p>
    </div>
    <div class="table-title">인스고위젯 관리</div>
    <div>
        <table class="table table-rows">
            <thead>
            <tr>
                <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
                <th class="width5p">번호</th>
                <th class="width40p">위젯명</th>
                <th class="width-2xs">등록일</th>
                <th class="width5p">등록자</th>
                <th class="width5p">위젯타입</th>
                <th class="width5p">레이아웃</th>
                <th class="width5p">썸네일사이즈</th>
                <th class="width10p">치환코드 복사</th>
                <!--<th class="width5p">미리보기</th>-->
                <th class="width5p">수정</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                $i = sizeof($data);
                foreach ($data as $key => $val) {
                    ?>
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="sno[<?php echo $val['sno']; ?>]" value="<?php echo $val['sno']; ?>" />
                        </td>
                        <td class="center number"><?php echo $i; ?></td>
                        <td class="center"><?php echo $val['insgoName'] ?></td>
                        <td class="center date"><?php echo $val['regDt'] ?></td>
                        <td class="center"><?php echo $val['insgoManagerId'] ?><br />(<?php echo $val['insgoManagerNm'] ?>)</td>
                        <td class="center"><?php echo $widgetType[$val['insgoDisplayType']] ?></td>
                        <td class="center"><?php echo ($val['insgoWidthCount'] && $val['insgoWidthCount']) ? ($val['insgoWidthCount'] . 'x' . $val['insgoHeightCount']) : '-' ?></td>
                        <td class="center"><?php echo $val['insgoThumbnailSize'] == 'auto' ? '자동맞춤' : $val['insgoThumbnailSizePx'] . 'px' ?></td>
                        <td class="center">
                            <a href="#" class="btn btn-gray btn-sm btn-preview" title="{=includeWidget('proc/_insgo.html', 'sno', '<?=$val['sno']?>')}">코드보기</a>
                            <button type="button" title="<?php echo $val['insgoName'] ?>" class="btn btn-white btn-sm btn-copy js-clipboard" data-clipboard-text="{=includeWidget('proc/_insgo.html', 'sno', '<?=$val['sno']?>')}">복사</button>
                        </td>
                        <!--<td class="center">
                            <input type="button" value="미리보기" class="btn btn-white btn-sm js-btn-preview" data-sno="<?php echo $val['sno']; ?>">
                        </td>-->
                        <td class="center">
                            <a href="./insgo_widget_config.php?sno=<?php echo $val['sno']; ?>" class="btn btn-white btn-sm">수정</a>
                        </td>
                    </tr>
                    <?php
                    $i--;
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="11">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <div class="table-action">
            <div class="pull-left">
                <input type="hidden" name="mode" />
                <button type="button" class="btn btn-white checkDelete">선택 삭제</button>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(function () {
        $(".js-btn-regist").click(function() {
            location.href = "./insgo_widget_config.php";
        });

        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert("선택된 위젯이 없습니다");
                return;
            }

            dialog_confirm("선택한 위젯을 삭제하시겠습니까?\n삭제 시 쇼핑몰 화면에서도 해당 위젯이 노출되지 않습니다.", function (result) {
                if (result) {
                    $("#insgoForm input[name='mode']").val("delete");
                    $("#insgoForm").submit();
                }
            });
        });

        var option = {
            trigger: 'hover',
            container: '#content',
        };
        $('.btn-preview').tooltip(option);

        /**
         * 인스고위젯 미리보기
         *
         */
        $('.js-btn-preview').click(function() {
            $('.js-btn-preview').prop('disabled', true);
            var sno = $(this).data('sno');
            var title = "인스고위젯 미리보기";
            $.get('./insgo_widget_preview.php',{ mode : 'list', sno : sno }, function(data){

                data = '<div id="viewInfoForm">'+data+'</div>';

                var layerForm = data;

                BootstrapDialog.show({
                    title:title,
                    size: get_layer_size('wide-lg'),
                    message: $(layerForm),
                    closable: true
                });
            });
        });

        $(document).on('click', '.modal, .modal .close', function(){
            $('.js-btn-preview').prop('disabled', false);
        });
    });
</script>