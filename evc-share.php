<?php

add_action ('admin_init', 'evc_update_share');
function evc_update_share () {
  
  $version = get_option('evc_version');
  $options = get_option('evc_options');
  $evc_autopost = get_option('evc_autopost');
  $evc_vk_api_autopost = get_option('evc_vk_api_autopost');
  
  if (isset($options) && $options && !empty($options) && (!$version || empty($version)) ) {
  //if (!isset($version) || empty($version) || !$version) {
    
    if (isset($options['access_token']) && !empty($options['access_token']))
      $evc_vk_api_autopost['access_token'] = $options['access_token'];     
    if (isset($options['app_id']) && !empty($options['app_id']))
      $evc_vk_api_autopost['app_id'] = $options['app_id'];     
    if (isset($options['access_token_url']) && !empty($options['access_token_url']))
      $evc_vk_api_autopost['access_token_url'] = $options['access_token_url'];     
      
    update_option('evc_vk_api_autopost', $evc_vk_api_autopost);     
    unset($options['access_token'], $options['access_token_url'], $options['app_id']);    
    
    
    if (isset($options['from_group']) && !empty($options['from_group'])) 
      $evc_autopost['format']['from_group'] = 'from_group';   
    if (isset($options['signed']) && !empty($options['signed']))
      $evc_autopost['format']['signed'] = 'signed';      
    if (isset($options['add_link']) && !empty($options['add_link']))
      $evc_autopost['format']['add_link'] = 'add_link';       
    
    unset($options['from_group'], $options['signed'], $options['add_link']);    
    
    $evc_autopost = wp_parse_args($evc_autopost, $options);
    update_option('evc_autopost', $evc_autopost);     

    
    //delete_option('evc_options');
    update_option('evc_version', evc_version());  
  }
  
}

add_action('wp_head', 'evc_share_meta', 99 );
function evc_share_meta () {
  //$options = get_option('evc_options');
  $options = evc_get_all_options(array('evc_vk_api_widgets','evc_sidebar_overlay','evc_sidebar_slide'));
  $s = $options;
  //print__r($s);
  echo '<meta property="vk:app_id" content="'.trim($options['site_app_id']).'" />';
  
  echo '<style type="text/css">';
  
  echo '
    #overlay-sidebar {
      width: '.$s['o_sidebar_width'].'px;
      margin: 0 0 0 -'.($s['o_sidebar_width'] / 2).'px; 
    }
    #slide-sidebar {
      width: '. $s['s_sidebar_width'].'px;
      right:-'.($s['s_sidebar_width'] + 43).'px; // width + 43px
      bottom: '.((strpos($s['s_sidebar_bottom'], '%') !== false) ? $s['s_sidebar_bottom'] : ($s['s_sidebar_bottom'] . 'px')) . ';
    }
  ';
  
  echo evc_post_sidebar_css();
    
  echo '</style>';
  

  echo '<script type="text/javascript">
    var VKWidgetsGroup = [];
    if (typeof ajaxurl == "undefined")
      ajaxurl = "' . 'http://'.$_SERVER['HTTP_HOST']. '/wp-admin/admin-ajax.php' .'";
  ';
    /*  
    o_sidebar_action
    o_sidebar_bottom
    o_sidebar_width
    o_sidebar_timeout
    o_sidebar_scroll
    o_sidebar_cookie_days
    o_sidebar_times
    */
    
  echo '
    oTimeout = '.($s['o_sidebar_timeout'] * 1000).';
    oScreens = '.$s['o_sidebar_scroll'].';
    oCookieExpires = '.$s['o_sidebar_cookie_days'].';
    oAction = "'.$s['o_sidebar_action'].'"; 
    oTop = "'.((strpos($s['o_sidebar_top'], '%') !== false) ? $s['o_sidebar_top'] : ($s['o_sidebar_top'] . 'px')).'"; 
  
    sTimeout = '.($s['s_sidebar_timeout']*1000).';
    sScreens = '.$s['s_sidebar_scroll'].';
    sCookieExpires = '.$s['s_sidebar_cookie_days'].';
    sAction = "'.$s['s_sidebar_action'].'"; 
    sSpeed = 800;
        
  </script>';  
}

function evc_post_sidebar_css($sidebar = 'bp') {
  //$options = get_option('evc_options');

  $options = evc_get_all_options(array(
    'evc_sidebar_before_post_content',
    'evc_sidebar_after_post_content'
  ));  

  $out = '';
  if (isset($options[$sidebar . '_sidebar_cols']) && !empty($options[$sidebar . '_sidebar_cols']) && is_numeric($options[$sidebar . '_sidebar_cols'])) {
    
    $out[] = '
      #'.($sidebar == 'bp' ? 'before' : 'after').'-post-content-sidebar aside {
        float: left;
    }';
    
    $px = '%';      
    if (isset($options[$sidebar . '_sidebar_cols_width']) && !empty($options[$sidebar . '_sidebar_cols_width'])) {
      $str = $options[$sidebar . '_sidebar_cols_width'];
      if (strpos($str , '%') !== false) {
        $str = str_replace('%', '', $str);        
      }
      else
        $px = 'px';
      
      $width = explode(' ', $str);
    }
    else {
      $col_width = 100 / $options[$sidebar . '_sidebar_cols'];
      for ($i = 0; $i < $options[$sidebar . '_sidebar_cols']; $i++)
        $width[] = $col_width;
    }
    
    $i = 1;
    foreach($width as $w) {
      $out[] = '
      #'.($sidebar == 'bp' ? 'before' : 'after').'-post-content-sidebar aside:nth-child('.$i.') {
        width: '.$w . $px . ';
      }';
      $i++;
    }
    $out = implode("\n", $out);    
  }
  return $out;
}

//add_action('wp_footer', 'evc_vk_widgets_async_load'); 
function evc_vk_widgets_async_load(){
  $options = get_option('evc_options');
  ?>
  <script type="text/javascript">
  /* <![CDATA[ */


  
  /* ]]> */
  </script><?php
  
}

add_action('wp_ajax_evc_add_vk_widget_stats', 'evc_add_vk_widget_stats');
add_action('wp_ajax_nopriv_evc_add_vk_widget_stats', 'evc_add_vk_widget_stats');
function evc_add_vk_widget_stats() {
  if(!empty($_POST))
    extract($_POST);  
  
  $gmt = current_time('timestamp', 1);
  // local time
  $date = gmdate('Y-m-d', current_time('timestamp'));
  if (false === ($vk_widgets_stats = get_transient('vk_widgets_stats')))
    $vk_widgets_stats = array();

  if (isset($vk_widgets_stats[$gid][$widget][$date][$waction]))
    $vk_widgets_stats[$gid][$widget][$date][$waction]++;
  else
    $vk_widgets_stats[$gid][$widget][$date][$waction] = 1;
  
  set_transient('vk_widgets_stats', $vk_widgets_stats, YEAR_IN_SECONDS); 
  
  return true;
}

function evc_get_vk_widget_stats () {
  if (false === ($vk_widgets_stats = get_transient('vk_widgets_stats')))
    return 'Статистика отсутствует.';  
  else
    return '<pre>' . print_r($vk_widgets_stats, 1) . '</pre>';
}

add_action('init', 'evc_widget_load_scripts'); 
function evc_widget_load_scripts () {
  wp_enqueue_script('jquery.cookie', plugins_url('js/jquery.cookie.js' , __FILE__), array('jquery'), null, false);   
  wp_enqueue_script('evc-share', plugins_url('js/evc-share.js' , __FILE__), array('jquery', 'jquery.cookie'), null, true); 
}

