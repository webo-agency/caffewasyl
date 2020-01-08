<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<div id="webarx-cookie-notice" role="banner" style="opacity: <?php echo ($this->get_option('webarx_cookie_notice_opacity') < 100 ? '.' : '') . $this->get_option('webarx_cookie_notice_opacity'); ?>;color: #<?php echo $this->get_option('webarx_cookie_notice_textcolor'); ?>; background-color: #<?php echo $this->get_option('webarx_cookie_notice_backgroundcolor'); ?>; visibility: hidden;">
    <div class="webarx-cookie-notice-container">
        <div class="webarx-cn-notice-text-container">
            <span id="webarx-cn-notice-text">
                <?php
                    echo $this->get_option('webarx_cookie_notice_message');
                    echo ($this->get_option('webarx_cookie_notice_privacypolicy_enable') == 1 ? ' - <a class="webarx-cn-notice-link" style="text-decoration: underline !important; color: #' . $this->get_option('webarx_cookie_notice_textcolor') . '; " href="' . $this->get_option('webarx_cookie_notice_privacypolicy_link') . '">' . $this->get_option('webarx_cookie_notice_privacypolicy_text') . '</a>' : '');
                ?>
            </span>
        </div>
        <div class="webarx-cn-notice-button-container">
            <button style="border-color: #<?php echo $this->get_option('webarx_cookie_notice_textcolor'); ?>; color: #<?php echo $this->get_option('webarx_cookie_notice_textcolor'); ?>; <?php echo ($this->get_option('webarx_cookie_notice_credits') == 1 ? ' margin-bottom: 20px; ' : ' '); ?>" onclick="setCookieForNotice('<?php echo $this->get_option('webarx_cookie_notice_cookie_expiration'); ?>')" id="webarx-cn-accept-cookie" data-cookie-set="accept" class="webarx-cn-set-cookie webarx-cn-button button"><?php echo $this->get_option('webarx_cookie_notice_accept_text'); ?></button>
            <?php
                echo ($this->get_option('webarx_cookie_notice_credits') == 1 ? '<a class="webarx-cn-protected-by" target="_blank" style="color: #' . $this->get_option('webarx_cookie_notice_textcolor') . ';" href="https://www.webarxsecurity.com/"><img style="width:13px; float: left; margin-right: 5px;" src="' . $this->plugin->url . '/assets/images/icon.svg" alt=""> Protected by WebARX</a>' : " ");
            ?>
        </div>
    </div>
</div>