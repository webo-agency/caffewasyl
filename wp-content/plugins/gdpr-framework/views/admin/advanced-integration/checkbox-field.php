<label for="gdpr_classidocs_integration">
    <input
        type="checkbox"
        name="gdpr_classidocs_integration"
        id="gdpr_classidocs_integration"
        class="js-gdpr-conditional"
        value="yes"
        <?= checked($hasclassidocs_integration, 'yes'); ?>
    >
    (<?= _x('Sign up for free here', '(Admin)', 'gdpr-framework'); ?>: <a target="_blank" href="https://www.data443.com/get-free-online-interactive-demo-team/"><?= _x('Click Here', '(Admin)', 'gdpr-framework'); ?></a>)
</label>