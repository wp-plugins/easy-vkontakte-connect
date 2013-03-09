<?php

define('EVC_API_URL','https://api.vk.com/method/');


register_activation_hook(__FILE__,'evc_activate');
function evc_activate (){
  $use_smilies = get_option('use_smilies');
  if ($use_smilies)
    update_option('use_smilies', false);   
  
  $options = get_option('evc_options');
  
  $defaults = array(
    'autopublish' => 0,
    'from_group' => 'enable',
    'add_link' => 'enable',
    'upload_photo_count' => 4,
    'excerpt_length' => 25,
    'excerpt_length_strings' => 2688,
    'message' => "%title%\n\n%excerpt%"
  );  
  if (!$options) {
    update_option('evc_options', $defaults);     
  }  
  else {
    foreach($defaults as $key => $value) {
      if (in_array($key, array('from_group', 'add_link', 'signed')) && !isset($options[$key]))
        unset ($defaults[$key]);   
    }
    update_option('evc_options', wp_parse_args($options, $defaults));     
  }
}


// load all the subplugins
add_action('plugins_loaded','evc_plugin_loader');
function evc_plugin_loader() {
  include_once('evc-stats.php');
}


// add the theme page
add_action('admin_menu', 'evc_add_page');
function evc_add_page() {
  global $evc_options_page;

  add_menu_page( 'Easy VKontakte Connect', 'Easy VK Connect', 'activate_plugins', 'evc', 'evc_options_page', '', '99.02' ); 
  $page = add_submenu_page( 'evc', 'Options Easy VKontakte Connect', 'Options', 'activate_plugins', 'evc', 'evc_options_page' );
}


// add the admin settings and such
add_action('admin_init', 'evc_admin_init'); 
function evc_admin_init(){
  global $evc_options_page; 
  evc_activate();
  $options = get_option('evc_options');


  
  if (empty($options['app_id']) || empty($options['page_id']) || empty($options['access_token'])) {
    add_action('admin_notices', create_function( '', "echo '<div class=\"error\"><p>".sprintf(__('Необходимо настроить плагин Easy VKontakte Connect на его <a href="%s">странице</a>.', 'evc'), admin_url('options-general.php?page=evc'))."</p></div>';" ) );
  }
 
  // Isotope DEPRECATED because:
  // https://make.wordpress.org/plugins/2012/12/20/gpl-and-the-repository/ 
  //wp_enqueue_script('jquery.isotope', plugins_url('js/jquery.isotope.min.js' , __FILE__), array('jquery', 'jquery-masonry'));   
  wp_enqueue_script('evc', plugins_url('js/evc.js' , __FILE__), array('jquery', 'jquery-masonry'), '1.0', true);  
  wp_enqueue_script('bootstrap', plugins_url('js/bootstrap.min.js' , __FILE__), array('jquery'), '2.2.2', true);  
  wp_enqueue_script('tinysort', plugins_url('js/jquery.tinysort.js' , __FILE__), array('jquery'), true); 

  register_setting( 'evc_options', 'evc_options', 'evc_options_validate' );  
  
  add_settings_section('evc_main', __('Основные настройки', ''), 'evc_main_text', 'evc');
  add_settings_field('evc_app_id', 'ID приложения', 'evc_app_id', 'evc', 'evc_main');
  add_settings_field('evc_access_token', 'Access Token', 'evc_access_token', 'evc', 'evc_main');
  add_settings_field('evc_page_id', 'ID страницы ВКонтакте', 'evc_page_id', 'evc', 'evc_main');
  add_settings_field('evc_autopublish', 'Автопубликация', 'evc_autopublish', 'evc', 'evc_main');

  add_settings_section('evc_wall', __('На стене ВКонтакте', ''), 'evc_wall_text', 'evc');
  add_settings_field('evc_from_options', 'Сообщение ВКонтакте', 'evc_wall_options', 'evc', 'evc_wall');
 
  add_settings_section('evc_publish', __('Настройки публикации', ''), 'evc_publish_text', 'evc');
  add_settings_field('evc_exclude_cats', 'Исключить категории', 'evc_exclude_cats', 'evc', 'evc_publish');
  add_settings_field('evc_photo_count', 'Изображения', 'evc_photo_count', 'evc', 'evc_publish');
  add_settings_field('evc_excerpt_length', 'Анонс', 'evc_excerpt_length', 'evc', 'evc_publish');
  add_settings_field('evc_message_mask', 'Сообщение', 'evc_message', 'evc', 'evc_publish'); 
 
}


