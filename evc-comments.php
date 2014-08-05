<?php

add_action('wp_footer', 'evc_comments_footer_scripts', 20); 
function evc_comments_footer_scripts(){
	global $post;
  $options = get_option('evc_comments');
  
  $comment_widget_layout = get_post_meta ($post->ID, 'comment_widget_layout', true);
  if ($comment_widget_layout)
    $options['comment_widget_layout'] = $comment_widget_layout;
   
  ?>
  <script type="text/javascript">
  /* <![CDATA[ */
	jQuery(document).ready(function($) {
    
    if ( typeof VKWidgetsComments !== 'undefined' && VKWidgetsComments.length ) {
		  if ($('#vk-widget-<?php echo $post->ID; ?>').length) {
        <?php 
        if ($options['comment_widget_layout'] == 'instead' && isset($options['comment_widget_respond']) && !empty($options['comment_widget_respond'])) { 
        ?>
        if ( $('<?php echo $options['comment_widget_respond'];?>').length ) {          
          $('<?php echo $options['comment_widget_respond'];?> form').hide();
		      $('<?php echo $options['comment_widget_respond'];?>').append($('#vk-widget-<?php echo $post->ID; ?>' ));
        }
        <?php 
        }
        if (isset($options['comment_widget_comments']) && !empty($options['comment_widget_comments'])) {         
          if ($options['comment_widget_layout'] == 'before') {
            
          ?>
            $('<?php echo $options['comment_widget_comments'];?>').prepend($('#vk-widget-<?php echo $post->ID; ?>' ));          
          <?php            
          }
          if ($options['comment_widget_layout'] == 'after') {
          ?>
            $('<?php echo $options['comment_widget_comments'];?>').append($('#vk-widget-<?php echo $post->ID; ?>' ));                    
          <?php  
          }
        }
        ?>
      }
		
		  <?php
        if (isset($options['comment_widget_hide_wp_comments']) && $options['comment_widget_hide_wp_comments']) {
      ?>
      cClose = false;
      $( "<?php echo $options['comment_widget_comments_list'];?>" ).wrap('<div class = "evc-comments-wrap"></div>');
		
      docViewHeight = $(window).height();
      $(document).scroll(function () {
        var docViewTop = $(window).scrollTop();
        var elemTop = $('.evc-comments-wrap').offset().top;
        //var elemBottom = elemTop + $('.evc-comments-wrap').height();         
        if ( elemTop * 3 / 4 <= docViewTop && !cClose ) {
          cClose = true;  
          $( ".evc-comments-wrap" ).animate({ "height": 0}, 800 );
          //console.log(elemTop + ' >= ' + docViewHeight+ '+'+ docViewTop);
        }
      });
      <?php
        }
      ?>
    }
 		
  
	}); // End jQuery 
   
  /* ]]> */
  </script><?php
}

add_action('comment_form_before','evc_comments_add_widget');
function evc_comments_add_widget () {
	global $post;
  $options = get_option('evc_comments');  
	
  $comment_widget_insert = get_post_meta ($post->ID, 'comment_widget_insert', true);
  if ($comment_widget_insert)
    $options['comment_widget_insert'] = $comment_widget_insert;
      
  if (isset($options['comment_widget_insert']) && $options['comment_widget_insert'] == 'auto') {
	  if ( (isset($options['comment_widget_for']) && $options['comment_widget_for'] == 'unregistered' && !is_user_logged_in()) || 
    (isset($options['comment_widget_for']) && $options['comment_widget_for'] == 'all') || 
    !isset($options['comment_widget_for']) ) {
	    echo evc_vk_widget_comments ('vk-widget-' . $post->ID);
    }
  }
}
	
