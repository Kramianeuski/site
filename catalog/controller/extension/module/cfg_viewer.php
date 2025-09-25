<?php
/*
copyright________________________________________
@project: Configurator OC - Viewer
@email: saper1985@gmail.com
@site: createrium.ru
_________________________________________________
*/
if(version_compare(VERSION, '2.3.0.0', '<')) {
	class_alias('ControllerExtensionModuleCFGViewer', 'ControllerModuleCFGViewer', false);
}

class ControllerExtensionModuleCFGViewer extends Controller {
	private $path = 'extension/module/cfg_viewer';
	private $model = null;
	private $cfg_settings =  null;
	private $curr_lang_id = 0;
	private $def_img_size = 120;

	public function __construct($params) {
		parent::__construct($params);

		$this->cfg_settings = $this->config->get('configurator_settings');
		$this->curr_lang_id = $this->config->get('config_language_id');

		if(version_compare(VERSION, '2.3.0.0', '>=')) {
			$this->load->model($this->path);
			$this->model = $this->model_extension_module_cfg_viewer;
		}else{
			$this->path = 'module/cfg_viewer';
			$this->load->model($this->path);
			$this->model = $this->model_module_cfg_viewer;
		}
	}

	public function index($settings) {
		static $mod_index = 1;

		$cfg_presets = array();

		//echo "<pre>";
		//var_dump($settings);
		//echo "</pre>";

		if($this->cfg_settings && !empty($this->cfg_settings['p_ctgrs']) && !empty($settings)) {
			$preset_categories = $this->cfg_settings['p_ctgrs'];
			//$chosen_source = $settings['preset_source'];
			$chosen_source = 'all';

			$request = array(
				'start'		=> 0,
				'limit'		=> $settings['limit'],
				'sorting'	=> $settings['sorting'],
			);

			if($chosen_source === 'all') {
				$cfg_presets = $this->model->getCFGPresets($request);

			}elseif($chosen_source === 'random' && count($preset_categories) > 1) {
				unset($preset_categories[0]);
				$rand_id = array_rand($preset_categories, 1);
				$request['category_id'] = (int)$rand_id;

				$cfg_presets = $this->model->getCFGPresets($request);
			}elseif(isset($preset_categories[$chosen_source])) {
				$request['category_id'] = (int)$chosen_source;

				$cfg_presets = $this->model->getCFGPresets($request);
			}
		}

		if($cfg_presets) {
			$this->load->language($this->path);
			$this->load->model('tool/image');
			$trim_len	= $settings['desc_trim_len'];
			$img_w		= (!empty($settings['img_w']))? (int)$settings['img_w'] : $this->def_img_size;
			$img_h		= (!empty($settings['img_h']))? (int)$settings['img_h'] : $this->def_img_size;
			$no_img		= $this->model_tool_image->resize('configurator/preset-no-img.png', $img_w, $img_h);

			foreach($cfg_presets as &$preset) {
				if(!empty($preset['img_path'])) {
					$preset_img = $this->model_tool_image->resize($preset['img_path'], $img_w, $img_h);
				}else{
					$preset_img = $no_img;
				}

				if($trim_len) {
					$brief_desc = (trim($preset['brief_desc']))? substr($preset['brief_desc'], 0, $trim_len) . '...' : '';
				}else{
					$brief_desc = $preset['brief_desc'];
				}

				$preset = array(
					'id'			=> $preset['id'],
					'category_id'	=> $preset['category_id'],
					'name'			=> $preset['name'],
					'brief_desc'	=> $brief_desc,
					'link'			=> $preset['link'],
					'views_num'		=> $preset['viewed'],
					'avg_rating'	=> round($preset['avg_rating']),
					'reviews_num'	=> $preset['reviews_num'],
					'date_added'	=> date('d.m.Y H:i', strtotime($preset['date_added'])),
					'image'			=> $preset_img,
				);
			}

			$data['cfg_presets']	= $cfg_presets;

			if(!empty($settings['title'][$this->curr_lang_id])) {
				$data['module_title'] = $settings['title'][$this->curr_lang_id];
			}else{
				$data['module_title'] = '';
			}

			if($settings['view_type'] == 'list' || $settings['view_type'] == 'carousel') {
				$data['view_type'] = $settings['view_type'];
			}else{
				$data['view_type'] = 'grid';
				$data['grid_cols'] = str_replace('grid_', '', $settings['view_type']);
			}

			if($settings['title_line']) {
				$data['title_class'] = 'title_one_line';
			}else{
				$data['title_class'] = '';
			}

			$data['vsbl_title']		= $settings['vsbl_title'];
			$data['vsbl_img']		= $settings['vsbl_img'];
			$data['vsbl_desc']		= $settings['vsbl_desc'];
			$data['vsbl_rating']	= $settings['vsbl_rating'];
			$data['vsbl_views']		= $settings['vsbl_views'];
			$data['vsbl_reviews']	= $settings['vsbl_reviews'];

			if(count($cfg_presets) <= $settings['crsl_items']) {
				$data['crsl_items'] = count($cfg_presets);
			}else{
				$data['crsl_items'] = $settings['crsl_items'];
			}

			if($settings['crsl_autoplay']) {
				$data['crsl_autoplay'] = $settings['crsl_autoplay'];
			}else{
				$data['crsl_autoplay'] = 'false';
			}

			$data['crsl_speed']			= $settings['crsl_speed'];
			$data['crsl_nav']			= $settings['crsl_nav'];
			$data['crsl_pagination']	= $settings['crsl_pagination'];

			$data['is_cfg_page'] = (strripos($this->request->get['route'], 'module/configurator', -1) !== false);
			$data['mod_index']	= $mod_index++;
			$config_tpl	= $this->config->get('config_template');

			if(version_compare(VERSION, '3.0.0.0', '>=')) {
				$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
				$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
			}else{
				$this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/owl.carousel.css');
				$this->document->addScript('catalog/view/javascript/jquery/owl-carousel/owl.carousel.min.js');
			}

			if(file_exists('catalog/view/theme/'. $config_tpl .'/stylesheet/configurator/cfg_viewer.css')) {
				$this->document->addStyle('catalog/view/theme/'. $config_tpl .'/stylesheet/configurator/cfg_viewer.css', 'stylesheet', 'screen');
			}else{
				$this->document->addStyle('catalog/view/theme/default/stylesheet/configurator/cfg_viewer.css', 'stylesheet', 'screen');
			}

			if(version_compare(VERSION, '2.2.0.0', '>=')) {
				$view_html = $this->load->view($this->path, $data);
			}else{
				$tpl_path = $config_tpl . '/template/' . $this->path . '.tpl';
				$tpl_path = (file_exists(DIR_TEMPLATE . $tpl_path))? $tpl_path : 'default/template/' . $this->path . '.tpl';
				$view_html = $this->load->view($tpl_path, $data);
			}

			return $view_html;
		}
	}
}