function evc_main_text() {
  $options = get_option('evc_options');  
  ?>
  <p>Основные настройки плагина Easy VKontakte Connect</p>
  <?php  
}

function evc_app_id() {
  $options = get_option('evc_options');
  $url = get_bloginfo('wpurl');

  $out = '<p>Чтобы получить <strong>ID приложения</strong>, необходимо <a href="http://vk.com/editapp?act=create" target="_blank">создать приложение</a> со следующими настройками:</p>
  <ol>
    <li><strong>Название:</strong> любое</li>
    <li><strong>Тип:</strong> Standalone-приложение</li>
  </ol>
  <p>В настройках приложения необходимо установить параметры в разделе <strong>Open API</strong>:</p>
  <ol>
    <li><strong>Адрес сайта:</strong> ' . $url .'</li>
    <li><strong>Базовый домен:</strong> '. basename($url) .'</li>
  </ol>
  <p>Если приложение с этими настройками у вас было создано ранее, вы можете найти его на <a href="http://vk.com/apps?act=settings" target="_blank">странице приложений</a> и, нажав "Редактировать", найти его ID.</p>
  '; 
  if (empty($options['app_id']) || 1 == 1)
    echo $out;

  echo "<input type='text' id='evcappid' name='evc_options[app_id]' value='{$options['app_id']}' size='40' /> (required)";  
}

function evc_access_token() {
  $options = get_option('evc_options');

  ?>  
  <script type="text/javascript">     
    jQuery("#evcappid").change( function() {
      jQuery('#getaccesstokenurl').attr({'href': 'http://oauth.vk.com/authorize?client_id='+ jQuery(this).val().trim() +'&scope=wall,photos,offline&redirect_uri=http://api.vk.com/blank.html&display=page&response_type=token', 'target': '_blank'});
    });   
  </script>
  
  <?php 
  $get_access_token_url = (!empty($options['app_id'])) ? 'http://oauth.vk.com/authorize?client_id='.$options['app_id'].'&scope=wall,photos,offline&redirect_uri=http://api.vk.com/blank.html&display=page&response_type=token' : 'javascript:void(0);';
      
  echo '<p>Чтобы получить <strong>Access Token</strong></p>
  <ol>
    <li>пройдите по <a href="'.$get_access_token_url.'" id = "getaccesstokenurl">ссылке</a>,</li>
    <li>подтвердите уровень доступа,</li>
    <li>скопируйте url открывшейся страницы в поле внизу.</li>
  </ol>'; 

  echo "<input type='text' id='evcaccesstokenurl' name='evc_options[access_token_url]' value='' size='40' />";    
  
  if (!empty($options['access_token']))
    echo "<br/><input type='text' id='evcaccesstoken' name='evc_options[access_token]' value='".trim($options['access_token'])."' size='40' /> ";  
}

function evc_page_id() {
  $options = get_option('evc_options');
  
  echo '<p>Вы можете создать для сайта <a href="http://vk.com/public.php?act=new" target="_blank">новую страницу</a> ВКонтакте или, если страница уже есть, найти ее среди ваших <a href="http://vk.com/public.php?act=newY" target="_blank">созданных страниц</a>. Чтобы увидеть page_id, нажмите "Рекламировать страницу", page_id - будет в адресной строке.</p>'; 
  echo "<input type='text' id='evcpageid' name='evc_options[page_id]' value='{$options['page_id']}' size='40' /> (required)";  
}


function evc_autopublish () {
  $options = get_option('evc_options');  
  ?>
  <ul>
  <li><label><input type="radio" name="evc_options[autopublish]" value="1" <?php checked(1, $options['autopublish']); ?> /> <?php _e('Включено', 'evc'); ?></label></li>
  <li><label><input type="radio" name="evc_options[autopublish]" value="0" <?php checked(0, $options['autopublish']); ?> /> <?php _e('Выключено', 'evc'); ?></label></li>
  </ul>
  <p>Автоматическая публикация новых материалов на стене ВКонтакте</p>
  <?php 
}


function evc_wall_text() {
  $options = get_option('evc_options');  
  ?>
  <p>Как сообщение будет выглядеть на стене ВКонтакте</p>
  <?php
}

