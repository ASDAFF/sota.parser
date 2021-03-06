<?
/**
 * Copyright (c) 2019 Created by ASDAFF asdaff.asad@yandex.ru
 */

$MESS ['parser_tab'] = "Парсер";
$MESS ['parser_settings_tab'] = "Дополнительные настройки";
$MESS ['parser_uniq_tab'] = "Обновление / Уникальность";
$MESS ['parser_save_error'] = "Ошибка сохранения парсера";
$MESS ['parser_title_add'] = "Добавление парсера";
$MESS ['parser_title_edit'] = "Редактирование парсера";
$MESS ['parser_list'] = "Список";
$MESS ['parser_list_title'] = "Список парсеров";
$MESS ['parser_add'] = "Добавить";
$MESS ['parser_copy'] = "Копировать";
$MESS ['parser_mnu_add'] = "Добавить новый парсер";
$MESS ['parser_mnu_copy'] = "Копировать парсер";
$MESS ['parser_delete'] = "Удалить";
$MESS ['parser_mnu_del_conf'] = "Удалить парсер?";
$MESS ['parser_mnu_del_conf'] = "Удалить парсер?";
$MESS ['parser_start'] = "Запустить";
$MESS ['parser_start_title'] = "Запустить парсер";
$MESS ['parser_stop'] = "Остановить";
$MESS ['parser_stop_title'] = "Остановить парсер";
$MESS ['parser_saved'] = "Парсер успешно сохранен.";
$MESS ['parser_act'] = "Активен:";
$MESS ['parser_name'] = "Название:";
$MESS ['parser_rss'] = "RSS канал:";
$MESS["parser_selector_preview_id_xml"] = "Селектор-атрибут, содержащий id товара:";
$MESS["parser_selector_id_xml_descr"] = "Если пусто, то товары выгружаться не будут. Например: id, [id]";
$MESS ['parser_rss_page'] = "URL страницы:";
$MESS ['parser_rss_catalog'] = "URL раздела каталога:";
$MESS ['parser_xml_catalog'] = "URL XML файла:";
$MESS ['parser_selector_catalog_xml_descr'] = "Например: offer. Далее все селектора в этой закладке идут относительно заданного параметра.";
$MESS ['parser_sort'] = "Сортировка:";
$MESS ['parser_iblock_id'] = "ID инфоблока:";
$MESS ['parser_iblock_id_catalog'] = "ID инфоблока-каталога:";
$MESS ['parser_section_id'] = "ID раздела:";
$MESS ['parser_selector'] = "Селектор контента:";
$MESS ['parser_selector_page'] = "Селектор новости на странице списка новостей:";
$MESS ['parser_selector_preview_catalog'] = "Селектор товара на странице каталога:";
$MESS ['parser_selector_preview_xml'] = "Селектор конкретного товара:";
$MESS ['parser_selector_detail_catalog'] = "Селектор товара на детальной странице:";
$MESS ['parser_first_url'] = "URL в rss ленте:";
$MESS ['parser_first_url_page'] = "URL на странице:";
$MESS ['parser_href_page'] = "Селектор ссылки:";
$MESS ['parser_href_descr_page'] = "Если ничего не задано, то переход по страницам будет осуществляться по первой найденной ссылке в селекторе списка новостей. Селектор указывается относительно селектора новости. <br/>Если селектор сам является ссылкой, то необходимо указать: a:parent";
$MESS ['parser_name_page'] = "Селектор наименования новости:";
$MESS ['parser_name_descr_page'] = "Если ничего не задано, то название новости будет браться, как текст селектора ссылки. Селектор указывается относительно селектора новости.";
$MESS ['parser_name_descr_xml'] = "Если наименование находится в атрибуте, то атрибут указываем в квадратных скобках, например: [name].";
$MESS ['parser_encoding'] = "Кодировка:";
$MESS ['parser_delete_tag'] = "Удалять все теги:";
$MESS ['parser_pagenavigation_tab'] = "Постраничная навигация";
$MESS ['parser_preview_tab'] = "Превью";
$MESS ['parser_selector_section_catalog'] = "Селектор одной категории:";
$MESS ['parser_attr_name_section_catalog'] = "Селектор-атрибут названия категории:";
$MESS ['parser_attr_category_descr'] = "Если пусто, то название берется из значения самой категории.";
$MESS ['parser_attr_id_section_catalog'] = "Селектор-атрибут, содержащий id категории:";
$MESS ['parser_attr_id_category_descr'] = "Если пусто, то категории не будут выгружаться. Например: categoryId, [id]";
$MESS ['parser_field_id_category_catalog'] = "Записывать id категории в:";
$MESS ['parser_field_id_category_descr'] = "Указывается поле, в которое будет записан id категории из xml.";
$MESS ['parser_attr_id_parrent_category_catalog'] = "Селектор-атрибут, содержащий id родительской категории:";
$MESS ['field_xml_id'] = "внешний код раздела";
$MESS ['field_ext'] = "пользовательское поле раздела";
$MESS ['parser_attr_id_parrent_category_descr'] = "Если пусто, то категории будут выгружаться без сохранения вложенности. Например: parent, [parentId]";
$MESS ['parser_selector_section_descr'] = "Указывается селектор одной категории. Если пусто, то категории товаров выгружаться не будут.";
$MESS ['parser_basic_settings_tab'] = "Основные настройки";
$MESS ['parser_detail_tab'] = "Детально";
$MESS ['parser_preview_delete_tag'] = "Удалять теги:";
$MESS ['parser_preview_delete_tag_descr'] = htmlspecialcharsEx('Пример: "<div>", "<p>", "<br>"');
$MESS ['parser_page_uniq'] = "Уникализация по:";
$MESS ['parser_page_uniq_url'] = "Урлу";
$MESS ['parser_page_uniq_name'] = "Названию";

$MESS ['parser_add_all_section'] = "<br /><span>Создано разделов:  ";
$MESS ['parser_updown_section_dop_desc'] = "   для раздела:  ";
$MESS ['parser_section_all'] = "[Все]";

