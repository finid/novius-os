<?php

namespace Fuel\Migrations;

class Version_0_2
{
    public function up()
    {
        // Rename lang, lang_common_id, lan_is_main columns. Replace lang by context. Resize lang columns.
        // Update context's columns with site::locale
        $alters = <<<SQL
ALTER TABLE `nos_blog_category` CHANGE `cat_lang` `cat_context` VARCHAR( 25 ) NOT NULL, CHANGE `cat_lang_common_id` `cat_context_common_id` INT( 11 ) NOT NULL, CHANGE `cat_lang_is_main` `cat_context_is_main` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `nos_blog_post` CHANGE `post_lang` `post_context` VARCHAR( 25 ) NOT NULL, CHANGE `post_lang_common_id` `post_context_common_id` INT( 11 ) NOT NULL, CHANGE `post_lang_is_main` `post_context_is_main` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `nos_news_category` CHANGE `cat_lang` `cat_context` VARCHAR( 25 ) NOT NULL, CHANGE `cat_lang_common_id` `cat_context_common_id` INT( 11 ) NOT NULL, CHANGE `cat_lang_is_main` `cat_context_is_main` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `nos_news_post` CHANGE `post_lang` `post_context` VARCHAR( 25 ) NOT NULL, CHANGE `post_lang_common_id` `post_context_common_id` INT( 11 ) NOT NULL, CHANGE `post_lang_is_main` `post_context_is_main` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `nos_page` CHANGE `page_lang` `page_context` VARCHAR( 25 ) NOT NULL, CHANGE `page_lang_common_id` `page_context_common_id` INT( 11 ) NOT NULL, CHANGE `page_lang_is_main` `page_context_is_main` TINYINT( 1 ) NOT NULL DEFAULT '0';

UPDATE `nos_blog_category` SET `cat_context` = CONCAT('main::', `cat_context`);
UPDATE `nos_blog_post` SET `post_context` = CONCAT('main::', `post_context`);
UPDATE `nos_news_category` SET `cat_context` = CONCAT('main::', `cat_context`);
UPDATE `nos_news_post` SET `post_context` = CONCAT('main::', `post_context`);
UPDATE `nos_page` SET `page_context` = CONCAT('main::', `page_context`);

ALTER TABLE `nos_user_role` ADD PRIMARY KEY ( `user_id` , `role_id` );

ALTER TABLE `nos_user` ADD `user_expert` tinyint(1) NOT NULL DEFAULT '0' AFTER `user_configuration`;

ALTER TABLE `nos_wysiwyg` ADD INDEX ( `wysiwyg_join_table` );
ALTER TABLE `nos_wysiwyg` ADD INDEX ( `wysiwyg_foreign_id` );

CREATE TABLE IF NOT EXISTS `nos_form` (
  `form_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `form_context` varchar(25) NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `form_virtual_name` varchar(30) NOT NULL,
  `form_manager_id` int(10) unsigned DEFAULT NULL,
  `form_client_email_field_id` int(10) unsigned DEFAULT NULL,
  `form_layout` text NOT NULL,
  `form_captcha` tinyint(1) NOT NULL,
  `form_submit_label` varchar(255) NOT NULL,
  `form_submit_email` text,
  `form_created_at` datetime NOT NULL,
  `form_updated_at` datetime NOT NULL,
  PRIMARY KEY (`form_id`),
  KEY `form_context` (`form_context`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `nos_form_answer` (
  `answer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `answer_form_id` int(10) unsigned NOT NULL,
  `answer_ip` varchar(40) NOT NULL,
  `answer_created_at` datetime NOT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `response_form_id` (`answer_form_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `nos_form_answer_field` (
  `anfi_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anfi_answer_id` int(10) unsigned NOT NULL,
  `anfi_field_id` int(10) unsigned NOT NULL,
  `anfi_field_type` varchar(100) NOT NULL,
  `anfi_value` text NOT NULL,
  PRIMARY KEY (`anfi_id`),
  UNIQUE KEY `anfi_answer_id` (`anfi_answer_id`,`anfi_field_id`),
  UNIQUE KEY `anfi_field_id` (`anfi_field_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `nos_form_field` (
  `field_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field_form_id` int(10) unsigned NOT NULL,
  `field_type` varchar(100) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_message` text NOT NULL,
  `field_virtual_name` varchar(30) NOT NULL,
  `field_choices` text NOT NULL,
  `field_created_at` datetime NOT NULL,
  `field_mandatory` tinyint(1) NOT NULL,
  `field_default_value` varchar(255) NOT NULL,
  `field_details` text NOT NULL,
  `field_style` enum('p','h1','h2','h3') NOT NULL,
  `field_width` tinyint(4) NOT NULL,
  `field_height` tinyint(4) NOT NULL,
  `field_limited_to` int(11) NOT NULL,
  `field_origin` varchar(30) NOT NULL,
  `field_origin_var` varchar(30) NOT NULL,
  `field_technical_id` varchar(30) NOT NULL,
  `field_technical_css` varchar(100) NOT NULL,
  PRIMARY KEY (`field_id`),
  KEY `field_form_id` (`field_form_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `nos_slideshow` (
  `slideshow_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slideshow_title` varchar(255) NOT NULL,
  `slideshow_context` varchar(25) NOT NULL,
  `slideshow_created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `slideshow_updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`slideshow_id`),
  KEY `slideshow_context` (`slideshow_context`),
  KEY `slideshow_created_at` (`slideshow_created_at`),
  KEY `slideshow_updated_at` (`slideshow_updated_at`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `nos_slideshow_image` (
  `slidimg_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slidimg_slideshow_id` varchar(255) NOT NULL,
  `slidimg_position` int(10) NOT NULL,
  `slidimg_title` varchar(255) DEFAULT NULL,
  `slidimg_description` text,
  `slidimg_link_to_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`slidimg_id`),
  KEY `slidimg_slideshow_id` (`slidimg_slideshow_id`,`slidimg_position`),
  KEY `slidimg_position` (`slidimg_position`)
) DEFAULT CHARSET=utf8;
SQL;
        foreach (explode(';', $alters) as $alter) {
            $alter = trim(trim($alter), PHP_EOL);
            if (!empty($alter)) {
                \DB::query($alter)->execute();
            }
        }

        // Clear pages cache, now cache use domain
        if (file_exists(\Config::get('cache_dir').'pages')) {
            \File::delete_dir(\Config::get('cache_dir').'pages', true, false);
        }


        // Update url_enhanced config file, integrate contexts
        \Config::load(APPPATH.'data'.DS.'config'.DS.'url_enhanced.php', 'data::url_enhanced');

        $url_enhanced_old = \Config::get("data::url_enhanced", array());
        $url_enhanced_new = array();
        foreach ($url_enhanced_old as $page_id) {
            $page = \Nos\Model_Page::find($page_id);
            if (!empty($page)) {
                $url_enhanced_new[$page_id] = array(
                    'url' => $page->page_entrance ? '' : $page->virtual_path(true),
                    'context' => $page->page_context,
                );
            }
        }
        \Config::save(APPPATH.'data'.DS.'config'.DS.'url_enhanced.php', $url_enhanced_new);
    }

    public function down()
    {

    }
}