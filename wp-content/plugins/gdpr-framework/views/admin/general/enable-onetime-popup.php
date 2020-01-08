<input
    type="checkbox"
    id="gdpr_onetime_popup"
    name="gdpr_onetime_popup"
    value="1"
    <?= checked($enabled, true); ?>
/>
<label for="gdpr_onetime_popup">
    <?= _x('Enable One Time Cookie Acceptance Popup', '(Admin)', 'gdpr-framework'); ?>
</label>
