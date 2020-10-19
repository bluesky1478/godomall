<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="button" value="저장" class="btn btn-red" id="btnbankdaConfig">
</div>

<div class="table-title gd-help-manual">
    미확인 입금자 등록
</div>
<form name="frmGhostDepositor" id="frmGhostDepositor" method="post" action="" onSubmit="return nsGhostDepositor.save();">
<div class="form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-3xl"/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>입금일자</th>
            <td colspan="3">
                <div class="input-group js-datepicker">
                    <input type="text" name="depositDate" value=<?=date('Ymd')?>" class="form-control width-xs">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                </div>
            </td>
        </tr>
        <tr>
            <th>입금자</th>
            <td>
                <input type="text" name="ghostDepositor" id="ghostDepositor" value="" class="form-control" />
            </td>
            <th>입금내역</th>
            <td>
                <select name="bankName" class="form-control">
                    <option value="">은행명</option>
                    <?php foreach ($rBank as $v){ ?>
                        <option value="<?php echo $v;?>"><?php echo $v;?></option>
                    <?php } ?>
                </select>
                <input type="text" name="depositPrice" id="depositPrice" value="" class="form-control" data-pattern="gdMemberId"/>원&nbsp;
                <button type="submit" class="btn btn-xs btn-gray" id="">미입금자등록</button>

            </td>
        </tr>
    </table>
</div>
</form>
<div class="notice-info notice-sm">
    확인되지 않은 입금자 리스트를 등록해 주세요. <br/> 등록된 미확인 입금자리스트가 쇼핑몰에 노출되어 입금자를 찾을 수 있습니다.
</div>
<div>&nbsp;</div>


<div class="table-title gd-help-manual">
    미확인 입금자 리스트
</div>
<form name="frmGhostDepositorListOption" id="frmGhostDepositorListOption" method="post" action="bankda_no_match_ps.php" onSubmit="return nsGhostDepositor.load();" class="js-form-enter-submit">
    <div class="search-detail-box form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>

            <tr>
                <th>검색어</th>
                <td>
                    <select name="key" class="form-control">
                        <option value="all">통합검색</option>
                        <option value="bankName">은행</option>
                        <option value="ghostDepositor">입금자</option>
                        <option value="depositPrice">입금액</option>
                    </select>
                    <input type="text" name="keyword" id="keyword" value="" class="form-control" />&nbsp;
                    <button type="submit" class="btn btn-xs btn-gray" >검색</button>
                </td>

            </tr>
            <tr>
                <th>입금일자 검색</th>
                <td>
                    <div class="form-inline">

                        <div class="input-group js-datepicker">
                            <input type="text" name="depositDate[]" value="<?php echo date('Ymd', strtotime('-6 day')); ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="depositDate[]" value="<?php echo date('Ymd'); ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>

                        <?= gd_search_date(gd_isset($search['periodFl'], 6), 'depositDate[]', false) ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div>&nbsp;</div>
</form>


<form name="frmGhostDepositorList" id="frmGhostDepositorList" method="post"  action="" target="ifrmHidden">

    <table class="table table-rows" id="oGhostDepositor">
        <colgroup>
            <col class="width-xs"/>
            <col class="width-xs"/>
            <col class="width-xl"/>
            <col class="width-xl"/>
            <col class="width-xl"/>
            <col class="width-xl"/>
        </colgroup>
        <thead>
        <tr>
            <th><input type="checkbox" id="chk_all" class="js-checkall"
                       data-target-name="chk"/></th>
            <th>번호</th>
            <th>입금일자</th>
            <th>고객명</th>
            <th>은행</th>
            <th>금액</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <div class="table-btn clearfix">
        <div class="pull-left">
            <button type="button" class="btn btn-xs btn-gray" id="btnCheckDel">선택삭제</button>
            <button type="button" class="btn btn-xs btn-gray" id="btnExcelDown">엑셀다운로드</button>
        </div>
    </div>
    <div class="center"><nav id="pageNavi"></nav></div>

</form>

<div class="table-title gd-help-manual">
    미확인 입금자 배너/팝업 노출 설정
</div>