$MESS ['parser_detail_delete_tag'] = "Удалять теги:";
$MESS ['parser_preview_text_type'] = "Тип поля:";
$MESS ['parser_detail_text_type'] = "Тип поля:";
$MESS ['parser_bool_preview_delete_tag'] = "Кроме следующих:";
$MESS ['parser_bool_detail_delete_tag'] = "Кроме следующих:";
$MESS ['parser_preview_delete_element'] = "Удалять элементы:";
$MESS ['parser_detail_delete_element'] = "Удалять элементы:";
$MESS ['parser_preview_delete_attribute'] = "Удалять атрибуты элементов:";
$MESS ['parser_detail_delete_attribute'] = "Удалять атрибуты элементов:";
$MESS ['parser_preview_first_img'] = "Первая картинка как превью:";
$MESS ['parser_detail_first_img'] = "Первая картинка как детальная:";
$MESS ['parser_preview_first_img_catalog'] = "Селектор-атрибут превью картинки:";
$MESS ['parser_preview_first_img_xml'] = "Селектор-атрибут превью картинки:";
$MESS ['parser_detail_first_img_catalog'] = "Селектор-атрибут детальной картинки:";
$MESS ['parser_detail_first_img_xml'] = "Селектор-атрибут детальной картинки:";
$MESS ['parser_preview_save_img'] = "Заменять пути/сохранять картинки на сервер:";
$MESS ['parser_detail_save_img'] = "Заменять пути/сохранять картинки на сервер:";
$MESS ['parser_index_element'] = "Индексировать элементы в поиске:";
$MESS ['parser_index_category'] = "Индексировать разделы в поиске:";
$MESS ['parser_code_element'] = "Создавать символьный код из названия:";
$MESS ['parser_code_category'] = "Создавать символьный код из названия для разделов:";
$MESS ['parser_resize_image'] = "Использовать настройки инфоблока для обработки изображений:";
$MESS ['parser_create_sitemap'] = "По окончании создавать карту сайта:";
$MESS ['parser_meta_description'] = "Парсить мета-описание страниц:";
$MESS ['parser_meta_keywords'] = "Парсить ключевые слова страниц:";
$MESS ['parser_meta_description_text'] = "Описание страницы:";
$MESS ['parser_meta_keywords_text'] = "Ключевые слова:";
$MESS ['parser_meta_title'] = "Парсить заголовок страниц:";
$MESS ['parser_first_title'] = "Записывать ссылку на первоисточник:";
$MESS ['parser_meta_description'] = "Парсить мета-описание страниц:";
$MESS ['parser_meta_keywords'] = "Парсить ключевые слова страниц:";
$MESS ['parser_start_agent'] = "Запускать по агенту:";
$MESS ['parser_time_agent'] = "Периодичность запуска(в секундах):";
$MESS ['parser_active_element'] = "Создавать активные элементы:";
$MESS ['parser_start_last_time'] = "Время последнего запуска:";
$MESS ['parser_sleep'] = "Время задержки(сек):";
$MESS ['parser_sleep_descr'] = "Некоторые сайты осуществляют контроль активности, то есть, если количество обращений к сайту за единицу времени будет более установленного значения, то пользователь будет блокирован. Для обхода данной защиты установите временные задержки между обращениями парсера к сайту.:";

$MESS ['parser_sleep_descr_xml'] = "Некоторые сайты осуществляют контроль активности, то есть, если количество обращений к сайту за единицу времени будет более установленного значения, то пользователь будет блокирован. Для обхода данной защиты установите временные задержки между обращениями парсера к сайту. Актуальна только при удаленном размещении XML файла.";

$MESS ['parser_selector_descr'] = 'Полный путь к селектору контента обозначается аналогично JQuery. <br/>Примеры: div#simpleid div.content; div#simpleid div.content:eq(0); div#simpleid div.content[style="margin-top:30px"]:eq(1)';
$MESS ['parser_first_url_descr'] = 'Не обязательное поле. Применяется, если rss лента включает в себя информацию с различных источников. Это поле позволяет указать URL, по которому будет производиться парсинг. То есть парсинг будет осуществляться, если в rss ленте будут найдены совпадения по указанному урлу. Пример: 1с-bitrix.ru(без указания www и http)';
$MESS ['parser_first_url_descr_page'] = 'Не обязательное поле. Применяется, если страница включает в себя информацию с различных источников. Это поле позволяет указать URL, по которому будет производиться парсинг. То есть парсинг будет осуществляться, если на странице будут найдены совпадения по указанному урлу. Пример: 1с-bitrix.ru(без указания www и http)';


$MESS ['parser_encoding_descr'] = "Кодировка сайта, который собираемся парсить. Определяется автоматически. Если не определена, то будет учитываться именно установленное вами значение.";

$MESS ['parser_preview_first_img_descr'] = "Картинка для превью будет браться первой из описания. Если таковой нет, то будет произведен поиск по всем атрибутам rss элементов потока.";
$MESS ['parser_preview_save_img_descr'] = "Сохранять все картинки из описания на сервер и заменять их пути своими.";
$MESS ['parser_preview_delete_element_descr'] = 'Удаляет перечисленные элементы. Перечисление идет через запятую. <br/>Примеры: div.simpleclass a, div.simpleclass:eq(5), div.simpleclass[align="left"]:eq(0)';
$MESS ['parser_preview_delete_attribute_descr'] = 'Удаляет перечисленные атрибуты элементов. Перечисление идет через запятую. <br/>Примеры: div.simpleclass a[href]';

$MESS ['parser_detail_first_img_descr'] = "Картинка для превью будет браться первой из контента.";
$MESS ['parser_detail_save_img_descr'] = "Сохранять все картинки из описания на сервер и заменять их пути своими.";
$MESS ['parser_detail_delete_element_descr'] = 'Удаляет перечисленные элементы. Перечисление идет через запятую. <br/>Примеры: div.simpleclass a, div.simpleclass:eq(5), div.simpleclass[align="left"]:eq(1)';
$MESS ['parser_detail_delete_attribute_descr'] = 'Удаляет перечисленные атрибуты элементов. Перечисление идет через запятую. <br/>Примеры: div.simpleclass a[href]';
$MESS["parser_category_select"] = "Выберите категорию парсера";
$MESS["parser_category_title"] = "Категория:";
$MESS ['parser_prop_id'] = "Выбор свойства из списка";
$MESS ['parser_date_type'] = "Выбор типа даты";
$MESS ['parser_date_public'] = "Записывать дату публикации:";
$MESS ['parser_date_active'] = "Устанавливать дату начала активности:";
$MESS ['parser_date_active_now'] = "Текущая дата";
$MESS ['parser_date_active_now_time'] = "Текущая дата и время";
$MESS ['parser_date_active_public'] = "Дата публикации в источнике";
$MESS ['parser_demo'] = "Модуль \"Парсер контента\" работает в демо-режиме.";

