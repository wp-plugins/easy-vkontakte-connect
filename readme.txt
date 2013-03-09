=== Easy VKontakte Connect ===
Contributors: alekseysolo
Tags: vkontakte, vk, autopublish, post, social, share, wall, analytics
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 1.1

This plugin allows you autopublish blog posts with pictures to VKontakte wall and provide VKontakte Wall Analytics. 

== Description ==

This plugin allows you to publish posts on the VKontakte wall in automatic or manual mode, along with the images attached to post and provide VKontakte Wall Analytics.

* Uses the API VKontakte
* **NEW in 1.0** Provide VKontakte Wall Analytics: Sort group wall posts by: likes, reposts, comments, publish time
* Automatically publish new posts on the VKontakte wall
* Manually publish posts on the VKontakte wall
* Publish images attached to the posts on the VKontakte wall 
* Note categories of posts which are ecluded from autopublish to VKontakte wall

Requires WordPress 3.2 and PHP 5.

Плагин позволяет публиковать на стене ВКонтакте статьи в автоматическом и ручном режиме вместе с изображениями, прикрепленными к статье и позволяет **анализировать сообщения со стены любой группы ВКонтакте** по лайкам, респостам, комментариям и времени публикации.

* Использует ВКонтакте API

**Анализ записей со стены группы ВКонтакте**

* Плагин позволяет отобразить для анализа до 100 записей со стены любой открытой (и закрытой, если пользователь является ее членом) группы (или паблика) ВКонтакте.
* Записи можно сортировать по 4 критериям: время публикации, число лайков, репостов, комментариев. Дополнительно возможна сортировка в двух направлениях: по возрастанию, по убыванию критерия.
* При изменении параметров сортировки перемещение записей происходит без обновления страницы.    

**Автопубликация на стене группы ВКонтакте**

* Автоматическая публикация новых статей на стену в ВКонтакте
* Ручная публикация статей на стену ВКонтакте
* Публикация **изображений**, прикрепленных к статье, на стене ВКонтакте
* Отметить категории, статьи из которых не будут автоматически опубликованы ВКонтакте


**Дополнительные возможности**

Предложения о дополнительных возможностях оставляйте на сайте плагина: http://ukraya.ru/tag/easy-vkontakte-connect


== Installation ==

1. Upload all files to the `/wp-content/plugins/easy-vkontakte-connect/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Follow the instruction of plugin configuration

== Screenshots ==

1. Option Page - 1.
2. Option Page - 2.
3. Edit Post Page.
4. VKontakte Wall Analytics page.

== Changelog ==

= 1.1 =
* **Important:** Correct to correspond VK API changes in photos.getWallUploadServer, photos.saveWallPhoto.
* **Important:** Correct access token scopes.
* Set sslverify = false in wp_remote_get.
* Add capability to show link to Group Analytics in admin bar.

= 1.0 =
* **New:** Provide VKontakte Wall Analytics.
* Process captcha if needed.
* New tags %link% in wall post publish mask.
* Cut posts in accordance with the VKontakte limits.
* Paragraph tags now are replaced by \n\n.

= 0.2 =
* Fix minor bugs.

= 0.1 =
* First stable release.