<form name="frmGhostDepositorconfig" id="frmGhostDepositorconfig" method="post" enctype="multipart/form-data" action="./bankda_no_match_ps.php">
    <input type="hidden" name="mode" value="config">
    <input type="hidden" name="bannerFileTmp" value="<?php echo $cfgGhostDepositor['bannerFile']; ?>"/>
    <input type="hidden" name="mobileBannerFileTmp" value="<?php echo $cfgGhostDepositor['mobileBannerFile']; ?>"/>
    <div class="search-detail-box form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-3xl"/>
            </colgroup>

            <tr>
                <th>배너 사용</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="use" value="1" <?=($cfgGhostDepositor['use'] == 1) ? 'checked' : ''?>> 사용</label>
                    <label class="radio-inline"><input type="radio" name="use" value="0" <?=($cfgGhostDepositor['use'] == 0) ? 'checked' : ''?>> 미사용</label>
                </td>
            </tr>
            <tr>
                <th>리스트 노출 기간</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="expire" value="3" <?=($cfgGhostDepositor['expire'] == 3) ? 'checked' : ''?>> 3일</label>
                    <label class="radio-inline"><input type="radio" name="expire" value="7" <?=($cfgGhostDepositor['expire'] == 7) ? 'checked' : ''?>> 7일</label>
                    <label class="radio-inline"><input type="radio" name="expire" value="14" <?=($cfgGhostDepositor['expire'] == 14) ? 'checked' : ''?>> 14일</label>
                    <label class="radio-inline"><input type="radio" name="expire" value="30" <?=($cfgGhostDepositor['expire'] == 30) ? 'checked' : ''?>> 30일</label>
                    <label class="radio-inline"><input type="radio" name="expire" value="60" <?=($cfgGhostDepositor['expire'] == 60) ? 'checked' : ''?>> 60일</label>
                </td>
            </tr>
            <tr>
                <th>입금 은행 숨김</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="hideBank" value="1" <?=($cfgGhostDepositor['hideBank'] == 1) ? 'checked' : ''?>> 사용</label>
                    <label class="radio-inline"><input type="radio" name="hideBank" value="0" <?=($cfgGhostDepositor['hideBank'] == 0) ? 'checked' : ''?>> 미사용</label>
                </td>
            </tr>
            <tr>
                <th>입금 금액 숨김</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="hideMoney" value="1" <?=($cfgGhostDepositor['hideMoney'] == 1) ? 'checked' : ''?>> 사용</label>
                    <label class="radio-inline"><input type="radio" name="hideMoney" value="0" <?=($cfgGhostDepositor['hideMoney'] == 0) ? 'checked' : ''?>> 미사용</label>
                </td>
            </tr>
            <tr>
                <th>뱅크다 자동 연동</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bankdaUse" value="1" <?=($cfgGhostDepositor['bankdaUse'] == 1) ? 'checked' : ''?>> 사용</label>
                    <label class="radio-inline"><input type="radio" name="bankdaUse" value="0" <?=($cfgGhostDepositor['bankdaUse'] == 0) ? 'checked' : ''?>> 미사용</label>
                </td>
            </tr>
            <tr>
                <th>뱅크다 자동 연동 제한금액</th>
                <td>
                    <input type="text" name="bankdaLimit" value="<?=$cfgGhostDepositor['bankdaLimit']?>" class="form-control" />원
                </td>
            </tr>
            <tr>
                <th>치환코드</th>
                <td>
                    <p>
                        {ghostDepositorBanner} <button type="button" data-clipboard-text="{ghostDepositorBanner}" class="js-clipboard btn btn-xs btn-gray" title="템플릿 치환코드">복사하기</button> <br>
                        [!] 치환코드를 복사하여 PC와 모바일 각각의 디자인 메뉴에서 HTML 소스에 삽입 시 미확인 입금자 배너가 쇼핑몰에 노출됩니다.
                    </p>
                </td>
            </tr>
        </table>
    </div>

<div class="table-title gd-help-manual">
    PC 미확인 입금자 디자인 설정
</div>