$MESS ['parser_type'] = "Тип парсера:";
$MESS ['parser_catalog_message'] = "Функционал парсинга каталога будет реализован в новом 2104 году! Следите за новостями и подписывайтесь на обновления модуля. Компания «Сотбит» работает для Вас.";

$MESS ['parser_pagenavigation_selector'] = "Селектор навигации:";
$MESS ['parser_pagenavigation_begin'] = "Начинать со страницы:";
$MESS ['parser_pagenavigation_one'] = "Селектор-атрибут пункта навигации:";
$MESS ['parser_pagenavigation_selector_descr'] = "Пример: .pagenavigation";
$MESS ['parser_pagenavigation_one_descr'] = "Задается относительно предыдущего параметра. Например: a[href]. Если поле пустое или нет атрибута, то по умолчанию a[href]. Если ссылка отсутствует и постраничная навигация отрабатывается скриптами, то необходимо воспользоваться блоком Работа с постраничной навигацией, расположенным ниже.";
$MESS ['parser_pagenavigation_delete'] = "Удалять элементы навигации:";
$MESS ['parser_preview_text_selector'] = "Селектор превью описания:";
$MESS ['parser_xml_text_selector'] = "Селектор-атрибут описания:";
$MESS ['parser_detail_text_selector'] = "Селектор детального описания:";
$MESS ['parser_selector_catalog_descr'] = "Пример: .catalog-section. Далее все селектора в этой закладке идут относительно заданного параметра ";
$MESS ['parser_selector_detail_catalog_descr'] = "Пример: .catalog-detail. Далее все селектора в этой закладке идут относительно заданного параметра ";
$MESS ['parser_preview_text_type_catalog'] = "Тип превью описания:";
$MESS ['parser_preview_delete_tag_catalog'] = "Удалять теги из описания:";
$MESS ['parser_preview_first_img_descr_catalog'] = "По умолчанию img:eq(0)[src]. Если картинка находится в другом элементе или атрибуте, то записываем, например: a:eq(0)[href]";
$MESS ['parser_preview_first_img_descr_xml'] = "Если картинки находится в атрибуте, то атрибут указываем в квадратных скобках, например: picture:eq(0), picture:eq(0)[src].";
$MESS ['parser_detail_first_img_descr_catalog'] = "По умолчанию img:eq(0)[src]. Если картинка находится в другом элементе или атрибуте, то записываем, например: a:eq(0)[href]. Также может возникнуть ситуация, когда селекторы детальной картинки могут отличаться в зависимости от ряда условия(одна картинка или их несколько). В этом случае перечисление селекторов идет через запятую.";
$MESS ['parser_detail_text_type_catalog'] = "Тип детального описания:";
$MESS ['parser_preview_price'] = "Селектор цены:";
$MESS ['parser_preview_count'] = "Селектор количества:";
$MESS ['parser_preview_count_xml'] = "Селектор-атрибут количества:";
$MESS ['parser_preview_count_descr'] = "Если ничего не задано, то количество будет парсится из детальной страницы. Если оба поля заполнены, то предпочтение отдается детальной странице. ";

$MESS ['parser_preview_count_xml_descr'] = "Если ничего не задано, то количество будет браться из вкладки \"Торговый каталог\"";

$MESS ['parser_detail_count'] = "Селектор количества:";
$MESS ['parser_detail_count_descr'] = "Если ничего не задано, то количество будет парсится из \"Превью\". Если оба поля заполнены, то предпочтение отдается детальной странице.";

$MESS ['parser_preview_price_xml'] = "Селектор-атрибут цены:";
$MESS ['parser_categoryId_catalog'] = "Селектор-атрибут категории товара:";
$MESS ['parser_selector_description_xml_descr'] = "Если описание находится в атрибуте, то атрибут указываем в квадратных скобках, например: description[name].";
$MESS ['parser_props_tab'] = "Свойства";
$MESS ['parser_preview_price_descr'] = "Если пустое, то селектор цены надо указывать на детальной странице товара. Если оба поля заполнены, то предпочтение отдается детальной странице.";
$MESS ['parser_preview_price_descr_xml'] = "Если цена находится в атрибуте, то атрибут указываем в квадратных скобках, например: [price].";
$MESS ['parser_price_type'] = "Тип цены:";
$MESS ['parser_currency'] = "Валюта:";
$MESS ['parser_catalog_tab'] = "Торговый каталог";
$MESS ['parser_catalog_koef'] = "Коэффициент единицы измерения:";
$MESS ['parser_measure'] = "Единица измерения:";

$MESS ['parser_href_catalog'] = "Селектор ссылки товара:";
$MESS ['parser_name_catalog'] = "Селектор названия товара:";
$MESS ['parser_name_catalog_xml'] = "Селектор-атрибут названия товара:";
$MESS ['parser_href_descr_catalog'] = "Если ничего не задано, то переход по страницам будет осуществляться по первой найденной ссылке в селекторе списка товаров. Селектор указывается относительно селектора товара.";
$MESS ['parser_categoryId_descr_catalog'] = "Айди категории, к которой будет прикреплен товар. Если айди находится в атрибуте, то атрибут указываем в квадратных скобках, например: [categoryId].";
$MESS ['parser_name_descr_catalog'] = "Если ничего не задано, то название товара будет браться, как текст селектора ссылки. Селектор указывается относительно селектора товара.";
$MESS ['parser_size_selector'] = "Парсинг размеров по селектору";

$MESS ['parser_size_length'] = "Длина, мм:";
$MESS ['parser_size_width'] = "Ширина, мм:";
$MESS ['parser_size_height'] = "Высота, мм:";
$MESS ['parser_size_weight'] = "Вес, гр:";
$MESS ['parser_selector_size_descr'] = "Парсинг размеров будет осуществляться по конкретным селекторам. Селектора идут относительно главного селектора товара. Также можно удалить лишние символы(двоеточие, точки с запятой и подобные). При удалении символы чередуются через запятую. Если необходимо удалить запятую, то дважды экранируем ее(\\\,).";

