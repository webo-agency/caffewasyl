<input type="text" class="gdpr-color-picker" name="gdpr_popup_<?php echo $content['option'];?>" id='color-picker' value="<?php if($content['value']!=""){?>
<?= _x($content['value'], 'gdpr-framework');?>
<?php } ?>" />