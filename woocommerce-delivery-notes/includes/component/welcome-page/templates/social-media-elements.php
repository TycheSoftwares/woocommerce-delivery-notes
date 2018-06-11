<?php
/**
 * Welcome page on activate or updation of the plugin
 */
?>
<div class="social-items-wrap">
    <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Ftychesoftwares&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=220596284639969" scrolling="no" frameborder="0" style="border:none;overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
    <a href="https://twitter.com/tychesoftwares" class="twitter-follow-button" data-show-count="false"><?php
        printf(
            esc_html_e( 'Follow %s', 'tychesoftwares' ),
            '@tychesoftwares'
        );
    ?></a>
    <script>!function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
            if (!d.getElementById(id)) {
                js = d.createElement(s);
                js.id = id;
                js.src = p + '://platform.twitter.com/widgets.js';
                fjs.parentNode.insertBefore(js, fjs);
            }
        }(document, 'script', 'twitter-wjs');
    </script>
</div>