function evc_wall_options () {
  $options = get_option('evc_options');  
  ?>  
  <p><label><input type="checkbox" name="evc_options[from_group]" value="enable" <?php @checked('enable', $options['from_group']); ?> /> Опубликовать пост от имени группы (или от имени пользователя)</label>
  <br/><label><input type="checkbox" name="evc_options[signed]" value="enable" <?php @checked('enable', $options['signed']); ?> /> Добавить к сообщению пользователя, опубликовавшего пост</label>
  <br/><label><input type="checkbox" name="evc_options[add_link]" value="enable" <?php @checked('enable', $options['add_link']); ?> /> Добавить ссылку на статью на сайте</label></p>
  <?php
}


function evc_publish_text() {
  $options = get_option('evc_options');  
  ?>
  <p>Какие данные из статьи включить в сообщение на стене ВКонтакте</p>
  <?php  
}

function evc_exclude_cats () {
  $options = get_option('evc_options');  
  echo '<div class = "categorydiv"><div class = "tabs-panel" style = "height:auto; max-height:200px;"><ul id="categorychecklist" class="list:category categorychecklist form-no-clear">';
  wp_terms_checklist( 0, array(
    'selected_cats'=>$options['exclude_cats'], 
    'walker' => new EVC_Walker_Checklist(),
    'checked_ontop' => false
  ));
  echo '</ul></div></div>';
  echo '<p>Статьи из отмеченных категорий не будут автоматически опубликованы на стене ВКонтакте</p>';      
}

function evc_photo_count() {
  $options = get_option('evc_options'); 
  ?>
  <select name="evc_options[upload_photo_count]" id="evc_upload_photo_count">
  <?php for($i = 0; $i < 6; $i++ ){ ?>
    <option value="<?php echo $i; ?>"<?php selected($i, $options['upload_photo_count']); ?>><?php echo $i; ?></option>
  <?php } ?>
  </select>
  <?php  
  echo '<p>Сколько изображений из статьи прикрепить к сообщению ВКонтакте?</p>';
}

function evc_excerpt_length () {
  $options = get_option('evc_options');   
  echo '<input type="text" class="small-text" value="'.$options['excerpt_length'].'" name="evc_options[excerpt_length]">';
  echo '<p>Сколько слов из статьи опубликовать в качестве анонса ВКонтакте?</p>';
  echo '<input type="text" class="small-text" value="'.$options['excerpt_length_strings'].'" name="evc_options[excerpt_length_strings]">';  
  echo '<p>Сколько <strong>знаков</strong> из статьи опубликовать в качестве анонса ВКонтакте? 
  <br/><strong>Не рекомендуется</strong> больше 2688.</p>';
}

function evc_message () {
  $options = get_option('evc_options');
  ?>
  <p><label>
  <textarea cols="50" rows="3" name="evc_options[message]"><?php echo esc_textarea($options['message']); ?></textarea></label><br/>Маска сообщения на стене ВКонтакте:</p>
  <ul><li><strong>%title%</strong> - заголовок статьи,</li>
  <li><strong>%excerpt%</strong> - анонс статьи,</li>
  <li><strong>%link%</strong> - ссылка на статью.</li></ul>
  <?php
}

function evc_options_validate ($input) {
  
  if(!empty($input['access_token_url'])) {
    $url = explode('#', $input['access_token_url']);
    $params = wp_parse_args($url[1]);
    $input['access_token'] = $params['access_token'];
  }
  
  if(!isset($input['post_category']) || empty($input['post_category']))
    $input['exclude_cats'] = array();
  else   
    $input['exclude_cats'] = $input['post_category'];
  unset($input['post_category']);
    
  return $input;    
}


class EVC_Walker_Checklist extends Walker {
  var $tree_type = 'category';
  var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

  function start_lvl(&$output, $depth, $args) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent<ul class='children'>\n";
  }

  function end_lvl(&$output, $depth, $args) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent</ul>\n";
  }

  function start_el(&$output, $category, $depth, $args) {
    extract($args);
    if ( empty($taxonomy) )
      $taxonomy = 'category';

    if ( $taxonomy == 'category' )
      $name = 'evc_options[post_category]';
    else
      $name = 'evc_options[tax_input]['.$taxonomy.']';

    $class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
    $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $category->name )) . '</label>';
  }

  function end_el(&$output, $category, $depth, $args) {
    $output .= "</li>\n";
  }
}