$MESS ['parser_size_find'] = "Парсинг размеров по названию";
$MESS ['parser_selector_find'] = "Селектор перечисления размеров:";
$MESS ['parser_find_size_descr'] = "Парсинг осуществляется по селектору перечисления размеров. И уже в нем осуществляется поиск размеров по конкретным названиям. Также можно удалить лишние символы(ед. измерения, двоеточия, точки с запятой и подобные). При удалении символы чередуются через запятую. Если необходимо удалить запятую, то дважды экранируем ее(\\\,).";

$MESS ['parser_more_image'] = "Доп. картинки";
$MESS ['parser_selector_more_image'] = "Селектор-атрибут перечисления доп. картинок:";
$MESS ['parser_selector_more_image_xml'] = "Селектор-атрибут перечисления доп. картинок:";
$MESS ['parser_selector_more_image_descr'] = "Пример: .image_list img[src]";
$MESS ['parser_selector_more_image_xml_descr'] = "Если картинки находится в атрибуте, то атрибут указываем в квадратных скобках, например: picture, picture[src].";

$MESS ['parser_selector_props'] = "Парсинг свойств из деталки по селектору";
$MESS ['parser_selector_props_xml'] = "Парсинг свойств по селектору";
$MESS ['parser_more_image_prop'] = "Свойство доп. картинок:";
$MESS ['parser_find_props'] = "Парсинг свойств из деталки по названию";
$MESS ['parser_find_props_xml'] = "Парсинг свойств и автоматическое создание";
$MESS ['parser_add_auto_props'] = "Автоматическое создание свойств:";
$MESS ['parser_add_auto_props_descr'] = "При включении несуществующие свойства будут создаваться автоматически, исходя из настроек ниже. ";
$MESS ['parser_selector_props_preview'] = 'Парсинг свойств из превью по селектору';
$MESS ['parser_find_props_preview'] = 'Парсинг свойств из превью по названию';
$MESS ['parser_selector_find_props'] = "Селектор перечисления свойств:";
$MESS ['parser_selector_find_props_xml'] = "Селектор перечисления свойств:";
$MESS ['parser_attr_auto_props'] = "Селектор-атрибут названия свойства:";
$MESS ['parser_attr_auto_props_descr'] = "Укажите селектор-атрибут, в котором находится название свойства.<br /><br />Если название свойства находится в атрибуте \"Селектор перечисления свойств:\", то укажите этот атрибут, например: [name]. <br /> Во всех остальных случаях укажите селектор и атрибут, например: value[name], или просто селектор, например: value<br /><br />
Если свойство или несколько свойств с таким названием существует, то текущее значение будет записано во все свойства. Поиск свойств по названию происходит среди свойств типа S - строка, N - числовое и L - список.";
$MESS ['parser_type_list'] = "[L]-список";
$MESS ['parser_selector_attr_value_auto_props'] = "Селектор-атрибут значения свойства:";
$MESS ['parser_selector_attr_value_auto_props_descr'] = "Если пусто, то значение свойства будет браться как текст из \"Селектор перечисления свойств.\"<br />Если значение свойства находиться в атрибуте \"Селектор перечисления свойств\", то укажите атрибут, например: [value]<br /><br />Во всех остальных случаях укажите селектор и атрибут, например: value, value_props[value]";

$MESS ['parser_uniq_descr_xml'] = "По умолчанию при выгрузке товаров заполняется внешний код XML_ID элемента, как md5(название+id элемента xml файла). Далее при обновлении уникализация осуществляется именно по внешнему коду. <br/><br /><b>Внимание! Если вы не хотите делать запись во внешний код и уникализировать по нему товар, то укажите, по какому полю производить уникализацию элементов. В этом случае внешний код XML_ID затираться не будет.</b>";

$MESS ['parser_type_string'] = "[S]-строка";
$MESS ['parser_category_description'] = "Категории";
$MESS ['parser_offer_description'] = "Товары";
$MESS ['parser_mode'] = "Режим парсера:";
$MESS ['parser_mode_descr'] = "При debug режиме осуществляется парсинг первых 3 страниц и 3 товаров каждой страницы. Debug режим необходим для отладки парсера. В рабочий режим парсер необходимо переводить, если вы полностью отладили и настроили парсинг!!!<br/><b>Работа парсера в демо-режиме ограничена и фактически аналогична работе парсера в debug режиме.</b>";

$MESS ['parser_header_uniq'] = "Проверка уникальности";
$MESS ['parser_uniq_update'] = "Обновлять товары";
$MESS ['parser_uniq_add_element'] = "Не создавать новые товары";
$MESS ['parser_uniq_add_element_descr'] = "При включении \"Не создавать новые товары\" будет происходить парсинг всех элементов каталога, но при этом, новые(товары, которых нет в инфоблоке) не будут добавляться, а будет происходить лишь обновление уже существующих товаров.<br /><br /><b style=\"red;\">Внимание!!!</b><br />Включение данной настройки не ускорит скорость парсинга и не уменьшит нагрузки.";
$MESS ['parser_header_uniq_field'] = "Обновлять поля";
$MESS ['parser_header_uniq_field_preview_descr'] = "Превью описание:";
$MESS ['parser_header_uniq_field_detail_descr'] = "Детальное описание:";
$MESS ['parser_header_uniq_field_preview_img'] = "Превью картинка:";
$MESS ['parser_header_uniq_field_detail_img'] = "Детальное картинка:";
$MESS ['parser_header_uniq_field_price'] = "Цена:";
$MESS ['parser_header_uniq_field_count'] = "Количество:";
$MESS ['parser_catalog_count_default'] = "Количество по умолчанию:";
$MESS ['parser_catalog_count_default_descr'] = "Поле должно иметь целое числовое значение. <br>Поле актуально в том случае, если из превью и детальной страницы количество не загрузилось. <br>Если пустое, то количество товара заноситься не будет.";
$MESS ['parser_catalog_count_default_xml_descr'] = "Поле должно иметь целое числовое значение. <br>Поле актуально в том случае, если из XML файла количество не загрузилось. <br>Если пустое, то количество товара заноситься не будет.";
$MESS ['parser_header_uniq_field_name'] = "Наименование:";
$MESS ['parser_header_uniq_field_descr'] = "Отмеченные поля будут обновляться при обновлении товаров.";
$MESS ['parser_uniq_name'] = "По названию:";
$MESS ['parser_uniq_prop'] = "По свойству:";
//$MESS ['parser_uniq_descr'] = "По умолчанию уникальность элементов проверяется по md5 от названия элемента и урла первоисточника. Эти данные заносятся в XML_ID и далее уже по XML_ID проверяется уникальность. Вы можете изменить поля, по которым будет проверяться уникальность. Если выбрано несколько полей, то проверка уникальности будет по логике И. Если вы не хотите использовать поле XML_ID для проверки уникальности(как по умолчанию), то вам надо переопределить, по каким полям будет проверяться уникальность.";
$MESS ['parser_uniq_descr'] = "По умолчанию при выгрузке товаров заполняется внешний код XML_ID элемента, как md5(название+url страницы). Далее при обновлении уникализация осуществляется именно по внешнему коду. <br/><b>Внимание! Если вы не хотите делать запись во внешний код и уникализировать по нему товар, то укажите, по какому полю производить уникализацию элементов. В этом случае внешний код XML_ID затираться не будет.</b>";


