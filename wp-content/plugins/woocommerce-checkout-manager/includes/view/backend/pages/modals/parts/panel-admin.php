<div id="tab_panel_admin" class="panel woocommerce_options_panel hidden" style="display: none;">
  <div class="options_group wooccm-premium">
    <p class="form-field">
      <label><?php esc_html_e('Listable', 'woocommerce-checkout-manager'); ?></label>
      <input <# if ( data.listable ) { #>checked="checked"<# } #> type="checkbox" name="listable" value="1">
             <span class="description hidden"><?php esc_html_e('Display in View Orders screen', 'woocommerce-checkout-manager'); ?></span>
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>                     
    <p class="form-field">
      <label><?php esc_html_e('Sortable', 'woocommerce-checkout-manager'); ?></label>
      <input <# if ( data.sortable ) { #>checked="checked"<# } #> type="checkbox" name="sortable" value="1">
             <span class="description hidden"><?php esc_html_e('Allow Sorting on View Orders screen', 'woocommerce-checkout-manager'); ?></span>
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>                   
    <p class="form-field">
      <label><?php esc_html_e('Filterable', 'woocommerce-checkout-manager'); ?></label>
      <input <# if ( data.filterable ) { #>checked="checked"<# } #> type="checkbox" name="filterable" value="1">
             <span class="description hidden"><?php esc_html_e('Allow Filtering on View Orders screen', 'woocommerce-checkout-manager'); ?></span>
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>
  </div>
</div>