<?php
/*
copyright________________________________________
@project: Configurator OC - Viewer
@email: saper1985@gmail.com
@site: createrium.ru
_________________________________________________
*/
if(version_compare(VERSION, '2.3.0.0', '<')) {
	class_alias('ModelExtensionModuleCFGViewer', 'ModelModuleCFGViewer', false);
}

class ModelExtensionModuleCFGViewer extends Model {
	
	public function getCFGPresets($input = null) {
		if(!empty($input['category_id'])) {
			$where_sql = "WHERE prst.`category_id` = " . (int)$input['category_id'] . " AND prst.`status` = 1 ";
		}else{
			$where_sql = "WHERE prst.`status` = 1 ";
		}
		
		if(isset($input['sorting'])) {
			switch($input['sorting']) {
				case 'name_asc':
					$order_sql = "ORDER BY prst_lang.`name` ASC";
					break;
				case 'name_desc':
					$order_sql = "ORDER BY prst_lang.`name` DESC";
					break;			
				case 'date_asc':
					$order_sql = "ORDER BY prst.`date_added` ASC";
					break;			
				case 'date_desc':
					$order_sql = "ORDER BY prst.`date_added` DESC";
					break;
				case 'view_asc':
					$order_sql = "ORDER BY prst.`viewed` ASC";
					break;			
				case 'view_desc':
					$order_sql = "ORDER BY prst.`viewed` DESC";
					break;
				case 'rate_asc':
					$order_sql = "ORDER BY `avg_rating` ASC";
					break;			
				case 'rate_desc':
					$order_sql = "ORDER BY `avg_rating` DESC";
					break;
				case 'rvw_asc':
					$order_sql = "ORDER BY `reviews_num` ASC";
					break;
				case 'rvw_desc':
					$order_sql = "ORDER BY `reviews_num` DESC";
					break;
				case 'def':
				default:
					$order_sql = "";
					break;
			}
		}else{
			$order_sql = "";
		}
		
		if(isset($input['start'], $input['limit']) && $input['limit']) {
			$limit_sql = "LIMIT " . (int)$input['start'] . ", " . (int)$input['limit'];
		}else{
			$limit_sql = "";
		}	

		return $this->db->query("
			SELECT 
				prst.`id`, 
				prst.`category_id`, 
				prst.`link`, 
				prst.`img_path`, 
				prst.`viewed`, 
				prst.`date_added`, 
				prst.`status`, 
				prst_lang.`language_id`, 
				prst_lang.`name`,
				prst_lang.`brief_desc`,
				IFNULL(AVG(rvw.`rating`), 0.0000) AS `avg_rating`,	
				COUNT(DISTINCT rvw.`id`) AS `reviews_num`
				
			FROM ".DB_PREFIX."configurator_presets prst
			JOIN ".DB_PREFIX."configurator_preset_language prst_lang
				ON prst.`id` = prst_lang.`preset_id`
			LEFT OUTER JOIN ".DB_PREFIX."configurator_reviews rvw
				ON prst.`id` = rvw.`preset_id` AND rvw.`status` = 1
				
			" . $where_sql . "
			AND prst_lang.`language_id` = ".(int)$this->config->get('config_language_id')."

			GROUP BY prst.`id`
			" . $order_sql . "
			" . $limit_sql . "
		")
		->rows;
	}
}
?>