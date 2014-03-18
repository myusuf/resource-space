<?php

/**
 * This hook will force initialize the tables and drop it
 * @param void
 * @return null
 */
function HookMediaapiTeam_pluginsInitialise()
{
    $deactivate = trim(getvalescaped('deactivate',''), '#');
    if ($deactivate === 'mediaapi') {
        sql_query("DROP TABLE mediaapi_oauth_tokens");
    } else {
        sql_query("SELECT mediaapi_token FROM mediaapi_oauth_tokens");

        // insers
        sql_query("
            INSERT INTO `resource_type_field`
            (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (77, 'uuid', 'MEDIA_OBJECT_UUID', 0, NULL, 1, 1, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 1, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'uuid', `title` = 'MEDIA_OBJECT_UUID', `hide_when_uploading` = 1
        ");
        sql_query("
            INSERT INTO `resource_type_field`
            (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (78, 'shortname', 'Short name', 0, NULL, 190, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'shortname', `title` = 'Short name'
        ");
        sql_query("
            INSERT INTO `resource_type_field`
            (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (79, 'longname', 'Long name', 0, NULL, 180, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'longname', `title` = 'Long name'
        ");

        sql_query("
            INSERT INTO `resource_type_field`
            (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (80, 'shortdescription', 'Short description', 0, NULL, 170, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'shortdescription', `title` = 'Short description'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (81, 'longdescription', 'Long description', 0, NULL, 160, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'longdescription', `title` = 'Long description'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (82, 'siteid', 'Site Id', 0, NULL, 150, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'siteid', `title` = 'Site Id'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (83, 'detailurl', 'Detail url', 0, NULL, 30, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'detailurl', `title` = 'Detail url'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (84, 'externalid', 'External id', 0, NULL, 40, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'externalid', `title` = 'External id'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (85, 'mediatype', 'Media type', 0, NULL, 50, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'mediatype', `title` = 'Media type'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (86, 'thumbnailurl', 'Thumbnail url', 0, NULL, 60, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'thumbnailurl', `title` = 'Thumbnail url'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (87, 'backgroundurl', 'Background url', 0, NULL, 70, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'backgroundurl', `title` = 'Background url'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (88, 'ccurl', 'Cc url', 0, NULL, 80, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'ccurl', `title` = 'Cc url'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (89, 'duration', 'Duration', 0, NULL, 90, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'duration', `title` = 'Duration'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (90, 'language', 'Language', 0, NULL, 100, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'language', `title` = 'Language'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (91, 'aspectratio', 'Aspect ratio', 0, NULL, 110, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'aspectratio', `title` = 'Aspect ratio'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (92, 'canembed', 'Can embed', 3, 'Yes,No', 120, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'canembed', `title` = 'Can embed'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (93, 'candownload', 'Can download', 3, 'Yes,No', 130, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'candownload', `title` = 'Can download'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (94, 'ispublished', 'Is published', 3, 'Yes,No', 140, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'ispublished', `title` = 'Is published'
        ");
        sql_query("
            INSERT INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`)
            VALUES
            (95, 'contributorid', 'Contributor id', 0, NULL, 20, 0, 0, 0, NULL, 1, 1, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)
            ON DUPLICATE KEY UPDATE
            `name` = 'contributorid', `title` = 'Contributor id'
        ");


	sql_query("INSERT IGNORE INTO `resource_type_field` (`ref`, `name`, `title`, `type`, `options`, `order_by`, `keywords_index`, `partial_index`, `resource_type`, `resource_column`, `display_field`, `use_for_similar`, `iptc_equiv`, `display_template`, `tab_name`, `required`, `smart_theme_name`, `exiftool_field`, `advanced_search`, `simple_search`, `help_text`, `display_as_dropdown`, `external_user_access`, `autocomplete_macro`, `hide_when_uploading`, `hide_when_restricted`, `value_filter`, `exiftool_filter`, `omit_when_copying`, `tooltip_text`, `regexp_filter`, `sync_field`, `display_condition`) VALUES
(97, 'title', 'Title', NULL, NULL, 0, 0, 0, 0, NULL, 1, 1, '2#005', NULL, NULL, 0, NULL, 'Title', 1, 0, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(98, 'caption', 'Caption', 1, NULL, 0, 1, 0, 0, NULL, 1, 1, NULL, '<div class=\"item\"><h3>[title]</h3><p>[value]</p></div>\r\n \r\n<div class=\"clearerleft\"> </div>', NULL, 0, NULL, 'Caption-Abstract,Description,ImageDescription', 1, 1, NULL, 0, 1, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL)");
        // add the captions resource typ
        sql_query("
            INSERT IGNORE INTO `resource_type` (`ref`, `name`, `allowed_extensions`, `order_by`) VALUE (5, 'Caption', NULL, NULL)
        ");

     }
}