$MESS ['parser_preview_from_detail'] = "Создавать превью картинку из детальной:";
$MESS ['parser_preview_from_detail_text'] = "Создавать превью описание из детального:";
$MESS ['parser_catalog_delete_symb'] = "Удалять символы:";
$MESS ['parser_delete_symb_descr'] = "Удаление символов позволяет удалять лишние символы из общего селектора свойства(названия ед. измерения, двоеточия, точки с запятой и подобное). Перечисление идет через запятую. Если необходжимо удалить саму запятую, то производим двойное экранирование(\\\,).";

$MESS ['parser_delete_symb_xml_descr'] = "Удаление символов позволяет удалять лишние символы из свойства(названия ед. измерения, двоеточия, точки с запятой и подобное). Перечисление идет через запятую. Если необходжимо удалить саму запятую, то производим двойное экранирование(\\\,).";
$MESS ['parser_delete_symb_descr_offer'] = "Удаление символов позволяет удалять лишние символы из общего селектора свойства(названия ед. измерения, двоеточия, точки с запятой и подобное). Перечисление идет через ||.<br><br>Также возможно указание регулярных выражений. Регулярка заключается в кавычки //. Пример: /(.)*/";

$MESS ['parser_delete_symb_descr_xml_offer'] = "Удаление символов позволяет удалять лишние символы из значения свойства(названия ед. измерения, двоеточия, точки с запятой и подобное). Перечисление идет через ||.<br><br>Также возможно указание регулярных выражений. Регулярка заключается в кавычки //. Пример: /(.)*/";

$MESS ['parser_header_uniq_field_more_img'] = "Доп. картинки:";
$MESS ['parser_cat_vat_id'] = "Ставка НДС:";
$MESS ['parser_cat_vat_included'] = "Включать НДС в цену:";
$MESS ['parser_proxy'] = "Прокси-сервер:";
$MESS ['parser_offer_parsing_selector_xml_name'] = "Селектор-атрибут наименования:";
$MESS ['parser_offer_parsing_selector_price_xml'] = "Селектор-атрибут цены:";
$MESS ['parser_offer_parsing_selector_quantity_xml'] = "Селектор-атрибут количества:";
$MESS ['parser_proxy_descr'] = "Указывается прокси-сервер. Пример: 109.194.20.27:3128";
$MESS ['parser_header_uniq_field_param'] = "Параметры каталога:";
$MESS ['parser_header_uniq_field_props'] = "Свойства товара:";
$MESS ['parser_pagenavigation_end'] = "по страницу:";
$MESS ['parser_pagenavigation_begin_descr'] = "Если ничего не задано, то будет парсится весь раздел каталога.";
$MESS ['parser_404_error'] = "Парсить при возникновении 404 ошибки:";
$MESS ['parser_404_descr'] = "Некоторые сайты при переходе по страницам навигации отдают 404 ошибку из-за SEO заморочек. Именно для таких случаев и предназначен выше приведенный параметр.";
$MESS ['parser_logs_tab'] = "Логи";
$MESS ['parser_header_logs'] = "Включить логирование:";
$MESS ['parser_header_logs_download'] = "Скачать лог:";
$MESS ['parser_header_log_descr'] = "Производится логирование последней выгрузки. Лог записывается файл.";
$MESS ['btn_stop_catalog'] = "Остановить";
$MESS ['parser_load_page'] = "Обработано страниц: ";
$MESS ['parser_load_product'] = " Импортировано товаров: ";
$MESS ['parser_load_product_error'] = " Из них с ошибками: ";
$MESS ['parser_all_error'] = " Всего ошибок: ";
$MESS['parser_loading_end'] = "Загрузка завершена";

$MESS['parser_update_N'] = "Не обновлять";
$MESS['parser_update_Y'] = "Обновлять";
$MESS['parser_update_empty'] = "Обновлять пустое значение";

$MESS['parser_work_price'] = "Работа с ценами";