<table class="table table-cols">
    <colgroup>
        <col class="width-sm"/>
        <col/>
    </colgroup>
    <tr>
        <th>
            배너
        </th>
        <td >
            <fieldset class="design-selector">
            <div>
                <label class="radio-inline"><input type="radio" name="bannerSkinType" value="select" <?=($cfgGhostDepositor['bannerSkinType'] == 'select' ? 'checked' : '')?>> 템플릿에서 선택</label>
                <label class="radio-inline"><input type="radio" name="bannerSkinType" value="direct" <?=($cfgGhostDepositor['bannerSkinType'] == 'direct' ? 'checked' : '')?>> 직접 업로드하기</label>
            </div>
            <div style="display:<?=($cfgGhostDepositor['bannerSkinType'] == 'select' ? '' : 'none')?>;" id="bannerSkinType_select" class="panel1">
                <table>
                    <tr>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_1.png"></div>
                            <div><input type="radio" name="bannerSkin" value="1" <?=($cfgGhostDepositor['bannerSkin'] == 1) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_2.png"></div>
                                <div><input type="radio" name="bannerSkin" value="2" <?=($cfgGhostDepositor['bannerSkin'] == 2) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_3.png"></div>
                                <div><input type="radio" name="bannerSkin" value="3" <?=($cfgGhostDepositor['bannerSkin'] == 3) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_4.png"></div>
                                <div><input type="radio" name="bannerSkin" value="4" <?=($cfgGhostDepositor['bannerSkin'] == 4) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_5.png"></div>
                                <div><input type="radio" name="bannerSkin" value="5" <?=($cfgGhostDepositor['bannerSkin'] == 5) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_6.png"></div>
                            <div><input type="radio" name="bannerSkin" value="6" <?=($cfgGhostDepositor['bannerSkin'] == 6) ? 'checked' : ''?>></div>
                        </td>
                </table>
            </div>
            <div style="padding: 10px; display:<?=($cfgGhostDepositor['bannerSkinType'] == 'direct' ? '' : 'none')?>;" id="bannerSkinType_direct" class="panel1">
                <table>
                    <tr>
                        <td class="form-inline">
                        <input type="file" name="bannerFile" class="form-control"> (jpg, gif, png 형식의 이미지)
                            <div>
                            <?php
                            if (empty($cfgGhostDepositor['bannerFile']) === false) {
                                echo gd_html_image(UserFilePath::data('ghost_depositor','banner', $cfgGhostDepositor['bannerFile'])->www(), '미확인입금자배너');
                                echo '<label><input type="checkbox" name="bannerFileDelete" value="y" /> 체크시 삭제</label>';
                            }
                            ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th>스킨</th>
        <td>
            <fieldset class="design-selector">
            <div>
                <label class="radio-inline"><input type="radio" name="designSkinType" value="select" <?=($cfgGhostDepositor['designSkinType'] == 'select' ? 'checked' : '')?>> 템플릿에서 선택</label>
                <label class="radio-inline"><input type="radio" name="designSkinType" value="direct" <?=($cfgGhostDepositor['designSkinType'] == 'direct' ? 'checked' : '')?>> 직접 입력하기</label>
            </div>
            <div style="padding: 5px; display:<?=($cfgGhostDepositor['designSkinType'] == 'select' ? '' : 'none')?>;" id="designSkinType_select" class="panel1">
                <table>
                    <tr>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_preview_1.png"></div>
                            <div><input type="radio" name="designSkin" value="1" <?=($cfgGhostDepositor['designSkin'] == 1) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_preview_2.png"></div>
                            <div><input type="radio" name="designSkin" value="2" <?=($cfgGhostDepositor['designSkin'] == 2) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_preview_3.png"></div>
                            <div><input type="radio" name="designSkin" value="3" <?=($cfgGhostDepositor['designSkin'] == 3) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_preview_4.png"></div>
                            <div><input type="radio" name="designSkin" value="4" <?=($cfgGhostDepositor['designSkin'] == 4) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_preview_5.png"></div>
                            <div><input type="radio" name="designSkin" value="5" <?=($cfgGhostDepositor['designSkin'] == 5) ? 'checked' : ''?>></div>
                        </td>
                        <td class="form-inline">
                            <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_preview_6.png"></div>
                            <div><input type="radio" name="designSkin" value="6" <?=($cfgGhostDepositor['designSkin'] == 6) ? 'checked' : ''?>></div>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="padding: 10px; display:<?=($cfgGhostDepositor['designSkinType'] == 'direct' ? '' : 'none')?>;" id="designSkinType_direct" class="panel1">
                <textarea name="designHtml" id="designHtml" type="editor"><?=$cfgGhostDepositor['designHtml']?></textarea><br>
            </div>
            </fieldset>
        </td>
    </tr>
</table>


<div class="table-title gd-help-manual mobileDesign" style="display:<?=($cfgGhostDepositor['ghostDepositorSame'] == 'y' ? 'none' : '')?>;">
    모바일 미확인 입금자 디자인 설정