function evc_vk_widget_group ($data, $echo = 1) {
  if ($data['options']['width'] === 0)
    $data['options']['width'] = 'auto';
  
  $out = '
    <script type="text/javascript">
      VKWidgetsGroup.push ({
        element_id: "vk-widget-'.$data['element_id'].'",
        options: '.evc_vk_widget_data_encode($data['options']).',
        group_id: '.(-1*$data['group_id']).'
      });  
    </script>

    <div class = "vk_widget_group" id = "vk-widget-'.$data['element_id'].'"></div>  
  ';
  
  if ($echo)
    echo $out;
  else
    return $out;
}

function evc_vk_widget_data_encode($data) {
  foreach($data as $key => $value)
    $out[] = $key . ': ' . (is_numeric($value) ? $value : '"'.$value.'"'); 
    
  return '{ ' . implode(", ", $out) . ' }';
}

/**
 * VK.Widgets.Group Class
 */
class VK_Widget_Group extends WP_Widget {

  function __construct() {
    $widget_ops = array('classname' => 'vk_group', 'description' => __( 'Виджет для сообществ') );
    parent::__construct('vk_group', __('VK Сообщества'), $widget_ops);
  }

  function widget( $args, $instance ) {
    extract( $args );

    $title = !empty($instance['title']) ? $instance['title'] : false;
    
    $data = '';
    
    $options = $instance;
    unset ($options['group_url'], $options['title']);
    $data['options'] = $options;
    
    $data['group_id'] = evc_stats_get_group_id($instance['group_url']);
    $data['element_id'] = $this->id;
    
    //print__r($data);
    echo $before_widget;
    if ( $title)
      echo $before_title . $title . $after_title;
    evc_vk_widget_group($data);
    echo $after_widget;
  }

  function update( $new_instance, $old_instance ) {
    //$instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    
    if ($new_instance['width'] < 120 && $new_instance['width'] > 0)
      $instance['width'] = 120;
    else
      $instance['width'] = intval($new_instance['width']);  
      
    if ($new_instance['height'] < 200)
      $instance['height'] = 200;      
    elseif ($new_instance['height'] > 1200)
      $instance['height'] = 1200;  
    else
      $instance['height'] = $new_instance['height'];  
      
    $instance = wp_parse_args($instance, $new_instance);
    return $instance;
  }