// display the admin options page
function evc_options_page() {
?>
  <div class="wrap">
    <h2><?php _e('Easy VKontakte Connect', 'evc'); ?></h2>
    <p><?php _e('Настройки плагина Easy VKontakte Connect.', 'evc'); ?></p>
    
    <form method="post" action="options.php">
      <?php settings_fields('evc_options'); ?>
      <table><tr>
        <td style='vertical-align:top;'>
          <?php do_settings_sections('evc'); ?>
        </td>
        <td style='vertical-align:top; width:25%; '>
          <div style='width:20em; float:right; background: #ffc; border: 1px solid #333; margin: 2px; padding: 5px'>
            <h3 align='center'><?php _e('Хотите Больше Возможностей?',''); ?></h3>
            <p>Предложения о дополнительных возможностях оставляйте на <a href = "http://ukraya.ru/tag/easy-vkontakte-connect">сайте плагина</a>.</p>
          </div>

        </td>
      </tr></table>
      
      <?php submit_button(); ?>
    </form>
  </div>
<?php
}

add_action('post_submitbox_misc_actions','evc_wall_post_check_box');
function evc_wall_post_check_box() {
  global $post;
  $options = get_option('evc_options');
  
  $captcha = get_post_meta($post->ID, '_evc_wall_post_captcha', true);
  if (isset($captcha) && !empty($captcha))
    $options['wall_post_flag'] = true;
?>
<div class="misc-pub-section">
<p><input type="checkbox" <?php checked($options['wall_post_flag'],true); ?> name="evc_wall_post" /> Опубликовать на стене ВКонтакте (EVC)</p>

<?php
  if (isset($captcha) && !empty($captcha)) {
?>
<p><span style = "color: #FF0000; border-bottom: 1px solid #FF0000;">Не опубликовано!</span>
<br/><img src = "<?php echo $captcha['img']; ?>" style = "margin:10px 0 3px;" />
<br/><input type="hidden" name="captcha_sid" value="<?php echo $captcha['sid']; ?>"><input type="text" value="" autocomplete="off" size="16" class="form-input-tip" name="captcha_key">
<br/>Введите текст с картинки, чтобы опубликовать запись ВКонтакте.</p>
<?php 
  }
?>
</div>
<?php
}

// this function prevents edits to existing posts from auto-posting
add_action('transition_post_status','evc_publish_auto_check',10,3);
function evc_publish_auto_check($new, $old, $post) {
  $options = get_option('evc_options');   
  if (($new == 'publish' && $old != 'publish' && $options['autopublish']) || ($_POST['evc_wall_post'] && $new == 'publish') ) {  
    if (!isset($options['exclude_cats']) || empty($options['exclude_cats']) || !in_category($options['exclude_cats'], $post))
      evc_wall_post($post->ID, $post);
  }
}

