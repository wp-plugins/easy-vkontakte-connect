=== Easy VKontakte Connect ===
Contributors: alekseysolo
Tags: vkontakte, vk, autopublish, post, social, share, wall, analytics
Requires at least: 3.2
Tested up to: 3.8
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Автопубликация записей с фото на стене ВКонтакте, анализ групп, кнопки, виджеты...

== Description ==

Весь API ВКонтакте. Уже реализовано: автопубликация на стену, виджет сообществ, невероятная четверка дополнительных сайдбаров для любых виджетов, анализ групп.

**1. Автопубликация записей с фото на стену группы ВКонтакте**

* По *графику* или в *ручном режиме*.
* Не публиковать записи из *отмеченных рубрик*.
* *Маска* сообщения для стены ВКонтакте.

*Быстрый старт*: подключите VK API и укажите урл группы, куда транслировать записи.

**2. Виджет сообществ ВКонтакте**

* Полностью **асинхронная** загрузка. **Без тормозов!**
* **Три вида**: только название группы, фото подписчиков, стена группы.
* Сколько угодно виджетов любых сообществ на одной странице.
* **Статистика** по дням и сообществам: сколько **вступило** и **вышло**.

*Легкая установка*: укажите урл группы ВКонтакте и поместите виджет на любой сайдбар.

**3. Невероятная четверка дополнительных сайдбаров для любых виджетов**

* **Всплывающий** - появляется поверх основного содержания сайта. 
* **Выезжающий** - плавно выезжает из правого нижнего угла сайта. 
* *До контента* - между заглавием и содержанием записи.
* *После контента* - сразу после содержания записи.
* Появляются через установленный **интервал** (после загрузки страницы) или **скроллинг** (пролистывание определенной части экрана).
* Гибкие настройки показов: сколько раз показать сайдбар пользователю и через какое время он увидит его снова. *Позаботьтесь о посетителях своего сайта!*

В сайдбары может быть помещено все, что угодно? от виджета сообществ ВКонтакте, до похожих записей и рекламы. *Воплощайте свои идеи!*

**4. Анализ групп ВКонтакте**

* Анализируйте **до 100 последних** записей со стены любой группы.
* **Сортировка** по числу: лайков, репостов, комментариев и дате публикации.

*Просто* укажите урл группы для анализа и все готово.

**5. Помощь и пожелания**

Техническая поддержка [на сайте плагина](http://ukraya.ru/tag/easy-vkontakte-connect "Техническая поддержка"). 


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
