<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.1.6
 * @ Release: 10/08/2022
 */

// Decoded file for php version 72.
class ModelExtensionModuleConfigurator extends Model
{
    private $path = "extension/module/configurator";
    public function __construct($params)
    {
        parent::__construct($params);
        if(version_compare(VERSION, "2.3.0.0", "<")) {
            $this->path = "module/configurator";
        }
    }
    private function checkList($list)
    {
        return is_string($list) || is_numeric($list) ? preg_match("/^(?:\\-?\\d\\,?\\s?)+\$/", $list) : false;
    }
    public function createModuleLayout()
    {
        $_obfuscated_0D1C2C1A3B2F5C3E161A050919303E2C340F5C0E061622_ = $this->db->query("SELECT `layout_id` FROM " . DB_PREFIX . "layout_route WHERE `route` = '" . $this->path . "'")->num_rows;
        if(!$_obfuscated_0D1C2C1A3B2F5C3E161A050919303E2C340F5C0E061622_) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout SET `name` = 'Configurator'");
            $_obfuscated_0D1E27130D3E2429183009130B0E0C2D291C2731350F01_ = $this->db->getLastId();
            $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route (`layout_id`, `store_id`, `route`) VALUES ('" . (int) $_obfuscated_0D1E27130D3E2429183009130B0E0C2D291C2731350F01_ . "', 0, '" . $this->path . "')");
        }
    }
    public function createModuleTables()
    {
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_sections` (`id` INT(11) UNSIGNED NOT NULL,`group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',`img_path` TEXT NOT NULL,`category_id_list` TEXT NOT NULL,`sort_order` INT(11) UNSIGNED NOT NULL DEFAULT '0',`qty_choice` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',`progress` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',`required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',`init_state` TINYINT(1) NOT NULL DEFAULT '1',`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',PRIMARY KEY (`id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_section_language` (`section_id` INT(11) UNSIGNED NOT NULL,`language_id` INT(11) UNSIGNED NOT NULL,`name` VARCHAR(255) NOT NULL,`description` TEXT NOT NULL,PRIMARY KEY (`section_id`, `language_id`),INDEX `name` (`name`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_conditions` (`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,`section_id` INT(11) UNSIGNED NULL DEFAULT NULL,`type` VARCHAR(16) NOT NULL,`progress_level` INT(11) UNSIGNED NULL DEFAULT '0',`trg_section_id` INT(11) UNSIGNED NULL DEFAULT '0',`qty_filled_min` INT(11) UNSIGNED NULL DEFAULT '0',`qty_filled_max` INT(11) UNSIGNED NULL DEFAULT '0',PRIMARY KEY (`id`),INDEX `type` (`type`),INDEX `section_id` (`section_id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_condition_language` (`condition_id` INT(11) UNSIGNED NOT NULL,`language_id` INT(11) UNSIGNED NOT NULL,`help_text` TEXT NOT NULL,PRIMARY KEY (`condition_id`, `language_id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_condition_products` (`product_id` INT(11) UNSIGNED NOT NULL,`condition_id` INT(11) UNSIGNED NOT NULL,`qty_filled_min` INT(11) UNSIGNED NULL DEFAULT '0',`qty_filled_max` INT(11) UNSIGNED NULL DEFAULT '0',PRIMARY KEY (`product_id`, `condition_id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_presets` (`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,`category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',`link_md5` CHAR(32) NOT NULL,`link` TEXT NOT NULL,`img_path` TEXT NOT NULL,`viewed` INT(11) UNSIGNED NOT NULL DEFAULT '0',`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',`date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),INDEX `link_md5` (`link_md5`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_preset_language` (`preset_id` INT(11) UNSIGNED NOT NULL,`language_id` INT(11) UNSIGNED NOT NULL,`name` VARCHAR(255) NOT NULL,`brief_desc` TEXT NOT NULL,`main_desc` TEXT NOT NULL,`meta_title` VARCHAR(255) NOT NULL DEFAULT '',`meta_desc` TEXT NOT NULL,`meta_keyword` TEXT NOT NULL,`tag` TEXT NOT NULL,PRIMARY KEY (`preset_id`, `language_id`),INDEX `name` (`name`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_reviews` (`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,`preset_id` INT(11) UNSIGNED NOT NULL,`customer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',`config_lang` INT(11) UNSIGNED NOT NULL DEFAULT '0',`email` VARCHAR(96) NOT NULL,`autor` VARCHAR(64) NOT NULL,`positive` TINYTEXT NOT NULL,`negative` TINYTEXT NOT NULL,`review` TEXT NOT NULL,`rating` INT(1) UNSIGNED NOT NULL DEFAULT '5',`recommend` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',`likes` INT(11) UNSIGNED NOT NULL DEFAULT '0',`dislikes` INT(11) UNSIGNED NOT NULL DEFAULT '0',`moderated` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',`date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),INDEX `preset_id` (`preset_id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_category_exclusions` (`category_id` INT(11) UNSIGNED NOT NULL,`exclusion_category_id` INT(11) NOT NULL,PRIMARY KEY (`category_id`, `exclusion_category_id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_product_exclusions` (`product_id` INT(11) UNSIGNED NOT NULL,`exclusion_product_id` INT(11) NOT NULL,PRIMARY KEY (`product_id`, `exclusion_product_id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_attribute_exclusions` (`exclusion_id` INT(11) UNSIGNED NOT NULL,`language_id` INT(11) UNSIGNED NOT NULL,`attribute_id` INT(11) UNSIGNED NOT NULL,`text` VARCHAR(155) NOT NULL DEFAULT '*',`exclusion_attribute_id` INT(11) UNSIGNED NOT NULL,`exclusion_text` VARCHAR(155) NOT NULL DEFAULT '*',PRIMARY KEY (`exclusion_id`, `language_id`, `attribute_id`, `exclusion_attribute_id`),UNIQUE INDEX `UNIQUE VALUES` (`exclusion_text`, `text`, `language_id`, `exclusion_attribute_id`, `attribute_id`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
        $this->db->query("\r\n\t\t\tCREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "configurator_history` (`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,`type` VARCHAR(64) NOT NULL DEFAULT 'info',`customer_id` INT(11) UNSIGNED NULL DEFAULT '0',`preset_id` INT(11) UNSIGNED NULL DEFAULT '0',`review_id` INT(11) UNSIGNED NULL DEFAULT '0',`client_ip` VARCHAR(45) NOT NULL DEFAULT '',`text` TEXT NOT NULL,`link` TEXT NOT NULL,`data` TEXT NOT NULL,`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),INDEX `type` (`type`))COLLATE='utf8_general_ci'ENGINE=MyISAM;");
    }
    public function deleteModuleData()
    {
        $query = $this->db->query("SELECT " . DB_PREFIX . "layout_route.`layout_id` FROM " . DB_PREFIX . "layout_route WHERE " . DB_PREFIX . "layout_route.`route` = '" . $this->path . "'");
        if($query->num_rows) {
            $this->load->model("design/layout");
            foreach ($query->rows as $key => $val) {
                $this->model_design_layout->deleteLayout($val["layout_id"]);
            }
        }
        $this->db->query("\r\n\t\t\tDROP TABLE IF EXISTS \r\n\t\t\t\t`" . DB_PREFIX . "configurator_sections`, \r\n\t\t\t\t`" . DB_PREFIX . "configurator_section_language`, \r\n\t\t\t\t`" . DB_PREFIX . "configurator_conditions`, \r\n\t\t\t\t`" . DB_PREFIX . "configurator_condition_language`, \r\n\t\t\t\t`" . DB_PREFIX . "configurator_condition_products`, \r\n\t\t\t\t`" . DB_PREFIX . "configurator_presets`,\r\n\t\t\t\t`" . DB_PREFIX . "configurator_preset_language`,\r\n\t\t\t\t`" . DB_PREFIX . "configurator_reviews`,\r\n\t\t\t\t`" . DB_PREFIX . "configurator_category_exclusions`,\r\n\t\t\t\t`" . DB_PREFIX . "configurator_product_exclusions`,\r\n\t\t\t\t`" . DB_PREFIX . "configurator_attribute_exclusions`,\r\n\t\t\t\t`" . DB_PREFIX . "configurator_attribute_history`\r\n\t\t");
        if(version_compare(VERSION, "3.0.0.0", ">=")) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = '" . $this->path . "'");
        } else {
            $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = '" . $this->path . "'");
        }
    }
    public function setURLAliasSEO($alias = "configurator")
    {
        $alias = trim($alias);
        $alias = !empty($alias) ? $this->db->escape($alias) : "configurator";
        if(version_compare(VERSION, "3.0.0.0", ">=")) {
            $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ = $this->config->get("config_language_id");
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->path . "'");
            if(!$query->num_rows) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url (`language_id`, `query`, `keyword`) VALUES ('" . (int) $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ . "', '" . $this->path . "', '" . $alias . "')");
            } elseif($query->row["keyword"] != $alias || $query->row["language_id"] != $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_) {
                $this->db->query("UPDATE " . DB_PREFIX . "seo_url SET `language_id` = '" . (int) $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ . "', `keyword` = '" . $alias . "' WHERE `seo_url_id` = '" . (int) $query->row["seo_url_id"] . "'");
            }
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE `query` = '" . $this->path . "'");
            if(!$query->num_rows) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (`query`, `keyword`) VALUES ('" . $this->path . "', '" . $alias . "')");
            } elseif($query->row["keyword"] != $alias) {
                $this->db->query("UPDATE " . DB_PREFIX . "url_alias SET `keyword` = '" . $alias . "' WHERE `url_alias_id` = '" . (int) $query->row["url_alias_id"] . "'");
            }
        }
    }
    public function getEventTypes()
    {
        return $this->db->query("\r\n\t\t\tSELECT GROUP_CONCAT(DISTINCT `type`SEPARATOR ',') AS `types`\r\n\t\t\tFROM " . DB_PREFIX . "configurator_history\r\n\t\t\tORDER BY `type`\r\n\t\t")->row["types"];
    }
    public function getHistory($input)
    {
        if(isset($input["start"]) && isset($input["limit"])) {
            $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ = "LIMIT " . (int) $input["start"] . ", " . (int) $input["limit"];
        } else {
            $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ = "";
        }
        if(!empty($input["type_filter"])) {
            $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ = "WHERE `type` = '" . $this->db->escape($input["type_filter"]) . "'";
        } else {
            $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ = "";
        }
        return $this->db->query("\r\n\t\t\tSELECT * FROM " . DB_PREFIX . "configurator_history\r\n\t\t\t" . $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ . "\r\n\t\t\tGROUP BY `id`\r\n\t\t\tORDER BY `date` DESC\r\n\t\t\t" . $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ . "\r\n\t\t")->rows;
    }
    public function getMainStat()
    {
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = $this->db->query("SELECT COUNT(`id`) AS 'preset_num',SUM(`status` = 1) AS 'preset_act_num',SUM(`status` = 0) AS 'preset_dis_num'FROM " . DB_PREFIX . "configurator_presets\r\n\t\t")->row;
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ += $this->db->query("SELECT SUM(`type` = 'preset_viewed' AND `date` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) AS 'preset_vwd_month_num',SUM(`type` = 'preset_viewed' AND `date` >= DATE_SUB(NOW(), INTERVAL 7 WEEK)) AS 'preset_vwd_week_num',SUM(`type` = 'preset_viewed' AND `date` >= DATE_SUB(NOW(), INTERVAL 1 DAY)) AS 'preset_vwd_day_num',SUM((`type` = 'added_to_cart' OR `type` = 'added_to_cart_err')AND `date` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) AS 'add_cart_month_num',SUM((`type` = 'added_to_cart' OR `type` = 'added_to_cart_err')AND `date` >= DATE_SUB(NOW(), INTERVAL 7 WEEK)) AS 'add_cart_week_num',SUM((`type` = 'added_to_cart' OR `type` = 'added_to_cart_err')AND `date` >= DATE_SUB(NOW(), INTERVAL 1 DAY)) AS 'add_cart_day_num'FROM " . DB_PREFIX . "configurator_history\r\n\t\t")->row;
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ += $this->db->query("SELECT COUNT(`id`) AS 'review_num',SUM(`moderated` = 1 AND `status` = 1) AS 'review_act_num',SUM(`moderated` = 1 AND `status` = 0) AS 'review_dis_num',SUM(`moderated` = 0) AS 'review_moder_num',SUM(`date_added` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) AS 'review_add_month_num',SUM(`date_added` >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS 'review_add_week_num',SUM(`date_added` >= DATE_SUB(NOW(), INTERVAL 1 DAY)) AS 'review_add_day_num',AVG(IF(`moderated` = 1 AND `status` = 1, `rating`, NULL)) AS 'review_rat_avg',AVG(IF(`moderated` = 1 AND `status` = 1 AND `date_added` >= DATE_SUB(NOW(), INTERVAL 1 MONTH), `rating`, NULL)) AS 'review_rat_mont_avg',AVG(IF(`moderated` = 1 AND `status` = 1 AND `date_added` >= DATE_SUB(NOW(), INTERVAL 7 DAY), `rating`, NULL)) AS 'review_rat_week_avg'FROM " . DB_PREFIX . "configurator_reviews\r\n\t\t")->row;
        return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
    }
    public function getIncludedCategories($category_id_list)
    {
        if($category_id_list && $this->checkList($category_id_list)) {
            return $this->db->query("\r\n\t\t\t\tSELECT cat.`category_id`, \r\n\t\t\t\t\t(if(cat.`parent_id` = 0, cat_desc.`name`, CONCAT(\r\n\t\t\t\t\t\t(\r\n\t\t\t\t\t\t\tSELECT GROUP_CONCAT(cat_desc2.`name` ORDER BY cat_path2.`level` SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') \r\n\t\t\t\t\t\t\tFROM " . DB_PREFIX . "category_path cat_path2 \r\n\t\t\t\t\t\t\tJOIN " . DB_PREFIX . "category_description cat_desc2 \r\n\t\t\t\t\t\t\t\tON cat_path2.`path_id` = cat_desc2.`category_id` \r\n\t\t\t\t\t\t\t\tAND cat_path2.`category_id` != cat_path2.`path_id`\r\n\t\t\t\t\t\t\tWHERE cat_path2.`category_id` = cat.`category_id` \r\n\t\t\t\t\t\t\tAND cat_desc2.`language_id` = cat_desc.`language_id` \r\n\t\t\t\t\t\t\tGROUP BY cat_path2.`category_id`\r\n\t\t\t\t\t\t\tORDER BY NULL\r\n\t\t\t\t\t\t), \r\n\t\t\t\t\t\t'&nbsp;&nbsp;&gt;&nbsp;&nbsp;', \r\n\t\t\t\t\t\tcat_desc.`name`\r\n\t\t\t\t\t))) AS 'name'\r\n\t\t\t\tFROM " . DB_PREFIX . "category cat \r\n\t\t\t\tJOIN " . DB_PREFIX . "category_path cat_path \r\n\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "category_description cat_desc\r\n\t\t\t\t\tON cat.`category_id` = cat_path.`category_id` \r\n\t\t\t\t\tAND cat.`category_id` = cat_desc.`category_id` \r\n\t\t\t\tWHERE cat.`category_id` IN (" . $category_id_list . ") \r\n\t\t\t\tAND cat.`status` = '1'\t\t\r\n\t\t\t\tAND cat_desc.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\tGROUP BY cat.`category_id`\r\n\t\t\t\tORDER BY `name`\r\n\t\t\t")->rows;
        }
        return [];
    }
    public function getSectionLangValues($section_id)
    {
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = [];
        $lang = $this->db->query("\r\n\t\t\t\tSELECT *\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_section_language \r\n\t\t\t\tWHERE `section_id` = '" . (int) $section_id . "'\r\n\t\t\t\tGROUP BY `language_id`\r\n\t\t\t")->rows;
        foreach ($lang as $value) {
            unset($value["section_id"]);
            $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ = $value["language_id"];
            $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_[$_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_] = $value;
        }
        return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
    }
    public function getSectionList($language_id = NULL)
    {
        if($language_id) {
            return $this->db->query("\r\n\t\t\t\t\tSELECT s.*,s_lang.`name`, s_lang.`description`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_sections s\r\n\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_section_language s_lang\r\n\t\t\t\t\t\tON s.`id` = s_lang.`section_id` AND s_lang.`language_id` = '" . (int) $language_id . "'\r\n\t\t\t\t\tGROUP BY s.`id`\r\n\t\t\t\t")->rows;
        }
        return $this->db->query("SELECT * FROM " . DB_PREFIX . "configurator_sections GROUP BY `id`")->rows;
    }
    public function updateSections($sections)
    {
        if(!$this->confirmProtection()) {
            return false;
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_sections");
        $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_section_language");
        if($sections) {
            $sql = "INSERT INTO " . DB_PREFIX . "configurator_sections (`id`, `group_id`, `img_path`, `category_id_list`, `id_main_section`, `id_dop_sections`, `sort_order`, `qty_choice`, `progress`, `required`, `hide`, `status`) VALUES ";
            $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ = "INSERT INTO " . DB_PREFIX . "configurator_section_language (`section_id`, `language_id`, `name`, `description`) VALUES ";
            $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_ = [];
            $_obfuscated_0D270A371934032E081F403517244036030C161E251501_ = [];
            foreach ($sections as $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_) {
                $category_id_list = $this->checkList($_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["category_id_list"]) ? $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["category_id_list"] : "";
                $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_[] = "('" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["id"] . "', '" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["group_id"] . "', '" . $this->db->escape($_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["img_path"]) . "', '" . $this->db->escape($category_id_list) . "', '" . (int)$_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["id_main_section"] . "', '" . $this->db->escape($_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["id_dop_sections"]) . "', '" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["sort_order"] . "', '" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["qty_choice"] . "', '" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["progress"] . "', '" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["required"] . "', '" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["hide"] . "',  '" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["status"] . "')";
                foreach ($_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["lang_values"] as $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ => $_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_) {
                    $_obfuscated_0D270A371934032E081F403517244036030C161E251501_[] = "('" . (int) $_obfuscated_0D3F2B190914265C281112215B0E2C2301025B19232422_["id"] . "', '" . (int) $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["name"]) . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["description"]) . "')";
                }
            }
            if($_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_) {
                $sql .= implode(",", $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_);
                $this->db->query($sql);
            }
            if($_obfuscated_0D270A371934032E081F403517244036030C161E251501_) {
                $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ .= implode(",", $_obfuscated_0D270A371934032E081F403517244036030C161E251501_);
                $this->db->query($_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_);
            }

			$_obfuscated_0D1936032A1F300C040734340F2C31112C070917361011_ = array();

			foreach ($sections as $section) {
				$_obfuscated_0D1936032A1F300C040734340F2C31112C070917361011_[]  = $section['id'];
			}

            $_obfuscated_0D3824261E0F0E0F3C1E303004312A0F0509243C042322_ = implode(",", $_obfuscated_0D1936032A1F300C040734340F2C31112C070917361011_);

            $this->db->query("\r\n\t\t\t\tDELETE cnd.*, cnd_lang.*, cnd_p.*\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_conditions cnd\r\n\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_condition_language cnd_lang\r\n\t\t\t\t\t\tON cnd.`id` = cnd_lang.`condition_id`\r\n\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_condition_products cnd_p\r\n\t\t\t\t\t\tON cnd.`type` = 'filled_prod' AND cnd.`id` = cnd_p.`condition_id`\r\n\t\t\t\t\t\t\r\n\t\t\t\tWHERE cnd.`section_id` NOT IN (" . $_obfuscated_0D3824261E0F0E0F3C1E303004312A0F0509243C042322_ . ")\r\n\t\t\t");
        }
        return true;
    }
    public function checkSectionExistence($section_id)
    {
        return $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "configurator_sections WHERE `id` = " . (int) $section_id)->row;
    }
    public function updateSectionInitState($section_id, $value)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "configurator_sections SET `init_state` = '" . (int) $value . "' WHERE `id` = '" . (int) $section_id . "'");
        return true;
    }
    public function setSectionCondition($condition)
    {
        if(!$this->confirmProtection()) {
            return false;
        }
        $_obfuscated_0D1F0D3C0A33022C182E060C3D1C02010F0B01233F2101_ = !empty($condition["id"]) ? (int) $condition["id"] : "";
        $_obfuscated_0D39303F1039041C39390B241B2D3706162210073F1E32_ = !empty($condition["progress_level"]) ? (int) $condition["progress_level"] : "";
        $_obfuscated_0D1B1D1F2A382F391507302632020A112B210625010A11_ = !empty($condition["trg_section_id"]) ? (int) $condition["trg_section_id"] : "";
        $_obfuscated_0D091925140236223D3D38393B3D3132143021402A3111_ = !empty($condition["qty_filled_min"]) ? (int) $condition["qty_filled_min"] : "";
        $_obfuscated_0D3922341A2E2B5C33155C293F07313F18242A22243601_ = !empty($condition["qty_filled_max"]) ? (int) $condition["qty_filled_max"] : "";
        $this->db->query("\r\n\t\t\tINSERT INTO " . DB_PREFIX . "configurator_conditions (`id`, `section_id`, `type`, `progress_level`, `trg_section_id`, `qty_filled_min`, `qty_filled_max`)VALUES ('" . $_obfuscated_0D1F0D3C0A33022C182E060C3D1C02010F0B01233F2101_ . "',\r\n\t\t\t\t'" . (int) $condition["section_id"] . "', \r\n\t\t\t\t'" . $this->db->escape($condition["type"]) . "',\r\n\t\t\t\t'" . $_obfuscated_0D39303F1039041C39390B241B2D3706162210073F1E32_ . "', \r\n\t\t\t\t'" . $_obfuscated_0D1B1D1F2A382F391507302632020A112B210625010A11_ . "',\r\n\t\t\t\t'" . $_obfuscated_0D091925140236223D3D38393B3D3132143021402A3111_ . "',\r\n\t\t\t\t'" . $_obfuscated_0D3922341A2E2B5C33155C293F07313F18242A22243601_ . "')ON DUPLICATE KEY UPDATE`type`= VALUES(`type`),`progress_level`= VALUES(`progress_level`),`trg_section_id`= VALUES(`trg_section_id`),`qty_filled_min`= VALUES(`qty_filled_min`),`qty_filled_max`= VALUES(`qty_filled_max`)");
        if($_obfuscated_0D1F0D3C0A33022C182E060C3D1C02010F0B01233F2101_) {
            $_obfuscated_0D180A391D0412172406262840280A30090D1D11142A32_ = true;
            $_obfuscated_0D36193C2E1D3D062A3E07351834281C2C2B5C22141101_ = $_obfuscated_0D1F0D3C0A33022C182E060C3D1C02010F0B01233F2101_;
        } else {
            $_obfuscated_0D180A391D0412172406262840280A30090D1D11142A32_ = false;
            $_obfuscated_0D36193C2E1D3D062A3E07351834281C2C2B5C22141101_ = $this->db->getLastId();
        }
        if($_obfuscated_0D36193C2E1D3D062A3E07351834281C2C2B5C22141101_) {
            if($condition["help_text"]) {
                $_obfuscated_0D270A371934032E081F403517244036030C161E251501_ = [];
                $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ = "INSERT INTO " . DB_PREFIX . "configurator_condition_language (`condition_id`, `language_id`, `help_text`) VALUES ";
                foreach ($condition["help_text"] as $_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_) {
                    $_obfuscated_0D270A371934032E081F403517244036030C161E251501_[] = "('" . (int) $_obfuscated_0D36193C2E1D3D062A3E07351834281C2C2B5C22141101_ . "', '" . (int) $_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["lang_id"] . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["text"]) . "')";
                }
                $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ .= implode(",", $_obfuscated_0D270A371934032E081F403517244036030C161E251501_);
                $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ .= " ON DUPLICATE KEY UPDATE `help_text` = VALUES(`help_text`)";
                $this->db->query($_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_);
            }
            if($condition["type"] === "filled_prod" && $condition["section_products"]) {
                $_obfuscated_0D3132280119243E2B5C115C5B3B3C12241426181F3101_ = [];
                $_obfuscated_0D1C390F2212382927053D312236163028320624163C11_ = "INSERT INTO " . DB_PREFIX . "configurator_condition_products (`product_id`, `condition_id`, `qty_filled_min`, `qty_filled_max`) VALUES ";
                foreach ($condition["section_products"] as $_obfuscated_0D392522303319072A153C0824283E1E24370D16101011_) {
                    $_obfuscated_0D3132280119243E2B5C115C5B3B3C12241426181F3101_[] = "('" . (int) $_obfuscated_0D392522303319072A153C0824283E1E24370D16101011_["id"] . "', '" . (int) $_obfuscated_0D36193C2E1D3D062A3E07351834281C2C2B5C22141101_ . "', '" . (int) $_obfuscated_0D392522303319072A153C0824283E1E24370D16101011_["qty_min"] . "', '" . (int) $_obfuscated_0D392522303319072A153C0824283E1E24370D16101011_["qty_max"] . "')";
                }
                $_obfuscated_0D1C390F2212382927053D312236163028320624163C11_ .= implode(",", $_obfuscated_0D3132280119243E2B5C115C5B3B3C12241426181F3101_);
                $_obfuscated_0D1C390F2212382927053D312236163028320624163C11_ .= "ON DUPLICATE KEY UPDATE`qty_filled_min` = VALUES(`qty_filled_min`),`qty_filled_max` = VALUES(`qty_filled_max`)";
                if($_obfuscated_0D180A391D0412172406262840280A30090D1D11142A32_) {
                    $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_condition_products WHERE `condition_id` = '" . $_obfuscated_0D1F0D3C0A33022C182E060C3D1C02010F0B01233F2101_ . "'");
                }
                $this->db->query($_obfuscated_0D1C390F2212382927053D312236163028320624163C11_);
            }
            return true;
        } else {
            return false;
        }
    }
    public function deleteSectionConditions($id_list)
    {
        if($this->confirmProtection() && $this->checkList($id_list)) {
            $this->db->query("\r\n\t\t\t\tDELETE cnd.*, cnd_lang.*, cnd_p.*\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_conditions cnd\r\n\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_condition_language cnd_lang\r\n\t\t\t\t\t\tON cnd.`id` = cnd_lang.`condition_id`\r\n\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_condition_products cnd_p\r\n\t\t\t\t\t\tON cnd.`type` = 'filled_prod' AND cnd.`id` = cnd_p.`condition_id`\r\n\t\t\t\t\r\n\t\t\t\tWHERE cnd.`id` IN (" . $id_list . ")\r\n\t\t\t");
            $_obfuscated_0D140E111C10355B0408222B35333C0F06391F255C3501_ = $this->db->countAffected();
            $this->db->query("\r\n\t\t\t\tUPDATE " . DB_PREFIX . "configurator_sections SET `init_state` = '1'\r\n\t\t\t\tWHERE `id` NOT IN (\r\n\t\t\t\t\tSELECT DISTINCT `section_id` FROM " . DB_PREFIX . "configurator_conditions\r\n\t\t\t\t)\r\n\t\t\t");
            return $_obfuscated_0D140E111C10355B0408222B35333C0F06391F255C3501_;
        }
        return false;
    }
    public function getSectionConditions($section_id = NULL)
    {
        $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ = $section_id ? "WHERE `section_id` = '" . (int) $section_id . "'" : "";
        return $this->db->query("SELECT * FROM " . DB_PREFIX . "configurator_conditions " . $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ . " GROUP BY `id` ORDER BY `type`")->rows;
    }
    public function getConditionProductNum($condition_id)
    {
        return $this->db->query("SELECT `product_id` FROM " . DB_PREFIX . "configurator_condition_products WHERE `condition_id` = '" . (int) $condition_id . "' GROUP BY `product_id`")->num_rows;
    }
    public function getConditionData($condition_id)
    {
        $condition = $this->db->query("SELECT * FROM " . DB_PREFIX . "configurator_conditions WHERE `id` = '" . (int) $condition_id . "'\r\n\t\t\t")->row;
        if($condition) {
            $condition["lang_values"] = $this->db->query("SELECT * FROM " . DB_PREFIX . "configurator_condition_language WHERE `condition_id` = '" . (int) $condition_id . "'\r\n\t\t\t\t")->rows;
            $condition["products"] = $this->db->query("\r\n\t\t\t\t\tSELECT cnd_p.* ,  CONCAT(p_desc.`name`, ' (',p.`quantity` ,') | ', ' ', cat_desc.`name`) AS `name`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_condition_products cnd_p\r\n\t\t\t\t\tJOIN " . DB_PREFIX . "product p \r\n\t\t\t\t\tJOIN " . DB_PREFIX . "product_to_category p_to_cat \r\n\t\t\t\t\tJOIN " . DB_PREFIX . "category cat \r\n\t\t\t\t\t\tON p.`product_id` = cnd_p.`product_id`\r\n\t\t\t\t\t\tAND p.`product_id` = p_to_cat.`product_id` \r\n\t\t\t\t\t\tAND cat.`category_id` = p_to_cat.`category_id`\r\n\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "product_description p_desc \r\n\t\t\t\t\t\tON p.`product_id` = p_desc.`product_id` \r\n\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "category_description cat_desc\r\n\t\t\t\t\t\tON cat.`category_id` = cat_desc.`category_id`\r\n\r\n\t\t\t\t\tWHERE cnd_p.`condition_id` = '" . (int) $condition_id . "' \r\n\t\t\t\t\tAND p_desc.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\t\tGROUP BY cnd_p.`product_id`\r\n\t\t\t\t")->rows;
        }
        return $condition;
    }
    public function getProdListByNameForCondition($input)
    {
        $_obfuscated_0D042821301D1907283F24073931120C0C343616103401_ = $input["qty_min"] ? "AND p.`minimum` <= '" . (int) $input["qty_min"] . "'" : "";
        $_obfuscated_0D5B285C3125142C1E0F0D183E1B260109262A26303411_ = $input["qty_max"] ? "AND p.`quantity` >= '" . (int) $input["qty_max"] . "'" : "";
        return $this->db->query("\r\n\t\t\tSELECT p.`product_id`,  CONCAT(p_desc.`name`, ' (',p.`quantity` ,') | ', ' ', cat_desc.`name`) AS `name`\r\n\t\t\tFROM " . DB_PREFIX . "product p \r\n\t\t\tJOIN " . DB_PREFIX . "product_description p_desc \r\n\t\t\tJOIN " . DB_PREFIX . "product_to_category p_to_cat \r\n\t\t\tJOIN " . DB_PREFIX . "category cat \r\n\t\t\tJOIN " . DB_PREFIX . "category_description cat_desc\r\n\t\t\t\tON p.`product_id` = p_desc.`product_id` \r\n\t\t\t\tAND p.`product_id` = p_to_cat.`product_id` \r\n\t\t\t\tAND cat.`category_id` = p_to_cat.`category_id`\r\n\t\t\t\tAND cat.`category_id` = cat_desc.`category_id`,\r\n\t\t\t(\r\n\t\t\t\tSELECT cat.`category_id`\r\n\t\t\t\tFROM " . DB_PREFIX . "category cat\r\n\t\t\t\tJOIN " . DB_PREFIX . "category_path cat_path \r\n\t\t\t\t\tON cat.`category_id` = cat_path.`category_id`\r\n\t\t\t\tWHERE cat.`status` = '1'\r\n\t\t\t\tAND cat_path.`path_id` IN (\r\n\t\t\t\t\tSELECT `category_id_list`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_sections\r\n\t\t\t\t\tWHERE `id` = '" . (int) $input["section_id"] . "'AND LENGTH(`category_id_list`) >= 1)GROUP BY cat.`category_id`ORDER BY NULL) rel_catWHERE cat.`category_id` = rel_cat.`category_id` AND p.`status` = '1' AND p.`quantity` > '0'  AND p.`quantity` >= p.`minimum` " . $_obfuscated_0D042821301D1907283F24073931120C0C343616103401_ . "\r\n\t\t\t" . $_obfuscated_0D5B285C3125142C1E0F0D183E1B260109262A26303411_ . "\r\n\t\t\tAND p_desc.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\tAND p_desc.`name` LIKE '%" . $this->db->escape($input["filter_name"]) . "%'\r\n\t\t\tGROUP BY p.`product_id`\r\n\t\t\tORDER BY p_desc.`name`\r\n\t\t\tLIMIT " . (int) $input["start"] . ", " . (int) $input["limit"] . "\r\n\t\t")->rows;
    }
    public function setDefaultMissingSectionGroups($id_list)
    {
        if(!$this->confirmProtection() || !$this->checkList($id_list)) {
            return false;
        }
        return $this->db->query("UPDATE " . DB_PREFIX . "configurator_sections SET `group_id` = '0' WHERE `group_id` NOT IN (" . $id_list . ")");
    }
    public function getIncludedItemsOfSection($inc_ctgr_list, $type)
    {
        if(!$this->checkList($inc_ctgr_list)) {
            return [];
        }
        if($type === "category") {
            return $this->db->query("\r\n\t\t\t\tSELECT cat.`category_id`, \r\n\t\t\t\t\t(if(cat.`parent_id` = 0, cat_desc.`name`, CONCAT(\r\n\t\t\t\t\t\t(\r\n\t\t\t\t\t\t\tSELECT GROUP_CONCAT(cat_desc2.`name` ORDER BY cat_path2.`level` SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') \r\n\t\t\t\t\t\t\tFROM " . DB_PREFIX . "category_path cat_path2 \r\n\t\t\t\t\t\t\tJOIN " . DB_PREFIX . "category_description cat_desc2 \r\n\t\t\t\t\t\t\t\tON cat_path2.`path_id` = cat_desc2.`category_id` \r\n\t\t\t\t\t\t\t\tAND cat_path2.`category_id` != cat_path2.`path_id`\r\n\t\t\t\t\t\t\tWHERE cat_path2.`category_id` = cat.`category_id` \r\n\t\t\t\t\t\t\tAND cat_desc2.`language_id` = cat_desc.`language_id` \r\n\t\t\t\t\t\t\tGROUP BY cat_path2.`category_id`\r\n\t\t\t\t\t\t\tORDER BY NULL\r\n\t\t\t\t\t\t), \r\n\t\t\t\t\t\t'&nbsp;&nbsp;&gt;&nbsp;&nbsp;', \r\n\t\t\t\t\t\tcat_desc.`name`\r\n\t\t\t\t\t))) AS 'name'\r\n\t\t\t\t\t\r\n\t\t\t\tFROM " . DB_PREFIX . "category cat \r\n\t\t\t\tJOIN " . DB_PREFIX . "category_path cat_path \r\n\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "category_description cat_desc\r\n\t\t\t\t\tON cat.`category_id` = cat_path.`category_id` \r\n\t\t\t\t\tAND cat.`category_id` = cat_desc.`category_id` \r\n\t\t\t\tWHERE cat.`status` = '1'\r\n\t\t\t\tAND cat_path.`path_id` IN (" . $inc_ctgr_list . ")\r\n\t\t\t\tAND cat_desc.`language_id` = '" . (int) $this->config->get("config_language_id") . "' \r\n\t\t\t\tGROUP BY cat.`category_id`\r\n\t\t\t\tORDER BY `name`\r\n\t\t\t")->rows;
        }
        if($type === "product") {
            return $this->db->query("\r\n\t\t\t\tSELECT p.`product_id`, \r\n\t\t\t\t\tp_desc.`name`,\r\n\t\t\t\t\tp_to_cat.`category_id`, \r\n\t\t\t\t\tcat_desc.`name` AS 'category_name' \r\n\t\t\t\tFROM " . DB_PREFIX . "product p \r\n\t\t\t\tJOIN " . DB_PREFIX . "product_description p_desc \r\n\t\t\t\tJOIN " . DB_PREFIX . "product_to_category p_to_cat \r\n\t\t\t\tJOIN " . DB_PREFIX . "category cat \r\n\t\t\t\tJOIN " . DB_PREFIX . "category_description cat_desc \r\n\t\t\t\t\tON p.`product_id` = p_desc.`product_id` \r\n\t\t\t\t\tAND p.`product_id` = p_to_cat.`product_id` \r\n\t\t\t\t\tAND p_to_cat.`category_id` = cat.`category_id` \r\n\t\t\t\t\tAND cat.`category_id` = cat_desc.`category_id` \r\n\t\t\t\tWHERE cat.`category_id` IN (\r\n\t\t\t\t\tSELECT cat2.`category_id`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "category cat2\r\n\t\t\t\t\tJOIN " . DB_PREFIX . "category_path cat_path \r\n\t\t\t\t\t\tON cat2.`category_id` = cat_path.`category_id` \r\n\t\t\t\t\tWHERE cat_path.`path_id` IN (" . $inc_ctgr_list . ")\r\n\t\t\t\t\tGROUP BY cat2.`category_id`\r\n\t\t\t\t\tORDER BY NULL\r\n\t\t\t\t) \r\n\t\t\t\tAND p_desc.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\tAND cat_desc.`language_id` = p_desc.`language_id`\r\n\t\t\t\tGROUP BY p.`product_id` \r\n\t\t\t\tORDER BY `category_name` \r\n\t\t\t")->rows;
        }
        return [];
    }
    public function getExclusionsOfSection($target_id_list, $type)
    {
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = [];
        if($this->checkList($target_id_list)) {
            if($type === "product") {
                $query = $this->db->query("\r\n\t\t\t\t\tSELECT p_excl.`product_id`,\r\n\t\t\t\t\t\tp_excl.`exclusion_product_id`,\r\n\t\t\t\t\t\tCASE\r\n\t\t\t\t\t\t\tWHEN p_excl.`exclusion_product_id` = '-1' THEN ''\r\n\t\t\t\t\t\tELSE (SELECT p_desc.`name`\r\n\t\t\t\t\t\t\tFROM " . DB_PREFIX . "product_description p_desc\r\n\t\t\t\t\t\t\tWHERE p_excl.`exclusion_product_id` = p_desc.`product_id`\r\n\t\t\t\t\t\t\tAND p_desc.`language_id` = '" . (int) $this->config->get("config_language_id") . "')\r\n\t\t\t\t\t\tEND 'name'\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_product_exclusions p_excl\r\n\t\t\t\t\tWHERE p_excl.`product_id` IN (" . $target_id_list . ") \r\n\t\t\t\t");
                $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = $query->rows;
            } elseif($type === "category") {
                $query = $this->db->query("\r\n\t\t\t\t\tSELECT cat_excl.`category_id`,\r\n\t\t\t\t\t\tcat_excl.`exclusion_category_id`,\r\n\t\t\t\t\t\t(if(cat.`parent_id` = '0', cat_desc.`name`, CONCAT(\r\n\t\t\t\t\t\t\t(\r\n\t\t\t\t\t\t\t\tSELECT GROUP_CONCAT(cat_desc2.`name` ORDER BY cat_path2.`level` SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') \r\n\t\t\t\t\t\t\t\tFROM " . DB_PREFIX . "category_path cat_path2 \r\n\t\t\t\t\t\t\t\tJOIN " . DB_PREFIX . "category_description cat_desc2 \r\n\t\t\t\t\t\t\t\t\tON cat_path2.`path_id` = cat_desc2.`category_id` \r\n\t\t\t\t\t\t\t\t\tAND cat_path2.`category_id` != cat_path2.`path_id`\r\n\t\t\t\t\t\t\t\tWHERE cat_path2.`category_id` = cat.`category_id` \r\n\t\t\t\t\t\t\t\tAND cat_desc2.`language_id` = cat_desc.`language_id` \r\n\t\t\t\t\t\t\t\tGROUP BY cat_path2.`category_id` \r\n\t\t\t\t\t\t\t\tORDER BY NULL\r\n\t\t\t\t\t\t\t), \r\n\t\t\t\t\t\t\t'&nbsp;&nbsp;&gt;&nbsp;&nbsp;', \r\n\t\t\t\t\t\t\tcat_desc.`name`\r\n\t\t\t\t\t\t))) AS 'name' \r\n\t\t\t\t\t\t\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_category_exclusions cat_excl, \r\n\t\t\t\t\t\t" . DB_PREFIX . "category_description cat_desc, \r\n\t\t\t\t\t\t" . DB_PREFIX . "category cat \r\n\t\t\t\t\tWHERE cat_excl.`category_id` IN (" . $target_id_list . ")\r\n\t\t\t\t\tAND cat_excl.`exclusion_category_id` = cat_desc.`category_id` \r\n\t\t\t\t\tAND cat_desc.`category_id` = cat.`category_id` \r\n\t\t\t\t\tAND cat_desc.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\t\t\r\n\t\t\t\t\tUNION ALL \r\n\t\t\t\t\t\r\n\t\t\t\t\tSELECT cat_excl.`category_id`, \r\n\t\t\t\t\t\tcat_excl.`exclusion_category_id`, \r\n\t\t\t\t\t\t''\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_category_exclusions cat_excl \r\n\t\t\t\t\tWHERE cat_excl.`exclusion_category_id` = '-1' \r\n\t\t\t\t");
                $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = $query->rows;
            }
        }
        return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
    }
    public function getExclusionsOfAttribute($attr_id_list = NULL, $excl_id_list = NULL, $clear = false)
    {
        if($clear) {
            $this->db->query("\r\n\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_attribute_exclusions\r\n\t\r\n\t\t\t\tWHERE exclusion_attribute_id NOT IN (\r\n\t\t\t\t\tSELECT attr.`attribute_id` \r\n\t\t\t\t\tFROM " . DB_PREFIX . "attribute attr\r\n\t\t\t\t)\r\n\t\t\t\tOR attribute_id NOT IN (\r\n\t\t\t\t\tSELECT attr.`attribute_id` \r\n\t\t\t\t\tFROM " . DB_PREFIX . "attribute attr\r\n\t\t\t\t)\r\n\t\t\t");
        }
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = [];
        $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ = "";
        if($attr_id_list || $excl_id_list) {
            $params = [];
            if($this->checkList($attr_id_list)) {
                $params[] = "attr_excl.`attribute_id` IN (" . $attr_id_list . ")";
            }
            if($this->checkList($excl_id_list)) {
                $params[] = "attr_excl.`exclusion_id` IN (" . $excl_id_list . ")";
            }
            if($params) {
                $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ = "WHERE " . implode(" AND ", $params);
            }
        }
        $query = $this->db->query("\r\n\t\t\tSELECT DISTINCT\r\n\t\t\t\tattr_excl.`exclusion_id` AS `excl_id`,\r\n\t\t\t\tlang.sort_order AS `s_order`,\r\n\t\t\t\tattr_desc.`language_id` AS `lang_id`,\r\n\t\t\t\tattr_desc.`attribute_id` AS `attr_id`,\r\n\t\t\t\tattr_desc.`name` AS `attr`,\r\n\t\t\t\tattr_group_desc.`name` AS `excl_g_name`,\r\n\t\t\t\tattr_desc2.`attribute_id` AS `excl_attr_id`,\r\n\t\t\t\tattr_desc2.`name` AS `excl_attr`,\r\n\t\t\t\t(\r\n\t\t\t\t\tSELECT attr_excl2.`text`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl2\r\n\t\t\t\t\tWHERE attr_excl2.`exclusion_id` = excl_id\r\n\t\t\t\t\tAND attr_excl2.`attribute_id` = attr_id\r\n\t\t\t\t\tAND attr_excl2.`exclusion_attribute_id` = excl_attr_id\r\n\t\t\t\t\tAND attr_excl2.`language_id` = lang_id\r\n\t\t\t\t) AS `attr_value`,\r\n\t\t\t\t(\r\n\t\t\t\t\tSELECT attr_excl2.`exclusion_text`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl2\r\n\t\t\t\t\tWHERE attr_excl2.`exclusion_id` = excl_id\r\n\t\t\t\t\tAND attr_excl2.`attribute_id` = attr_id\r\n\t\t\t\t\tAND attr_excl2.`exclusion_attribute_id` = excl_attr_id\r\n\t\t\t\t\tAND attr_excl2.`language_id` = lang_id\r\n\t\t\t\t) AS `excl_attr_value`\r\n\t\t\t\r\n\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl\r\n\t\t\tJOIN " . DB_PREFIX . "attribute attr\r\n\t\t\tJOIN " . DB_PREFIX . "attribute_description attr_desc\r\n\t\t\tJOIN " . DB_PREFIX . "attribute_description attr_desc2\r\n\t\t\tJOIN " . DB_PREFIX . "attribute_group_description attr_group_desc\r\n\t\t\tJOIN " . DB_PREFIX . "language lang\r\n\t\t\t\tON attr_excl.`attribute_id` = attr_desc.`attribute_id`\r\n\t\t\t\tAND attr_excl.`exclusion_attribute_id` = attr_desc2.`attribute_id`\r\n\t\t\t\tAND attr_excl.`exclusion_attribute_id` = attr.`attribute_id`\r\n\t\t\t\tAND attr_group_desc.`attribute_group_id` = attr.`attribute_group_id`\r\n\t\t\t\tAND attr_desc.`language_id` = attr_desc2.`language_id`\r\n\t\t\t\tAND attr_desc.`language_id` = attr_group_desc.`language_id`\r\n\t\t\t\tAND attr_desc.`language_id` = lang.`language_id`\r\n\t\t\t\tAND lang.`status` = '1'\r\n\t\t\t\r\n\t\t\t" . $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ . "\r\n\t\t\tORDER BY excl_id, s_order, lang_id\r\n\t\t");
        foreach ($query->rows as $key => $_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_) {
            $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_[$_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["excl_id"]][$key] = ["lang_id" => $_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["lang_id"], "attr_id" => $_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["attr_id"], "attr" => $_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["attr"], "attr_value" => htmlspecialchars_decode($_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["attr_value"]), "excl_attr_id" => $_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["excl_attr_id"], "excl_attr" => $_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["excl_attr"], "excl_g_name" => $_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["excl_g_name"], "excl_attr_value" => htmlspecialchars_decode($_obfuscated_0D33035B070E343B3015172F0E0C5C1C173826211A2822_["excl_attr_value"])];
        }
        return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
    }
    public function getAttributeData($input)
    {
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = [];
        $_obfuscated_0D353F1B382923240D0A1E27070721332A130D2D1F2A32_ = $input["attr_data"];
        if($input["target_data"] === "attr_value" || $input["target_data"] === "excl_attr_value") {
            if($input["target_data"] === "attr_value") {
                $_obfuscated_0D263B231C092A1901370E1121342E06060C140B1F2132_ = "AND p_attr.`attribute_id` = '" . (int) $_obfuscated_0D353F1B382923240D0A1E27070721332A130D2D1F2A32_["attr_id"] . "'";
            } else {
                $_obfuscated_0D263B231C092A1901370E1121342E06060C140B1F2132_ = $_obfuscated_0D353F1B382923240D0A1E27070721332A130D2D1F2A32_["excl_attr_id"] ? "AND p_attr.`attribute_id` = '" . (int) $_obfuscated_0D353F1B382923240D0A1E27070721332A130D2D1F2A32_["excl_attr_id"] . "'" : "";
            }
            $query = $this->db->query("\r\n\t\t\t\tSELECT DISTINCT \r\n\t\t\t\t\tp_attr.`attribute_id`,\r\n\t\t\t\t\tp_attr.`product_id`,\r\n\t\t\t\t\tp_attr.`text`,\r\n\t\t\t\t\tp_attr.`language_id`\r\n\r\n\t\t\t\tFROM " . DB_PREFIX . "product_attribute p_attr,\r\n\t\t\t\t\t(SELECT DISTINCT p_attr.`product_id`\r\n\r\n\t\t\t\t\tFROM " . DB_PREFIX . "product_attribute p_attr\r\n\r\n\t\t\t\t\tWHERE p_attr.`language_id` = '" . (int) $_obfuscated_0D353F1B382923240D0A1E27070721332A130D2D1F2A32_["lang_id"] . "'\r\n\t\t\t\t\t" . $_obfuscated_0D263B231C092A1901370E1121342E06060C140B1F2132_ . "\r\n\t\t\t\t\tAND p_attr.`text` LIKE '%" . $this->db->escape($input["filter_name"]) . "%'\r\n\t\t\t\t\tLIMIT " . (int) $input["start"] . ", " . (int) $input["limit"] . ") target_p\r\n\r\n\t\t\t\tWHERE p_attr.`product_id` IN(target_p.`product_id`)\r\n\t\t\t\t" . $_obfuscated_0D263B231C092A1901370E1121342E06060C140B1F2132_ . "\r\n\t\t\t\tORDER BY p_attr.`language_id`\r\n\t\t\t");
            $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = $query->rows;
        } elseif($input["target_data"] === "excl_attr") {
            $query = $this->db->query("\r\n\t\t\t\tSELECT DISTINCT \r\n\t\t\t\t\tattr.`attribute_id`,\r\n\t\t\t\t\tattr_desc.`name`,\r\n\t\t\t\t\tattr_goup.`attribute_group_id`,\r\n\t\t\t\t\tattr_goup_desc.`name` AS `group_name`,\r\n\t\t\t\t\tattr_desc.`language_id`\r\n\t\t\t\t\t\r\n\t\t\t\tFROM " . DB_PREFIX . "attribute attr \r\n\t\t\t\tJOIN " . DB_PREFIX . "attribute_description attr_desc\r\n\t\t\t\tJOIN " . DB_PREFIX . "attribute_group attr_goup\r\n\t\t\t\tJOIN " . DB_PREFIX . "attribute_group_description attr_goup_desc\r\n\t\t\t\t\tON attr.`attribute_id` = attr_desc.`attribute_id`\r\n\t\t\t\t\tAND attr_goup.`attribute_group_id` = attr_goup_desc.`attribute_group_id`\r\n\t\t\t\t\tAND attr.`attribute_group_id` = attr_goup.`attribute_group_id`\r\n\t\t\t\t\tAND attr_desc.`language_id` = attr_goup_desc.`language_id`,\r\n\t\t\t\t(SELECT DISTINCT attr.`attribute_id`\r\n\r\n\t\t\t\tFROM " . DB_PREFIX . "attribute attr \r\n\t\t\t\tJOIN " . DB_PREFIX . "attribute_description attr_desc\r\n\t\t\t\t\tON attr.`attribute_id` = attr_desc.`attribute_id`\r\n\r\n\t\t\t\tWHERE attr_desc.`language_id` = '" . (int) $_obfuscated_0D353F1B382923240D0A1E27070721332A130D2D1F2A32_["lang_id"] . "'\r\n\t\t\t\tAND attr_desc.`name` LIKE '%" . $this->db->escape($input["filter_name"]) . "%'\r\n\t\t\t\tLIMIT " . (int) $input["start"] . ", " . (int) $input["limit"] . ") target_attr\t\r\n\r\n\t\t\t\tWHERE attr.`attribute_id` IN(target_attr.`attribute_id`)\r\n\t\t\t\tORDER BY attr_desc.`language_id`, attr.`attribute_id`\r\n\t\t\t");
            $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = $query->rows;
        }
        return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
    }
    public function getNumAttrExclusions($attr_id_list = NULL)
    {
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = [];
        if(empty($attr_id_list)) {
            $attr_id_list = "SELECT `attribute_id` FROM " . DB_PREFIX . "attribute";
        } elseif(!$this->checkList($attr_id_list)) {
            return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
        }
        $query = $this->db->query("\r\n\t\t\tSELECT `attribute_id`, COUNT(DISTINCT `exclusion_id`) AS `count`\r\n\r\n\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions\r\n\r\n\t\t\tWHERE `attribute_id` IN(" . $attr_id_list . ")\r\n\t\t\tGROUP BY `attribute_id`\r\n\t\t\tORDER BY NULL\r\n\t\t");
        foreach ($query->rows as $_obfuscated_0D3C2E3F182C19103D1C135B3E3B263F2E3D0625031432_) {
            $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_[$_obfuscated_0D3C2E3F182C19103D1C135B3E3B263F2E3D0625031432_["attribute_id"]] = $_obfuscated_0D3C2E3F182C19103D1C135B3E3B263F2E3D0625031432_["count"];
        }
        return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
    }
    public function setExclusion($type, $params)
    {
        if($this->confirmProtection()) {
            if($type === "attribute") {
                $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ = (int) $params["excl_id"];
                $_obfuscated_0D333313061D2A2D382B182D25061040213B2C33150A22_ = (int) $params["attr_id"];
                $_obfuscated_0D383621261D171C342E3F091C32251F0A011D372C3B32_ = (int) $params["excl_attr_id"];
                $_obfuscated_0D2F0A342812383B04100D051F4035073709340B143222_ = $params["excl_val_data"];
                if(!$_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_) {
                    $query = $this->db->query("SELECT max(`exclusion_id`) AS `last_excl_id` FROM " . DB_PREFIX . "configurator_attribute_exclusions");
                    $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ = (int) $query->row["last_excl_id"] + 1;
                }
                $sql = "SELECT attr_excl.`exclusion_id` FROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl WHERE ";
                $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_ = [];
                foreach ($_obfuscated_0D2F0A342812383B04100D051F4035073709340B143222_ as $key => $_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_) {
                    $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_[] = "(\r\n\t\t\t\t\t\tattr_excl.`exclusion_id`\t\t\t\t<> '" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "'\r\n\t\t\t\t\t\tAND attr_excl.`language_id`\t\t\t\t= '" . (int) $_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["lang_id"] . "'\r\n\t\t\t\t\t\tAND attr_excl.`attribute_id`\t\t\t= '" . $_obfuscated_0D333313061D2A2D382B182D25061040213B2C33150A22_ . "'\r\n\t\t\t\t\t\tAND attr_excl.`text`\t\t\t\t\t= '" . $this->db->escape($_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["attr_value"]) . "'\r\n\t\t\t\t\t\tAND attr_excl.`exclusion_attribute_id`\t= '" . $_obfuscated_0D383621261D171C342E3F091C32251F0A011D372C3B32_ . "'\r\n\t\t\t\t\t\tAND attr_excl.`exclusion_text`\t\t\t= '" . $this->db->escape($_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["excl_attr_value"]) . "'\r\n\t\t\t\t\t)";
                }
                if($_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_) {
                    $sql .= implode(" OR ", $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_);
                    $_obfuscated_0D2F112410071021143C0601300136103835262F282A01_ = $this->db->query($sql)->num_rows;
                } else {
                    $_obfuscated_0D2F112410071021143C0601300136103835262F282A01_ = NULL;
                }
                if(!$_obfuscated_0D2F112410071021143C0601300136103835262F282A01_) {
                    $sql = "INSERT IGNORE INTO " . DB_PREFIX . "configurator_attribute_exclusions (`exclusion_id`, `language_id`, `attribute_id`, `text`, `exclusion_attribute_id`, `exclusion_text`) VALUES ";
                    $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_ = [];
                    foreach ($_obfuscated_0D2F0A342812383B04100D051F4035073709340B143222_ as $_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_) {
                        $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_[] = "('" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "', '" . (int) $_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["lang_id"] . "', '" . $_obfuscated_0D333313061D2A2D382B182D25061040213B2C33150A22_ . "', '" . $this->db->escape($_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["attr_value"]) . "', '" . $_obfuscated_0D383621261D171C342E3F091C32251F0A011D372C3B32_ . "', '" . $this->db->escape($_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["excl_attr_value"]) . "')";
                        $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_[] = "('" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "', '" . (int) $_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["lang_id"] . "', '" . $_obfuscated_0D383621261D171C342E3F091C32251F0A011D372C3B32_ . "', '" . $this->db->escape($_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["excl_attr_value"]) . "', '" . $_obfuscated_0D333313061D2A2D382B182D25061040213B2C33150A22_ . "', '" . $this->db->escape($_obfuscated_0D1702082A36222407063817091D34220C1929343B1701_["attr_value"]) . "')";
                    }
                    if($_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_) {
                        $sql .= implode(",", $_obfuscated_0D25181D5B1E16380A3D232E1729081A2A120C142B0622_);
                        $sql .= " ON DUPLICATE KEY UPDATE`language_id`= VALUES(`language_id`),`attribute_id`= VALUES(`attribute_id`),`text`= VALUES(`text`),`exclusion_attribute_id`= VALUES(`exclusion_attribute_id`),`exclusion_text`= VALUES(`exclusion_text`)";
                        $this->db->query($sql);
                    }
                    return $this->getExclusionsOfAttribute($_obfuscated_0D333313061D2A2D382B182D25061040213B2C33150A22_, $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_);
                } else {
                    return "attr_excl_exists";
                }
            } elseif($type === "category" || $type === "product") {
                $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ = (int) $params["target_id"];
                $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ = (int) $params["excl_id"];
                if($_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ != "-1") {
                    $this->db->query("\r\n\t\t\t\t\t\tINSERT IGNORE INTO " . DB_PREFIX . "configurator_" . $type . "_exclusions (`" . $type . "_id`, `exclusion_" . $type . "_id`)\r\n\t\t\t\t\t\tVALUES ('" . $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ . "', '" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "'), ('" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "', '" . $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ . "')\r\n\t\t\t\t\t");
                } else {
                    $this->db->query("\r\n\t\t\t\t\t\tINSERT IGNORE INTO " . DB_PREFIX . "configurator_" . $type . "_exclusions (`" . $type . "_id`, `exclusion_" . $type . "_id`)\r\n\t\t\t\t\t\tVALUES ('" . $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ . "', '" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "')\r\n\t\t\t\t\t");
                }
                return true;
            }
        }
        return false;
    }
    public function deleteExclusion($type, $params)
    {
        if($this->confirmProtection()) {
            if($type === "attribute") {
                return $this->db->query("\r\n\t\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_attribute_exclusions\r\n\t\t\t\t\tWHERE `exclusion_id` = '" . (int) $params["target_id"] . "' \r\n\t\t\t\t");
            }
            if($type === "category" || $type === "product") {
                $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ = (int) $params["target_id"];
                $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ = (int) $params["excl_id"];
                if($_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ != "-1") {
                    return $this->db->query("\r\n\t\t\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_" . $type . "_exclusions\r\n\t\t\t\t\t\tWHERE `" . $type . "_id` = '" . $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ . "' AND `exclusion_" . $type . "_id` = '" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "' \r\n\t\t\t\t\t\tOR `" . $type . "_id` = '" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "' AND `exclusion_" . $type . "_id` = '" . $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ . "' \r\n\t\t\t\t\t");
                }
                return $this->db->query("\r\n\t\t\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_" . $type . "_exclusions\r\n\t\t\t\t\t\tWHERE `" . $type . "_id` = '" . $_obfuscated_0D0C370E081A0E110A14221B5C0C1E18301D1102183432_ . "' AND `exclusion_" . $type . "_id` = '" . $_obfuscated_0D01393C0514361515273C2529302A1E363731282A3132_ . "' \r\n\t\t\t\t\t");
            }
        }
        return false;
    }
    public function deleteRelatedExclusions($rel_id_list, $type)
    {
        if(!$this->confirmProtection() || !$this->checkList($rel_id_list)) {
            return false;
        }
        if($type === "product") {
            return $this->db->query("\r\n\t\t\t\tDELETE p_excl.*\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_product_exclusions p_excl,\r\n\t\t\t\t(\r\n\t\t\t\t\tSELECT p.`product_id`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "product p \r\n\t\t\t\t\tJOIN " . DB_PREFIX . "product_to_category p_to_cat\r\n\t\t\t\t\t\tON p.`product_id` = p_to_cat.`product_id`,\r\n\t\t\t\t\t(\r\n\t\t\t\t\t\tSELECT cat.`category_id`\r\n\t\t\t\t\t\tFROM " . DB_PREFIX . "category cat\r\n\t\t\t\t\t\tJOIN " . DB_PREFIX . "category_path cat_path \r\n\t\t\t\t\t\t\tON cat.`category_id` = cat_path.`category_id` \r\n\t\t\t\t\t\tWHERE cat_path.`path_id` IN (" . $rel_id_list . ")\r\n\t\t\t\t\t\tGROUP BY cat.`category_id`\r\n\t\t\t\t\t\tORDER BY NULL\r\n\t\t\t\t\t) rel_cat\r\n\t\t\t\t\tWHERE p_to_cat.`category_id` = rel_cat.`category_id`\r\n\t\t\t\t) p\r\n\t\t\t\tWHERE p_excl.`product_id` = p.`product_id` \r\n\t\t\t\tOR p_excl.`exclusion_product_id` = p.`product_id`\r\n\t\t\t");
        }
        if($type === "category") {
            return $this->db->query("\r\n\t\t\t\tDELETE cat_excl.*\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_category_exclusions cat_excl,\r\n\t\t\t\t(\r\n\t\t\t\t\tSELECT cat.`category_id`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "category cat\r\n\t\t\t\t\tJOIN " . DB_PREFIX . "category_path cat_path \r\n\t\t\t\t\t\tON cat.`category_id` = cat_path.`category_id` \r\n\t\t\t\t\tWHERE cat_path.`path_id` IN (" . $rel_id_list . ")\r\n\t\t\t\t\tGROUP BY cat.`category_id`\r\n\t\t\t\t\tORDER BY NULL\r\n\t\t\t\t) rel_cat\r\n\t\t\t\tWHERE cat_excl.`category_id` = rel_cat.`category_id` \r\n\t\t\t\tOR cat_excl.`exclusion_category_id` = rel_cat.`category_id`\r\n\t\t\t");
        }
        if($type === "attribute") {
            return $this->db->query("\r\n\t\t\t\tDELETE attr_excl.*\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl,\r\n\t\t\t\t(\r\n\t\t\t\t\tSELECT attr_excl.`exclusion_id`\r\n\t\t\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl\r\n\t\t\t\t\tWHERE attr_excl.`attribute_id` IN (" . $rel_id_list . ")\r\n\t\t\t\t) rel_attr_excl\r\n\t\t\t\tWHERE attr_excl.`exclusion_id` = rel_attr_excl.`exclusion_id`\r\n\t\t\t");
        }
        return true;
    }
    public function deleteTargetRelatedExclusions($target_attr_id, $attr_id_list)
    {
        if(!$this->confirmProtection() || !$this->checkList($attr_id_list)) {
            return false;
        }
        return $this->db->query("\r\n\t\t\tDELETE attr_excl.*\r\n\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl,\r\n\t\t\t(\r\n\t\t\t\tSELECT attr_excl.`exclusion_id`\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_attribute_exclusions attr_excl\r\n\t\t\t\tWHERE attr_excl.`attribute_id` = '" . (int) $target_attr_id . "'\r\n\t\t\t\tAND attr_excl.`exclusion_attribute_id` IN (" . $attr_id_list . ")\r\n\t\t\t) rel_attr_excl\r\n\t\t\tWHERE attr_excl.`exclusion_id` = rel_attr_excl.`exclusion_id`\r\n\t\t");
    }
    public function setPreset($preset)
    {
        if(!$this->confirmProtection()) {
            return false;
        }
        $_obfuscated_0D071205360213391A05260237070104192E0E403C3B22_ = !empty($preset["id"]) ? (int) $preset["id"] : "";
        $this->db->query("\r\n\t\t\tINSERT INTO " . DB_PREFIX . "configurator_presets (`id`, `category_id`, `link_md5`, `link`, `img_path`, `status`)VALUES ('" . $_obfuscated_0D071205360213391A05260237070104192E0E403C3B22_ . "',\r\n\t\t\t\t'" . (int) $preset["category_id"] . "', \r\n\t\t\t\t'" . $this->db->escape($preset["link_md5"]) . "',\r\n\t\t\t\t'" . $this->db->escape($preset["link"]) . "', \r\n\t\t\t\t'" . $this->db->escape($preset["img_path"]) . "', \r\n\t\t\t\t'" . (int) $preset["status"] . "')ON DUPLICATE KEY UPDATE`category_id`= VALUES(`category_id`),`link_md5`= VALUES(`link_md5`),`link`= VALUES(`link`),`img_path`= VALUES(`img_path`),`status`= VALUES(`status`)");
        $_obfuscated_0D3B01025B5B3D26341A1F1C3C222A2D2429141B273C11_ = $_obfuscated_0D071205360213391A05260237070104192E0E403C3B22_ ?: $this->db->getLastId();
        $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ = "INSERT INTO " . DB_PREFIX . "configurator_preset_language (`preset_id`, `language_id`, `name`, `brief_desc`, `main_desc`, `meta_title`, `meta_desc`, `meta_keyword`) VALUES ";
        $_obfuscated_0D270A371934032E081F403517244036030C161E251501_ = [];
        foreach ($preset["lang_values"] as $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ => $_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_) {
            $_obfuscated_0D270A371934032E081F403517244036030C161E251501_[] = "('" . (int) $_obfuscated_0D3B01025B5B3D26341A1F1C3C222A2D2429141B273C11_ . "', '" . (int) $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["name"]) . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["brief_desc"]) . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["main_desc"]) . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["meta_title"]) . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["meta_desc"]) . "', '" . $this->db->escape($_obfuscated_0D021725385C2F15320C1B02192A2C342F11360B043F01_["meta_keyword"]) . "')";
        }
        if($_obfuscated_0D270A371934032E081F403517244036030C161E251501_) {
            $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ .= implode(",", $_obfuscated_0D270A371934032E081F403517244036030C161E251501_);
            $_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_ .= "ON DUPLICATE KEY UPDATE`name`= VALUES(`name`),`brief_desc`= VALUES(`brief_desc`),`main_desc`= VALUES(`main_desc`),`meta_title`= VALUES(`meta_title`),`meta_desc`= VALUES(`meta_desc`),`meta_keyword`= VALUES(`meta_keyword`)";
            $this->db->query($_obfuscated_0D23101B0D325B1B23123E2E0C1F2B0E072B5C1A152D22_);
        }
        return $_obfuscated_0D3B01025B5B3D26341A1F1C3C222A2D2429141B273C11_;
    }
    public function deletePresets($id_list)
    {
        if($this->confirmProtection() && $this->checkList($id_list)) {
            $this->db->query("\r\n\t\t\t\tDELETE prst, prst_lang, rvw\r\n\t\t\t\t\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_presets prst\r\n\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_preset_language prst_lang\r\n\t\t\t\t\tON prst.`id` = prst_lang.`preset_id`\r\n\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_reviews rvw\r\n\t\t\t\t\tON prst.`id` = rvw.`preset_id`\r\n\t\t\t\t\t\r\n\t\t\t\tWHERE prst.`id` IN (" . $id_list . ")\r\n\t\t\t");
            return $this->db->countAffected();
        }
        return false;
    }
    public function getPresetByID($preset_id)
    {
        return $this->db->query("\r\n\t\t\tSELECT prst.*, \r\n\t\t\t\tIFNULL(AVG(rvw.rating), 0.0000) AS `average_rate`,\r\n\t\t\t\tCOUNT(DISTINCT rvw.`id`) AS `reviews_num`,\r\n\t\t\t\tCOUNT(DISTINCT rvw2.`id`) AS `active_reviews_num`\r\n\t\t\t\t\r\n\t\t\tFROM " . DB_PREFIX . "configurator_presets prst\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_reviews rvw\r\n\t\t\t\tON prst.`id` = rvw.`preset_id`\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_reviews rvw2\r\n\t\t\t\tON prst.`id` = rvw2.`preset_id` AND rvw2.`status` = 1\r\n\t\t\t\t\r\n\t\t\tWHERE prst.`id` = '" . (int) $preset_id . "'\r\n\t\t\tGROUP BY prst.`id`\r\n\t\t")->row;
    }
    public function getPresetOfDuplicateLinkCode($link_code, $preset_id)
    {
        return $this->db->query("\r\n\t\t\tSELECT DISTINCT prst.`id`, prst_lang.`name` \r\n\t\t\tFROM " . DB_PREFIX . "configurator_presets prst \r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_preset_language prst_lang\r\n\t\t\t\tON prst.`id` = prst_lang.`preset_id` \r\n\t\t\t\tAND prst_lang.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\r\n\t\t\tWHERE prst.`link_md5` = '" . $this->db->escape($link_code) . "'\r\n\t\t\tAND prst.`id` <> '" . (int) $preset_id . "' \r\n\t\t")->row;
    }
    public function getPresetListByName($input)
    {
        return $this->db->query("\r\n\t\t\tSELECT prst.`id`, prst_lang.`name`\r\n\t\t\t\r\n\t\t\tFROM " . DB_PREFIX . "configurator_presets prst\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_preset_language prst_lang\r\n\t\t\t\tON prst.`id` = prst_lang.`preset_id` \r\n\t\t\t\tAND prst_lang.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\r\n\t\t\tWHERE prst_lang.`name` LIKE '%" . $this->db->escape($input["filter_name"]) . "%'\r\n\t\t\tGROUP BY prst.`id`\r\n\t\t\tORDER BY prst_lang.`name`\r\n\t\t\tLIMIT " . (int) $input["start"] . ", " . (int) $input["limit"] . "\r\n\t\t")->rows;
    }
    public function getPresetLangValues($preset_id)
    {
        $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_ = [];
        $lang = $this->db->query("\r\n\t\t\t\tSELECT *\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_preset_language \r\n\t\t\t\tWHERE `preset_id` = '" . (int) $preset_id . "'\r\n\t\t\t\tGROUP BY `language_id`\r\n\t\t\t")->rows;
        foreach ($lang as $value) {
            unset($value["preset_id"]);
            $_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_ = $value["language_id"];
            $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_[$_obfuscated_0D3C0A33011B1A1E1A350C301039031904030E0D331932_] = $value;
        }
        return $_obfuscated_0D232D390935065B0F191237313B04131E212206300D22_;
    }
    public function getPresetList($input = [])
    {
        if(isset($input["start"]) && isset($input["limit"])) {
            $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ = "LIMIT " . (int) $input["start"] . ", " . (int) $input["limit"];
        } else {
            $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ = "";
        }
        return $this->db->query("\r\n\t\t\tSELECT prst.*, \r\n\t\t\t\tprst_lang.*,\r\n\t\t\t\tIFNULL(AVG(rvw.`rating`), 0.0000) AS `average_rate`,\t\r\n\t\t\t\tCOUNT(DISTINCT rvw.`id`) AS `reviews_num`,\r\n\t\t\t\tCOUNT(DISTINCT rvw2.`id`) AS `active_reviews_num`\r\n\t\t\t\t\r\n\t\t\tFROM " . DB_PREFIX . "configurator_presets prst\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_preset_language prst_lang\r\n\t\t\t\tON prst.`id` = prst_lang.`preset_id`\r\n\t\t\t\tAND prst_lang.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_reviews rvw\r\n\t\t\t\tON prst.`id` = rvw.`preset_id`\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_reviews rvw2\r\n\t\t\t\tON prst.`id` = rvw2.`preset_id` AND rvw2.`status` = 1\r\n\r\n\t\t\tGROUP BY prst.`id`\r\n\t\t\t" . $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ . "\r\n\t\t")->rows;
    }
    public function getPresetLinkList($status = true)
    {
        if($status) {
            $_obfuscated_0D090C082F0C40090A2E2B0D17060A04251833311F0201_ = "WHERE prst.`status` = '1'";
        } else {
            $_obfuscated_0D090C082F0C40090A2E2B0D17060A04251833311F0201_ = "";
        }
        return $this->db->query("\r\n\t\t\tSELECT prst.`id`, prst_lang.`name`, prst.`link_md5`, prst.`link`, prst.`status`\r\n\t\t\t\t\r\n\t\t\tFROM " . DB_PREFIX . "configurator_presets prst \r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_preset_language prst_lang\r\n\t\t\t\tON prst.`id` = prst_lang.`preset_id` \r\n\t\t\t\tAND prst_lang.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t" . $_obfuscated_0D090C082F0C40090A2E2B0D17060A04251833311F0201_ . "\r\n\t\t\tGROUP BY prst.`id`\r\n\t\t")->rows;
    }
    public function getNumPresets()
    {
        return $this->db->query("SELECT `id` FROM " . DB_PREFIX . "configurator_presets")->num_rows;
    }
    public function setReview($review)
    {
        if(!$this->confirmProtection()) {
            return false;
        }
        $_obfuscated_0D2D2A310337092103253D130318233D2E1D2533331922_ = $review["id"] ? (int) $review["id"] : "";
        $preset_id = (int) $review["preset_id"];
        $_obfuscated_0D3E0A01153819281B153F03122E142D32133E27391811_ = (int) $review["customer_id"];
        if(!$_obfuscated_0D2D2A310337092103253D130318233D2E1D2533331922_ && $preset_id && $_obfuscated_0D3E0A01153819281B153F03122E142D32133E27391811_) {
            $_obfuscated_0D022F10101F0D0C06060A231F071E101E3C5B312C2732_ = $this->db->query("\r\n\t\t\t\tSELECT rvw.`preset_id`, rvw.`moderated`, rvw.`status`\r\n\t\t\t\t\t\r\n\t\t\t\tFROM " . DB_PREFIX . "configurator_reviews rvw\r\n\t\t\t\t\r\n\t\t\t\tWHERE rvw.`preset_id` = '" . $preset_id . "'\r\n\t\t\t\tAND rvw.`customer_id` = '" . $_obfuscated_0D3E0A01153819281B153F03122E142D32133E27391811_ . "'\r\n\t\t\t")->num_rows;
            if($_obfuscated_0D022F10101F0D0C06060A231F071E101E3C5B312C2732_) {
                return "review_exists";
            }
        }
        $this->db->query("\r\n\t\t\tINSERT INTO " . DB_PREFIX . "configurator_reviews (`id`, `preset_id`, `customer_id`, `config_lang`, `email`, `autor`, `positive`, `negative`, `review`, `rating`, `recommend`, `likes`, `dislikes`, `moderated`, `status`, `date_added`)VALUES ('" . $_obfuscated_0D2D2A310337092103253D130318233D2E1D2533331922_ . "', \r\n\t\t\t\t'" . $preset_id . "', \r\n\t\t\t\t'" . $_obfuscated_0D3E0A01153819281B153F03122E142D32133E27391811_ . "', \r\n\t\t\t\t'" . (int) $this->config->get("config_language_id") . "',\r\n\t\t\t\t'" . $this->db->escape($review["email"]) . "', \r\n\t\t\t\t'" . $this->db->escape($review["autor"]) . "',\r\n\t\t\t\t'" . $this->db->escape($review["positive"]) . "',\r\n\t\t\t\t'" . $this->db->escape($review["negative"]) . "',\r\n\t\t\t\t'" . $this->db->escape($review["review"]) . "',\r\n\t\t\t\t'" . (int) $review["rating"] . "',\r\n\t\t\t\t'" . (int) $review["recommend"] . "',\r\n\t\t\t\t'" . (int) $review["likes"] . "',\r\n\t\t\t\t'" . (int) $review["dislikes"] . "',\r\n\t\t\t\t'1',\r\n\t\t\t\t'" . (int) $review["status"] . "',\r\n\t\t\t\t'" . $this->db->escape($review["date_added"]) . "')ON DUPLICATE KEY UPDATE`preset_id`= VALUES(`preset_id`),`customer_id`= VALUES(`customer_id`),`config_lang`= VALUES(`config_lang`),`email`= VALUES(`email`),`autor`= VALUES(`autor`),`positive`= VALUES(`positive`),`negative`= VALUES(`negative`),`review`= VALUES(`review`),`rating`= VALUES(`rating`),`recommend`= VALUES(`recommend`),`likes`= VALUES(`likes`),`dislikes`= VALUES(`dislikes`),`moderated`= VALUES(`moderated`),`status`= VALUES(`status`),`date_added`= VALUES(`date_added`)");
        return $_obfuscated_0D2D2A310337092103253D130318233D2E1D2533331922_ ?: $this->db->getLastId();
    }
    public function deleteReviews($id_list)
    {
        if($this->confirmProtection() && $this->checkList($id_list)) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_reviews WHERE `id` IN (" . $id_list . ")");
            return $this->db->countAffected();
        }
        return false;
    }
    public function getReviewByID($review_id)
    {
        return $this->db->query("\r\n\t\t\tSELECT rvw.*, prst_lang.`name` AS `preset_name`\r\n\r\n\t\t\tFROM " . DB_PREFIX . "configurator_reviews rvw\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_preset_language prst_lang\r\n\t\t\t\tON rvw.`preset_id` = prst_lang.`preset_id`\r\n\t\t\t\tAND prst_lang.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\t\r\n\t\t\tWHERE rvw.`id` = '" . (int) $review_id . "'\r\n\t\t\tGROUP BY rvw.`id`\r\n\t\t")->row;
    }
    public function setModerationReviewIsTrue($review_id)
    {
        if(!$this->confirmProtection()) {
            return false;
        }
        $this->db->query("UPDATE " . DB_PREFIX . "configurator_reviews SET `moderated` = 1 WHERE `id` = '" . (int) $review_id . "'");
    }
    public function getReviewList($input = [])
    {
        if(!empty($input["preset_id"])) {
            $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ = "WHERE rvw.`preset_id` = '" . (int) $input["preset_id"] . "'";
        } else {
            $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ = "";
        }
        if(isset($input["start"]) && isset($input["limit"])) {
            $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ = "LIMIT " . (int) $input["start"] . ", " . (int) $input["limit"];
        } else {
            $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ = "";
        }
        return $this->db->query("\r\n\t\t\tSELECT rvw.*, prst_lang.`name` AS `preset_name`\r\n\r\n\t\t\tFROM " . DB_PREFIX . "configurator_reviews rvw\r\n\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_preset_language prst_lang\r\n\t\t\t\tON rvw.`preset_id` = prst_lang.`preset_id`\r\n\t\t\t\tAND prst_lang.`language_id` = '" . (int) $this->config->get("config_language_id") . "'\r\n\t\t\t\t\r\n\t\t\t" . $_obfuscated_0D5B04291A133525132126185B2E3B21250C2333301322_ . "\r\n\t\t\tGROUP BY rvw.`id`\r\n\t\t\tORDER BY rvw.`moderated`, rvw.`date_added` DESC, rvw.`id` DESC\r\n\t\t\t" . $_obfuscated_0D230E07223723271B35222A5B010C25310E062A5B3822_ . "\r\n\t\t")->rows;
    }
    public function getNumReviews($preset_id = NULL)
    {
        if(!empty($preset_id)) {
            return $this->db->query("SELECT `id` FROM " . DB_PREFIX . "configurator_reviews WHERE `preset_id` = '" . (int) $preset_id . "'")->num_rows;
        }
        return $this->db->query("SELECT `id` FROM " . DB_PREFIX . "configurator_reviews")->num_rows;
    }
    public function setDefaultMissingPresetCategories($id_list)
    {
        if(!$this->confirmProtection() || !$this->checkList($id_list)) {
            return false;
        }
        return $this->db->query("UPDATE " . DB_PREFIX . "configurator_presets SET `category_id` = '0' WHERE `category_id` NOT IN (" . $id_list . ")");
    }
    public function performToolkitOperations($operation)
    {
        if($this->confirmProtection()) {
            switch ($operation) {
                case "del_history":
                    $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_history");
                    if($_obfuscated_0D21341D321C073B171A0C5C0C5C18380A33280E103101_ = $this->db->countAffected()) {
                        $this->db->query("ALTER TABLE " . DB_PREFIX . "configurator_history AUTO_INCREMENT = 1");
                        return $_obfuscated_0D21341D321C073B171A0C5C0C5C18380A33280E103101_;
                    }
                    return "not_found";
                    break;
                case "del_history_older_month":
                    $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_history WHERE `date` < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
                    return $this->db->countAffected() ? $this->db->countAffected() : "not_found";
                    break;
                case "del_history_older_week":
                    $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_history WHERE `date` < DATE_SUB(NOW(), INTERVAL 7 DAY)");
                    return $this->db->countAffected() ? $this->db->countAffected() : "not_found";
                    break;
                case "del_history_older_day":
                    $this->db->query("DELETE FROM " . DB_PREFIX . "configurator_history WHERE `date` < DATE_SUB(NOW(), INTERVAL 1 DAY)");
                    return $this->db->countAffected() ? $this->db->countAffected() : "not_found";
                    break;
                case "del_extinct_conditions":
                    $_obfuscated_0D21341D321C073B171A0C5C0C5C18380A33280E103101_ = 0;
                    $this->db->query("\r\n\t\t\t\t\t\tDELETE cnd.*, cnd_lang.*, cnd_p.*\r\n\t\t\t\t\t\t\tFROM " . DB_PREFIX . "configurator_conditions cnd\r\n\t\t\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_condition_language cnd_lang\r\n\t\t\t\t\t\t\t\tON cnd.`id` = cnd_lang.`condition_id`\r\n\t\t\t\t\t\t\tLEFT OUTER JOIN " . DB_PREFIX . "configurator_condition_products cnd_p\r\n\t\t\t\t\t\t\t\tON cnd.`type` = 'filled_prod' AND cnd.`id` = cnd_p.`condition_id`\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\tWHERE cnd.`section_id` NOT IN (SELECT `id` FROM oc_configurator_sections)\r\n\t\t\t\t\t");
                    $_obfuscated_0D21341D321C073B171A0C5C0C5C18380A33280E103101_ += $this->db->countAffected();
                    $this->db->query("\r\n\t\t\t\t\t\tDELETE cond_p.*\r\n\t\t\t\t\t\tFROM " . DB_PREFIX . "configurator_condition_products cond_p\r\n\t\t\t\t\t\tWHERE cond_p.`product_id` NOT IN(\r\n\t\t\t\t\t\t\tSELECT p.`product_id`\r\n\t\t\t\t\t\t\tFROM " . DB_PREFIX . "product p \r\n\t\t\t\t\t\t\tJOIN " . DB_PREFIX . "product_to_category p_to_cat \r\n\t\t\t\t\t\t\tJOIN " . DB_PREFIX . "category cat \r\n\t\t\t\t\t\t\t\tON p.`product_id` = p_to_cat.`product_id` \r\n\t\t\t\t\t\t\t\tAND cat.`category_id` = p_to_cat.`category_id`,\r\n\t\t\t\t\t\t\t(\r\n\t\t\t\t\t\t\t\tSELECT cat.`category_id`\r\n\t\t\t\t\t\t\t\tFROM " . DB_PREFIX . "category cat\r\n\t\t\t\t\t\t\t\tJOIN " . DB_PREFIX . "category_path cat_path \r\n\t\t\t\t\t\t\t\t\tON cat.`category_id` = cat_path.`category_id`\r\n\t\t\t\t\t\t\t\tWHERE cat_path.`path_id` IN (\r\n\t\t\t\t\t\t\t\t\tSELECT `category_id_list`\r\n\t\t\t\t\t\t\t\t\tFROM " . DB_PREFIX . "configurator_sectionsWHERE LENGTH(`category_id_list`) >= 1)GROUP BY cat.`category_id`ORDER BY NULL) rel_catWHERE cat.`category_id` = rel_cat.`category_id` GROUP BY p.`product_id`)");
                    $_obfuscated_0D21341D321C073B171A0C5C0C5C18380A33280E103101_ += $this->db->countAffected();
                    return $_obfuscated_0D21341D321C073B171A0C5C0C5C18380A33280E103101_ ?: "not_found";
                    break;
                case "del_excl_extinct_prod":
                    $this->db->query("\t\r\n\t\t\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_product_exclusions\r\n\r\n\t\t\t\t\t\tWHERE " . DB_PREFIX . "configurator_product_exclusions.`product_id` NOT IN (\r\n\t\t\t\t\t\t\tSELECT p.`product_id` FROM " . DB_PREFIX . "product p)\r\n\t\t\t\t\t\tOR " . DB_PREFIX . "configurator_product_exclusions.`exclusion_product_id` NOT IN (\r\n\t\t\t\t\t\t\tSELECT p.`product_id` FROM " . DB_PREFIX . "product p)\r\n\t\t\t\t\t");
                    return $this->db->countAffected() ? $this->db->countAffected() : "not_found";
                    break;
                case "del_excl_extinct_ctgrs":
                    $this->db->query("\t\r\n\t\t\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_category_exclusions\r\n\r\n\t\t\t\t\t\tWHERE " . DB_PREFIX . "configurator_category_exclusions.`category_id` NOT IN (\r\n\t\t\t\t\t\t\tSELECT cat.`category_id` FROM " . DB_PREFIX . "category cat)\r\n\t\t\t\t\t\tOR " . DB_PREFIX . "configurator_category_exclusions.`exclusion_category_id` NOT IN (\r\n\t\t\t\t\t\t\tSELECT cat.`category_id` FROM " . DB_PREFIX . "category cat)\r\n\t\t\t\t\t");
                    return $this->db->countAffected() ? $this->db->countAffected() : "not_found";
                    break;
                case "del_excl_extinct_attr_var":
                    $this->db->query("\t\r\n\t\t\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_attribute_exclusions\r\n\r\n\t\t\t\t\t\tWHERE " . DB_PREFIX . "configurator_attribute_exclusions.`text` NOT IN (\r\n\t\t\t\t\t\t\tSELECT p_attr.`text` FROM " . DB_PREFIX . "product_attribute p_attr)\r\n\t\t\t\t\t\tAND " . DB_PREFIX . "configurator_attribute_exclusions.`text` <> '*'\r\n\t\t\t\t\t\tOR " . DB_PREFIX . "configurator_attribute_exclusions.`exclusion_text` NOT IN (\r\n\t\t\t\t\t\t\tSELECT p_attr.`text` FROM " . DB_PREFIX . "product_attribute p_attr)\r\n\t\t\t\t\t\tAND " . DB_PREFIX . "configurator_attribute_exclusions.`exclusion_text` <> '*'\r\n\t\t\t\t\t");
                    return $this->db->countAffected() ? $this->db->countAffected() : "not_found";
                    break;
                case "del_excl_attr_extinct_lang":
                    $this->db->query("\r\n\t\t\t\t\t\tDELETE FROM " . DB_PREFIX . "configurator_attribute_exclusions\r\n\r\n\t\t\t\t\t\tWHERE " . DB_PREFIX . "configurator_attribute_exclusions.`language_id` NOT IN (\r\n\t\t\t\t\t\t\tSELECT DISTINCT lang.`language_id`\r\n\t\t\t\t\t\t\tFROM " . DB_PREFIX . "language lang\r\n\t\t\t\t\t\t\tWHERE lang.`status` = '1')\r\n\t\t\t\t\t");
                    return $this->db->countAffected() ? $this->db->countAffected() : "not_found";
                    break;
                case "create_needing_tables":
                    $this->createModuleTables();
                    return true;
                    break;
            }
        }
        return false;
    }
    public function checkLicense($input_key = NULL)
    {
        if($this->confirmProtection($input_key)) {
            return true;
        }
         return true;
    }
    private function confirmProtection($input_key = NULL)
    {
     	 return true;
    }
}
if(version_compare(VERSION, "2.3.0.0", "<")) {
    class_alias("ModelExtensionModuleConfigurator", "ModelModuleConfigurator", false);
}

?>