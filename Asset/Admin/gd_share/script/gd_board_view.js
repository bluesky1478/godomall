/**
 * Created by LeeNamJu on 2015-11-03.
 */

$(document).ready(function () {
    $('img.js-smart-img').each(function(){
        $(this).css('max-width','100%');
    });
    $('.js-btn-memo-modify').bind('click', function () {
        var textarea = $(this).closest('tr').find('.js-textarea-modify-memo');
        var memoText = $(this).closest('tr').find('.js-text-memo');
        $('.js-textarea-reply-memo').hide();
        if (textarea.is(':visible')) {
            memoText.show();
            textarea.hide();
        }
        else {
            memoText.hide();
            textarea.show();
        }
    })

    $('.js-btn-modify').bind('click', function () {
        var memo = $(this).closest('form').find('textarea[name=modifyMemo]').val();
        $(this).closest('form').find('input[name=mode]').val('modify');
        $(this).closest('form').find('input[name=memo]').val(memo);
        $(this).closest('form').submit();
    })

    $('.js-btn-memo-reply').bind('click', function () {
        var textarea = $(this).closest('tr').find('.js-textarea-reply-memo');
        var memoText = $(this).closest('tr').find('.js-text-memo');
        memoText.show();
        $('.js-textarea-modify-memo').hide();
        if (textarea.is(':visible')) {
            $('.js-text-reply-memo').show();
            textarea.hide();
        }
        else {
            $('.js-text-reply-memo').hide();
            textarea.show();
        }
    })

    $('.js-btn-reply').bind('click', function () {
        var memo = $(this).closest('form').find('textarea[name=replyMemo]').val();
        $(this).closest('form').find('input[name=mode]').val('reply');
        $(this).closest('form').find('input[name=memo]').val(memo);
        $(this).closest('form').submit();
    })

    $('.js-btn-memo-delete').bind('click', function () {
        var form = $(this).closest('tr').find('form');
        var mode = $(this).closest('tr').find('input[name=mode]');
        BootstrapDialog.confirm({
            title: $(this).attr('title'),
            message: $(this).data('message'),
            callback: function (result) {
                if (result) {
                    mode.val('delete');
                    form.submit();
                }
            }
        });
    })


})
;

