jQuery(function ($) {
    $(".notice.is-dismissible").each(function () {
        var $this = $(this),
            $button = $(
                '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>'
            ),
            btnText = wp.i18n.dismiss || "";

        $button.find(".screen-reader-text").text(btnText);
        $this.append($button);

        $button.on("click.notice-dismiss", function (event) {
            event.preventDefault();

            $this.fadeTo(100, 0, function () {
                $(this).slideUp(100, function () {
                    $(this).remove();

                    $.post(wcdn_ts_dismiss_notice.ts_admin_url, {
                        action:
                            wcdn_ts_dismiss_notice.ts_prefix_of_plugin + "_tracker_dismiss_notice",
                        tracking_notice: wcdn_ts_dismiss_notice.tracking_notice,
                    });
                });
            });
        });
    });
});