function evc_wall_post ($id, $post) {

  $options = get_option('evc_options');   
  
  // Post to wall 
  $m = array();
  preg_match_all('/%([\w-]*)%/m', $options['message'], $mt, PREG_PATTERN_ORDER);
 
  if (in_array('title', $mt[1])) {
    $m['%title%'] = get_the_title($post->ID);
    $m['%title%'] = strip_tags($m['%title%']);
    $m['%title%'] = html_entity_decode($m['%title%'], ENT_QUOTES, 'UTF-8');
    $m['%title%'] = htmlspecialchars_decode($m['%title%']);
  } 

  if (in_array('excerpt', $mt[1]))
    $m['%excerpt%'] = evc_make_excerpt($post); 
  
  if (in_array('link', $mt[1]))
    $m['%link%'] = apply_filters('evc_publish_permalink', null, $post->ID);    

  $message = str_replace( array_keys($m), array_values($m), $options['message'] );
  $message = strip_tags($message);
  $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
  $message = htmlspecialchars_decode($message);  
 
  $permalink = $options['add_link'] ? apply_filters('evc_publish_permalink', wp_get_shortlink($post->ID), $post->ID) : ''; 
  
  $attach = array();
  $images = evc_upload_photo($id, $post);  
  if ($images['i'])
    $attach[] = implode(',',$images['i']);
  if (!empty($permalink))
    $attach[] = $permalink;
  
  $params = array();
   
  $params = array(
    'access_token' => $options['access_token'],  
    'owner_id' => apply_filters('evc_wall_post_gid', '-' . $options['page_id'], $post),
    // 1: from group name; 0: from username
    'from_group' => $options['from_group'], 
    // add username to post?
    'signed' => isset($options['signed']) && !empty($options['signed']) ? $options['signed'] : 0,
    'message' => $message,     
    // if no attachments - 'message' is available
    //'attachments' => $attachments 

  ); 
  
  // CAPTCHA
  if (isset($_POST['captcha_sid']) && isset($_POST['captcha_key']) && !empty($_POST['captcha_sid']) && !empty($_POST['captcha_key']) ) {
    $params['captcha_sid'] = $_POST['captcha_sid'];
    $params['captcha_key'] = trim($_POST['captcha_key']);
  }
  
  if (!empty($attach))
    $params['attachments'] = implode(',', $attach);
    
  $query = http_build_query($params);  
  
  $data = wp_remote_get(EVC_API_URL.'wall.post?'.$query, array('sslverify' => false)); 
  
  if (is_wp_error($data))
    return $data->get_error_message();
  
  $resp = json_decode($data['body'],true);  

  if ($resp['error']) {
    if ($resp['error']['error_code'] == 14) {
      $captcha = array(
        'sid' => $resp['error']['captcha_sid'],
        'img' => $resp['error']['captcha_img']
      );
      update_post_meta($post->ID, '_evc_wall_post_captcha', $captcha);
          
      if (!isset($options['error_email'])) {
        // Admin Notification
        $from = array('From:"'.get_option('blogname').'" <'.get_option('admin_email').'>');  

        $message  = 'Запись ВКонтакте не опубликована' . "\r\n\r\n";
        $message  .= 'Требуется captcha (пройдите по ссылке): ' . admin_url('post.php?post='.$post->ID.'&action=edit') . "\r\n\r\n";
        $message  .= 'ВНИМАНИЕ!'."\r\n".'Это уведомление отправляется только ОДИН раз.'."\r\n".'Пока не введена captcha дальнейшая публикация записей ВКонтакте НЕВОЗМОЖНА.';
        $message  .= "\r\n\r\n".'Ответы на ваши вопросы можно найти тут: .';
        $message  .= "\r\n\r\n".'Спасибо, что выбрали Easy VKontakte Connect (EVC), пожалуй один из лучших плагинов интеграции с ВКонтактом )';

        @wp_mail(get_option('admin_email'), 'EVC: требуется captcha!', $message, $from);

        $options['error_email'] = current_time('timestamp', 1);
      }
    }
  }
  else {
    delete_post_meta($post->ID, '_evc_wall_post_captcha');
    unset($options['error_email']);  
  }
  update_option('evc_options', $options);     

  // Wall Post with link  
  if ($resp['response']['processing'] || $resp['response']['post_id']) {
    update_post_meta($post->ID, '_evc_wall_post', date("Y-m-d H:i:s"));
    update_post_meta($post->ID, '_evc_wall_post_id', $resp['response']['post_id']);
  }

  return true;
}