  function form( $instance ) {
    //Defaults
    $instance = wp_parse_args( (array) $instance, array( 
      'title' => '', 
      'group_url' => '', 
      'mode' => 0,
      'wide' => 2,
      'width' => '0',
      'height' => '200',
      'color1' =>'FFFFFF',
      'color2' =>'2B587A',
      'color3' =>'5B7FA6'
    ) );
    $title = esc_attr( $instance['title'] );
    
  ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок виджета:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    
    <p>
      <label for="<?php echo $this->get_field_id('group_url'); ?>"><?php _e('Ссылка на страницу группы:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('group_url'); ?>" name="<?php echo $this->get_field_name('group_url'); ?>" type="text" value="<?php echo $instance['group_url']; ?>" />
      <small>Например, http://vk.com/pasportvzubi</small>
    </p>    
    
    <p>
      <label for="<?php echo $this->get_field_id('mode'); ?>"><?php _e( 'Вид:' ); ?></label>
      <select name="<?php echo $this->get_field_name('mode'); ?>" id="<?php echo $this->get_field_id('mode'); ?>" class="widefat">
        <option value="0"<?php selected( $instance['mode'], '0' ); ?>><?php _e('Участники'); ?></option>
        <option value="2"<?php selected( $instance['mode'], '2' ); ?>><?php _e('Стена группы'); ?></option>
        <option value="1"<?php selected( $instance['mode'], '1' ); ?>><?php _e('Только название' ); ?></option>
      </select>
    </p>    
    <?php 
    /*
    <p>
      <label for="<?php echo $this->get_field_id('wide'); ?>"><?php _e( 'Вид стены группы:' ); ?></label>
      <select name="<?php echo $this->get_field_name('wide'); ?>" id="<?php echo $this->get_field_id('wide'); ?>" class="widefat">
        <option value="0"<?php selected( $instance['wide'], '0' ); ?>><?php _e('Стандартный'); ?></option>
        <option value="1"<?php selected( $instance['wide'], '1' ); ?>><?php _e('Расширенный'); ?></option>
      </select>
      <small><b>Расширенный</b> - к каждой записи будет добавлено фото группы и кнопка "Мне нравится". <b>Только</b> если в предыдущем поле выбрано "Стена группы".</small>
    </p> 
    */
    ?>
    
    <p>
      <label ><?php _e('Ширина х высота:'); ?></label>
      <br/><input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" size="5" value="<?php echo $instance['width']; ?>" />&nbsp;x&nbsp;
      

      <input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" size="5" value="<?php echo $instance['height']; ?>" /> px
      <br/><small>Ширина от 120. Если "0" - подстраивается под ширину блока (responsive).</small>
      <br/><small>Высота от 200 до 1200.</small>
    </p>    
    
    <p>
      <label><?php _e('Цвет фона, текста, кнопок:'); ?></label>
      <br/># <input id="<?php echo $this->get_field_id('color1'); ?>" name="<?php echo $this->get_field_name('color1'); ?>" type="text" size="5" value="<?php echo $instance['color1']; ?>" />
 
    
<input id="<?php echo $this->get_field_id('color2'); ?>" name="<?php echo $this->get_field_name('color2'); ?>" type="text" size="5" value="<?php echo $instance['color2']; ?>" />   
    
<input id="<?php echo $this->get_field_id('color3'); ?>" name="<?php echo $this->get_field_name('color3'); ?>" type="text" size="5" value="<?php echo $instance['color3']; ?>" /></p>                

    <div style = "border-width:1px 1px 1px 4px; border-color:#DDDDDD #DDDDDD #DDDDDD #2EA2CC; border-style: solid; background-color: #F7FCFE; padding: 1px 12px; margin-bottom:13px;" ><p style = "margin: 0.5em 0 !important; padding: 2px !important; "><a href = "http://ukraya.ru/192/easy-vk-connect-1-3" target = "_blank">Руководство</a> и <a href = "http://ukraya.ru/196/easy-vkontakte-connect-1-3-support" target = "_blank">помощь</a> по настройке виджета.</p></div>

<?php
  }

}

function evc_widgets_init() {

  register_widget('VK_Widget_Group');

  
  register_sidebar( array(
    'name'          => __( 'Всплывающий', 'evc' ),
    'id'            => 'overlay-sidebar',
    'description'   => __( 'Появляется поверх основного содержания сайта.', 'evc' ),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h1 class="widget-title">',
    'after_title'   => '</h1>',
  ) );  
  
  register_sidebar( array(
    'name'          => __( 'Выезжающий', 'evc' ),
    'id'            => 'slide-sidebar',
    'description'   => __( 'Выезжает в правом нижнем углу сайта.', 'evc' ),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h1 class="widget-title">',
    'after_title'   => '</h1>',
  ) );  

  register_sidebar( array(
    'name'          => __( 'До контента', 'evc' ),
    'id'            => 'before-post-content-sidebar',
    'description'   => __( 'Показывается до контента поста.', 'evc' ),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h1 class="widget-title">',
    'after_title'   => '</h1>',
  ) );    
  
  register_sidebar( array(
    'name'          => __( 'После контента', 'evc' ),
    'id'            => 'after-post-content-sidebar',
    'description'   => __( 'Показывается после контента поста. ', 'evc' ),
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h1 class="widget-title">',
    'after_title'   => '</h1>',
  ) );    
  
}
add_action('widgets_init', 'evc_widgets_init');


function evc_sidebar_is ($sidebar = 'o' ) {
  //$options = get_option('evc_options'); 
  $options = evc_get_all_options(array(
    'evc_sidebar_overlay', 
    'evc_sidebar_slide',
    'evc_sidebar_before_post_content',
    'evc_sidebar_after_post_content'
  ));  
  
  
  if (!isset($options[$sidebar . '_sidebar_is']) || isset($options[$sidebar . '_sidebar_is']['all']))
    return true;
  
  foreach($options[$sidebar . '_sidebar_is'] as $page) {
    if (call_user_func('is_' . $page) )
      return true;
  }
  
  return false;
}

add_action('wp_footer', 'evc_overlay_sidebar');
function evc_overlay_sidebar () {
  //$options = get_option('evc_options'); 
  $options = evc_get_all_options(array(
    'evc_sidebar_overlay', 
    'evc_sidebar_slide'
  ));  
  
  
  $o_sidebar = true;
  if ((isset($_COOKIE['oSidebar']) && $s['o_sidebar_times'] && $_COOKIE['oSidebar'] > $options['o_sidebar_times']) || !evc_sidebar_is() ) 
    $o_sidebar = false;
  
  if ( is_active_sidebar( 'overlay-sidebar' ) && $o_sidebar ) {
    echo '<div id="overlay-sidebar-wrap" class = "">';
      echo '<div id="overlay-sidebar-bg" class = "hide"></div>';
      echo '<div id="overlay-sidebar" class="overlay_sidebar_class  widget-area" role="complementary">';
        echo '<div title="Close" tabindex="0" class="overlay-sidebar-close"></div>';
        dynamic_sidebar( 'overlay-sidebar' ); 
      echo '</div><!-- #overlay-sidebar -->';
    echo '</div><!-- #overlay-sidebar-wrap -->';
  }
  
  $s_sidebar = true;
  if ((isset($_COOKIE['sSidebar']) && $s['s_sidebar_times'] && $_COOKIE['sSidebar'] > $options['s_sidebar_times'] ) || !evc_sidebar_is('s') )
    $s_sidebar = false;
    
  if ( is_active_sidebar( 'slide-sidebar' ) && $s_sidebar  ) {
    echo '<div id="slide-sidebar-wrap" class = "">';
      echo '<div id="slide-sidebar" class="slide_sidebar_class widget-area" role="complementary">';
        echo '<div title="Close" tabindex="0" class="slide-sidebar-close"></div>';
        dynamic_sidebar( 'slide-sidebar' ); 
      echo '</div><!-- #slide-sidebar -->';
    echo '</div><!-- #slide-sidebar-wrap -->';
  }
}

add_action('the_content', 'evc_content_sidebar');
function evc_content_sidebar ($content) {
  if (!is_single())
    return $content;
    
  if ( is_active_sidebar( 'before-post-content-sidebar' ) ) {
    ob_start();
    echo '<div id="before-post-content-sidebar-wrap" class = "">';
      echo '<div id="before-post-content-sidebar" class="widget-area before-post-content-sidebar" role="complementary">';
        dynamic_sidebar( 'before-post-content-sidebar' ); 
      echo '</div><!-- #before-post-content-sidebar -->';
    echo '</div><!-- #before-post-content-sidebar-wrap -->';
    $out .= ob_get_clean();
  }
  
  $out .= $content;
  
  if ( is_active_sidebar( 'after-post-content-sidebar' ) ) {
    ob_start();
    echo '<div id="after-post-content-sidebar-wrap" class = "">';
      echo '<div id="after-post-content-sidebar" class="widget-area after-post-content-sidebar" role="complementary">';
        dynamic_sidebar( 'after-post-content-sidebar' ); 
      echo '</div><!-- #after-post-content-sidebar -->';
    echo '</div><!-- #after-post-content-sidebar-wrap -->';
    $out .= ob_get_clean();
  }
  
  return $out;
}

add_action( 'wp_enqueue_scripts', 'evc_share_styles' );
function evc_share_styles () {
  wp_register_style( 'evc-share-style', plugins_url('css/style-share.css', __FILE__) );
  wp_enqueue_style( 'evc-share-style' );
  
  // Enqueue DashIcons
  wp_enqueue_style( 'dashicons' );  
}


/* Admin Page */

if (!class_exists(WP_Settings_API_Class))
  include_once('inc/wp-settings-api-class.php'); 
  

/*
  $options = evc_get_all_options(array(
    'evc_vk_api_autopost',
    'evc_vk_api_widgets',
    'evc_sidebar_overlay',
    'evc_sidebar_slide',
    'evc_sidebar_before_post_content',
    'evc_sidebar_after_post_content',
    'evc_autopost'
  ));   

*/  
  
function evc_get_all_options ($options) {
  $options = apply_filters('evc_get_all_options', $options);
  if (empty($options))
    return array();
  $out = array();
  foreach($options as $option) {
    $values = get_option($option);
    if ($values && !empty($values))
      $out += $values;
  }
  return $out;
}
  
function evc_delete_all_options ($options) {
  $options = apply_filters('evc_get_all_options', $options);
  if (empty($options))
    return false;
  foreach($options as $option) {
    delete_option($option);
  }
  return true;
}  
  
function evc_vk_api_settings_admin_init() {
  global $evc_vk_api_settings;
  
  $evc_vk_api_settings = new WP_Settings_API_Class;
    
  $options = evc_get_all_options(array(
    'evc_vk_api_autopost', 
    'evc_vk_api_widgets'
  )); 
  
  $tabs = array(
    'evc_vk_api_autopost' => array(
      'id' => 'evc_vk_api_autopost',
      'name' => 'evc_vk_api_autopost',
      'title' => __( 'Для автопостинга', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_vk_api_autopost_section' => array(
          'id' => 'evc_vk_api_autopost_section',
          'name' => 'evc_vk_api_autopost_section',
          'title' => __( 'Настройки VK API для автопостинга', 'evc' ),
          'desc' => 'Если вы <b>не собираетесь</b> использовать <a href = "'.admin_url('admin.php?page=evc-autopost').'">модуль автопостинга</a>, можно не заполнять.',          
        )
      )
    ),
    'evc_vk_api_widgets' => array(
      'id' => 'evc_vk_api_widgets',
      'name' => 'evc_vk_api_widgets',
      'title' => __( 'Для виджетов', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_vk_api_widgets_section' => array(
          'id' => 'evc_vk_api_widgets_section',
          'name' => 'evc_vk_api_widgets_section',
          'title' => __( 'Настройки VK API для виджетов', 'evc' ),
          'desc' => __( 'Если вы собираетесь использовать <b>только</b> <a href = "'.admin_url('admin.php?page=evc-autopost').'">модуль автопостинга</a>, можно не заполнять.', 'evc' ),          
        )
      )
    )   
  );
  
  // VKWP Bridge Compatible
  $evc_bridge = get_option('evc_bridge'); 
  if (isset($evc_bridge) && !empty($evc_bridge)) {
    
  }

  $url = get_bloginfo('wpurl');
  $url_arr = explode(".", basename($url));
  $domain = $url_arr[count($url_arr)-2] . "." . $url_arr[count($url_arr)-1];
  
  $site_app_id_desc = '<p>Чтобы получить доступ к <b>API ВКонтакте</b>, вам нужно <a href="http://vk.com/editapp?act=create" target="_blank">создать приложение</a> со следующими настройками:</p>
  <ol>
    <li><strong>Название:</strong> любое</li>
    <li><strong>Тип:</strong> Веб-сайт</li>
    <li><strong>Адрес сайта:</strong> ' . $url .'</li>
    <li><strong>Базовый домен:</strong> '. $domain .'</li>
  </ol>
  <p>Если приложение с этими настройками у вас было создано ранее, вы можете найти его на <a href="http://vk.com/apps?act=settings" target="_blank">странице приложений</a> и, затем нажмите "Редактировать", чтобы открылись его параметры.</p>
  <p>В полях ниже вам нужно указать: <b>ID приложения</b> и его <b>Защищенный ключ</b>.</p>';   
   
  $site_get_access_token_url = (!empty($options['site_app_id'])) ? evc_share_vk_login_url() : 'javascript:void(0);';
        
  $site_access_token_desc = '<p>Чтобы получить <strong>Access Token</strong>:</p>
  <ol>
    <li>Пройдите по <a href="'.$site_get_access_token_url.'" id = "getaccesstokenurl">ссылке</a></li>
    <li>Подтвердите уровень доступа.</li>
  </ol>';     
  
  
  $app_id_desc = '<p>Чтобы получить <strong>ID приложения</strong>, необходимо <a href="http://vk.com/editapp?act=create" target="_blank">создать приложение</a> со следующими настройками:</p>
  <ol>
    <li><strong>Название:</strong> любое</li>
    <li><strong>Тип:</strong> Standalone-приложение</li>
  </ol>
  <p>В настройках приложения необходимо установить параметры в разделе <strong>Open API</strong>:</p>
  <ol>
    <li><strong>Адрес сайта:</strong> ' . $url .'</li>
    <li><strong>Базовый домен:</strong> '. $domain .'</li>
  </ol>
  <p>Если приложение с этими настройками у вас было создано ранее, вы можете найти его на <a href="http://vk.com/apps?act=settings" target="_blank">странице приложений</a> и, нажав "Редактировать", найти его ID.</p>'; 
    
  $get_access_token_url = (!empty($options['app_id'])) ? 'http://oauth.vk.com/authorize?client_id='.$options['app_id'].'&scope=wall,photos,offline&redirect_uri=http://api.vk.com/blank.html&display=page&response_type=token' : 'javascript:void(0);';
      
  $access_token_desc = '<p>Чтобы получить <strong>Access Token</strong></p>
  <ol>
    <li>пройдите по <a href="'.$get_access_token_url.'" id = "getaccesstokenurl" target = "_blank">ссылке</a>,</li>
    <li>подтвердите уровень доступа,</li>
    <li>скопируйте url открывшейся страницы в поле внизу.</li>
  </ol>';  
  
  $fields = array(
   'evc_vk_api_autopost_section' => array(
      array(
        'name' => 'app_id_desc',
        'desc' => __( $app_id_desc, 'evc' ),
        'type' => 'html',
      ), 
      array(
        'name' => 'app_id',
        'label' => __( 'ID приложения', 'evc' ),
        'desc' => __( 'ID вашего приложения VK.', 'evc' ),
        'type' => 'text'
      ), 
     array(
        'name' => 'access_token_desc',
        'desc' => __( $access_token_desc, 'evc' ),
        'type' => 'html',
      ), 
      array(
        'name' => 'access_token_url',
        'label' => __( 'Access Token Url', 'evc' ),
        'desc' => __( '', 'evc' ),
        'type' => 'text'    
      ),        
      array(
        'name' => 'access_token',
        'label' => __( 'Access Token', 'evc' ),
        'desc' => __( 'Значение будет подставлено автоматически, как только вы скопируете урл в поле выше и нажмете "Сохранить".', 'evc_bridge' ),
        'type' => 'text',
        'readonly' => true      
      ),                  
    ), 
    
    'evc_vk_api_widgets_section' => array(
      array(
        'name' => 'site_app_id_desc',
        'desc' => __( $site_app_id_desc, 'evc' ),
        'type' => 'html',
      ), 
      array(
        'name' => 'site_app_id',
        'label' => __( 'ID приложения', 'evc' ),
        'desc' => __( 'ID вашего приложения VK.', 'evc' ),
        'type' => 'text'
      ), 
      array(
        'name' => 'site_app_secret',
        'label' => __( 'Защищенный ключ', 'evc' ),
        'desc' => __( 'Защищенный ключ вашего приложения VK.', 'evc' ),
        'type' => 'text'
      ),       
    ),   
     
  );
 
  if (isset($options['site_app_id']) && !empty($options['site_app_id']) && isset($options['site_app_secret']) && !empty($options['site_app_secret'])) {
    
    array_push(
      $fields['evc_vk_api_widgets_section'],
      array(
        'name' => 'site_access_token_desc',
        'desc' => __( $site_access_token_desc, 'evc' ),
        'type' => 'html',
      ), 
      array(
        'name' => 'site_access_token',
        'label' => __( 'Access Token', 'evc' ),
        'desc' => __( 'Значение будет подставлено автоматически, как только вы пройдете по указанной выше ссылке.', 'evc' ),
        'type' => 'text',
        'readonly' => true      
      )
    );

  }

 //set sections and fields
 $evc_vk_api_settings->set_option_name( 'evc_options' );
 $evc_vk_api_settings->set_sections( $tabs );
 $evc_vk_api_settings->set_fields( $fields );

 //initialize them
 $evc_vk_api_settings->admin_init();
}
add_action( 'admin_init', 'evc_vk_api_settings_admin_init' );


