<form id="frmInformList" name="formSetup" action="../policy/base_agreement_ps.php" method="post">
    <input type="hidden" name="mode" value="modifyInform"/>
    <input type="hidden" name="mallSno" value="<?=$mallSno;?>"/>
    <input type="hidden" name="informCd" value=""/>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>이전 약관 노출 여부</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline" for="displayInformFlY">
                            <input id="displayInformFlY" type="radio" name="<?=$item === 'agreement' ? 'displayAgreementFl' : 'displayPrivateFl';?>" value="y" <?= $data['checked']['displayInformFl']['y']; ?> />노출함
                        </label>
                        <label class="radio-inline" for="displayInformFlN">
                            <input id="displayInformFlN" type="radio" name="<?=$item === 'agreement' ? 'displayAgreementFl' : 'displayPrivateFl';?>" value="n" <?= $data['checked']['displayInformFl']['n']; ?> />노출 안함
                        </label>
                        <p class="notice-info">이전 약관 노출 여부를 "노출함"으로 선택 시, 이전약관보기에 등록된 "쇼핑몰 노출함 상태 체크된 약관"이 쇼풍몰에 모두 노출됩니다.</p>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?=$item === 'agreement' ? '약관 ' : '개인정보처리방침<br/>';?>리스트</th>
                <td>
                    <div>
                        <div class="inform-menu">
                            <span class="width6p">번호</span>
                            <span class="width12p">쇼핑몰노출</span>
                            <span class="width55p">약관명</span>
                            <span class="width15p">등록일/수정일</span>
                            <span class="width11p">보기</span>
                        </div>
                        <div class="inform-list">
                            <?php
                            if (gd_isset($data['data']) && is_array($data['data'])) {
                                $i = count($data['data']);
                                foreach ($data['data'] as $key => $val) { ?>
                                    <div class="inform-data<?= $scrollFl === true ? ' inform-data-scroll' : '';?>">
                                        <span class="width6p center"><?=$i--;?></span>
                                        <span class="width12p center">
                                            <input type="hidden" name="beforeDisplayShopFl[<?=$val['informCd'];?>]" value="<?=$val['displayShopFl'];?>">
                                            <input type="hidden" name="displayShopFl[<?=$val['informCd'];?>]" value="n">
                                            <input type="checkbox" name="displayShopFl[<?=$val['informCd'];?>]" value="y" class="<?=$key == 0 ? 'disabled':'';?>" <?= $data['checked']['displayShopFl'][$val['informCd']];?> <?=$key == 0 ? 'onclick="return false;"':'';?>>
                                        </span>
                                        <span id="informNm<?=$val['informCd'];?>" class="width55p informNm">
                                            <?=$val['informNm'];?>
                                            <?=$key == 0 ? '<span id="enforcement" class="text-red">(현재시행중)</span>':'';?>
                                        </span>
                                        <span class="width15p center">
                                            <?=$val['regDt'];?><?=empty($val['modDt']) === false ? '<br />' . $val['modDt'] : '';?>
                                        </span>
                                        <span class="width11p center">
                                            <button class="btn btn-white btn-sm js-view-inform" data-informcd="<?=$val['informCd'];?>">보기</button>
                                        </span>
                                    </div>
                                    <?php
                                }
                            } else { ?>
                                <div class="inform-data center pd5">저장된 약관이 없습니다.</div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr name="viewArea" class="display-none">
                <th>약관명<button type="button" class="btn btn-gray btn-sm mgl20 js-modify-title modify">수정</button></th>
                <td>
                    <div id="informTitle" class="form-inline"></div>
                    <div id="modifyTitle" class="form-inline">
                        <input type="text" name="title" class="form-control js-maxlength width-2xl" maxlength="30"/>
                    </div>
                </td>
            </tr>
            <tr name="viewArea" class="display-none">
                <th>약관내용</th>
                <td>
                    <div class="form-inline">
                        <textarea id="informContent" class="form-control width100p" rows="8" readonly="readonly"></textarea>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="center">
        <input type="submit" value="저장" class="btn btn-lg btn-red">
    </div>
</form>
<script>
    <!--
    $(document).ready(function() {
        var targetInformCd = 0;
        var mallSno = $('input[name="mallSno"]', '#frmInformList').val();

        // 약관 보기
        $('.js-view-inform').on('click', function(btn) {
            btn.preventDefault();
            targetInformCd = $(btn.target).data('informcd');
            $('input[name="informCd"]', '#frmInformList').val(targetInformCd);
            var modifyBtn = $('.js-modify-title');
            if (modifyBtn.hasClass('save')) {
                $('#informTitle', '#frmInformList').show();
                $('#modifyTitle').hide();
                modifyBtn.removeClass('save').addClass('modify').text('수정');
            }
            $.post('./base_agreement_ps.php', {mode: 'view', mallSno: mallSno, informCd: targetInformCd, code: targetInformCd}, function (data) {
                $('#frmInformList input[maxlength]').maxlength({
                    showOnReady: true,
                    alwaysShow: true
                });
                $('tr[name="viewArea"]').removeClass('display-none');
                $('#informTitle', '#frmInformList').text(data.title);
                $('input[name="title"]', '#modifyTitle').val(data.title).trigger('input');
                $('#modifyTitle').hide();
                $('#informContent').text(data.content).scrollTop(0);
            });
        });

        // 약관명 수정
        $('.js-modify-title').on('click', function() {
            var $this = $(this);
            if ($(this).hasClass('modify')) {
                $('#informTitle', '#frmInformList').hide();
                $('#modifyTitle').show();
                $('.js-maxlength', '#frmInformList').trigger('maxlength.reposition');
                $(this).removeClass('modify').addClass('save').text('저장');
            } else {
                if ($('input[name="title"]', '#modifyTitle').val() === '') {
                    alert('약관명을 입력해주세요.');
                    return false;
                }
                if (targetInformCd > 0) {
                    var params ={
                        mode: 'modifyInformTitle',
                        informCd: targetInformCd,
                        informNm: $('input[name="title"]', '#modifyTitle').val(),
                        mallSno: mallSno
                    };
                    $.post('./base_agreement_ps.php', params, function (data) {
                        $('#informTitle', '#frmInformList').show().text(data.title);
                        $('#modifyTitle').hide();
                        $this.removeClass('save').addClass('modify').text('수정');
                        $('#informNm' + targetInformCd).text(data.title);
                        alert(data.message);
                    });
                }
            }
        });

        $('#frmInformList').validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                title: {
                    required: function () {
                        return $('input[name="title"]', '#frmInformList').is(':visible');
                    }
                }
            },
            messages: {
                title: {
                    required: '약관명을 입력해주세요.'
                }
            }
        });

        $('input[name="title"]', '#modifyTitle').on('keyup', function() {
            $(this).val(stripTags($(this).val()));
        });
    });
    //-->
</script>