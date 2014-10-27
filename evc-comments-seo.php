<?php

include_once('inc/evc-api.php'); 

// 2014-04-24

add_action('wp_head', 'evc_comments_meta', 100 );
function evc_comments_meta () {
  global $post;
  
  echo '<script type="text/javascript">';
  if (is_single()) {
    echo 'evc_post_id = '. $post->ID .';'; 
  }
  else
    echo 'evc_post_id = false; '; 
  echo '</script>';        
}

 
function evc_comments_add_comment ($comment, $post_id, $widget_api_id, $comment_parent = null) {
  if (isset($comment['cid']))
    $comment['id'] = $comment['cid'];
    
  $vk_item_id = 'app' . $widget_api_id . '_' . $comment['id'];

  $comment_wp_id = evc_get_wpid_by_vkid($vk_item_id, 'comment');
  if ($comment_wp_id && isset($comment_wp_id[$vk_item_id]))
    return $comment_wp_id[$vk_item_id];
    
    if (isset($comment['user']) && !empty($comment['user'])) {
      $user_wp_id = evc_get_wpid_by_vkid($comment['user']['id'], 'user');
      if(!$user_wp_id) {
        $user_wp_id = evc_add_user($comment['user']);
        if (!$user_wp_id)
          return false;
      }
      else
        $user_wp_id = $user_wp_id[$comment['user']['id']];
              
    }
    else
      return false;
    
    $args = array(
      'comment_post_ID' => $post_id,
      'comment_content' => $comment['text'],
      'user_id' => $user_wp_id,
      'comment_date' => date('Y-m-d H:i:s', $comment['date'] + ( get_option( 'gmt_offset' ) * 3600 )),
      'comment_approved' => 1,
      
      'comment_author_IP' => preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] ),
      'comment_agent' => substr($_SERVER['HTTP_USER_AGENT'], 0, 254)    
    );
    
    if (isset($comment_parent) && !empty($comment_parent))
      $args['comment_parent'] = $comment_parent;
    
    $args = apply_filters('evc_comments_add_comment_args', $args, $comment);
    //print__r($args); //
    
    $comment_wp_id = wp_insert_comment($args);
    
    if ($comment_wp_id)
      update_comment_meta($comment_wp_id, 'vk_item_id', $vk_item_id);
    
  return $comment_wp_id;
}



add_action('wp_ajax_evc_comments_refresh', 'evc_comments_refresh_comments');
add_action('wp_ajax_nopriv_evc_comments_refresh', 'evc_comments_refresh_comments');
function evc_comments_refresh_comments () { 
  $evc_comments_refresh = get_transient('evc_comments_refresh');
  
  $options = evc_get_all_options(array(
    'evc_vk_api_widgets',
    'evc_comments',
    'evc_comments_pro'
  )); 
   
  if(!empty($_POST))
    extract($_POST);  
  //$out = print_r($_POST, 1);
  //print json_encode($out);
  //exit();    
  $options['comments_pro_count'] = !isset($options['comments_pro_count']) ? 10 : $options['comments_pro_count'];
  $args = array(
    'widget_api_id' => $widget_api_id, 
    'url' => $url, 
    //'offset' => 0,
    'count' => $options['comments_pro_count'], 
  );  
  if (isset($page_id)) {
    $args['page_id'] = $page_id;
    $post_id = $page_id; // Be Careful!!!
  }
  
  if (!isset($post_id) || !$post_id)
    return false;
  
  if (isset($evc_comments_refresh) && $evc_comments_refresh)  
    return false;
    
  set_transient('evc_comments_refresh', sprintf( '%.22F', microtime( true ) ) );
  $comments = evc_comments_get_comments($args);
  delete_transient('evc_comments_refresh');
  
  if (!$comments || !$comments['count'])
    return false;
    
  // Add new Comments
  foreach ($comments['posts'] as $comment) {
    unset($comment_wp_id);
    $comment_wp_id = evc_comments_add_comment($comment, $post_id, $widget_api_id);
    //print__r($comment_wp_id);
    if (isset($comment['comments'])) {
      foreach($comment['comments']['replies'] as $reply) 
        $reply_wp_id = evc_comments_add_comment($reply, $post_id, $widget_api_id, $comment_wp_id);
    }
  }  
  //return true;
  
  evc_refresh_vk_img_all ();
  
  print json_encode($comments['count']);
  exit(); 
}  
  
  
  

