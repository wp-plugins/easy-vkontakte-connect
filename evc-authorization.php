<?php

function evc_auth_login_url ($redirect_url = false, $echo = false) {
  add_filter('evc_share_vk_login_url_scope', 'evc_auth_login_url_scope');
  if(!$echo)
    $url = evc_share_vk_login_url ($redirect_url, $echo);
  else
    evc_share_vk_login_url ($redirect_url, $echo);
  remove_filter('evc_share_vk_login_url_scope', 'evc_auth_login_url_scope');
  
  if (isset($url))
    return $url;
}

function evc_auth_login_url_scope () {
  return '';
}

// !!!
add_action('init', 'evc_auth_authorization');  
function evc_auth_authorization () {
  
  if ( !is_admin() && false !== ( $token = evc_auth_get_token() )  ) { //!!!
    evc_auth_user_authorize($token['user_id']);
    $redirect = remove_query_arg( array('code'), $_SERVER['REQUEST_URI'] );  
    //print__r($redirect);
    wp_redirect(site_url($redirect));
    exit;
  }
}  

function evc_auth_get_token () {
  return evc_share_get_token();
}

function evc_auth_user_authorize($user_vk_id) {
  $user_wp_id = evc_get_wpid_by_vkid($user_vk_id, 'user');
  if(!$user_wp_id) {
    $user_vk_data = evc_vkapi_get_users(array('user_ids' => $user_vk_id));
    if (!$user_vk_data || !isset($user_vk_data[0]))
      return false;
    $user_wp_id = evc_add_user($user_vk_data[0]);
  }
  else
    $user_wp_id = $user_wp_id[$user_vk_id];

  if (!$user_wp_id)
    return false;
        
  wp_set_auth_cookie($user_wp_id, true);
  evc_refresh_vk_img_all ();
  
  return $user_wp_id;     
}

add_action('login_form', 'evc_auth_register_form' );
function evc_auth_register_form() {
  $options = get_option('evc_widget_auth');
  
  if (isset($options['tvc_auth_button']) && $options['tvc_auth_button'] ) {
    ?>
    <p>&nbsp;<input type="button" name="evc_vk_login" id="evc_vk_login" class="button button-primary button-large" value="Войти через ВКонтакте" onclick="location.href='<?php echo evc_auth_login_url(); ?>'" /></p>
    <br class="clear" />
   <?php
  }
}

add_action('login_form_register', 'evc_auth_login_init' );
add_action('login_form_login', 'evc_auth_login_init' );
function evc_auth_login_init() {
  if (is_user_logged_in()) {
    wp_redirect(site_url());
    exit;
  }
}