</div>
<table class="table table-cols mobileDesign" style="display:<?=($cfgGhostDepositor['ghostDepositorSame'] == 'y' ? 'none' : '')?>;">
    <colgroup>
        <col class="width-sm"/>
        <col/>
    </colgroup>
    <tr>
        <th>
            배너
        </th>
        <td >
            <fieldset class="design-selector">
                <div>
                    <label class="radio-inline"><input type="radio" name="mobileBannerSkinType" value="select" <?=($cfgGhostDepositor['mobileBannerSkinType'] == 'select' ? 'checked' : '')?>> 템플릿에서 선택</label>
                    <label class="radio-inline"><input type="radio" name="mobileBannerSkinType" value="direct" <?=($cfgGhostDepositor['mobileBannerSkinType'] == 'direct' ? 'checked' : '')?>> 직접 업로드하기</label>
                </div>
                <div style="display:<?=($cfgGhostDepositor['mobileBannerSkinType'] == 'select' ? '' : 'none')?>;" id="mobileBannerSkinType_select" class="panel1">
                    <table>
                        <tr>
                            <td class="form-inline">
                                <div class="btn_ghost_depositor"><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_1_m.png"></div>
                                <div><input type="radio" name="mobileBannerSkin" value="1" <?=($cfgGhostDepositor['mobileBannerSkin'] == 1) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div class="btn_ghost_depositor"><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_2_m.png"></div>
                                <div><input type="radio" name="mobileBannerSkin" value="2" <?=($cfgGhostDepositor['mobileBannerSkin'] == 2) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div class="btn_ghost_depositor"><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_3_m.png"></div>
                                <div><input type="radio" name="mobileBannerSkin" value="3" <?=($cfgGhostDepositor['mobileBannerSkin'] == 3) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div class="btn_ghost_depositor"><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_4_m.png"></div>
                                <div><input type="radio" name="mobileBannerSkin" value="4" <?=($cfgGhostDepositor['mobileBannerSkin'] == 4) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div class="btn_ghost_depositor"><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_5_m.png"></div>
                                <div><input type="radio" name="mobileBannerSkin" value="5" <?=($cfgGhostDepositor['mobileBannerSkin'] == 5) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div class="btn_ghost_depositor"><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_banner_6_m.png"></div>
                                <div><input type="radio" name="mobileBannerSkin" value="6" <?=($cfgGhostDepositor['mobileBannerSkin'] == 6) ? 'checked' : ''?>></div>
                            </td>
                    </table>
                </div>
                <div style="padding: 10px; display:<?=($cfgGhostDepositor['mobileBannerSkinType'] == 'direct' ? '' : 'none')?>;" id="mobileBannerSkinType_direct" class="panel1">
                    <table>
                        <tr>
                            <td class="form-inline">
                                <input type="file" name="mobileBannerFile" class="form-control"> (jpg, gif, png 형식의 이미지)
                                <div>
                                    <?php
                                    if (empty($cfgGhostDepositor['mobileBannerFile']) === false) {
                                        echo gd_html_image(UserFilePath::data('ghost_depositor','banner', $cfgGhostDepositor['mobileBannerFile'])->www(), '미확인입금자배너');
                                        echo '<label><input type="checkbox" name="mobileBannerFileDelete" value="y" /> 체크시 삭제</label>';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th>스킨</th>
        <td>
            <fieldset class="design-selector">
                <div>
                    <label class="radio-inline"><input type="radio" name="mobileDesignSkinType" value="select" <?=($cfgGhostDepositor['mobileDesignSkinType'] == 'select' ? 'checked' : '')?>> 템플릿에서 선택</label>
                    <label class="radio-inline"><input type="radio" name="mobileDesignSkinType" value="direct" <?=($cfgGhostDepositor['mobileDesignSkinType'] == 'direct' ? 'checked' : '')?>> 직접 입력하기<u class="js-btn-preview-template" data-target="goodsTemplate">(미리보기)</u></label>
                    <div class="goodsTemplate" style="bottom:50px;left:530px;position:absolute;display:none;border:2px solid #ddd;"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/soultiond_preview.png"></div>
                </div>
                <div style="padding: 5px; display:<?=($cfgGhostDepositor['mobileDesignSkinType'] == 'select' ? '' : 'none')?>;" id="mobileDesignSkinType_select" class="panel1">
                    <table>
                        <tr>
                            <td class="form-inline">
                                <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_mobile_preview_1.png"></div>
                                <div><input type="radio" name="mobileDesignSkin" value="1" <?=($cfgGhostDepositor['mobileDesignSkin'] == 1) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_mobile_preview_2.png"></div>
                                <div><input type="radio" name="mobileDesignSkin" value="2" <?=($cfgGhostDepositor['mobileDesignSkin'] == 2) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_mobile_preview_3.png"></div>
                                <div><input type="radio" name="mobileDesignSkin" value="3" <?=($cfgGhostDepositor['mobileDesignSkin'] == 3) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_mobile_preview_4.png"></div>
                                <div><input type="radio" name="mobileDesignSkin" value="4" <?=($cfgGhostDepositor['mobileDesignSkin'] == 4) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_mobile_preview_5.png"></div>
                                <div><input type="radio" name="mobileDesignSkin" value="5" <?=($cfgGhostDepositor['mobileDesignSkin'] == 5) ? 'checked' : ''?>></div>
                            </td>
                            <td class="form-inline">
                                <div><img src="<?=PATH_ADMIN_GD_SHARE ?>img/ghost_depositor_mobile_preview_6.png"></div>
                                <div><input type="radio" name="mobileDesignSkin" value="6" <?=($cfgGhostDepositor['mobileDesignSkin'] == 6) ? 'checked' : ''?>></div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="padding: 10px; display:<?=($cfgGhostDepositor['mobileDesignSkinType'] == 'direct' ? '' : 'none')?>;" id="mobileDesignSkinType_direct" class="panel1">
                    <textarea name="mobileDesignHtml" id="mobileDesignHtml" type="editor"><?=$cfgGhostDepositor['mobileDesignHtml']?></textarea><br>
                </div>
            </fieldset>
        </td>
    </tr>
</table>
</form>


<script type="text/javascript">
    var g_jsonData = {};
    var g_page = {
        current : 1,	// 현재 페이지
        limit : 10,		// 페이지별 레코드
        pages : 10		// 끊어 보여줄 페이지수
    };

    var nsGhostDepositor = function() {

        return {

            init : function() {

                var self = this;

                $( '#bannerSkinType_' + $("input[name='bannerSkinType']:checked").val()).show();
                $( '#designSkinType_' + $("input[name='designSkinType']:checked").val()).show();

                $(".design-selector div label input").each(function() {

                      $(this).click(function () {

                          if('select' == this.value) {
                              $('#'+ this.name + "_select").show();
                              $('#'+ this.name + "_direct").hide();
                          }else{
                              $('#'+ this.name + "_select").hide();
                              $('#'+ this.name + "_direct").show();
                          }
                      });
                });

                self.load();

            },
            save : function() {

                var self = this;

                $.ajax({
                    type: "POST",
                    url: "./bankda_no_match_ps.php",
                    data: 'mode=insert&' + $('#frmGhostDepositor').serialize(),
                    dataType: "json",
                    success: function (json) {
                        console.log(json);
                        if (json.result == true)
                        {
                            self.load();
                        }

                    }
                });

                return false;

            },
            list : function(page) {

                $('#oGhostDepositor tbody tr').each(function(){
                    $(this).remove();
                });

                // 페이징 계산 및 html 생성
                g_page.current = page;
                g_page.total	= Math.ceil(g_jsonData.page.total / g_page.limit);

                if (g_page.total && g_page.current > g_page.total) g_page.current = g_page.total;
                g_page.start		= (Math.ceil(g_page.current/ g_page.pages)-1)*g_page.pages;

                g_page.navi = '<ul class="pagination pagination-sm">';

                if(g_page.current>g_page.pages){
                    g_page.navi += ' <li><a aria-label="Previous" href="javascript:nsGhostDepositor.list('+g_page.start+');" ><span aria-hidden="true">«</span></a></li>';
                }

                var i = 0;

                while(i + g_page.start < g_page.total && i < g_page.pages) {
                    i++;
                    g_page.move = i + g_page.start;
                    g_page.navi += (g_page.current == g_page.move) ? ' <li class="active"><a href="#">'+ g_page.move + '</a></li>' : ' <li><a href="javascript:nsGhostDepositor.list('+g_page.move+');">'+g_page.move+'</a></li>';
                }

                if(g_page.total>g_page.move){
                    g_page.next = g_page.move+1;
                    g_page.navi += ' <li><a aria-label="Next" href="javascript:nsGhostDepositor.list('+g_page.next+');" ><span aria-hidden="true">»</span></a></li>';
                }

                // 리스트, 페이징 출력
                var start, end, row , i, html;

                _start = (g_page.current - 1) * g_page.limit;
                _end = _start + g_page.limit;

                var no = parseInt(g_jsonData.page.total - _start);

                for (i = _start;i < _end ;i++) {

                    row = g_jsonData.body[i];

                    if (row != undefined) {

                        html = '<tr height="25" align="center">';
                        html += '<td><input type="checkbox" name="chk[]" value="'+ row.sno +'"></td>';
                        html += '<td>'+(no--)+'</td>';
                        html += '<td>'+row.depositDate+'</td>';
                        html += '<td>'+row.ghostDepositor+'</td>';
                        html += '<td>'+row.bankName+'</td>';
                        html += '<td>'+row.depositPrice.number_format()+'원</td>';
                        html += '</tr>';
                        $('#oGhostDepositor tbody').append(html);

                    }

                }
                if(!g_jsonData.page.total) $('#oGhostDepositor tbody').append('<tr height="25" align="center"><td colspan="6">검색된 정보가 없습니다.</td></tr>');

                g_page.navi += '</ul>';

                $('#pageNavi').html(g_page.navi);

            },
            load : function() {

                var self = this;

                $.ajax({
                    type: "POST",
                    url: "./bankda_no_match_ps.php",
                    data: 'mode=load&' + $('#frmGhostDepositorListOption').serialize(),
                    dataType: "json",
                    success: function (json) {
                        if (json.result == true)
                        {
                            g_jsonData = json;
                            self.list(1);
                        }

                    }
                });

                return false;
            },
            del : function() {
                var self = this;

                if ($('#frmGhostDepositorList').serialize() == '')
                {
                    alert('삭제할 항목을 선택해 주세요.');
                    return false;
                }

                var $checked = $("input[name='chk[]']:checked", $('#frmGhostDepositorList'));

                dialog_confirm('선택한 ' + $checked.length + '개의 미확인 입금자를 정말로 삭제하시겠습니까? \n삭제시 정보는 복구되지 않습니다.', function (result) {
                    if (result) {

                        $.ajax({
                            type: "POST",
                            url: "./bankda_no_match_ps.php",
                            data: 'mode=delete&' + $('#frmGhostDepositorList').serialize(),
                            dataType: "json",
                            success: function (json) {
                                self.load();
                            }
                        });
                    }
                });

            },
            download : function() {

                var inputs = '<input type="hidden" name="mode" value="download" />';
                $($('#frmGhostDepositorListOption').serialize().split('&')).each(function(){
                    var pair = this.split('=');
                    inputs+='<input type="hidden" name="'+ pair[0].replace('%5B%5D','[]') +'" value="'+ pair[1] +'" />';
                });

                var $form = $('<form></form>');
                $form.attr('action', 'bankda_no_match_ps.php');
                $form.attr('method', 'post');
                $form.attr('target', 'ifrmProcess');
                $form.appendTo('body');
                $form.append(inputs);
                $form.submit();
                $($form).remove();

            }
        }
    }();

    function iciSelect(obj)
    {
        var row = obj.parentNode.parentNode;
        row.style.background = (obj.checked) ? "#F9FFF0" : '';
    }

    function chkBoxAll(El,mode)
    {
        if (!El || !El.length) return;
        for (i=0;i<El.length;i++){
            if (El[i].disabled) continue;
            El[i].checked = (mode=='rev') ? !El[i].checked : mode;
            iciSelect(El[i]);
        }
    }

    function _fnInit() {
        nsGhostDepositor.init();
    }

    $('#btnCheckDel').click(function () {
        nsGhostDepositor.del();
    });

    $('#btnExcelDown').click(function () {
        nsGhostDepositor.download();
    });

    $("#frmGhostDepositorconfig").validate({
        submitHandler: function (form) {
            form.target = 'ifrmProcess';
            form.submit();
        }
    });

    $('#btnbankdaConfig').click(function () {
        $("#frmGhostDepositorconfig").submit();
    });

    $(document).ready(function () {
        _fnInit();
    });

    $('.js-btn-preview-template').hover(function () {
        var targetClass = $(this).data('target');
        $('.' + targetClass).show();
    }, function () {
        var targetClass = $(this).data('target');
        $('.' + targetClass).hide();
    })

</script>

