function evc_upload_photo($id, $post) {
  
  $options = get_option('evc_options');
  
  if (!$options['upload_photo_count'])
    return false;
  if ($options['upload_photo_count'] > 5)
    $options['upload_photo_count'] = 5;
    
  // Find first 5 attached images  
  $post_images = get_children( array( 
    'post_parent' => $post->ID, 
    'post_status' => 'inherit',     
    'post_type' => 'attachment', 
    'post_mime_type' => 'image', 
    'orderby' => 'menu_order id', 
    'order' => 'ASC', 
    'numberposts' => $options['upload_photo_count'] 
  ));  
  // if no attached photo
  if (!$post_images)
    return false;
  
  if ( $post_images ) {
    $i = 1;
    foreach($post_images as $image) {
      $images['file'.$i] = '@' . get_attached_file($image->ID );
      $i++;
    }
  }  
 
  $params = array(
    'access_token' => $options['access_token'],
    'gid' => $options['page_id'] // Removed minus sign
  );
  
  // Get Wall Upload Server
  $query = http_build_query($params);
  $data = wp_remote_get(EVC_API_URL.'photos.getWallUploadServer?'.$query, array('sslverify' => false));
    
  if (is_wp_error($data))
    return $data->get_error_message();

  $resp = json_decode($data['body'],true);
  if (!$resp['response']['upload_url'])
    return false;
    
  // Upload photo to server  
  $curl = new Wp_Http_Curl();
  $data = $curl->request( $resp['response']['upload_url'], array(
    'body' => $images, 
    'method' => 'POST'
  ));    
  
  if (is_wp_error($data))
    return $data->get_error_message();
  
  $resp = json_decode($data['body'],true);
  if (!$resp['photo'])
    return false;
  
  // Save Wall Photo
  $params = array();
  $params = array(
    'access_token' => $options['access_token'],
    'gid' => $options['page_id'], // Removed minus sign
    'server' => $resp['server'],
    'photo' => $resp['photo'],
    'hash' => $resp['hash']
  ); 
  $query = http_build_query($params);
  $data = wp_remote_get(EVC_API_URL.'photos.saveWallPhoto?'.$query, array('sslverify' => false));   
 
  if (is_wp_error($data))
    return $data->get_error_message();
  
  $resp = json_decode($data['body'],true);
  if (!$resp['response'])
    return false; 
  //print__r($resp);
  
  foreach($resp['response'] as $r)
    $attachments[] = $r['id'];
    
  return array('i' => $attachments);
}

// Main Idea from Otto, http://ottopress.com/wordpress-plugins/simple-facebook-connect/
function evc_make_excerpt($post) { 
  $options = get_option('evc_options');  
    
  if ( !empty($post->post_excerpt) ) 
    $text = $post->post_excerpt;
  else 
    $text = $post->post_content;
  
  $text = strip_shortcodes( $text );

  // filter the excerpt or content, but without texturizing
  if ( empty($post->post_excerpt) ) {
    remove_filter( 'the_content', 'wptexturize' );
    $text = apply_filters('the_content', $text);
    add_filter( 'the_content', 'wptexturize' );
  } else {
    remove_filter( 'the_excerpt', 'wptexturize' );
    $text = apply_filters('the_excerpt', $text);
    add_filter( 'the_excerpt', 'wptexturize' );
  }

  $text = str_replace(']]>', ']]&gt;', $text);
  $text = wp_strip_all_tags($text);
  $text = str_replace(array("\r\n","\r","\n"),"\n\n",$text);

  $excerpt_more = apply_filters('excerpt_more', '...');
  $excerpt_more = html_entity_decode($excerpt_more, ENT_QUOTES, 'UTF-8');
  $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
  $text = htmlspecialchars_decode($text);
  
  //$max = min(500, apply_filters('evc_excerpt_length', 500));
  
  $max = !empty($options['excerpt_length']) ? $options['excerpt_length'] : 20;
  
  if ($max < 1) return ''; // nothing to send
  $words = explode(' ', $text);
  
  if (count($words) >= $max) {
    $words = array_slice($words, 0, $max);
    array_push ($words, $excerpt_more);
    $text = implode(' ', $words);
  }
  
  $text = evc_excerpt_strlen($text);
  
  return $text;
}


function evc_excerpt_strlen ($text, $max_strlen = 2688) {
  $options = get_option('evc_options');  
  if (isset($options['excerpt_length_strings']) && !empty($options['excerpt_length_strings'])) {
    $max_strlen = $options['excerpt_length_strings'] > $max_strlen ? $max_strlen : $options['excerpt_length_strings'];
  }
  
  if (strlen($text) >= $max_strlen) {
    $text = substr($text, 0, $max_strlen);
    $words = explode(' ', $text);
    array_pop($words); // strip last word
    
    $excerpt_more = apply_filters('excerpt_more', '...');
    $excerpt_more = html_entity_decode($excerpt_more, ENT_QUOTES, 'UTF-8');
    array_push ($words, $excerpt_more);
    
    $text = implode(' ', $words);
  }

  return $text;
}

/*
add_filter( 'evc_excerpt_length', 'theme_evc_excerpt_length' );
function theme_evc_excerpt_length() {
  return  20;
}
*/

// fix shortlink 
add_filter('evc_publish_permalink', 'evc_publish_shortlink_fix', 10, 2);
function evc_publish_shortlink_fix($link, $id) {
  if (empty($link)) 
    $link = get_permalink($id);
  
  return $link;
}