$MESS['parser_price_terms_no'] = "Без условия";
$MESS['parser_price_terms_up'] = "Если цена выше";
$MESS['parser_price_terms_down'] = "Если цена ниже";
$MESS['parser_price_updown_no'] = "Не изменять";
$MESS['parser_price_updown_up'] = "Увеличить";
$MESS['parser_price_updown_down'] = "Уменьшить";
$MESS['parser_price_percent'] = "Проценты";
$MESS['parser_price_abs_value'] = "Абсолютная величина";
$MESS['parser_price_updown'] = 'Изменять цену:';
$MESS['parser_price_terms'] = 'Условие изменения цены:';
$MESS['parser_price_type_value'] = 'Тип изменения:';
$MESS['parser_price_value'] = 'Величина изменения:';
$MESS['parser_convert_currency'] = 'Конвертировать в валюту:';
$MESS['parser_convert_no'] = 'Нет';
$MESS['parser_step'] = 'Количество товаров, выгружаемых за один шаг парсера:';
$MESS['parser_start_agent_descr'] = 'Рекомендуется запускать агенты из под крона.';
$MESS['parser_auth'] = 'Авторизация';
$MESS['parser_auth_active'] = 'Производить авторизацию на стороннем сайте:';
$MESS['parser_auth_url'] = 'URL авторизационной страницы:';
$MESS['parser_auth_url_descr'] = 'URL страницы, на которой происходит авторизация. Если пустое, то авторизация происходит на странице раздела.';
$MESS['parser_auth_url_xml_descr'] = 'URL страницы, на которой происходит авторизация.';
$MESS['parser_auth_selector'] = 'Селектор формы авторизации:';
$MESS['parser_auth_login'] = 'Логин:';
$MESS['parser_auth_login_name'] = 'Имя поля логина:';
$MESS['parser_auth_password'] = 'Пароль:';
$MESS['parser_auth_password_name'] = 'Имя поля пароля:';
$MESS['parser_auth_password_descr'] = 'Если форма авторизации стандартная, то по-умолчанию достаточно указать лишь логин и пароль. Если же для авторизации не используются такие типы полей, как text, password, то необходимо указать имена полей.';
$MESS['parser_auth_check'] = 'Проверить авторизацию';
$MESS['parser_work_price_num'] = 'Условие';
$MESS['parser_price_num_add'] = 'Добавить условие';
$MESS['parser_price_num_del'] = 'Удалить условие';
$MESS['parser_price_terms_delta'] = 'Если цена в промежутке';
$MESS['parser_price_from'] = 'От';
$MESS['parser_price_to'] = 'До';
$MESS['parser_auth_no'] = 'Неудачная авторизация. Ключи доступа не подходят.';
$MESS['parser_auth_ok'] = 'Авторизация прошла успешно. Ключи доступа актуальные.';
$MESS['parser_auth_error_selector'] = 'Селектор формы авторизации не определен.';
$MESS['parser_selector_find_descr'] = 'Если общий селектор отсутствует и перечисление идет через тег &lt;br&gt;, то пишем примерно следующее: .props br';
$MESS['parser_selector_find_xml_descr'] = 'Укажите селектор, который является контейнером для одного свойства. Например: param';
$MESS['type_add_auto_props'] = 'Выберите тип создаваемых свойств:';
$MESS['type_add_auto_props_description'] = 'Все созданные свойства будут иметь указанный тип. S - строка, L - список. Если свойство уже есть в системе, то оно не будет менять свой тип.';
$MESS['parser_detail_name'] = 'Селектор названия товара';
$MESS['parser_detail_name_descr'] = 'Если указан, то селектор названия товара в превьюшке будет игнорироваться.';

$MESS['parser_cat_price_offer'] = 'Цены только в инфоблоке торговых предложений:';
$MESS['parser_url_dop'] = 'Дополнительные урлы разделов:';
$MESS['parser_url_xml_dop'] = 'Дополнительные урлы XML файлов:';
$MESS['parser_add_section'] = "Создавать разделы из XML файла";
$MESS['parser_url_dop_descr'] = 'Поле предназначено для парса нескольких разделов одновременно. Каждый урл пишется с новой строки.';
$MESS['parser_rss_dop_descr'] = "Поля предназначены для парса урлов в разные разделы. Нажмите \"Добавить\" и укажите URL и раздел, в который нужно парсить каталог.";
$MESS['parser_add_rss_dop_button'] = "Добавить";
$MESS['parser_url_dop_descr_xml'] = 'Поле предназначено для парса нескольких XML файлов одновременно. Каждый урл файла пишется с новой строки.';
$MESS['parser_add_section_descr'] = 'При включении парсер создаст категории внутри инфоблока и распределит товары согласно данным из XML.';
$MESS['parser_prop_default'] = "Значение по умолчанию";

$MESS ['parser_price_format'] = "Формат цены(разделители):";
$MESS ['parser_price_format1'] = " тысячи ";
$MESS ['parser_price_format2'] = " копейки ";
$MESS ['parser_price_format_descr'] = "Заполняется в том случае, если цена состоит из тысяч и копеек со своими разделителями. В большинстве случаев можно оставлять пустым.";

$MESS["sota_parser_select_prop"] = "Выберите свойство";
$MESS["sota_parser_select_prop_new"] = "[Создать]";
$MESS["parser_iblock_id_descr"] = "В данный раздел производится парсинг товаров по ссылкам из полей \"URL раздела каталога\" и \"Дополнительные урлы разделов\"";
$MESS["parser_price_okrug"] = "Округление цены:";
$MESS["parser_price_okrug_no"] = "Не округлять";
$MESS["parser_price_okrug_up"] = "Округлять с указанной точностью";
$MESS["parser_price_okrug_ceil"] = "Округление в большую сторону до целого";
$MESS["parser_price_okrug_floor"] = "Округление в меньшую сторону до целого";
$MESS["parser_price_okrug_delta1"] = "до";
$MESS["parser_price_okrug_delta2"] = "знаков после запятой";
$MESS["parser_price_okrug_descr"] = "Округления до целых чисел точность(количество знаков после запятой) не учитывают.";

