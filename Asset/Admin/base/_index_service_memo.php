<?php if ($mainServiceAccess === true && $mainServiceMemoAccess == '') return; ?>
<!--// 관리자메모 -->
<form id="frmMemo" method="post">
    <input type="hidden" name="mode" value="memo">
    <input type="hidden" name="code" value="save">
    <input type="hidden" name="oldViewAuth">
    <div class="main-section">
        <div class="table-title btm-line">
            <span class="gd-help-manual">관리메모</span>
            <?php if ($setting['memo']['all']['isChanged'] == 'y') { ?><span class="js-memo" data-type="all"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/icon_new.png" style="margin-left:5px"></span><?php } ?>
            <?php if ($setting['memo']['self']['isChanged'] == 'y') { ?><span class="js-memo" data-type="self"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/icon_new.png" style="margin-left:5px"></span><?php } ?>
            <div class="pull-right form-inline">
                <div class="memo-action">
                    <select class="form-control" name="viewAuth">
                        <option value="all" <?php if ($setting['memo']['self']['viewAuth'] == 'all') echo 'selected' ?>>공유메모</option>
                        <option value="self" <?php if ($setting['memo']['self']['viewAuth'] == 'self') echo 'selected' ?>>개인메모</option>
                    </select>
                    <button type="button" class="btn btn-icon-fold-on js-memo-fold"><!--메모열기--></button>
                </div>
            </div>
        </div>
        <div id="adminMemo">
            <div class="js-memo" data-type="all">
                <textarea name="memo[all]" class="form-control mgb10" maxlength="1000" style="border-top: 0px;height:132px;resize: vertical;" placeholder="공유하실 운영업무 등을 기록하세요."><?= gd_isset($setting['memo']['all']['contents']) ?></textarea>
                <div class="pull-left">
                    <span style="font-size:11px;color:#777">최종수정 : <?= gd_isset($setting['memo']['all']['modDt'], '-') ?></span>
                </div>
            </div>

            <div class="js-memo" data-type="self">
                <textarea name="memo[self]" class="form-control mgb10" maxlength="1000" style="border-top: 0px;height:132px;resize:vertical;" placeholder="내가 해야할 업무 등을 기록하세요."><?= gd_isset($setting['memo']['self']['contents']) ?></textarea>
                <div class="pull-left">
                    <span style="font-size:11px;color:#777">최종수정 : <?= gd_isset($setting['memo']['self']['modDt'], '-') ?></span>
                </div>
            </div>

            <div class="pull-right">
                <button type="button" class="btn btn-white btn-sm js-memo-clear">전체삭제</button>
                <button type="button" class="btn btn-red-line-s btn-sm js-memo-save" style="margin-left:4px">저장</button>
            </div>
            <div class="clear-both"></div>
        </div>
    </div>
</form>
