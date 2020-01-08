<input type="text" class="gdpr-text-field" name="gdpr_popup_allow_text" value="<?php if($content!=""){?>
<?= _x($content, 'gdpr-framework');?>
<?php } else { ?><?= _x('Accept', 'gdpr-framework');?><?php } ?>" />