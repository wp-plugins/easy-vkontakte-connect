=== Easy VKontakte Connect ===
Contributors: alekseysolo
Tags: vkontakte, vk, autopublish, post, social, share, wall, analytics
Requires at least: 3.2
Tested up to: 3.8
Stable tag: 1.3
License: GPLv2 or later

Автопубликация записей с фото на стене ВКонтакте, анализ групп, кнопки, виджеты...

== Description ==

**1. Автопубликация записей с фото на стену ВКонтакте**

* Автоматический и ручной режимы.
* Не публиковать записи из заданных рубрик и др.

**2. Виджет сообществ ВКонтакте**

* **Асинхронная** загрузка (без тормозов!).
* Три вида на выбор: название группы, стена группы, подписчики.
* Легко разместить в любом месте сайта.

**3. Дополнительные сайдбары для любых виджетов**

* Появление через **интервал** или **скроллинг** (после пролистывания экрана).
* **Всплывающий** - появляется поверх основного содержания сайта.
* **Выезжающий** - выезжает из правого нижнего угла сайта.
* **До** и **после** контента.

В сайдбары можно поместить виджет сообществ ВКонтакте, рекламу, список похожих постов или любую другую информацию.

**4. Анализ групп ВКонтакте**

* До 100 записей со стены любой группы
* Сортировка по лайкам, репостам, комментариям и дате.

**Помощь и пожелания**

Техническая поддержка на сайте плагина: http://ukraya.ru/tag/easy-vkontakte-connect . 

This plugin allows you to publish posts on the VKontakte wall in automatic or manual mode, along with the images attached to post and provide VKontakte Wall Analytics.

* Uses the API VKontakte
* VK Community Widget
* Sidebars: overlay, slide, before and after posts; triggered by timeout or scrolling actions.
* **NEW in 1.0** Provide VKontakte Wall Analytics: Sort group wall posts by: likes, reposts, comments, publish time
* Automatically publish new posts on the VKontakte wall
* Manually publish posts on the VKontakte wall
* Publish images attached to the posts on the VKontakte wall 
* Note categories of posts which are ecluded from autopublish to VKontakte wall

Requires WordPress 3.2 and PHP 5.

== Installation ==

1. Upload all files to the `/wp-content/plugins/easy-vkontakte-connect/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Follow the instruction of plugin configuration

== Screenshots ==

1. VK API Settings.
2. Sidebars Settings.
3. Autopost Settings.
4. VK Community Widget.
5. Edit Post Page.
6. VKontakte Wall Analytics page.

== Changelog ==

= 1.3 =
* VK Community Widget
* Sidebars: overlay, slide, before and after posts; triggered by timeout or scrolling actions.

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