$MESS["sota_parser_select_prop_but"] = "Добавить";
$MESS["parser_prop_detail_preview_descr"] = "Внимание! Свойства, выгруженные с детальной страницы, являются приоритетными.";
$MESS["parser_prop_detail_preview_descr_file"] = "<br/><br/>При парсинге свойств по селектору возможен парсинг свойств типа файл(F). Для этого необходимо указать селектор элемента и атрибут, например: .instructions[href]<br><br>Также возможен парсинг свойств из атрибутов элементов: a[title], img[title].";
$MESS["parser_prop_detail_preview_descr_file_xml"] = "Если значение свойства находится непосредственно в селекторе, то пишем, например, следующее: param, если значение свойства находится в атрибуте, то например: param[title]";
$MESS["parser_offer_tab"] = "Торговые предложения";
$MESS["parser_offer_selector"] = "Главный селектор контейнера торговых предложений:";
$MESS["parser_offer_selector_item"] = "Селектор отдельного оффера:";
$MESS["parser_offer_table_desc"] = "Торговые предложения табличного вида";
$MESS["parser_offer_container_desc"] = "Торговые предложения из отдельного контейнера";
$MESS["parser_offer_load_no"] = "Не выгружать";
$MESS["parser_offer_load_table"] = "Выгружать из табличного вида";
$MESS["parser_offer_load"] = "Выгружать офферы:";
$MESS["parser_offer_selector_head"] = "Селектор блока шапки таблицы:";
$MESS["parser_offer_selector_head_th"] = "Селектор наименования параметра в шапке таблицы:";
$MESS["parser_offer_selector_head_th_descr"] = "Необходим, если поля и свойства определяются по названию. Относительно предыдущего поля. Пример: th, td";
$MESS["parser_offer_selector_item_td"] = "Селектор значения параметра в теле таблицы:";
$MESS["parser_offer_selector_item_td_descr"] = "Необходим, если поля и свойства определяются по названию. Считается относительно предыдущего поля. Пример: td";
$MESS["parser_offer_parsing_selector"] = "Парсинг полей и свойств по селектору";
$MESS["parser_offer_parsing_find"] = "Парсинг полей и свойств по названию";
$MESS["parser_offer_parsing_selector_name"] = "Наименование:";
$MESS["parser_offer_parsing_selector_price"] = "Цена:";
$MESS["parser_offer_parsing_selector_quantity"] = "Количество:";
$MESS["parser_offer_load_descr"] = "После изменения параметра необходимо Применить настройки.";
$MESS["parser_offer_selector_descr"] = "Относительно селектора товара на детальной странице. Пример: table, .mainOfferTable";
$MESS["parser_offer_selector_head_descr"] = "Необходим, если поля и свойства определяются по названию. Считается относительно предыдущего параметра. Данный параметр необходим, если парсинг полей и свойств осуществляется по названию. Пример: thead tr, tr:eq(0)";
$MESS["parser_offer_selector_item_descr"] = "Считается относительно главного контейнера офферов. Пример: tbody tr, tr:eq(0)+tr";
$MESS["parser_offer_parsing_selector_prop"] = "Парсинг свойств по селектору";
$MESS["parser_offer_table_desc_1"] = "Артикул";
$MESS["parser_offer_table_desc_2"] = "Цвет";
$MESS["parser_offer_table_desc_3"] = "Формат, мм";
$MESS["parser_offer_table_desc_4"] = "Кол-во страниц";
$MESS["parser_offer_table_desc_5"] = "Кол-во языков";
$MESS["parser_offer_table_desc_6"] = "Плотность бумаги";
$MESS["parser_offer_table_desc_7"] = "Блок";
$MESS["parser_offer_table_desc_8"] = "Цена (без НДС), руб.";
$MESS["parser_offer_table_desc_9"] = "коричневый";
$MESS["parser_offer_table_desc_10"] = "70 г/м";
$MESS["parser_offer_table_desc_11"] = "шитый";
$MESS["parser_offer_table_desc_12"] = "А5";
$MESS["parser_offer_parsing_find_prop"] = "Парсинг свойств по названию";
$MESS["parser_header_element_action"] = "Действия над элементами";
$MESS["parser_element_action"] = "Что делать с товарами, присутствующими в предыдущей выгрузке и отсутствующими в текущей:";
$MESS["parser_offer_add_name"] = "Параметр уникализации офферов:";
$MESS["parser_default_props"] = "Значения свойств по умолчанию";
$MESS["parser_action_N"] = "ничего";
$MESS["parser_action_A"] = "деактивировать";
$MESS["parser_action_D"] = "удалить";
$MESS["parser_action_NULL"] = "обнулить количество";
$MESS["parser_add_delete_symb_props"] = "Добавление/удаление символов полей и свойств";
$MESS["parser_sota_PARSER_NAME_E"] = "Название товара";
$MESS["parser_action_props_delete"] = "Удалить";
$MESS["parser_action_props_add_begin"] = "Добавить в начало";
$MESS["parser_action_props_add_end"] = "Добавить в конец";
$MESS["sota_parser_select_action_props"] = "Выберите действие";
$MESS["sota_parser_action_props_descr"] = htmlspecialchars("Укажите необходимые символы или слова, если вы хотите удалить/добавить их к указанному полю или свойству. Если необходимо записать пробел, то укажите его спец. символ: &nbsp;") . "<br><br>Внимание! Действия возможны только над свойствами и полями типа Строка(S)";
$MESS["parser_element_action_descr"] = "Работает в том случае, если отмечена галочка Обновлять товары. Товары из предыдущей выгрузки сравниваются с товарами из текущей. На основании этого и производятся действия над элементами.";
$MESS["parser_offer_add_name_descr"] = "<b>Важный параметр!!! Особенности:</b><br>1. Указанные свойства добавляются в название оффера.<br>2. Если название оффера отсутствует, то название полностью будет состоять из значений указанных свойств.<br>3. По данному параметру происходит уникализации офферов.<br>4. Если ничего не указано, то уникальность будет определяться по названию оффера.";
$MESS["parser_offer_more_props_descr"] = "Свойства торгового предложения";
$MESS["parser_offer_more_add_name_heading"] = "Название торгового предложения";
$MESS["parser_offer_more_add_name_desc"] = "Значения выбранных свойств будут занесены в название торгового предложения.<br /> Укажите символьные коды свойств в нужном Вам порядке(перечисление идет через |). <br />Например: \"ARTICLE|COLOR|SIZE\". <br />Если пусто, то название будет сформировано из свойств в том порядке, в котором они указаны в предыдущей настройке.<br /><br /><span style='color:red'><b>Внимание!!!</b><br >Данный шаблон применяется только в случае, если уникальность офферов определяться по названию.</span>";
$MESS["parser_offer_more_add_name_notes"] = "Шаблон названия торгового предложения:";
$MESS["parser_offer_add_name_one_descr"] = "<b>Важный параметр!!! Особенности:</b><br>1. Указанные свойства добавляются в название оффера.<br>2. По данному параметру происходит уникализации офферов.";

