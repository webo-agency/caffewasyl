<ul class="wc-tabs">
  <li class="general_options active">
    <a href="#tab_panel_general"><span><?php esc_html_e('General', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <# if ( _.contains(<?php echo json_encode($option); ?>, data.type)) { #>
  <li class="options_options">
    <a href="#tab_panel_options"><span><?php esc_html_e('Options', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <# } #>
  <# if ( _.contains(<?php echo json_encode(array('select', 'multiselect')); ?>, data.type)) { #>
  <li class="price_options">
    <a href="#tab_panel_select2"><span><?php esc_html_e('Select2', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <# } #>
  <li class="display_options">
    <a href="#tab_panel_display"><span><?php esc_html_e('Display', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <# if ( !_.contains(<?php echo json_encode(array_merge($option, $template)); ?>, data.type)) { #>
  <li class="price_options">
    <a href="#tab_panel_price"><span><?php esc_html_e('Price', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <# } #>
  <# if (data.type == 'time') { #>
  <li class="timepicker_options">
    <a href="#tab_panel_timepicker"><span><?php esc_html_e('Timepicker', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <# } #>
  <# if (data.type == 'date') { #>
  <li class="datepicker_options">
    <a href="#tab_panel_datepicker"><span><?php esc_html_e('Datepicker', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <# } #>
  <li class="admin_options">
    <a href="#tab_panel_admin"><span><?php esc_html_e('Admin', 'woocommerce-checkout-manager'); ?></span></a>
  </li>
  <!--  <li class="suggestions_options">
      <a href="#tab_panel_suggestions"><span><?php esc_html_e('Suggestions', 'woocommerce-checkout-manager'); ?></span></a>
    </li>-->
</ul>