function evc_vk_widget_comments ($element_id = null, $args = array(), $page_id = null) {  
	global $post;
  $options = get_option('evc_comments');	
	
  if (!isset($element_id))
    $element_id = 'vk-widget-' . $post->ID;
    
	$o['width'] = $options['comment_widget_width'];
	$o['height'] = $options['comment_widget_height'];
	$o['limit'] = $options['comment_widget_limit'];
	
	if (!isset($options['comment_widget_attach']) || empty($options['comment_widget_attach']) || isset($options['comment_widget_attach']['none']) )
		$o['attach'] = 'false';
	else {
		foreach($options['comment_widget_attach'] as $attach)
			$o['attach'][] = $attach;
		
		$o['attach'] = implode(',', $o['attach']);
	}
	
	$o['norealtime'] = $options['comment_widget_norealtime'];
	$o['autoPublish'] = $options['comment_widget_autopublish'];
  
  $o = wp_parse_args($args, $o);
	$o = evc_vk_widget_data_encode($o);
	
  $out = '
<script type="text/javascript">
  VKWidgetsComments.push ({
    element_id: "'.$element_id.'",
    options: '.$o;
  
  if (isset($page_id))
    $out .= ',page_id: '.$page_id;
  elseif (isset($options['comment_widget_page_id']) && $options['comment_widget_page_id'])
    $out .= ',page_id: '.$post->ID;
  
  $out .= '
  });  
  
  VKWidgets.push ({
    type: "comments",
    element_id: "'.$element_id.'",
    options: '.$o;
  
  if (isset($page_id))
    $out .= ',page_id: '.$page_id;
  elseif (isset($options['comment_widget_page_id']) && $options['comment_widget_page_id'])
    $out .= ',page_id: '.$post->ID;
  
  $out .= '
  });    
</script>';

		$out .= '<div class = "vk_widget_comments" id = "'.$element_id.'"></div>	
  ';
  
  return $out;
}	
	
	
function evc_comments_admin_init() {
  global $evc_comments;
  $evc_comments_author = get_option('evc_comments_author');
    
  $evc_comments = new WP_Settings_API_Class;
  
  $tabs = array(
    'evc_comments' => array(
      'id' => 'evc_comments',
      'name' => 'evc_comments',
      'title' => __( 'Комментарии', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_comments_section' => array(
          'id' => 'evc_comments_section',
          'name' => 'evc_comments_section',
          'title' => __( 'Настройки виджета комментариев ВКонтакте', 'evc' ),
          'desc' => __( 'Основные настройки для виджета комментариев.', 'evc' ),          
        ),
        'evc_comments_show' => array(
          'id' => 'evc_comments_show',
          'name' => 'evc_comments_show',
          'title' => __( 'Отображение виджета комментариев ВКонтакте', 'evc' ),
          'desc' => __( 'Основные настройки отображения виджета комментариев.', 'evc' ),          
        ),        
        'evc_comments_dev' => array(
          'id' => 'evc_comments_dev',
          'name' => 'evc_comments_dev',
          'title' => __( 'Служебные настройки', 'evc' ),
          'desc' => __( 'Меняйте только, если понимаете что делаете.', 'evc' ),          
        )               
      )
    ), 
    'evc_comments_pro' =>  array(
      'id' => 'evc_comments_pro',
      'name' => 'evc_comments_pro',
      'title' => __( 'Расширенная версия', 'evc' ),
      'desc' => __( 'Расширенная версия', 'evc' ),
      'submit_button' => false,
      'sections' => array(
        'evc_comments_pro_section' => array(
          'id' => 'evc_comments_pro_section',
          'name' => 'evc_comments_pro_section',
          'title' => __( 'VK SEO комментарии', 'evc' ),
          'desc' => __( '<p><b>Тонны бесплатного уникального контента</b> на ваш сайт! 
<br/><b>Толпы посетителей</b> по низкочастотным запросам! 
<br/>Заставьте поисковые системы <b>индексировать</b> комментарии, оставленные через <em>виджет комментариев ВКонтакте</em>!</p> 
<p>'.get_submit_button('Установить сейчас', 'secondary', 'get_vk_seo_comments', false).'</p>
<p>Модуль <em>VK SEO комментарии</em> <b>импортирует комментарии</b>, оставленные через <em>виджет комментариев ВКонтакте</em> на ваш сайт. Они превращаются в обычные комментарии, которые оставляют зарегистрированные пользователи.</p>
<p>При этом импортируются:
<ol><li><b>Имя</b> и <b>Фамилия</b> пользователя (оставившего комментарий),</li>
<li><b>Аватар</b> пользователя,</li>
<li><b>Текст</b> комментария,</li>
<li><b>Ветки</b> комментариев.</li></ol></p>
<p>Если на вашем сайте уже был установлен <em>виджет комментариев ВКонтакте</em>, <b>плагин сам импортирует</b> ранее оставленные комментарии.</p>
<p><b>Профессиональная техническая поддержка бесплатно</b> поможет решить любую проблему по работе плагина.</p>
<p><b>Зарабатывайте</b> с нами. Попробуйте плагин сами и предложите его своим друзьям или клиентам. Первого мая, в День Труда мы запускаем нашу партнерскую программу. Все, кто приобретет плагин до этой даты, получат повышенные партнерские отчисления.</p>
<p>'.get_submit_button('Установить сейчас', 'primary', 'get_vk_seo_comments2', false).'</p>', 'evc' ),         
        )
      )
    )     
  );  
  $tabs = apply_filters('evc_comments_admin_tabs', $tabs);
  
  $fields = array(
   'evc_comments_section' => array(					
      array(
        'name' => 'comment_widget_width',
        'label' => __( 'Ширина блока', 'evc' ),
        'desc' => __( 'Ширина блока комментариев, в px (больше 300). 
				<br/>Например: <code>300</code>.', 'evc' ),
				'type' => 'text',
				'default' => 300
      ),                 
      array(
        'name' => 'comment_widget_height',
        'label' => __( 'Высота блока', 'evc' ),
        'desc' => __( 'Высота блока комментариев, в px (больше 500). 
				<br/>Если <code>0</code> - не ограничена.
				<br/>Например: <code>500</code>.', 'evc' ),
				'type' => 'text',
				'default' => 0
      ),           
      array(
        'name' => 'comment_widget_limit',
        'label' => __( 'Число комментариев', 'evc' ),
        'desc' => __( 'Количество комментариев на странице: от 5 до 100. 
				<br/>Например: <code>10</code>.', 'evc' ),
				'type' => 'text',
				'default' => 10
      ),           
      array(
        'name' => 'comment_widget_attach',
        'label' => __( 'Прикрепления', 'evc' ),
        'desc' => __( 'Разрешить или запретить прикрепления к комментариям.', 'evc' ),
        'type' => 'multicheck',
        'options' => array(
          'none' => '<b>Запретить все</b> прикрепления.',
          'all' => '<b>Разрешить все</b> прикрепления.',
					'graffiti' => '<small>Разрешить граффити.</small>',
					'photo' => '<small>Разрешить изображения.</small>',
					'audio' => '<small>Разрешить аудио.</small>',
					'video' => '<small>Разрешить видео.</small>',
					'link' => '<small>Разрешить ссылки.</small>',
        ),
        'default' => array(
          'none' => 'none'
        )				
      ),   
      array(
        'name' => 'comment_widget_norealtime',
        'label' => __( 'Обновление', 'evc' ),
        'desc' => __( 'Обновление ленты комментариев в реальном времени.', 'evc' ),
        'type' => 'radio',
				'default' => '0',
        'options' => array(
          '0' => 'Включено',
          '1' => 'Отключено',
        )
      ), 			
      array(
        'name' => 'comment_widget_autopublish',
        'label' => __( 'Публиковать в статус', 'evc' ),
        'desc' => __( 'Автоматическая публикация комментария в статус пользователя.', 'evc' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '1' => 'Включено',
          '0' => 'Отключено',
        )
      )
   ),
   'evc_comments_show' => array(       
      array(
        'name' => 'comment_widget_insert',
        'label' => __( 'Размещать виджет', 'evc' ),
        'desc' => __( 'Автоматически или вручную размещать виджет комментариев на странице сайта.', 'evc' ),
        'type' => 'radio',
        'default' => 'auto',
        'options' => array(
          'auto' => 'Автоматически',
          'manual' => 'Вручную',
        )
      ),
      array(
        'name' => 'comment_widget_layout',
        'label' => __( 'Поместить виджет', 'evc' ),
        'desc' => __( 'В каком месте на странице поместить виджет комментариев ВКонтакте.', 'evc' ),
        'type' => 'radio',
        'default' => 'instead',
        'options' => array(
          'instead' => '<b>Вместо</b> стандартной формы комментариев',
          'before' => '<b>До</b> блока комментариев',
          'after' => '<b>После</b> блока комментариев',
        )
      ),
      array(
        'name' => 'comment_widget_for',
        'label' => __( 'Показывать виджет', 'evc' ),
        'desc' => __( 'Кому показывать виджет комментариев ВКонтакте.', 'evc' ),
        'type' => 'radio',
        'default' => 'all',
        'options' => array(
          'all' => 'Всем посетителям',
          'unregistered' => 'Только <b>незарегистрированным</b> посетителям',
        )
      ),
      array(
        'name' => 'comment_widget_hide_wp_comments',
        'label' => __( 'Скрывать комментарии', 'evc' ),
        'desc' => __( 'Скрывать вордпресс комментарии от посетителей.', 'evc' ),
        'type' => 'radio',
        'default' => '1',
        'options' => array(
          '1' => 'Да',
          '0' => 'Нет',
        )
      )
   ),
   'evc_comments_dev' => array(        
      array(
        'name' => 'comment_widget_page_id',
        'label' => __( 'Page ID', 'evc' ),
        'desc' => __( 'Использовать в том случае, если у одной и той же статьи может быть несколько адресов.', 'evc' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '1' => 'Использовать',
          '0' => 'Не использовать',
        )
      ),      
      array(
        'name' => 'comment_widget_respond',
        'label' => __( '', 'evc' ),
        'desc' => __( 'Родительский CSS контейнер для формы "Написать комментарий".', 'evc' ),
        'type' => 'text',
        'default' => '#respond'
      ), 
      array(
        'name' => 'comment_widget_comments_list',
        'label' => __( '', 'evc' ),
        'desc' => __( 'CSS контейнер для списка комментариев.', 'evc' ),
        'type' => 'text',
        'default' => '#comments .comment-list'
      ), 
      array(
        'name' => 'comment_widget_comments',
        'label' => __( '', 'evc' ),
        'desc' => __( 'CSS контейнер для блока комментариев.', 'evc' ),
        'type' => 'text',
        'default' => '#comments'
      ),                                      				                
    )    
  );
  $fields = apply_filters('evc_comments_admin_fields', $fields);
  
  $evc_comments->set_sections( $tabs );
  $evc_comments->set_fields( $fields );

  //initialize them
  $evc_comments->admin_init();
}
add_action( 'admin_init', 'evc_comments_admin_init' );


// Register the plugin page
function evc_comments_admin_menu() {
  global $evc_comments_page; 
   
  $evc_comments_page = add_submenu_page( 'evc', 'Виджет комментариев ВКонтакте', 'Комментарии', 'activate_plugins', 'evc-comments', 'evc_comments_page' );

}
add_action( 'admin_menu', 'evc_comments_admin_menu', 25 );


add_action('evc_vk_async_init', 'evc_comments_vk_async_init');
function evc_comments_vk_async_init() {
  ?>
  //console.log(VKWidgetsComments);
  // COMMENTS
    if (typeof VKWidgetsComments !== 'undefined' ) {
      //console.log(VKWidgetsComments);
      for (index = 0; index < VKWidgetsComments.length; ++index) {
        VK.Widgets.Comments(
          VKWidgetsComments[index].element_id, 
          VKWidgetsComments[index].options, 
          VKWidgetsComments[index].page_id
        );
      }
    <?php  
      do_action('evc_comments_vk_async_init');
    ?>    
    }
<?php
}


// Display the plugin settings options page
function evc_comments_page() {
  global $evc_comments;
	$options = evc_get_all_options(array(
		'evc_vk_api_widgets',
		'evc_comments'
	));	

  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>Виджет комментариев ВКонтакте</h2>';
    
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
        $evc_comments->show_navigation();
        $evc_comments->show_forms();
      echo '</div>';
    echo '</div>';	
		
    
  echo '</div>';
}