// Register the plugin page
function evc_vk_api_admin_menu() {
  global $evc_vk_api_settings_page; 
  
  $evc_vk_api_settings_page = add_submenu_page( 'evc', 'Настройки API ВКонтакте', 'Настройки VK API', 'activate_plugins', 'evc', 'evc_vk_api_settings_page' );
 
  //add_action("load-$evc_sidebar_settings_page", 'evc_stats_screen_options');
  //add_action( 'admin_print_styles-' . $evc_sidebar_settings_page, 'evc_stats_styles' );
  add_action( 'admin_footer-'. $evc_vk_api_settings_page, 'evc_vk_api_settings_page_js' );
}
add_action( 'admin_menu', 'evc_vk_api_admin_menu', 20 );


function evc_vk_api_settings_page_js () {
?>
<script type="text/javascript" >
  jQuery(document).ready(function($) {

    $("#evc_vk_api_autopost\\[app_id\\]").change( function() {
      if ($(this).val().trim().length) {
        $(this).val($(this).val().trim());
        $('#getaccesstokenurl').attr({'href': 'http://oauth.vk.com/authorize?client_id='+ $(this).val().trim() +'&scope=wall,photos,offline&redirect_uri=http://api.vk.com/blank.html&display=page&response_type=token', 'target': '_blank'});
        
      }
      else {
        $('#getaccesstokenurl').attr({'href': 'javscript:void(0);'});
      }
      
    });  
  
  }); // jQuery End
</script>
<?php  
}


// Display the plugin settings options page
function evc_vk_api_settings_page() {
  global $evc_vk_api_settings;
  
  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>Настройки API ВКонтакте</h2>';
    
    echo '<div id = "col-container">';  
      echo '<div id = "col-right" class = "evc">';
        echo '<div class = "evc-box">';
        evc_ad();
        echo '</div>';
      echo '</div>';
      echo '<div id = "col-left" class = "evc">';
        settings_errors();
        $evc_vk_api_settings->show_navigation();
        $evc_vk_api_settings->show_forms();
      echo '</div>';
    echo '</div>';    

  echo '</div>';
}