function evc_comments_get_comments($args = array()) { 
  $options = evc_get_all_options(array(
    'evc_vk_api_widgets',
    'evc_comments'
  ));  
  
  $default = array(
    'order' => 'date', 
    'fields' => 'photo_100,photo_max_orig,screen_name,replies', 
    'v' => '5.10', 
    'lang' => 'ru'
  );
  if (isset($page_id))
    $default['page_id'] = $page_id;
  
  $args = wp_parse_args($args, $default);
  $args = apply_filters('evc_comments_get_comments_args', $args);  
  $query = http_build_query($args);
  //print__r($args); //   
  //exit(); 
  $data = wp_remote_get(EVC_API_URL.'widgets.getComments?'.$query, array(
    'sslverify' => false
  ));  
  //evc_add_log('evc_comments_get_comments:' . print_r($data, 1));
   
  if (is_wp_error($data)) {
    evc_add_log('evc_comments_get_comments: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return false;
  }

  if (isset($data['response']) && isset($data['response']['code']) && $data['response']['code'] != 200 ){
    evc_add_log('evc_wall_post: RESPONSE ERROR. ' . $data['response']['code'] . ' '. $data['response']['message']);
    return false;
  } 
  
  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) { 
    
    if (isset($resp['error']['error_code']))
      evc_add_log('evc_comments_get_comments: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']); 
    else
      evc_add_log('evc_comments_get_comments: VK Error. ' . $resp['error']);           
    return false; 
  }  

  //print__r($resp); //
  return $resp['response'];    
}    

add_action('evc_comments_vk_async_init', 'evc_comments_pro_vk_async_init');
function evc_comments_pro_vk_async_init() {
  $options = get_option('evc_comments_pro');
  
  if (isset($options['comments_pro_on']) && $options['comments_pro_on'] == 0)
    return '';
  
  //<script type="text/javascript">  
  ?>

      evcCommentsRefresh = false;            
      var new_data = {
        post_id: evc_post_id,
        url: document.URL,
        widget_api_id: jQuery('meta[property="vk:app_id"]').attr('content'),
        action: 'evc_comments_refresh'
      };

      VK.Observer.subscribe('widgets.comments.new_comment', function(num, last_comment, date, sign, wID, n) {

        if ( typeof VKWidgetsComments[n - 1] !== 'undefined' && typeof VKWidgetsComments[n - 1].page_id !== 'undefined' ) 
          new_data.page_id = VKWidgetsComments[n - 1].page_id;
        else
          new_data.page_id = VKWidgetsComments[0].page_id;
        //console.log(new_data); //
        
        if (!evcCommentsRefresh) {
          evcCommentsRefresh = true;
          jQuery.ajax({
            url: ajaxurl,
            data: new_data,
            type:"POST",
            dataType: 'json',
            success: function(data) {
              //console.log(data); //
              evcCommentsRefresh = false;
            }
          }); 
        }
        
      });
      
      VK.Observer.subscribe('widgets.comments.delete_comment', function(num, last_comment, date, sign) {
        //console.log(date);
        
      });    
     
<?php
//</script>
}

add_filter('evc_comments_admin_tabs', 'evc_comments_pro_admin_tabs');
function evc_comments_pro_admin_tabs($tabs) {
  $tabs['evc_comments_pro'] =  array(
    'id' => 'evc_comments_pro',
    'name' => 'evc_comments_pro',
    'title' => __( 'Импорт & Индексация', 'evc' ),
    'desc' => __( 'Импорт & Индексация', 'evc' ),
    'sections' => array(
      'evc_comments_pro_section' => array(
        'id' => 'evc_comments_pro_section',
        'name' => 'evc_comments_pro_section',
        'title' => __( 'Импорт & Индексация', 'evc' ),
        'desc' => __( 'Основные настройки импорта и индексации комментариев, оставленных через виджет комментариев ВКонтакте.', 'evc' ),          
      )
    )
  ); 
  return $tabs;
}

add_filter('evc_comments_admin_fields', 'evc_comments_pro_admin_fields');
function evc_comments_pro_admin_fields($fields) {
  $fields['evc_comments_pro_section'] =  array( 
    array(
      'name' => 'comments_pro_on',
      'label' => __( 'Импорт комментариев', 'evc' ),
      'desc' => __( 'Импортировать ли на сайт комментарии, оставленные через виджет комментариев ВКонтакте.', 'evc' ),
      'type' => 'radio',
      'default' => '1',
      'options' => array(
        '1' => 'Включить',
        '0' => 'Отключить',
      )
    ),            
    array(
      'name' => 'comments_pro_count',
      'label' => __( 'Глубина синхронизации', 'evc' ),
      'desc' => __( 'От <code>10</code> до <code>200</code>. Установите высокое значение, если виджет комментариев стоял у вас на сайте раньше, и вы хотите <b>импортировать коментарии, оставленные ранее</b>. В иных случаях можно оставить значение по умолчанию., ', 'evc' ),
      'type' => 'text',
      'default' => 10
    ),  
    array(
      'name' => 'img_refresh',
      'label' => __( 'Загружать изображений', 'evc' ),
      'desc' => __( 'Импортируя комментарии, плагин сохраняет на сайте аватары новых пользователей. Чтобы снизить нагрузку на сайт, аватары сохраняются пакетами. Эта опция контролирует сколько изображений загружать в одном пакете. Для слабых серверов оставьте <code>10</code>. Для хороших можно увеличить до любого значения начиная с <code>50</code> и более.', 'evc' ),
      'type' => 'text',
      'default' => 10,
    )      
  ); 
  return $fields;
}

