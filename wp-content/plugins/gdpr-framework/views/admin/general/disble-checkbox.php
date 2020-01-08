<input
    type="checkbox"
    id=gdpr_<?php echo $content['option_name'];?>
    name=gdpr_<?php echo $content['option_name'];?>
    value="1"
    <?= checked($content['value'], true); ?>
/>
<label for="gdpr_<?php echo $content['option_name'];?>">
    <?= _x($content['option'].'.', '(Admin)', 'gdpr-framework'); ?>
</label>