$MESS["parser_offer_parsing_selector_name_descr"] = "Название торгового предложения. Может быть пустое, если отмечены свойства параметра уникализации. Тогда название будет состоять из значений указанных свойств.";
$MESS["parser_offer_parsing_selector_price_descr"] = "Если пусто, то цена будет браться из блока Превью или Детально";
$MESS["parser_offer_parsing_selector_quantity_descr"] = "Если пусто, то количество будет браться из блока Превью или Детально";
$MESS["parser_offer_parsing_selector_price_xml_descr"] = "Если пусто, то цена будет браться из блока \"Основные настройки\"";
$MESS["parser_offer_parsing_selector_quantity_xml_descr"] = "Если пусто, то количество будет браться из блока \"Основные настройки\"";
$MESS["parser_offer_load_one"] = "Офферы с одной характеристикой";
$MESS["parser_offer_load_more"] = "Офферы из нескольких контейнеров";
$MESS["parser_dop_load_rss"] = "URL раздела каталога №";
$MESS["parser_caption_detete_button"] = "Delete";
$MESS["parser_offer_load_container"] = "Офферы из отдельного контейнера";
$MESS["parser_offer_selector_item_xml_descr"] = "Считается относительно \"Селектор конкретного товара\"";
$MESS["parser_offer_one_selector"] = "Селектор контейнера отдельной характеристики оффера";
$MESS["parser_offer_one_selector_descr"] = "Фактически это селектор выбора характеристики оффера, который указан в Параметре уникализации. Пример: select option, #charect div.size";
$MESS["parser_offer_one_price_attr"] = "Атрибут цены";
$MESS["parser_offer_one_quantity_attr"] = "Атрибут количества";
$MESS["parser_offer_one_price_attr_descr"] = "Иногда цена записывается в атрибут предыдущего параметра. Например: data-price. Если ничего не задано, то цена будет браться из вкладок Превью или Детально.";
$MESS["parser_offer_one_quantity_attr_descr"] = "Иногда количество записывается в атрибут предыдущего параметра. Например: data-count. Если ничего не задано, то количество будет браться из вкладок Превью или Детально.";
$MESS["parser_offer_one_quantity_attr_descr"] = "Иногда количество записывается в атрибут предыдущего параметра. Например: data-count. Если ничего не задано, то количество будет браться из вкладки \"Основные настройки\".";
$MESS["parser_offer_one_price_attr_xml_descr"] = "Иногда цена записывается в атрибут предыдущего параметра. Например: data-price. Если ничего не задано, то цена будет браться из вкладок \"Основные настройки\".";
$MESS["parser_work_pagenavigation"] = "Работа с постраничной навигацией";
$MESS["parser_work_pagenavigation_var"] = "Переменная-параметр постраничной навигации";
$MESS["parser_work_pagenavigation_var_step"] = "Шаг постраничной навигации";
$MESS["parser_work_pagenavigation_other_var"] = "Другие переменные-параметры";
$MESS["parser_work_pagenavigation_page_count"] = "Количество страниц навигации";
$MESS["parser_standart_pagenavigation"] = "Стандартная постраничная навигация";
$MESS["parser_work_pagenavigation_descr"] = "Данный блок предназначен, если стандартная навигация не работает(отсутствие ссылок href). В таком случае вы можете настроить постраничную навигацию самостоятельно.";
$MESS["parser_work_pagenavigation_var_descr"] = "Переменная в урле, отвечающая за постраничную навигацию. Пример: page, PAGE_1. Далее парсер сам будет подставлять значения к этой переменной. Например: page=1, page=2";
$MESS["parser_work_pagenavigation_var_step_descr"] = "Указывается количество, на которое нужно увеличить \"Переменная-параметр постраничной навигации\", чтобы попасть на следующую страницу каталога.";
$MESS["parser_work_pagenavigation_other_var_descr"] = "Перечислить переменные и их значения, которые также отвечают за постраничную навигацию. Пример: set_filter=Y&back=1&ajax=Y";
$MESS["parser_work_pagenavigation_page_count_descr"] = "Данный параметр необходимо заполнять, если блок Стандартной навигации не заполнен и вы заранее знаете количество страниц.";
$MESS["parser_local_tab"] = "Сервисы";
$MESS["parser_loc_type"] = "Тип перевода";
$MESS["parser_loc_no"] = "Не переводить";
$MESS["parser_loc_yandex"] = "Яндекс.Переводчик";
$MESS["parser_loc_yandex_key"] = "Ключ от API Яндекс.Переводчик";
$MESS["parser_loc_yandex_key_descr"] = "Данный ключ вы можете получить совершенно бесплатно по адресу: <a target='_blank' href='https://tech.yandex.ru/keys/get/?service=trnsl'>https://tech.yandex.ru/keys/get/?service=trnsl</a>";
$MESS["parser_loc_yandex_lang"] = "Направление перевода";
$MESS["parser_loc_yandex_lang_descr"] = "Может задаваться одним из следующих способов:<br>
- В виде пары кодов языков («с какого»-«на какой»), разделенных дефисом. Например, en-ru обозначает перевод с английского на русский.<br>
- В виде кода конечного языка (например ru). В этом случае сервис пытается определить исходный язык автоматически.<br><br>
Ограничения:<br>
- Максимальный размер передаваемого текста составляет 10000 символов.
";
$MESS["parser_loc_fields"] = "Переводить поля";
$MESS["parser_loc_fields_name"] = "Название";
$MESS["parser_loc_fields_preview_text"] = "Превью текст";
$MESS["parser_loc_fields_detail_text"] = "Детальное описание";
$MESS["parser_loc_fields_props"] = "Свойства";
$MESS["parser_loc_type_head"] = "Перевод текста";
$MESS["parser_loc_uniq"] = "Отправлять уникальный текст в Яндекс";
$MESS["parser_loc_uniq_domain"] = "Выберите домен";
$MESS["sota_parser_loc_uniq_no"] = "Не отправлять";
$MESS["parser_loc_uniq_domain_descr"] = "Осуществляется отправка только детального описания при создании нового элемента. При обновлении товаров отправка не осуществляется.<br><br>
Разрешается добавлять не более 100 текстов в сутки, при этом размер текста должен быть в пределах от 500 до 32000 символов.
";
$MESS["parser_auth_type"] = "Тип авторизации:";
$MESS["parser_auth_type_form"] = "Стандартная авторизация через form";
$MESS["parser_auth_type_http"] = "HTTP-аутентификация";
$MESS["button_caption"] = "Выбрать";
$MESS["parser_mode_descr_xml"] = "При debug режиме осуществляется парсинг первых 30 элементов XML файла. Debug режим необходим для отладки парсера. В рабочий режим парсер необходимо переводить, если вы полностью отладили и настроили парсинг!!!
Работа парсера в демо-режиме ограничена и фактически аналогична работе парсера в debug режиме.";
$MESS["parser_load_section_add"] = "Создано разделов";
$MESS["parser_encoding_xml"] = "Кодировка XML файла, который собираемся парсить. Определяется автоматически. Если не определена, то будет учитываться именно установленное вами значение. ";
$MESS["parser_mode_descr_yml"] = "По умолчанию парсер настроен для парсинга файлов формата yml.";
$MESS["parser_offer_one_separator"] = "Разделитель:";
$MESS["parser_offer_one_separator_xml_descr"] = htmlspecialchars("Если офферы, находящиеся в контейнере(например: <param name=\"size\">L, M, XL, XXL</param>),  разделены знаком, то укажите этот знак.");

?>