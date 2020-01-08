<textarea name="gdpr_popup_content" rows="5" cols="40">
<?php if($content!=""){?>
<?= _x($content, 'gdpr-framework');?>
<?php }else{ ?>
<?= _x('This website uses cookies to ensure you get the best experience on our website.', 'gdpr-framework');?>
<?php } ?>
</textarea>