function evc_sidebar_settings_admin_init() {
  global $evc_sidebar_settings;
  
  $evc_sidebar_settings = new WP_Settings_API_Class;
  //$options = get_option('evc_options'); 
  $options = evc_get_all_options(array(
    'evc_sidebar_overlay', 
    'evc_sidebar_slide',
    'evc_sidebar_before_post_content',
    'evc_sidebar_after_post_content'
  ));  

  
  $tabs = array(
    'evc_sidebar_overlay' => array(
      'id' => 'evc_sidebar_overlay',
      'name' => 'evc_sidebar_overlay',
      'title' => __( 'Всплывающий', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_sidebar_overlay_section' => array(
          'id' => 'evc_sidebar_overlay_section',
          'name' => 'evc_sidebar_overlay_section',
          'title' => __( 'Всплывающий сайдбар', 'evc' ),
          'desc' => __( 'Появляется поверх основного содержания сайта.', 'evc' ),          
        ),
        'evc_sidebar_overlay_cookies_section' => array(
          'id' => 'evc_sidebar_overlay_cookies_section',
          'name' => 'evc_sidebar_overlay_cookies_section',
          'title' => __( 'Управление показами сайдбара', 'evc' ),
          'desc' => __( 'Манипулируя параметрами "Куки" и "Демонстрации" можно задать частоту появления сайдбара для пользователя в единицу времени.
          <br/>Например, <b>показывать сайдбар посетителю</b>: 
          <ol>
            <li>один раз в день / <i>куки: 1; демонстрации: 1</i>;</li>
            <li>не больше cеми раз в неделю / <i>куки: 7; демонстрации: 7</i>;</li>
            <li>при каждом просмотре страницы / <i>куки: 365; демонстрации: 0</i>;</li>
            <li>и т.д.</li>
          </ol>', 'evc' ),           
        )        
      )
    ),
    
    'evc_sidebar_slide' => array(
      'id' => 'evc_sidebar_slide',
      'name' => 'evc_sidebar_slide',
      'title' => __( 'Выезжающий', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_sidebar_slide_section' => array(
          'id' => 'evc_sidebar_slide_section',
          'name' => 'evc_sidebar_slide_section',
          'title' => __( 'Выезжающий сайдбар', 'evc' ),
          'desc' => __( 'Выезжает в правом нижнем углу сайта.', 'evc' ),          
        ),
       'evc_sidebar_slide_cookies_section' => array(
          'id' => 'evc_sidebar_slide_cookies_section',
          'name' => 'evc_sidebar_slide_cookies_section',
          'title' => __( 'Управление показами сайдбара', 'evc' ),
          'desc' => __( 'Манипулируя параметрами "Куки" и "Демонстрации" можно задать частоту появления сайдбара для пользователя в единицу времени.
          <br/>Например, <b>показывать сайдбар посетителю</b>: 
          <ol>
            <li>один раз в день / <i>куки: 1; демонстрации: 1</i>;</li>
            <li>не больше cеми раз в неделю / <i>куки: 7; демонстрации: 7</i>;</li>
            <li>при каждом просмотре страницы / <i>куки: 365; демонстрации: 0</i>;</li>
            <li>и т.д.</li>
          </ol>', 'evc' ),          
        )         
      )
    ),
    
    'evc_sidebar_before_post_content' => array(
      'id' => 'evc_sidebar_before_post_content',
      'name' => 'evc_sidebar_before_post_content',
      'title' => __( 'До контента', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_sidebar_before_post_content_section' => array(
          'id' => 'evc_sidebar_before_post_content_section',
          'name' => 'evc_sidebar_before_post_content_section',
          'title' => __( 'До контента', 'evc' ),
          'desc' => __( 'Показывается до контента поста.', 'evc' ),          
        )
      )
    ),
    'evc_sidebar_after_post_content' => array(
      'id' => 'evc_sidebar_after_post_content',
      'name' => 'evc_sidebar_after_post_content',
      'title' => __( 'После контента', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_sidebar_after_post_content_section' => array(
          'id' => 'evc_sidebar_after_post_content_section',
          'name' => 'evc_sidebar_after_post_content_section',
          'title' => __( 'После контента', 'evc' ),
          'desc' => __( 'Показывается после контента поста.', 'evc' ),          
        )
      )
    ),       
      
  );
      
  
  $fields = array(
    'evc_sidebar_overlay_section' => array(
      array(
        'name' => 'o_sidebar_width',
        'label' => __( 'Ширина', 'evc' ),
        'desc' => __( 'Ширина сайдбара в пикселях (px).
        <br/>Например: <code>380</code>.', 'evc' ),
        'type' => 'text',
        'default' => '380'
      ), 
      array(
        'name' => 'o_sidebar_top',
        'label' => __( 'Отступ сверху', 'evc' ),
        'desc' => __( 'CSS свойство top. В процентах или пикселях (px).
        <br/>Например: <code>40</code> или <code>10%</code>.', 'evc' ),
        'type' => 'text',
        'default' => '40'
      ),  
     array(
        'name' => 'o_sidebar_action',
        'label' => __( 'Появляется', 'evc_bridge' ),
        'desc' => __( 'Событие, которое инициирует появление сайдбара.', 'evc_bridge' ),
        'type' => 'radio',
        'default' => 'timeout',
        'options' => array(
          'timeout' => 'Интервал / <i>через заданное время после загрузки страницы.</i>',
          'scroll' => 'Скроллинг / <i>после пролистывания экрана.</i>'
        )
      ), 
      array(
        'name' => 'o_sidebar_timeout',
        'label' => __( 'Интервал', 'evc' ),
        'desc' => __( 'Через сколько секунд после загрузки страницы показать сайдбар.
        <br/>Например: <code>5</code>.', 'evc' ),
        'type' => 'text',
        'default' => '5'
      ),        
      array(
        'name' => 'o_sidebar_scroll',
        'label' => __( 'Скроллинг', 'evc' ),
        'desc' => __( 'Какую часть видимого экрана должен пролистать пользователь до появления сайдбара.
        <br/>Например: <code>0.75</code>. Рекомендуемое значение: от <code>0.1</code> до <code>1</code>.', 'evc' ),
        'type' => 'text',
        'default' => '0.75'
      ),              
      array(
        'name' => 'o_sidebar_is',
        'label' => __( 'Страницы', 'evc' ),
        'desc' => __( 'На страницах <a href = "">какого типа</a> показывать сайдбар.', 'evc' ),
        'type' => 'multicheck',
        'options' => array(
          'all' => 'На всех страницах.',
          'front_page' => 'На главной, <small>is_front_page()</small>.',
          'single' => 'На страницах постов, <small>is_single()</small>.',
          'page' => 'На страницах page, <small>is_page()</small>.',
          'tax' => 'На страницах таксономии, <small>is_tax()</small>.'
        ),
        'default' => 'all'
      ),        
    ),   
    'evc_sidebar_overlay_cookies_section' => array(
       array(
        'name' => 'o_sidebar_cookie_days',
        'label' => __( 'Куки', 'evc' ),
        'desc' => __( 'Сколько дней хранить куки (cookies).
        <br/>Например: <code>365</code>.', 'evc' ),
        'type' => 'text',
        'default' => '365'
      ),       
      array(
        'name' => 'o_sidebar_times',
        'label' => __( 'Демонстрации', 'evc' ),
        'desc' => __( 'Сколько раз за период жизни куки показывать сайдбар пользователю.
        <br/><code>0</code> - без ограничений.', 'evc' ),
        'type' => 'text',
        'default' => '0'
      )   
     ),    

    'evc_sidebar_slide_section' => array(
      array(
        'name' => 's_sidebar_width',
        'label' => __( 'Ширина', 'evc' ),
        'desc' => __( 'Ширина сайдбара в пикселях (px).
        <br/>Например: <code>380</code>.', 'evc' ),
        'type' => 'text',
        'default' => '380'
      ), 
      array(
        'name' => 's_sidebar_bottom',
        'label' => __( 'Отступ снизу', 'evc' ),
        'desc' => __( 'CSS свойство bottom. В процентах или пикселях (px).
        <br/>Например: <code>20</code> или <code>10%</code>', 'evc' ),
        'type' => 'text',
        'default' => '20'
      ),  
     array(
        'name' => 's_sidebar_action',
        'label' => __( 'Появляется', 'evc_bridge' ),
        'desc' => __( 'Событие, которое инициирует появление сайдбара.', 'evc_bridge' ),
        'type' => 'radio',
        'default' => 'scroll',
        'options' => array(
          'timeout' => 'Интервал / <i>через заданное время после загрузки страницы.</i>',
          'scroll' => 'Скроллинг / <i>после пролистывания экрана.</i>'
        )
      ), 
      array(
        'name' => 's_sidebar_timeout',
        'label' => __( 'Интервал', 'evc' ),
        'desc' => __( 'Через сколько секунд после загрузки страницы показать сайдбар.
        <br/>Например: <code>5</code>.', 'evc' ),
        'type' => 'text',
        'default' => '5'
      ),        
      array(
        'name' => 's_sidebar_scroll',
        'label' => __( 'Скроллинг', 'evc' ),
        'desc' => __( 'Какую часть видимого экрана должен пролистать пользователь, для появления сайдбара.
        <br/>Например: <code>0.75</code>.', 'evc' ),
        'type' => 'text',
        'default' => '0.75'
      ),    
      array(
        'name' => 's_sidebar_is',
        'label' => __( 'Страницы', 'evc' ),
        'desc' => __( 'На страницах <a href = "">какого типа</a> показывать сайдбар.', 'evc' ),
        'type' => 'multicheck',
        'default' => 'single',
        'options' => array(
          'all' => 'На всех страницах.',
          'front_page' => 'На главной, <small>is_front_page()</small>.',
          'single' => 'На страницах постов, <small>is_single()</small>.',
          'page' => 'На страницах page, <small>is_page()</small>.',
          'tax' => 'На страницах таксономии, <small>is_tax()</small>.'
        ),
        //'default' => 'all'
      ) 
     ),
    'evc_sidebar_slide_cookies_section' => array(
      array(
        'name' => 's_sidebar_cookie_days',
        'label' => __( 'Куки', 'evc' ),
        'desc' => __( 'Сколько дней хранить куки (cookies).
        <br/>Например: <code>365</code>.', 'evc' ),
        'type' => 'text',
        'default' => '365'
      ),       
      array(
        'name' => 's_sidebar_times',
        'label' => __( 'Демонстрации', 'evc' ),
        'desc' => __( 'Сколько раз за период жизни куки показывать сайдбар пользователю.
        <br/><code>0</code> - без ограничений.', 'evc' ),
        'type' => 'text',
        'default' => '0'
      )   
    ),              

    'evc_sidebar_before_post_content_section' => array(
      array(
        'name' => 'bp_sidebar_cols',
        'label' => __( 'Колонок', 'evc' ),
        'desc' => __( 'Количество колонок. Если не указано, виджеты будут расположены вертикально, один под другим.', 'evc' ),
        'type' => 'text'
      ), 
      array(
        'name' => 'bp_sidebar_cols_width',
        'label' => __( 'Ширина колонок', 'evc' ),
        'desc' => __( 'Например, <code>20% 80%</code> или <code>360 140</code>.', 'evc' ),
        'type' => 'text'
      ),       
    ),
    'evc_sidebar_after_post_content_section' => array(
      array(
        'name' => 'ap_sidebar_cols',
        'label' => __( 'Колонок', 'evc' ),
        'desc' => __( 'Количество колонок. Если не указано, виджеты будут расположены вертикально, один под другим.', 'evc' ),
        'type' => 'text'
      ), 
      array(
        'name' => 'ap_sidebar_cols_width',
        'label' => __( 'Ширина колонок', 'evc' ),
        'desc' => __( 'Например, <code>20% 80%</code> или <code>360 140</code>.', 'evc' ),
        'type' => 'text'
      ),       
    ),
    
  );
 
 
  

 //set sections and fields
 $evc_sidebar_settings->set_option_name( 'evc_options' );
 $evc_sidebar_settings->set_sections( $tabs );
 $evc_sidebar_settings->set_fields( $fields );

 //initialize them
 $evc_sidebar_settings->admin_init();
}
add_action( 'admin_init', 'evc_sidebar_settings_admin_init' );

// Register the plugin page
function evc_sidebar_admin_menu() {
  global $evc_sidebar_settings_page; 
   
  $evc_sidebar_settings_page = add_submenu_page( 'evc', 'Настройки сайдбаров', 'Сайдбары', 'activate_plugins', 'evc-sidebar', 'evc_sidebar_settings_page' );
 
  //add_action("load-$evc_sidebar_settings_page", 'evc_stats_screen_options');
  //add_action( 'admin_print_styles-' . $evc_sidebar_settings_page, 'evc_stats_styles' );
  //add_action( 'admin_footer-'. $evc_sidebar_settings_page, 'evc_sidebar_settings_page_js' );
}
add_action( 'admin_menu', 'evc_sidebar_admin_menu', 50 );


function open_sans_cyrillic () {  
  wp_deregister_style('open-sans');
  wp_register_style( 'open-sans', '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext,cyrillic,cyrillic-ext');
  wp_enqueue_style( 'open-sans' );
}
add_action( 'admin_enqueue_scripts', 'open_sans_cyrillic' );


// Display the plugin settings options page
function evc_sidebar_settings_page() {
  global $evc_sidebar_settings;

  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>Настройки сайдбаров</h2>';
    echo '<p>Дополнительные сайдбары появятся на <a href = "'.admin_url('widgets.php').'">странице виджетов</a>, и вы сможете поместить в них любой виджет по вашему желанию.</p>';
  
    echo '<div id = "col-container">';  
      echo '<div id = "col-right" class = "evc">';
        echo '<div class = "evc-box">';
        evc_ad();
        echo '</div>';
      echo '</div>';
      echo '<div id = "col-left" class = "evc">';
        settings_errors();
        $evc_sidebar_settings->show_navigation();
        $evc_sidebar_settings->show_forms();
      echo '</div>';
    echo '</div>';
    
  echo '</div>';
}

add_filter ('pre_update_option_evc_vk_api_autopost', 'evc_update_option_filter',10,2);
function evc_update_option_filter($newvalue, $oldvalue) {
  
  if (isset($newvalue['access_token_url']) && !empty($newvalue['access_token_url'])) {
    $url = explode('#', $newvalue['access_token_url']);
    $params = wp_parse_args($url[1]);
    $newvalue['access_token'] = $params['access_token'];  
  }

  return $newvalue;
}


function evc_autopost_settings_admin_init() {
  global $evc_autopost_settings;
  
  $evc_autopost_settings = new WP_Settings_API_Class;
  
  $tabs = array(
    'evc_autopost' => array(
      'id' => 'evc_autopost',
      'name' => 'evc_autopost',
      'title' => __( 'Автопостинг', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_autopost_section' => array(
          'id' => 'evc_autopost_section',
          'name' => 'evc_autopost_section',
          'title' => __( 'Настройки автопостинга', 'evc' ),
          'desc' => __( 'Настройки автопостинга записей из WordPress на стену группы ВКонтакте.', 'evc' ),          
        )
      )
    ),  
  );  
  
  $fields = array(
   'evc_autopost_section' => array(
      array(
        'name' => 'page_url',
        'label' => __( 'Ссылка на страницу', 'evc' ),
        'desc' => __( 'Урл страницы, на которую вы будете публиковать новости.
        <br/>Например: <code>http://vk.com/pasportvzubi</code>.
        <br/><br/>Вы можете создать <a href="http://vk.com/public.php?act=new" target="_blank">новую страницу</a> ВКонтакте или найти среди ваших уже <a href="http://vk.com/public.php?act=newY" target="_blank">созданных страниц</a>.', 'evc' ),
        'type' => 'text'    
      ),      
     array(
        'name' => 'page_id',
        'label' => __( 'ID страницы ВКонтакте', 'evc' ),
        'desc' => __( 'Значение будет подставлено автоматически.', 'evc' ),
        'type' => 'text',
        'readonly' => true              
      ),     
     array(
        'name' => 'autopublish',
        'label' => __( 'Автопубликация', 'evc' ),
        'desc' => __( 'Запустить или остановить автоматическую публикацию новых материалов на стене ВКонтакте.', 'evc' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '1' => 'Запущена',
          '0' => 'Остановлена',
        )
      ),  
      array(
        'name' => 'format',
        'label' => __( 'Оформление', 'evc' ),
        'desc' => __( 'Как сообщение будет выглядеть на стене ВКонтакте.', 'evc' ),
        'type' => 'multicheck',
        'options' => array(
          'from_group' => 'Опубликовать пост от имени группы (или от имени пользователя)',
          'signed' => 'Добавить к сообщению пользователя, опубликовавшего пост',
          'add_link' => 'Добавить ссылку на статью на сайте',
        ),
        'default' => array(
          'from_group' => 'from_group',
          'add_link' => 'add_link'
        )
        
      ),  
      array(
        'name' => 'exclude_cats',
        'label' => __( 'Исключить категории', 'evc' ),
        'desc' => __( 'Статьи из отмеченных категорий не будут автоматически опубликованы на стене ВКонтакте.', 'evc' ),
        'type' => 'select_category_checklist'
        
      ),           
      array(
        'name' => 'upload_photo_count',
        'label' => __( 'Изображения', 'evc' ),
        'desc' => __( 'Сколько изображений из статьи прикрепить к сообщению ВКонтакте.', 'evc' ),
        'type' => 'select',
        'default' => '4',
        'options' => array(
          '0' => '0',
          '1' => '1',
          '2' => '2',
          '3' => '3',
          '4' => '4',
          '5' => '5'
        )                   
     ),  
     array(
        'name' => 'excerpt_length',
        'label' => __( 'Анонс', 'evc' ),
        'desc' => __( 'Сколько слов из статьи опубликовать в качестве анонса ВКонтакте.', 'evc' ),
        'type' => 'text',
        'default' => 25
      ),  
    array(
        'name' => 'excerpt_length_strings',
        'desc' => __( 'Сколько <strong>знаков</strong> из статьи опубликовать в качестве анонса ВКонтакте. 
  <br/><strong>Не рекомендуется</strong> больше 2688.', 'evc' ),
        'type' => 'text',            
        'default' => 2688
      ),                
     array(
        'name' => 'message',
        'label' => __( 'Сообщение', 'evc_bridge' ),
        'desc' => __( 'Маска сообщения для стены ВКонтакте:
        <br/><code>%title%</code> - заголовок статьи.
        <br/><code>%excerpt%</code> - анонс статьи,
        <br/><code>%link%</code> - ссылка на статью.', 'evc' ),
        'type' => 'textarea',
        'default' => "%title%\n\n%excerpt%"
      )                
    )   
  );

 //set sections and fields
 $evc_autopost_settings->set_option_name( 'evc_options' );
 $evc_autopost_settings->set_sections( $tabs );
 $evc_autopost_settings->set_fields( $fields );

 //initialize them
 $evc_autopost_settings->admin_init();
}
add_action( 'admin_init', 'evc_autopost_settings_admin_init' );


// Register the plugin page
function evc_autopost_admin_menu() {
  global $evc_autopost_settings_page; 
   
  $evc_autopost_settings_page = add_submenu_page( 'evc', 'Автопостинг на стену ВКонтакте', 'Автопостинг', 'activate_plugins', 'evc-autopost', 'evc_autopost_settings_page' );
 
  //add_action("load-$evc_sidebar_settings_page", 'evc_stats_screen_options');
  //add_action( 'admin_print_styles-' . $evc_sidebar_settings_page, 'evc_stats_styles' );
  add_action( 'admin_footer-'. $evc_autopost_settings_page, 'evc_autopost_settings_page_js' );
}
add_action( 'admin_menu', 'evc_autopost_admin_menu', 20 );

function evc_autopost_settings_page_js() {
?>
<script type="text/javascript" >
  jQuery(document).ready(function($) {

    $("#evc_autopost\\[page_url\\]").focusout(function () {
      var data = {
        action: 'evc_share_get_group_id',
        group_url: $("#evc_autopost\\[page_url\\]").val()
      };

      $.ajax({
        url: ajaxurl,
        data: data,
        type:"POST",
        dataType: 'json',  
        beforeSend: function() {
          $("#evc_autopost\\[page_url\\]\\[spinner\\]").css({'display': 'inline-block'});
        },            
        success: function(data) {
          $("#evc_autopost\\[page_url\\]\\[spinner\\]").hide();
          if (data['gid'] < 0)
            data['gid'] = -1 * data['gid'];
          $("#evc_autopost\\[page_id\\]").val(data['gid']);
          
          //console.log(data);
        }
      });                   
    });
  }); // jQuery End
</script>
<?php
}

add_action('wp_ajax_evc_share_get_group_id', 'evc_share_get_group_id');
function evc_share_get_group_id() {
  
  if(!empty($_POST))
    extract($_POST);  
  
  if (isset($group_url) && !empty($group_url)) {
    
    $gid = evc_stats_get_group_id($group_url);
    if (!$gid)
      $out['error'] = 'Error';
    else
      $out['gid'] = $gid;
  }
  else 
    $out['error'] = 'Error';
  
  print json_encode($out);
  exit;    
}


// Display the plugin settings options page
function evc_autopost_settings_page() {
  global $evc_autopost_settings;
  $options = evc_get_all_options(array(
    'evc_autopost',
    'evc_vk_api_autopost'
  ));  


  
  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>Настройки автопостинга</h2>';
    
    if (!isset($options['access_token']) || empty($options['access_token'])) {
      echo '<div class="error"><p>Необходимо настроить API ВКонтакте. Откройте вкладку "<a href="'.admin_url('admin.php?page=evc').'">Для автопостинга</a>".</p></div>';
    }
 
    echo '<div id = "col-container">';  
      echo '<div id = "col-right" class = "evc">';
        echo '<div class = "evc-box">';
        evc_ad();
        echo '</div>';
      echo '</div>';
      echo '<div id = "col-left" class = "evc">';
        settings_errors();
        $evc_autopost_settings->show_navigation();
        $evc_autopost_settings->show_forms();
      echo '</div>';
    echo '</div>';  
    
    
  echo '</div>';
}


function evc_widget_settings_admin_init() {
  global $evc_widget_settings;
  
  $evc_widget_settings = new WP_Settings_API_Class;
  $options = get_option('evc_options'); 
  
  // Compatible  
  
  $tabs = array(
    'evc_widget_groups' => array(
      'id' => 'evc_widget_groups',
      'name' => 'evc_widget_groups',
      'title' => __( 'Сообщества', 'evc' ),
      'desc' => __( '', 'evc' ),
      'submit_button' => false,
      'sections' => array(
        'evc_widget_groups_section' => array(
          'id' => 'evc_widget_groups_section',
          'name' => 'evc_widget_groups_section',
          'title' => __( 'Виджет сообществ', 'evc' ),
          'desc' => __( 'Чтобы добавить виджет сообществ ВКонтакте, откройте <a href = "'.admin_url('widgets.php').'">панель виджетов</a> WordPress, перетащите виджет "VK Сообщества" на любой из доступных сайдбаров и настройте его.', 'evc' ),          
        )
      )
    ),  
    'evc_widget_stats' => array(
      'id' => 'evc_widget_stats',
      'name' => 'evc_widget_stats',
      'title' => __( 'Статистика', 'evc' ),
      'desc' => __( '', 'evc' ),
      'submit_button' => false,
      'sections' => array(
        'evc_widget_stats_section' => array(
          'id' => 'evc_widget_stats_section',
          'name' => 'evc_widget_stats_section',
          'title' => __( 'Статистика действий ', 'evc' ),
          'desc' => evc_get_vk_widget_stats(),          
        )
      )
    ),    
  );  
  
  $fields = array(
   /*
   'evc_widget_groups_section' => array(
      array(
        'name' => 'widget_groups_desc',
        'desc' => '',
        'type' => 'html',
      ),                      
    )   
    */
  );

 //set sections and fields
 $evc_widget_settings->set_option_name( 'evc_options' );
 $evc_widget_settings->set_sections( $tabs );
 $evc_widget_settings->set_fields( $fields );

 //initialize them
 $evc_widget_settings->admin_init();
}
add_action( 'admin_init', 'evc_widget_settings_admin_init' );


// Register the plugin page
function evc_widget_admin_menu() {
  global $evc_widget_settings_page; 
   
  $evc_widget_settings_page = add_submenu_page( 'evc', 'Кнопки и виджеты ВКонтакте', 'Кнопки и виджеты', 'activate_plugins', 'evc-widgets', 'evc_widget_settings_page' );
 
  //add_action("load-$evc_sidebar_settings_page", 'evc_stats_screen_options');
  //add_action( 'admin_print_styles-' . $evc_sidebar_settings_page, 'evc_stats_styles' );
  //add_action( 'admin_footer-'. $evc_widget_settings_page, 'evc_widget_settings_page_js' );
}
add_action( 'admin_menu', 'evc_widget_admin_menu', 30 );



// Display the plugin settings options page
function evc_widget_settings_page() {
  global $evc_widget_settings;
  $options = evc_get_all_options(array(
    'evc_vk_api_widgets'
  ));  
  
  
  
  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>Кнопки и виджеты ВКонтакте</h2>';
    
    if (!isset($options['site_access_token']) || empty($options['site_access_token'])) {
      echo '<div class="error"><p>Необходимо настроить API ВКонтакте. Откройте вкладку "<a href="'.admin_url('admin.php?page=evc').'">Для виджетов</a>".</p></div>';
    }    
    
    echo '<div id = "col-container">';  
      echo '<div id = "col-right" class = "evc">';
        echo '<div class = "evc-box">';
        evc_ad();
        echo '</div>';
      echo '</div>';
      echo '<div id = "col-left" class = "evc">';
        settings_errors();
        $evc_widget_settings->show_navigation();
        $evc_widget_settings->show_forms();
      echo '</div>';
    echo '</div>';
  echo '</div>';
}

function evc_ad () {
  echo '
    <div class = "evc-boxx">
      <p><a href = "http://ukraya.ru/192/easy-vk-connect-1-3" target = "_blank">Руководство</a> и <a href = "http://ukraya.ru/196/easy-vkontakte-connect-1-3-support" target = "_blank">помощь</a> по настройке плагина.</p>
    </div>
    <h3>Автонаполняемый сайт из группы ВКонтакте в один клик!</h3>
    <p>Плагин <a href = "http://ukraya.ru/162/vk-wp-bridge" target = "_blank">VK-WP Bridge</a> позволяет создать полноценный сайт или раздел на уже действующем сайте, полностью (посты, фото, видео, комментарии, лайки и т.п.) синхронизированный с группой ВКонтакте и автообновляемый по графику.</p>
    <p><i>Хватит работать на ВКонтакте!<br/>Пусть <a href = "http://ukraya.ru/162/vk-wp-bridge" target = "_blank">ВКонтакте поработает на вас</a>!</i></p>';
        
}

add_action('admin_head', 'evc_admin_head', 99 );
function evc_admin_head () {
  echo '<style type="text/css">
    #col-right.evc {
      width: 35%;
    }
    #col-left.evc {
      width: 65%;
    }    
    .evc-box{
      padding:0 20px 0 40px;
    }
    .evc-boxx {
      background: none repeat scroll 0 0 #FFFFFF;
      border-left: 4px solid #2EA2CC;
      box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
      margin: 5px 0 15px;
      padding: 1px 12px;
    }
    .evc-boxx h3 {
      line-height: 1.5;
    }
    .evc-boxx p {
      margin: 0.5em 0;
      padding: 2px;
    }
  </style>'; 
}


define('EVC_TOKEN_URL', 'https://oauth.vk.com/access_token');
define('EVC_AUTORIZATION_URL', 'https://oauth.vk.com/authorize');
function evc_share_vk_login_url ($redirect_url = false, $echo = false) {
  //$options = get_option('evc_options');
  $options = evc_get_all_options(array(
    'evc_vk_api_widgets'
  ));  
  
  if (!$redirect_url) {
    $redirect_url = remove_query_arg( array('code', 'redirect_uri', 'settings-updated'), $_SERVER['REQUEST_URI'] );
    $redirect_url = get_bloginfo('wpurl') . $redirect_url;
  }

  $params = array(
    'client_id' => trim($options['site_app_id']),
    'redirect_uri' => $redirect_url,
    'display' => 'page',
    'response_type' => 'code',
    'scope' => apply_filters('evc_share_vk_login_url_scope', 'video,friends,offline') //
  );
  $query = http_build_query($params);  
  
  $out = EVC_AUTORIZATION_URL . '?' . $query;
  
  if ($echo)
    echo $out;
  else
    return $out;
}

add_action('admin_init', 'evc_share_vk_autorization');  
function evc_share_vk_autorization () {
  
  if ( false !== ( $token = evc_share_get_token() ) ) {
    $options = get_option('evc_vk_api_widgets');
    
    if (isset($token['access_token']) && !empty($token['access_token'])) {
      $options['site_access_token'] = $token['access_token'];
      update_option('evc_vk_api_widgets', $options);
    }
    $redirect = remove_query_arg( array('code'), $_SERVER['REQUEST_URI'] );  
    //print__r($redirect);
    wp_redirect(site_url($redirect));
    exit;
  }
   
}  
  
function evc_share_get_token () {
  $options = get_option('evc_vk_api_widgets');    
  
  if (isset($_GET['code']) && !empty($_GET['code'])) {
  // http://example.com/?code=df75esdf514c8b0c5e70b
  // http://example.com/?error=access_denied&error_reason=user_denied&error_description=User+denied+your+request
   
    $_SERVER['REQUEST_URI'] = remove_query_arg( array('code'), $_SERVER['REQUEST_URI'] );   
      
    $params = array(
      'client_id' => trim($options['site_app_id']),
      'redirect_uri' =>  site_url($_SERVER['REQUEST_URI']),
      'client_secret' => $options['site_app_secret'],
      'code' => $_GET['code']
    );
    $query = http_build_query($params);      
    //print__r($query); //
    
    $data = wp_remote_get(EVC_TOKEN_URL.'?'.$query);
    //print__r($data); //
    //exit; 
    if (is_wp_error($data)) {
      //print__r($data); //
      //exit;
      return false;
    }
  
    $resp = json_decode($data['body'],true);
    // {"error":"invalid_grant","error_description":"Code is invalid or expired."}
    // {"access_token":"e4cfd9sdfsdf123456929df640b6ab298aa77d87a250900b9393a4e46","expires_in":8456456345,"user_id":1}
    //print__r($resp);
    //exit;
    if (isset($resp['error'])) {
      return false; 
    }
      
    return $resp;  
  }
  return false;  
}