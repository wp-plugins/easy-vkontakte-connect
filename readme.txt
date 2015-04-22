=== Easy VKontakte Connect ===
Contributors: alekseysolo
Tags: vkontakte, vk, autopublish, post, social, share, wall, analytics, comments, polls, surveys
Requires at least: 3.2
Tested up to: 4.2
Stable tag: 1.8.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Автопубликация записей с фото на стене ВКонтакте, анализ групп, кнопки, виджеты...

== Description ==

Весь API ВКонтакте. 

* Автопубликация записей с фото на стену группы ВКонтакте. *!!!** Поддерживаются post_type. 
* **!!!** Кнопки Поделиться: 7 социальных сетей, интерактивный настройщик, 4 темы и множество вариантов отображения. Сети: ВКонтакте, Одноклассники, Мой Мир, Facebook, Google+, Twitter, Pinterest.
* **!!!** Социальный замок: чтобы увидеть закрытое содержимое на сайте, нужно подписаться на группу ВКонтакте.
* Авторизация через ВКонтакте.
* Опросы ВКонтакте: создать, добавить на сайт, поделиться.
* Виджет комментариев ВКонтакте; **!!!** респонсивный.
* Индексация & импорт комментариев, оставленных через виджет комментариев ВКонтакте.
* Виджет сообществ ВКонтакте.
* Невероятная четверка сайдбаров: всплывающий, выезжающий, до и после контента.
* Анализ групп ВКонтакте.

Подробности и техническая поддержка [на сайте плагина](http://ukraya.ru/428/easy-vkontakte-connect-evc "Техническая поддержка"). 


This plugin allows you to publish posts on the VKontakte wall in automatic or manual mode, along with the images attached to post and provide VKontakte Wall Analytics.

* Uses the API VKontakte
* **!!!** Social share buttons with interactive builder. jQuery part based on the Social Likes library by Artem Sapegin, [git](https://github.com/sapegin/social-likes "Social Likes library by Artem Sapegin").
* VK Community Widget
* Sidebars: overlay, slide, before and after posts; triggered by timeout or scrolling actions.
* Provide VKontakte Wall Analytics: Sort group wall posts by: likes, reposts, comments, publish time
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

1. Social Likes Buttons Themes and Variation.
2. VK API Settings.
3. Sidebars Settings.
4. Autopost Settings.
5. VK Community Widget.
6. Edit Post Page.
7. VKontakte Wall Analytics page.

== Changelog ==

= 1.8.3.1 / 2015-03-26 =
* Added superglobal options for buttons inserting.

= 1.8.3 / 2015-03-18 =
* Added post_types filters for autoposting.
* Fixed Emoji in Groups Analytics.
* Fixed quotes in social buttons.
* Added overlay-sidebar responsivity.
* Added social-likes 2015-03-10 v3.0.14

= 1.8.2 / 2014-12-30 =
* Added features setting VK Comments widget and Share Buttons for each pages and posts separetly.
* Added Responsivity for VK Comments Widget.
* Added ability to place shortcode in widgets.
* Fixed problem with ad column.
* Fixed wrong shortcode for polls in All Poll page.
* Added evc-polls vk error 17 handler.
* Added social-likes 2014-12-11 v3.0.10

= 1.8.1 / 2014-10-27 =
* New sidebar action: when leave the page. Increase your conversion!
* Fix minor bugs.

= 1.8 / 2014-09-29 =
* Added social share buttons with interactive builder.
* Added slide sidebar responsive width.
* Added vk community shortcode.
* Fixed minor bugs in comments widget.

= 1.7.1 / 2014-08-05 =
* **!!!** Added compatibility with Amazing Group Members Online Stats in PRO version.
* Added missing option Show VK login button.
* Changed autopost method, maybe increased posted text size.
* Added additional error handler.

= 1.7 / 2014-07-14 =
* Added VK Athorization.
* Added Social Locker.
* Etc...

= 1.6 / 2014-07-01 =
* Add VK Polls widget.
* Fix error in VK Community Widget.
* Etc...

= 1.5.1 / 2014-05-06 =
* Fix undefined variable in evc_share_meta.

= 1.5 =
* **Important** Added VK Comments Indexation feature.
* Return parameters wide in VK Cummunity Widget settings.

= 1.4 =
* Add VK Comments Widget.

= 1.3.1 =
* Correct links in message.
* Add dashicons to front page.

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
