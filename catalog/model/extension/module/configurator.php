<?php
/*
copyright________________________________________
@project: Configurator OC
@email: saper1985@gmail.com
@site: createrium.ru
_________________________________________________
*/
if(version_compare(VERSION, '2.3.0.0', '<')) {
	class_alias('ModelExtensionModuleConfigurator', 'ModelModuleConfigurator', false);
}

class ModelExtensionModuleConfigurator extends Model {

	private function checkList($list) {
		return (is_string($list) || is_numeric($list))? preg_match('/^(?:\-?\d\,?\s?)+$/', $list) : false;
	}


	public function getPresetThisPage() {
		$decoded_link = htmlspecialchars_decode($this->request->server['REQUEST_URI'], ENT_QUOTES);
		preg_match('/s\d+\=\d+q.+$/', $decoded_link, $path_matches);
		$cfg_path = array_shift($path_matches);
		$link_md5 = ($cfg_path)? md5($cfg_path) : '';

		if($link_md5) {
			return $this->db->query("
				SELECT prst.*,
					prst_lang.*,
					IFNULL(AVG(rvw.rating), 0.0000) AS `average_rate`,
					COUNT(DISTINCT rvw.`id`) AS `reviews_num`,
					CONCAT(
						IFNULL(SUM(rvw.`rating` = '5'), 0), '-',
						IFNULL(SUM(rvw.`rating` = '4'), 0), '-',
						IFNULL(SUM(rvw.`rating` = '3'), 0), '-',
						IFNULL(SUM(rvw.`rating` = '2'), 0), '-',
						IFNULL(SUM(rvw.`rating` = '1'), 0)
					) AS 'rating_details'

				FROM ".DB_PREFIX."configurator_presets prst
				LEFT OUTER JOIN ".DB_PREFIX."configurator_preset_language prst_lang
					ON prst.`id` = prst_lang.`preset_id`
				LEFT OUTER JOIN ".DB_PREFIX."configurator_reviews rvw
					ON prst.`id` = rvw.`preset_id` AND rvw.`moderated` = '1' AND rvw.`status` = '1'

				WHERE prst.`status` = '1'
				AND prst.`link_md5` = '".$this->db->escape($link_md5)."'
				AND prst_lang.`language_id` = '".(int)$this->config->get('config_language_id')."'
				GROUP BY prst.`id`
			")
			->row;
		}else{
			return array();
		}
	}


	public function getPresetData($value, $cols = array(), $language_id = null) {
		if(is_numeric($value) && ctype_digit((string)$value)) {
			$target_sql = "AND prst.`id` = '".(int)$value."'";
		}elseif(is_string($value) && mb_strlen($value) == 32) {
			$target_sql = "AND prst.`link_md5` = '".$this->db->escape($value)."'";
		}else{
			return false;
		}

		if($language_id) {
			$language_sql = "AND prst_lang.`language_id` = '".(int)$language_id."'";
		}else{
			$language_sql = "AND prst_lang.`language_id` = '".(int)$this->config->get('config_language_id')."'";
		}

		$columns = array();
		$col_names = array(
			'id'			=> 'prst.`id`',
			'category_id'	=> 'prst.`category_id`',
			'link_md5'		=> 'prst.`link_md5`',
			'link'			=> 'prst.`link`',
			'img_path'		=> 'prst.`img_path`',
			'viewed'		=> 'prst.`viewed`',
			'status'		=> 'prst.`status`',
			'date_added'	=> 'prst.`date_added`',
			'name'			=> 'prst_lang.`name`',
			'brief_desc'	=> 'prst_lang.`brief_desc`',
			'main_desc'		=> 'prst_lang.`main_desc`',
			'meta_title'	=> 'prst_lang.`meta_title`',
			'meta_desc'		=> 'prst_lang.`meta_desc`',
			'meta_keyword'	=> 'prst_lang.`meta_keyword`',
			'tag'			=> 'prst_lang.`tag`',
		);

		if(!empty($cols) && is_array($cols)) {
			foreach($cols as $col) {
				if(isset($col_names[$col])) $columns[] = $col_names[$col];
			}
		}

		$columns_sql = ($columns)? implode(',', $columns) : "prst.*, prst_lang.*";

		return $this->db->query("
			SELECT DISTINCT ".$columns_sql."
			FROM ".DB_PREFIX."configurator_presets prst
			LEFT OUTER JOIN ".DB_PREFIX."configurator_preset_language prst_lang
				ON prst.`id` = prst_lang.`preset_id`
			WHERE prst.`status` = '1'
			".$target_sql."
			".$language_sql."
		")
		->row;
	}


	public function updateViewedOfPreset($preset_id) {
		$this->db->query("UPDATE ".DB_PREFIX."configurator_presets SET `viewed` = (`viewed` + 1) WHERE `id` = '".(int)$preset_id."'");
	}


	public function getReviewsOfPreset($preset_id, $start = 0, $limit = 15) {
		return $this->db->query("
			SELECT *
			FROM ".DB_PREFIX."configurator_reviews rvw

			WHERE rvw.`preset_id` = '".(int)$preset_id."'
			AND rvw.`moderated` = '1'
			AND rvw.`status` = '1'
			GROUP BY rvw.`id`
			ORDER BY rvw.`date_added`
			LIMIT ".(int)$start.", ".(int)$limit."
		")
		->rows;
	}


	public function setReviewOfPreset($review) {
		$user_reviews = $this->db->query("
			SELECT rvw.`preset_id`, rvw.`moderated`, rvw.`status`

			FROM ".DB_PREFIX."configurator_reviews rvw

			WHERE rvw.`preset_id` = '".(int)$review['preset_id']."'
			AND rvw.`customer_id` = '".(int)$review['customer_id']."'
		")
		->rows;

		foreach($user_reviews as $user_review) {
			if($user_review['moderated'] && $user_review['status']) {
				return 'review_exists';
			}elseif(!$user_review['moderated'] && !$user_review['status']){
				return 'review_moderation';
			}
		}

		$this->db->query("
			INSERT INTO ".DB_PREFIX."configurator_reviews (
				`preset_id`, `customer_id`, `config_lang`, `email`, `autor`, `positive`, `negative`, `review`, `rating`, `recommend`
			)
			VALUES (
				'".(int)$review['preset_id']."',
				'".(int)$review['customer_id']."',
				'".(int)$this->config->get('config_language_id')."',
				'".$this->db->escape($review['email'])."',
				'".$this->db->escape($review['autor'])."',
				'".$this->db->escape($review['positive'])."',
				'".$this->db->escape($review['negative'])."',
				'".$this->db->escape($review['review'])."',
				'".(int)$review['rating']."',
				'".(int)$review['recommend']."'
			)
		");

		return $this->db->getLastId();
	}


	public function setVoteForReviewOfPreset($review_id, $vote) {
		$this->db->query("
			UPDATE ".DB_PREFIX."configurator_reviews
			SET " . (((int)$vote)? "`likes` = (`likes` + 1)" : "`dislikes` = (`dislikes` + 1)") . "
			WHERE `id` = '".(int)$review_id."'
		");
	}


	public function getSectionList() {
		return $this->db
			->query("
				SELECT s.* , s_lang.`name`, s_lang.`description`
				FROM ".DB_PREFIX."configurator_sections s
				LEFT JOIN ".DB_PREFIX."configurator_section_language s_lang
					ON s.`id` = s_lang.`section_id`
				WHERE s.`status` = '1'
				AND s_lang.`language_id` = ".(int)$this->config->get('config_language_id')."
				ORDER BY s.`group_id`
			")
			->rows;
	}


	public function getCategoryTree($category_list) {
		$category_tree = array();

		if($category_list && $this->checkList($category_list)) {
			$query = $this->db->query("
				SELECT cat.`category_id`,
					(if(cat.`parent_id` = 0,
						cat_desc.`name`,
						CONCAT(
							(SELECT GROUP_CONCAT(cat_desc2.`name` ORDER BY cat_path2.`level` SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;')
							FROM ".DB_PREFIX."category_path cat_path2
							JOIN ".DB_PREFIX."category_description cat_desc2
								ON cat_path2.`path_id` = cat_desc2.`category_id`
								AND cat_path2.`category_id` != cat_path2.`path_id`
							WHERE cat_path2.`category_id` = cat.`category_id`
							AND cat_desc2.`language_id` = cat_desc.`language_id`
							GROUP BY cat_path2.`category_id`
							ORDER BY NULL),
							'&nbsp;&nbsp;&gt;&nbsp;&nbsp;',
							cat_desc.`name`
						)
					)) AS 'path_n_name'
				FROM ".DB_PREFIX."category cat
				JOIN ".DB_PREFIX."category_description cat_desc
				JOIN ".DB_PREFIX."category_path cat_path
					ON cat.`category_id` = cat_desc.`category_id`
					AND cat.`category_id` = cat_path.`category_id`
				WHERE cat.`status` = '1'
				AND cat_path.`path_id` IN (".$category_list.")
				AND cat_desc.`language_id` = '".(int)$this->config->get('config_language_id')."'
				GROUP BY cat.`category_id`
				ORDER BY `path_n_name`
			");

			foreach($query->rows as $category) {
				$category_tree[$category['category_id']] = $category['path_n_name'];
			}
		}

		return $category_tree;
	}


	public function getProductsOfSection($input) {
		//$input['trg_ctgr_id_list'];
		//$input['added_prod_id_list'];
		//$input['filter'];
		//$input['sorting'];
		//$input['start'];
		//$input['limit'];
		//$input['qty_choice'];
		//$input['desc_trim_len'];

		if(!$this->checkList($input['trg_ctgr_id_list'])) return array();

		$query = $this->db->query("
			SELECT cat.`category_id`
			FROM ".DB_PREFIX."category cat
			JOIN ".DB_PREFIX."category_path cat_path
				ON cat.`category_id` = cat_path.`category_id`
			WHERE cat_path.`path_id` IN (
				SELECT DISTINCT (if(cat_excl.`exclusion_category_id` = -1, cat_excl.`category_id`, cat_excl.`exclusion_category_id`)) AS 'category_id'
				FROM ".DB_PREFIX."category cat
				JOIN ".DB_PREFIX."category_path cat_path
					ON cat.`category_id` = cat_path.`category_id`
				JOIN ".DB_PREFIX."product_to_category p_to_cat
					ON cat.`category_id` = p_to_cat.`category_id`,
				".DB_PREFIX."configurator_category_exclusions cat_excl
				WHERE cat_excl.`exclusion_category_id` = '-1'
				".(($this->checkList($input['added_prod_id_list']))?
				"OR p_to_cat.`product_id` IN (".$input['added_prod_id_list'].") AND cat_excl.`category_id` = cat_path.`path_id`" : "")."
			)
			AND cat.`status` = '1'
			GROUP BY cat.`category_id`
			ORDER BY NULL
		");

		if($query->num_rows) {
			$excl_ctgrs_id = implode(',', array_column($query->rows, 'category_id'));
			$excl_ctgrs_sql = "AND cat.`category_id` NOT IN (".$excl_ctgrs_id.")";
		}else{
			$excl_ctgrs_sql = "";
		}

		$query = $this->db->query(
			(($this->checkList($input['added_prod_id_list']))?
				"SELECT DISTINCT p_attr2.`product_id`
					FROM ".DB_PREFIX."configurator_attribute_exclusions attr_excl
					JOIN ".DB_PREFIX."product_attribute p_attr
						ON p_attr.`attribute_id` = attr_excl.`attribute_id`
						AND p_attr.`language_id` = attr_excl.`language_id`
						AND (p_attr.`text` = attr_excl.`text` COLLATE utf8_bin OR '*' = attr_excl.`text`)
					JOIN ".DB_PREFIX."product_attribute p_attr2
						ON p_attr2.`attribute_id` = attr_excl.`exclusion_attribute_id`
						AND p_attr2.`language_id` = attr_excl.`language_id`
						AND (p_attr2.`text` = attr_excl.`exclusion_text` COLLATE utf8_bin OR '*' = attr_excl.`exclusion_text`)

					WHERE p_attr.`product_id` IN (".$input['added_prod_id_list'].")
					AND attr_excl.`language_id` = '".(int)$this->config->get('config_language_id')."'

				UNION

				SELECT DISTINCT (if(p_excl.`exclusion_product_id` = -1, p_excl.`product_id`, p_excl.`exclusion_product_id`)) AS 'product_id'
				FROM ".DB_PREFIX."configurator_product_exclusions p_excl
				WHERE p_excl.`exclusion_product_id` = '-1'
				OR p_excl.`product_id` IN (".$input['added_prod_id_list'].")"
			:
				"SELECT DISTINCT (if(p_excl.`exclusion_product_id` = -1, p_excl.`product_id`, p_excl.`exclusion_product_id`)) AS 'product_id'
				FROM ".DB_PREFIX."configurator_product_exclusions p_excl
				WHERE p_excl.`exclusion_product_id` = '-1'"
			)
		);

		if($query->num_rows) {
			$excl_prods_id = implode(',', array_column($query->rows, 'product_id'));
			$excl_prods_sql = "AND p.`product_id` NOT IN (".$excl_prods_id.")";
		}else{
			$excl_prods_sql = "";
		}

		$valid_min_sql = (!$input['qty_choice'])? "AND p.`minimum` <= '1'" : "";

		if(empty($input['desc_trim_len'])) {
			$desc_sql = "p_desc.`description`";
		}else{
			$desc_sql = "SUBSTRING(p_desc.`description`, 1, ".(int)($input['desc_trim_len'] + 12).") AS `description`";
		}

		if($input['filter']) {
			$filter_sql = "AND p_desc.`name` LIKE '%".$this->db->escape($input['filter'])."%'";
		}else{
			$filter_sql = "";
		}

		switch($input['sorting']) {
			case 'name_asc':
				$order_by_sql = "ORDER BY p_desc.`name` ASC";
				break;
			case 'name_desc':
				$order_by_sql = "ORDER BY p_desc.`name` DESC";
				break;
			case 'price_asc':
				$order_by_sql = "ORDER BY p.`price` ASC";
				break;
			case 'price_desc':
				$order_by_sql = "ORDER BY p.`price` DESC";
				break;
			case 'default':
			default:
				$order_by_sql = "ORDER BY cat.`category_id` ASC";
				break;
		}

		$this->db->query("SET SQL_BIG_SELECTS = 1");

		return $this->db->query("
			SELECT p.`product_id`,
				p_desc.`name`,
				p.`quantity`,
				p.`minimum`,
				p.`sku`,
				p.`image`,
				p.`price`,
				(SELECT GROUP_CONCAT(CONCAT(pd.`quantity`, ':'), pd.`price`) FROM " . DB_PREFIX . "product_discount pd
					WHERE pd.`product_id` = p.`product_id`
					AND pd.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "'
					AND ((pd.`date_start` = '0000-00-00' OR pd.`date_start` < NOW())
					AND (pd.`date_end` = '0000-00-00' OR pd.`date_end` > NOW()))
					ORDER BY pd.`quantity` ASC) AS `discount`,
				(SELECT ps.`price` FROM " . DB_PREFIX . "product_special ps
					WHERE ps.`product_id` = p.`product_id`
					AND ps.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "'
					AND ((ps.`date_start` = '0000-00-00' OR ps.`date_start` < NOW())
					AND (ps.`date_end` = '0000-00-00' OR ps.`date_end` > NOW()))
					ORDER BY ps.`priority` ASC, ps.`price` ASC LIMIT 1) AS `special`,
				p.`tax_class_id`,
				".$desc_sql.",
				cat.`category_id`
			FROM ".DB_PREFIX."product p
			JOIN ".DB_PREFIX."product_description p_desc
			JOIN ".DB_PREFIX."product_to_category p_to_cat
			JOIN ".DB_PREFIX."category cat
				ON p.`product_id` = p_desc.`product_id`
				AND p.`product_id` = p_to_cat.`product_id`
				AND cat.`category_id` = p_to_cat.`category_id`,
			(
				SELECT cat.`category_id`
				FROM ".DB_PREFIX."category cat
				JOIN ".DB_PREFIX."category_path cat_path
					ON cat.`category_id` = cat_path.`category_id`
				WHERE cat.`status` = '1'
				AND cat_path.`path_id` IN (".$input['trg_ctgr_id_list'].")
				GROUP BY cat.`category_id`
				ORDER BY NULL
			) rel_cat

			WHERE cat.`category_id` = rel_cat.`category_id`
			".$excl_ctgrs_sql."
			".$excl_prods_sql."
			".$filter_sql."
			AND p.`status` = '1'
			AND p_desc.`language_id` = '".(int)$this->config->get('config_language_id')."'
			GROUP BY p.`product_id`
			".$order_by_sql."
			LIMIT ".(int)$input['start'].", ".(int)$input['limit']."
		")
		->rows;

			//AND p.quantity > '0'
			//AND p.quantity >= p.minimum
			//".$valid_min_sql."
	}


	public function getProductOfConfiguration($input) {
		//$input['product_id'];
		//$input['quantity'];
		//$input['category_id_list'];
		//$input['added_prod_id_list'];

		if(!$input['product_id'] || !$this->checkList($input['category_id_list'])) return array();

		$query = $this->db->query("
			SELECT cat.`category_id`
			FROM ".DB_PREFIX."category cat
			JOIN ".DB_PREFIX."category_path cat_path
				ON cat.`category_id` = cat_path.`category_id`
			WHERE cat_path.`path_id` IN (
				SELECT DISTINCT (if(cat_excl.`exclusion_category_id` = -1, cat_excl.`category_id`, cat_excl.`exclusion_category_id`)) AS 'category_id'
				FROM ".DB_PREFIX."category cat
				JOIN ".DB_PREFIX."category_path cat_path
					ON cat.`category_id` = cat_path.`category_id`
				JOIN ".DB_PREFIX."product_to_category p_to_cat
					ON cat.`category_id` = p_to_cat.`category_id`,
				".DB_PREFIX."configurator_category_exclusions cat_excl
				WHERE cat_excl.`exclusion_category_id` = '-1'
				".(($this->checkList($input['added_prod_id_list']))?
				"OR p_to_cat.`product_id` IN (".$input['added_prod_id_list'].") AND cat_excl.`category_id` = cat_path.`path_id`" : "")."
			)
			AND cat.`status` = '1'
			GROUP BY cat.`category_id`
			ORDER BY NULL
		");

		if($query->num_rows) {
			$excl_ctgrs_id = implode(',', array_column($query->rows, 'category_id'));
			$excl_ctgrs_sql = "AND cat.`category_id` NOT IN (".$excl_ctgrs_id.")";
		}else{
			$excl_ctgrs_sql = "";
		}

		$query = $this->db->query(
			(($this->checkList($input['added_prod_id_list']))?
				"SELECT DISTINCT p_attr2.`product_id`
					FROM ".DB_PREFIX."configurator_attribute_exclusions attr_excl
					JOIN ".DB_PREFIX."product_attribute p_attr
						ON p_attr.`attribute_id` = attr_excl.`attribute_id`
						AND p_attr.`language_id` = attr_excl.`language_id`
						AND (p_attr.`text` = attr_excl.`text` COLLATE utf8_bin OR '*' = attr_excl.`text`)
					JOIN ".DB_PREFIX."product_attribute p_attr2
						ON p_attr2.`attribute_id` = attr_excl.`exclusion_attribute_id`
						AND p_attr2.`language_id` = attr_excl.`language_id`
						AND (p_attr2.`text` = attr_excl.`exclusion_text` COLLATE utf8_bin OR '*' = attr_excl.`exclusion_text`)

					WHERE p_attr.`product_id` IN (".$input['added_prod_id_list'].")
					AND attr_excl.`language_id` = '".(int)$this->config->get('config_language_id')."'

				UNION

				SELECT DISTINCT (if(p_excl.`exclusion_product_id` = -1, p_excl.`product_id`, p_excl.`exclusion_product_id`)) AS 'product_id'
				FROM ".DB_PREFIX."configurator_product_exclusions p_excl
				WHERE p_excl.`exclusion_product_id` = '-1'
				OR p_excl.`product_id` IN (".$input['added_prod_id_list'].")"
			:
				"SELECT DISTINCT (if(p_excl.`exclusion_product_id` = -1, p_excl.`product_id`, p_excl.`exclusion_product_id`)) AS 'product_id'
				FROM ".DB_PREFIX."configurator_product_exclusions p_excl
				WHERE p_excl.`exclusion_product_id` = '-1'"
			)
		);

		if($query->num_rows) {
			$excl_prods_id = implode(',', array_column($query->rows, 'product_id'));
			$excl_prods_sql = "AND p.`product_id` NOT IN (".$excl_prods_id.")";
		}else{
			$excl_prods_sql = "";
		}

		$this->db->query("SET SQL_BIG_SELECTS = 1");

		return $this->db->query("
			SELECT p.`product_id`,
				p_desc.`name`,
				p.`quantity`,
				p.`minimum`,
				p.`sku`,
				p.`image`,
				p.`price`,
				(SELECT GROUP_CONCAT(CONCAT(pd.`quantity`, ':'), pd.`price`) FROM " . DB_PREFIX . "product_discount pd
					WHERE pd.`product_id` = p.`product_id`
					AND pd.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "'
					AND ((pd.`date_start` = '0000-00-00' OR pd.`date_start` < NOW())
					AND (pd.`date_end` = '0000-00-00' OR pd.`date_end` > NOW()))
					ORDER BY pd.`quantity` ASC) AS `discount`,
				(SELECT ps.`price` FROM " . DB_PREFIX . "product_special ps
					WHERE ps.`product_id` = p.`product_id`
					AND ps.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "'
					AND ((ps.`date_start` = '0000-00-00' OR ps.`date_start` < NOW())
					AND (ps.`date_end` = '0000-00-00' OR ps.`date_end` > NOW()))
					ORDER BY ps.`priority` ASC, ps.`price` ASC LIMIT 1) AS `special`,
				p.`tax_class_id`,
				cat.`category_id`
			FROM ".DB_PREFIX."product p
			JOIN ".DB_PREFIX."product_description p_desc
			JOIN ".DB_PREFIX."product_to_category p_to_cat
			JOIN ".DB_PREFIX."category cat
				ON p.`product_id` = p_desc.`product_id`
				AND p.`product_id` = p_to_cat.`product_id`
				AND cat.`category_id` = p_to_cat.`category_id`,
			(
				SELECT cat.`category_id`
				FROM ".DB_PREFIX."category cat
				JOIN ".DB_PREFIX."category_path cat_path
					ON cat.`category_id` = cat_path.`category_id`
				WHERE cat.`status` = '1'
				AND cat_path.`path_id` IN (".$input['category_id_list'].")
				GROUP BY cat.`category_id`
				ORDER BY NULL
			) rel_cat

			WHERE cat.`category_id` = rel_cat.`category_id`
			".$excl_ctgrs_sql."
			".$excl_prods_sql."
			AND p.`status` = '1'
			AND p.`product_id` = ".$input['product_id']."
			AND p_desc.`language_id` = '".(int)$this->config->get('config_language_id')."'
			GROUP BY p.`product_id`
		")
		->row;

			//AND p.`quantity` > '0'
			//AND p.`quantity` >= p.`minimum`
			//AND p.`minimum` <= ".$input['quantity']."
			//AND p.`quantity` >= ".$input['quantity']."
	}


	public function getSectionConditions($section_id = null) {
		$where_sql = ($section_id)? "WHERE cnd.`section_id` = '".(int)$section_id."'" : "";

		return $this->db
			->query("
				SELECT cnd.*, cnd_lang.`help_text`
				FROM ".DB_PREFIX."configurator_conditions cnd
				LEFT OUTER JOIN ".DB_PREFIX."configurator_condition_language cnd_lang
					ON cnd.`id` = cnd_lang.`condition_id`
					AND cnd_lang.`language_id` = '".(int)$this->config->get('config_language_id')."'
				".$where_sql."
				GROUP BY cnd.`id`
			")
			->rows;
	}


	public function getConditionProducts($condition_id = null) {
		$where_sql = ($condition_id)? "WHERE `condition_id` = '".(int)$condition_id."'" : "";

		return $this->db
			->query("SELECT * FROM ".DB_PREFIX."configurator_condition_products ".$where_sql." GROUP BY `product_id`")
			->rows;
	}


	public function addToHistory($data) {
		return $this->db->query("
			INSERT INTO ".DB_PREFIX."configurator_history (`type`, `customer_id`, `preset_id`, `review_id`, `client_ip`, `text`, `link`, `data`)
			VALUES (
				'".$this->db->escape($data['type'])."',
				'".(int)$data['customer_id']."',
				'".(int)$data['preset_id']."',
				'".(int)$data['review_id']."',
				'".$this->db->escape($data['client_ip'])."',
				'".$this->db->escape($data['text'])."',
				'".$this->db->escape($data['link'])."',
				'".$this->db->escape($data['data'])."'
			)
		");
	}

	public function addConfiguratorProduct($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', status = '" . (int)$data['status'] . "', date_available = NOW(), date_added = NOW(), date_modified = NOW()");

		$product_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', name = '" . $this->db->escape($data['name']). "', language_id = '" . (int)$data['language_id'] . "'") ;

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	
		if (isset($data) and  $data['kits'] != '') {
			$price = 0;
			$price_kit = 0;
			$type_extra_charge = 0;
			$extra_charge = 0;
		
			foreach ($data['kits'] as $kit) {
		
				if (isset($kit['kit_product_option_value_id'])) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_kit SET product_id = '" . (int)$product_id . "', kit_product = '" . $this->db->escape($kit['kit_product_id']) . "' , kit_quantity = '" . $this->db->escape($kit['kit_quantity']) . "', kit_product_option_value_id = '" . $this->db->escape($kit['kit_product_option_value_id']) . "', sort_order = '0'");
				 }else{
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_kit SET product_id = '" . (int)$product_id . "', kit_product = '" . $this->db->escape($kit['kit_product_id']) . "' , kit_quantity = '" . $this->db->escape($kit['kit_quantity']) . "', kit_product_option_value_id = '0', sort_order = '0'");
				}
		
				if (isset($kit['kit_product_option_value_id'])) {
					$kit_product_option_value_id = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$kit['kit_product_id']. "' AND pov.product_option_value_id = '" . (int)$kit['kit_product_option_value_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
					if ($kit_product_option_value_id->num_rows) {
						$price_kit = $price_kit + ($kit_product_option_value_id->row['price'] * $kit['kit_quantity']);
					}
				}
			}
		
			if($type_extra_charge == 1){
				$price = $price_kit + $extra_charge;
			}else if ($type_extra_charge == 2){
				$price = $price_kit + (($price_kit/100) * $extra_charge);
			}else{
				$price = $price_kit;
			}
		
			$this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . (float)$price . "', price_kit = '" . (float)$price_kit . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		$this->cache->delete('product');
		return $product_id;
	}

}
?>