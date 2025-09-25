<?php
/*
copyright________________________________________
@project: Configurator OC
@email: saper1985@gmail.com
@site: createrium.ru
_________________________________________________
*/
if(version_compare(VERSION, '2.3.0.0', '<')) {
	class_alias('ControllerExtensionModuleConfigurator', 'ControllerModuleConfigurator', false);
}

class ControllerExtensionModuleConfigurator extends Controller {
	private $path = 'extension/module/configurator';
	private $SSL = true;
	private $model = null;
	private $settings = null;
	private $curr_lang_id = 0;
	private $history_lang = array();
	private $added_prod_id_arr = array();
	private $unav_prod_id_arr = array();
	private $def_img_size = 120;
	private $ajax_upd = false;

	private $count_required = 0;
	private $count_hide = 0;
    private $count_id_main_section = 0;
	private $count_id_dop_sections = 0;
	private $count_progress = 0;
	private $build_level = 0;
	private $total_cost = 0;

	private $meta_title = '';
	private $meta_desc = '';
	private $meta_keyword = '';
	private $main_title = '';
	private $main_desc = '';

	public function __construct($params) {
		parent::__construct($params);

		$this->settings = $this->config->get('configurator_settings');
		$this->curr_lang_id = $this->config->get('config_language_id');
		$this->ajax_upd = isset($_GET['ajax_upd']);

		if(version_compare(VERSION, '2.3.0.0', '>=')) {
			$this->load->model($this->path);
			$this->model = $this->model_extension_module_configurator;
		}else{
			$this->path = 'module/configurator';
			$this->load->model($this->path);
			$this->model = $this->model_module_configurator;

			if(version_compare(VERSION, '2.2.0.0', '<')) {
				$this->SSL = 'SSL';
			}
		}

		if(isset($this->settings['history_text'])) {
			$this->history_lang = $this->settings['history_text'];
		}else{
			$err_text = 'Error: The history language is not set';
			$this->history_lang = array(
				'rvw_added' 		=> $err_text,
				'preset_viewed' 	=> $err_text,
				'unav_prod_preset' 	=> $err_text,
				'unav_prod_page'	=> $err_text,
				'added_to_cart' 	=> $err_text,
				'added_to_cart_err'	=> $err_text,
			);
		}
	}


	public function index() {
		$this->load->language($this->path);
		$this->load->model('tool/image');

		//Preset link test
		if(isset($_POST['check_preset_link'])) {
			$sct_data = $this->getSectionsData();
			$response = ($sct_data['added_products'])? ((!$this->unav_prod_id_arr)? 'valid' : 'prod_unav') : 'prod_missing';

			return $this->response->setOutput($response);
		}

		//Common data
		$data = $this->language->get('lang_data');
		$data['ext_route'] = 'index.php?route=' . $this->path;
		$data['ext_seo_route'] = $ext_seo_route = $this->url->link($this->path, '', $this->SSL);
		$data['start_qry_smb'] = ($this->config->get('config_seo_url'))? '?' : '&';

		if($this->customer->isLogged()) {
			$data['user'] = array(
				'id'			=> $this->customer->getId(),
				'first_name'	=> $this->customer->getFirstName(),
				'last_name'		=> $this->customer->getLastName(),
				'email'			=> $this->customer->getEmail(),
			);
		}else{
			$data['user'] = array();
		}

		$config_currency = $this->config->get('config_currency');
		$data['currency'] = array(
			'rate'			=> $this->currency->getValue($config_currency),
			'd_place'		=> $this->currency->getDecimalPlace($config_currency),
			'smb_left'		=> $this->currency->getSymbolLeft($config_currency),
			'smb_right'		=> $this->currency->getSymbolRight($config_currency),
		);

		$data['txt_cost']		= '';
		$data['prt_title']		= '';
		$data['prt_tbl_title']	= '';
		$data['prt_qr_code']	= '';
		$data['prt_contcs']		= '';
		$data['prt_text']		= '';
		$data['prt_notice']		= '';

		if(isset($this->settings['lang_values'][$this->curr_lang_id])) {
			$lang_values = $this->settings['lang_values'][$this->curr_lang_id];

			if(isset($lang_values['meta_title'])) {
				$this->meta_title = $lang_values['meta_title'];
			}

			if(isset($lang_values['meta_desc'])) {
				$this->meta_desc = $lang_values['meta_desc'];
			}

			if(isset($lang_values['meta_keyword'])) {
				$this->meta_keyword = $lang_values['meta_keyword'];
			}

			if(isset($lang_values['main_title'])) {
				$this->main_title = $lang_values['main_title'];
			}

			if(!empty($lang_values['main_desc'])) {
				$this->main_desc = html_entity_decode($lang_values['main_desc'], ENT_QUOTES);
			}

			if(isset($lang_values['txt_cost'])) {
				$data['txt_cost'] = $lang_values['txt_cost'];
			}

			if(!empty($lang_values['prt_title'])) {
				$data['prt_title'] = html_entity_decode($lang_values['prt_title'], ENT_QUOTES);
			}

			if(!empty($lang_values['prt_tbl_title'])) {
				$data['prt_tbl_title'] = html_entity_decode($lang_values['prt_tbl_title'], ENT_QUOTES);
			}

			if(!empty($lang_values['prt_qr_code'])) {
				$data['prt_qr_code'] = urlencode(strip_tags(htmlspecialchars_decode($lang_values['prt_qr_code'], ENT_QUOTES)));
			}

			if(!empty($lang_values['prt_contcs'])) {
				$data['prt_contcs'] = str_replace(array("\r\n", "\r", "\n"), '', html_entity_decode($lang_values['prt_contcs'], ENT_QUOTES));
			}

			if(!empty($lang_values['prt_text'])) {
				$data['prt_text'] = str_replace(array("\r\n", "\r", "\n"), '', html_entity_decode($lang_values['prt_text'], ENT_QUOTES));
			}

			if(!empty($lang_values['prt_notice'])) {
				$data['prt_notice'] = str_replace(array("\r\n", "\r", "\n"), '', html_entity_decode($lang_values['prt_notice'], ENT_QUOTES));
			}
		}

		if(!empty($this->settings['prt_logo'])) {
			$data['prt_logo'] = $this->model_tool_image->resize($this->settings['prt_logo'], 500, 282);
		}else{
			$data['prt_logo'] = '';
		}

		if(isset($this->settings['checkout_url'])) {
			$data['checkout_url'] = $this->settings['checkout_url'];
		}else{
			$data['checkout_url'] = '/index.php?route=checkout/checkout';
		}

		if(isset($this->settings['another_img'])) {
			$data['another_img'] = (int)$this->settings['another_img'];
		}else{
			$data['another_img'] = 1;
		}

		if(isset($this->settings['prod_title_a'])) {
			$data['prod_title_a'] = (int)$this->settings['prod_title_a'];
		}else{
			$data['prod_title_a'] = 1;
		}

		if(isset($this->settings['prod_load'])) {
			$data['prod_load'] = (int)$this->settings['prod_load'];
		}else{
			$data['prod_load'] = 50;
		}

		if(isset($this->settings['progress_min'])) {
			$data['progress_min'] = (int)$this->settings['progress_min'];
		}else{
			$data['progress_min'] = 0;
		}

		if(isset($this->settings['brdcrmb'])) {
			$data['brdcrmb'] = (int)$this->settings['brdcrmb'];
		}else{
			$data['brdcrmb'] = 1;
		}

		if(isset($this->settings['section_grid'])) {
			list($data['grid_type'], $data['max_cols']) = explode('-', $this->settings['section_grid']);
		}else{
			$data['grid_type'] = 'cell';
			$data['max_cols'] = 3;
		}

		if(isset($this->settings['rvw_status'])) {
			$data['rvw_status'] = $this->settings['rvw_status'];
		}else{
			$data['rvw_status'] = 1;
		}

		$toolkit = $this->config->get('configurator_toolkit_data');

		if(!empty($toolkit['custom_css'])) {
			$patterns = array('#:*[\s]{2,}#', '#/\*(?:[^*]*(?:\*(?!/))*)*\*/#');
			$custom_css = preg_replace($patterns, '', html_entity_decode($toolkit['custom_css'], ENT_QUOTES));
		}else{
			$custom_css = '';
		}

		if(!empty($toolkit['custom_js'])) {
			$data['custom_js'] = trim(html_entity_decode($toolkit['custom_js'], ENT_QUOTES));
		}else{
			$data['custom_js'] = '';
		}

		if(!empty($toolkit['service_code'])) {
			$data['service_code'] = trim(html_entity_decode($toolkit['service_code'], ENT_QUOTES));
		}else{
			$data['service_code'] = '';
		}

		//Sections data
		$data = array_merge($data, $this->getSectionsData());

		//Preset data
		if($data['preset'] = $this->getPresetThisPage()) {
			$data['unav_prod_alert'] = ($this->checkUnavailableProducts($data['preset']))? $this->language->get('txt_unav_prod_prst') : null;
			$preset_id = $data['preset']['id'];
		}else{
			$data['unav_prod_alert'] = ($this->checkUnavailableProducts())? $this->language->get('txt_unav_prod_page') : null;
			$preset_id = 0;
		}

		//Current data
		$this->session->data['cfg_curr_page'] = $this->getCurrentUrl();
		$this->session->data['cfg_curr_total'] = $this->total_cost;
		$this->session->data['cfg_curr_preset_id'] = $preset_id;

		//Output
		$data['meta_title']	= $this->meta_title;
		$data['main_title']	= $this->main_title;
		$data['main_desc']	= $this->main_desc;
		$data['ajax_upd']	= $this->ajax_upd;
		$config_tpl			= $this->config->get('config_template');

		if(!$this->ajax_upd) {
			$this->document->setTitle($this->meta_title);
			$this->document->setDescription($this->meta_desc);
			$this->document->setKeywords($this->meta_keyword);

			if(!$preset_id) {
				$this->document->addLink($ext_seo_route, 'canonical');
			}

			if(file_exists('catalog/view/theme/'. $config_tpl .'/stylesheet/configurator/configurator.css')) {
				$this->document->addStyle('catalog/view/theme/'. $config_tpl .'/stylesheet/configurator/configurator.css', 'stylesheet', 'screen, print');
			}else{
				$this->document->addStyle('catalog/view/theme/default/stylesheet/configurator/configurator.css', 'stylesheet', 'screen, print');
			}

			$data['column_left']	= $this->load->controller('common/column_left');
			$data['column_right']	= $this->load->controller('common/column_right');
			$data['content_top']	= $this->load->controller('common/content_top');
			$data['content_bottom']	= $this->load->controller('common/content_bottom');
			$data['footer']			= $this->load->controller('common/footer');

			if($custom_css) {
				$data['header'] = preg_replace('/<\/head>/', '<style>'.$custom_css.'</style></head>', (string)$this->load->controller('common/header'), 1);
			}else{
				$data['header'] = $this->load->controller('common/header');
			}
		}else{
			$data['column_left']	= '';
			$data['column_right']	= '';
			$data['content_top']	= '';
			$data['content_bottom']	= '';
			$data['footer']			= '';
			$data['header']			= '';
		}

		if(version_compare(VERSION, '2.2.0.0', '>=')) {
			$view_html = $this->load->view($this->path, $data);
		}else{
			$tpl_path = $config_tpl . '/template/' . $this->path . '.tpl';
			$tpl_path = (file_exists(DIR_TEMPLATE . $tpl_path))? $tpl_path : 'default/template/' . $this->path . '.tpl';
			$view_html = $this->load->view($tpl_path, $data);
		}

		if($this->ajax_upd) {
			$patterns = array(
				//Clearing HTML tags from spaces and tabs
				'/\>[^\S ]+/s'		=> '>',
				'/[^\S ]+\</s'		=> '<',
				'/\>[\r\n\t ]+\</s'	=> '><',
				//Shorten multiple whitespace sequences;
				'/([\t ])+/s'		=> ' ',
				//Remove leading and trailing spaces
				'/^([\t ])+/m'		=> '',
				'/([\t ])+$/m'		=> '',
				//Remove empty lines (sequence of line-end and white-space characters)
				'/[\r\n]+([\t ]?[\r\n]+)+/s' => "\n",
			);

			$view_html = preg_replace(array_keys($patterns), array_values($patterns), $view_html);
		}

		$this->response->setOutput($view_html);
	}


	private function processConditions(&$sections, $conditions, &$cnd_hints, $added_products) {
		$state_change = false;

		//filling

		foreach($conditions['filling'] as $cnd_id => $condition) {
			$sct_id = $condition['section_id'];

			if($sections[$sct_id]['init_state'] !== 1 && $trg_sct_id = $condition['trg_section_id']) {
				if($sections[$trg_sct_id]['init_state'] === 1 && isset($added_products[$trg_sct_id])) {
					$added_prod = $added_products[$trg_sct_id];
				}else{
					if($condition['help_text']) $cnd_hints[$sct_id][$cnd_id] = $condition['help_text'];
					continue;
				}

				if($condition['type'] == 'filled') {
					$cnd_qty_min = $condition['qty_filled_min'];
					$cnd_qty_max = $condition['qty_filled_max'];
					$check_min = (!$cnd_qty_min || $added_prod['chosen_qty'] >= $cnd_qty_min);
					$check_max = (!$cnd_qty_max || $added_prod['chosen_qty'] <= $cnd_qty_max);

					if($check_min && $check_max) {
						$sections[$sct_id]['init_state'] = 1;
						$state_change = true;
					}elseif($condition['help_text']) {
						$cnd_hints[$sct_id][$cnd_id] = $condition['help_text'];
					}
				}elseif($condition['type'] == 'filled_prod') {
					foreach($this->model->getConditionProducts($cnd_id) as $cnd_prod) {
						if($added_prod['product_id'] == $cnd_prod['product_id']) {
							$cnd_qty_min = $cnd_prod['qty_filled_min'];
							$cnd_qty_max = $cnd_prod['qty_filled_max'];
							$check_min = (!$cnd_qty_min || $added_prod['chosen_qty'] >= $cnd_qty_min);
							$check_max = (!$cnd_qty_max || $added_prod['chosen_qty'] <= $cnd_qty_max);

							if($check_min && $check_max) {
								$sections[$sct_id]['init_state'] = 1;
								$state_change = true;
							}elseif($condition['help_text']) {
								$cnd_hints[$sct_id][$cnd_id] = $condition['help_text'];
							}
						}
					}
				}
			}
		}

		//progress

		$this->count_progress = 0;
		$count_progress_ready = 0;

		foreach($sections as $sct_id => $section) {
			if($section['init_state'] !== -1 && $section['progress']) {
				$this->count_progress++;

				if($section['init_state'] === 1 && isset($added_products[$sct_id])) {
					$count_progress_ready++;
				}
			}
		}

		$this->build_level = ($this->count_progress)? (int)($count_progress_ready / ($this->count_progress * 0.01)) : 0;

		foreach($conditions['progress'] as $cnd_id => $condition) {
			$sct_id = $condition['section_id'];

			if($sections[$sct_id]['init_state'] !== 1) {
				if($this->build_level >= $condition['progress_level']) {
					$sections[$sct_id]['init_state'] = 1;
					$state_change = true;
				}elseif($condition['help_text']) {
					$cnd_hints[$sct_id][$cnd_id] = $condition['help_text'];
				}
			}
		}

		//state

		foreach($conditions['state'] as $cnd_id => $condition) {
			$sct_id = $condition['section_id'];

			if($sections[$sct_id]['init_state'] !== 1 && $trg_sct_id = $condition['trg_section_id']) {
				$active = ($condition['type'] == 'active' && $sections[$trg_sct_id]['init_state'] === 1);
				$inactive = ($condition['type'] == 'inactive' && $sections[$trg_sct_id]['init_state'] !== 1);

				if($active || $inactive) {
					$sections[$sct_id]['init_state'] = 1;
					$state_change = true;
				}elseif($condition['help_text']) {
					$cnd_hints[$sct_id][$cnd_id] = $condition['help_text'];
				}
			}
		}

		//recursion

		if($state_change) {
			$this->processConditions($sections, $conditions, $cnd_hints, $added_products);
		}
	}


	private function getSectionsData() {
		$section_no_img		= $this->model_tool_image->resize('configurator/section-no-img.png', $this->def_img_size, $this->def_img_size);
		$section_groups		= (isset($this->settings['s_groups']))? $this->settings['s_groups'] : array(0 => ['name' => '']);
		$region_sections	= array();
		$sections			= array();
		$inc_categories		= array();
		$added_products		= array();
		$sct_cnd_hints		= array();
		$sct_data_json		= array();

		foreach($this->model->getSectionList() as $s_val) {
			$sct_id = (int)$s_val['id'];
			$sections[$sct_id] = array(
				'id'			=> $sct_id,
				'group_id'		=> (int)$s_val['group_id'],
				'sort_order'	=> (int)$s_val['sort_order'],
				'qty_choice'	=> (int)$s_val['qty_choice'],
				'progress'		=> (int)$s_val['progress'],
				'required'		=> (int)$s_val['required'],
				'hide'			=> (int)$s_val['hide'],
				'id_main_section'	=> $s_val['id_main_section'],
				'id_dop_sections'	=> $s_val['id_dop_sections'],
				'status'		=> (int)$s_val['status'],
				'init_state'	=> (int)$s_val['init_state'],
				'name'			=> $s_val['name'],
				'description'	=> $s_val['description'],
				'img_tumb'		=> (!empty($s_val['img_path']))? $this->model_tool_image->resize($s_val['img_path'], $this->def_img_size, $this->def_img_size) : $section_no_img,
			);

			$inc_categories[$sct_id]['num'] = count(explode(',', $s_val['category_id_list']));
			$inc_categories[$sct_id]['tree'] = $this->model->getCategoryTree($s_val['category_id_list']);
			$inc_ctgr_id_list = array_keys($inc_categories[$sct_id]['tree']);

			if($added_prod = $this->getAddedProduct($sct_id, $inc_ctgr_id_list)) {
				$added_products[$sct_id] = $added_prod;
			}
		}

		//echo '<pre>';
		//var_dump($sections);
		//echo '</pre>';

		//Check conditions
		$conditions = array(
			'filling'	=> [],
			'progress'	=> [],
			'state'		=> [],
		);

		foreach($this->model->getSectionConditions() as $condition) {
			$cnd_id = $condition['id'];
			$cnd_type = $condition['type'];

			if($cnd_type == 'filled' || $cnd_type == 'filled_prod') {
				$conditions['filling'][$cnd_id] = $condition;
			}elseif($cnd_type == 'active' || $cnd_type == 'inactive') {
				$conditions['state'][$cnd_id] = $condition;
			}elseif($cnd_type == 'progress') {
				$conditions['progress'][$cnd_id] = $condition;
			}
		}

		$this->processConditions($sections, $conditions, $sct_cnd_hints, $added_products);

		//The formation of the main sorted array

		foreach($section_groups as $gr_id => $group) {
			$grp_name = ($gr_id && isset($group['name'][$this->curr_lang_id]))? $group['name'][$this->curr_lang_id] : '';
			$rgn_sections = array();

			foreach($sections as $sct_id => $section) {
				if($gr_id == $section['group_id']) {
					$sct_data_json[$sct_id] = array(
						'name'				=> $section['name'],
						'qty_choice'		=> $section['qty_choice'],
						'inc_ctgr_num'		=> $inc_categories[$sct_id]['num'],
						'inc_ctgr_tree'		=> $inc_categories[$sct_id]['tree'],
					);

					if(isset($added_products[$sct_id])) {
						if($section['init_state'] !== 1) {
							unset($added_products[$sct_id]);
						}else{
							$this->total_cost += $added_products[$sct_id]['total_cost'];
						}
					}

					if($section['init_state'] !== -1) {
						if($section['required']) ++$this->count_required;

						$rgn_sections[$sct_id] = $section;
					}

					unset($sections[$sct_id]);
				}
			}

			uasort($rgn_sections, function ($a, $b) {
				if($a['sort_order'] != $b['sort_order']) {
					return $a['sort_order'] > $b['sort_order'];
				}else{
					return $a['id'] > $b['id'];
				}
			});

			$region_sections[$gr_id] = array('group_name' => $grp_name, 'sections' => $rgn_sections);


		}

		//echo '<pre>';
		//var_dump($region_sections);
		//echo '</pre>';

		//output

		return array(
			'region_sections'	=> $region_sections,
			'added_products'	=> $added_products,
			'sct_cnd_hints'		=> $sct_cnd_hints,
			'sct_data_json'		=> ($sct_data_json)? json_encode($sct_data_json) : '{}',
			'required_num'		=> $this->count_required,
			'progress_num'		=> $this->count_progress,
			'build_level'		=> $this->build_level,
			'total_cost'		=> $this->total_cost,
			'total_cost_format'	=> $this->currency->format($this->total_cost, $this->session->data['currency']),
		);
	}


	private function getAddedProduct($section_id, $category_id_arr) {
		if($category_id_arr && isset($this->request->get['s'.$section_id]) && !preg_match('/[^\q0-9]/', $this->request->get['s'.$section_id])) {
			$prod_params = explode('q', $this->request->get['s'.$section_id]);
			$product_id = (int)$prod_params[0];
			$chosen_qty = (!empty($prod_params[1]))? (int)$prod_params[1] : 1;
		}else{
			return null;
		}

		$product = $this->model->getProductOfConfiguration(array(
			'product_id'			=> $product_id,
			'quantity'				=> $chosen_qty,
			'category_id_list'		=> implode(',', $category_id_arr),
			'added_prod_id_list'	=> implode(',', $this->added_prod_id_arr),
		));

		if(!empty($product['product_id'])) {
			$this->added_prod_id_arr[$product_id] = $product_id;
			$product = $this->productDataPreparation($product, $this->def_img_size, $this->def_img_size);
			$options = $this->getProductOptions($product_id, $product['tax_class_id']);
			$url_params = 's'.$section_id.'='.$product_id.'q'.$chosen_qty;

			if($product['special']) {
				$product_cost = $product['special'];
			}else{
				$product_cost = $product['price'];

				if($product['discount']) {
					foreach($product['discount'] as $d_val) {
						if($chosen_qty >= $d_val['d_count']) $product_cost = $d_val['d_price'];
					}
				}
			}

			foreach($options as $opt_id => $opt) {

				$opt_key = 'o'.$section_id.'-'.$opt_id;

				switch($opt['type']) {
					case 'radio':
					case 'image':
					case 'select':
						if(isset($this->request->get[$opt_key])) {
							$opt_rqst_id = $this->request->get[$opt_key];

							if(ctype_digit($opt_rqst_id) && !empty($opt['product_option_value'][$opt_rqst_id])) {
								$opt_val = $opt['product_option_value'][$opt_rqst_id];
								$product_cost += (float)($opt_val['price_prefix'] . $opt_val['price']);

								$options[$opt_id]['value'] = $opt_rqst_id;
								$url_params .= '&'.$opt_key.'='.$opt_rqst_id;
                                $product_cost = $opt_val['price'];
							}
						}
					break;
					case 'checkbox':
						if(isset($this->request->get[$opt_key])) {
							$opt_rqst_str = $this->request->get[$opt_key];

							if(!preg_match('/[^\,0-9]/', $opt_rqst_str)) {
								$prepared_val = explode(',', $opt_rqst_str);
								$opt_rqst_arr = (is_array($prepared_val))? $prepared_val : array($prepared_val);
								$selected_opt_arr = array();

								foreach($opt['product_option_value'] as $opt_val_id => $opt_val) {
									if(in_array($opt_val_id, $opt_rqst_arr)) {
										$product_cost += (float)($opt_val['price_prefix'] . $opt_val['price']);
										array_push($selected_opt_arr, $opt_val_id);
									}
								}

								if($selected_opt_arr) {
									$options[$opt_id]['value'] = $selected_opt_arr;
									$url_params .= '&'.$opt_key.'='.implode(',', $selected_opt_arr);
								}
							}
						}
					break;
					case 'text':
					case 'textarea':
					case 'date':
					case 'time':
					case 'datetime':
						if(isset($this->request->get[$opt_key])) {
							$opt_rqst_str = $this->request->get[$opt_key];
							$value_str = strip_tags(html_entity_decode($opt_rqst_str, ENT_QUOTES));

							$options[$opt_id]['value'] = $value_str;
							$url_params .= '&'.$opt_key.'='.rawurlencode($value_str);
						}
					break;
				}

				if($opt['required'] && empty($options[$opt_id]['value']))  return null;
			}

			$prod_total_cost = (float)$product_cost * $chosen_qty;

			$product['options']				= $options;
			$product['total_cost']			= $prod_total_cost;
			$product['total_cost_format']	= $this->currency->format($prod_total_cost, $this->session->data['currency']);
			$product['chosen_qty']			= $chosen_qty;
			$product['url_params']			= $url_params;

			//echo '<pre>';
		    //var_dump($product);
		    //echo '</pre>';



			return $product;


		}else{
			$this->unav_prod_id_arr[$section_id] = $product_id;
			return false;
		}
	}


	private function checkUnavailableProducts($preset = null) {
		if($this->unav_prod_id_arr) {
			$curr_url = $this->getCurrentUrl();
			$key = substr(md5($curr_url), 0, 8);

			if(empty($this->session->data['cfg_history_unprod'][$key]) || $this->session->data['cfg_history_unprod'][$key] <= time()) {
				$this->session->data['cfg_history_unprod'][$key] = (time() + 3600);

				if($preset) {
					$event_text = sprintf($this->history_lang['unav_prod_preset'],
						$curr_url,
						$preset['name'],
						count($this->unav_prod_id_arr)
					);

					$this->addToHistory(array(
						'preset_id' => $preset['id'],
						'text' 		=> $event_text,
						'link' 		=> $curr_url,
					), 'unav_prod_preset');
				}else{
					$event_text = sprintf($this->history_lang['unav_prod_page'],
						$curr_url,
						count($this->unav_prod_id_arr)
					);

					$this->addToHistory(array(
						'text' 	=> $event_text,
						'link' 	=> $curr_url,
					), 'unav_prod_page');
				}
			}

			return true;
		}else{
			return false;
		}
	}


	public function actionListProducts() {
		$products_arr = array();

		// file_put_contents('/var/www/a-energ.ru/test_conf.txt', PHP_EOL . 'start: ' . floor(microtime(true) * 1000), FILE_APPEND);

		if(isset($this->request->post['params'])) {
			$params = $this->request->post['params'];

			if(!empty($params['inc_ctgr_id_list'])) {
				// дефолтная функция, оч долго
				// $products_arr = $this->model->getProductsOfSection(array(
				// 	'trg_ctgr_id_list'		=> (!empty($params['target_ctgr_id']))? (int)$params['target_ctgr_id'] : $params['inc_ctgr_id_list'],
				// 	'added_prod_id_list'	=> (!empty($params['added_prod_id_list']))? $params['added_prod_id_list'] : '',
				// 	'filter'				=> (!empty($params['filter']))? substr(trim($params['filter']), 0, 155) : '',
				// 	'sorting'				=> (isset($params['sorting']))? $params['sorting'] : 'default',
				// 	'start'					=> (isset($params['start']))? $params['start'] : 0,
				// 	'limit'					=> (isset($params['limit']))? $params['limit'] : 50,
				// 	'qty_choice'			=> (isset($params['qty_choice']))? $params['qty_choice'] : true,
				// 	'desc_trim_len'			=> (!empty($this->settings['desc_trim']))? $this->settings['desc_trim'] : false,
				// ));

				if(!empty($params['target_ctgr_id'])){
					$products_arr = $this->model->getProductsOfSection(array(
						'trg_ctgr_id_list'		=> (int)$params['target_ctgr_id'],
						'added_prod_id_list'	=> (!empty($params['added_prod_id_list']))? $params['added_prod_id_list'] : '',
						'filter'				=> (!empty($params['filter']))? substr(trim($params['filter']), 0, 155) : '',
						'sorting'				=> (isset($params['sorting']))? $params['sorting'] : 'default',
						'start'					=> (isset($params['start']))? $params['start'] : 0,
						'limit'					=> (isset($params['limit']))? $params['limit'] : 50,
						'qty_choice'			=> (isset($params['qty_choice']))? $params['qty_choice'] : true,
						'desc_trim_len'			=> (!empty($this->settings['desc_trim']))? $this->settings['desc_trim'] : false,
					));
				}else{
					foreach(explode(",", $params['inc_ctgr_id_list']) as $tmp_id){
						$prods_tmp = $this->model->getProductsOfSection(array(
							'trg_ctgr_id_list'		=> $tmp_id,
							'added_prod_id_list'	=> (!empty($params['added_prod_id_list']))? $params['added_prod_id_list'] : '',
							'filter'				=> (!empty($params['filter']))? substr(trim($params['filter']), 0, 155) : '',
							'sorting'				=> (isset($params['sorting']))? $params['sorting'] : 'default',
							'start'					=> (isset($params['start']))? $params['start'] : 0,
							'limit'					=> (isset($params['limit']))? $params['limit'] : 50,
							'qty_choice'			=> (isset($params['qty_choice']))? $params['qty_choice'] : true,
							'desc_trim_len'			=> (!empty($this->settings['desc_trim']))? $this->settings['desc_trim'] : false,
						));
						$products_arr = array_merge($products_arr, $prods_tmp);
					}
					
				}

				// file_put_contents('/var/www/a-energ.ru/test_conf.txt', PHP_EOL . '1: ' . floor(microtime(true) * 1000), FILE_APPEND);

				foreach($products_arr as &$product) {
					$product = $this->productDataPreparation($product);
				}

				// file_put_contents('/var/www/a-energ.ru/test_conf.txt', PHP_EOL . '2: ' . floor(microtime(true) * 1000), FILE_APPEND);
			}
		}

		$this->responseJSON($products_arr);
	}


	private $prepar_params = null;

	private function productDataPreparation($product, $img_w = null, $img_h = null) {
		if(!$this->prepar_params) {
			$this->load->model('tool/image');
			$customer_price = $this->config->get('config_customer_price');

			$this->prepar_params = array(
				'img_w'			=> ($img_w)? (int)$img_w : ((!empty($this->settings['img_w']))? (int)$this->settings['img_w'] : $this->def_img_size),
				'img_h'			=> ($img_h)? (int)$img_h : ((!empty($this->settings['img_h']))? (int)$this->settings['img_h'] : $this->def_img_size),
				'desc_trim'		=> (!empty($this->settings['desc_trim']))? (int)$this->settings['desc_trim'] : false,
				'title_a'		=> (!empty($this->settings['prod_title_a'])),
				'show_price'	=> (!$customer_price || $customer_price && $this->customer->isLogged()),
			);
		}

		$product['product_id']		= (int)$product['product_id'];
		$product['category_id']		= (int)$product['category_id'];
		$product['tax_class_id']	= (int)$product['tax_class_id'];
		$product['quantity']		= (int)$product['quantity'];
		$product['minimum']			= ($product['minimum'])? (int)$product['minimum']: 1;
		$product['name']			= htmlspecialchars_decode($product['name'], ENT_QUOTES);
		$product['href']			= ($this->prepar_params['title_a'])? $this->url->link('product/product', 'product_id=' . $product['product_id'], $this->SSL) : null;
		$product['price']			= ($this->prepar_params['show_price'])? $this->calcPrice($product['price'], $product['tax_class_id']) : 0;

		if((float)$product['special']) {
			$product['special'] = $this->calcPrice($product['special'], $product['tax_class_id']);
			$product['discount'] = null;
		}elseif(!empty($product['discount'])) {
			$discount = array();
			$discounts_arr = explode(',', $product['discount']);

			foreach($discounts_arr as $d_val) {
				$d_data = explode(':', $d_val);

				if($d_data[0] === 1) {
					$product['price'] = $this->calcPrice($d_data[1], $product['tax_class_id']);
				}else{
					$discount[] = array(
						'd_count' => $d_data[0],
						'd_price' => $this->calcPrice($d_data[1], $product['tax_class_id'])
					);
				}
			}

			$product['discount'] = $discount;
		}

		$product['thumbnail'] = $this->model_tool_image->resize(
			(($product['image'])?: 'placeholder.png'),
			$this->prepar_params['img_w'],
			$this->prepar_params['img_h']
		);

		unset($product['image']);

		if(!empty($product['description'])) {
			if(!$this->prepar_params['desc_trim']) {
				$product['description'] = htmlspecialchars_decode($product['description'], ENT_QUOTES);
			}else{
				$desc = strip_tags(htmlspecialchars_decode($product['description']), ENT_QUOTES);
				$desc = substr($desc, 0, strrpos($desc, ' ')) . '... ';
				$product['description'] = "<p class=\"short-desc\">" . $desc . "</p>" ;
			}
		}else{
			$product['description'] = '';
		}

/*
		if($this->config->get('config_review_status')) {
			$product['rating'] = (int)$product['rating'] ? $product['rating'] : false;
		}
*/
		return $product;
	}


	public function getProductOptionsRequest() {
		if(isset($this->request->post['product_id'], $this->request->post['tax_class_id'])) {
			$options = $this->getProductOptions($this->request->post['product_id'], $this->request->post['tax_class_id']);
		}else{
			$options = array();
		}

		$this->responseJSON($options);
	}


	private function getProductOptions($product_id, $tax_class_id) {
		$this->load->model('catalog/product');

			$data['position'] = isset($setting['position']) ? $setting['position'] : '';
			
		$this->load->model('tool/image');

		$options = array();
		$product_id = (int)$product_id;
		$tax_class_id = (int)$tax_class_id;
		$show_price = (
			!$this->config->get('config_customer_price') ||
			($this->config->get('config_customer_price') && $this->customer->isLogged())
		);

		foreach($this->model_catalog_product->getProductOptions($product_id) as $option) {
			$product_option_value_data = array();


			foreach($option['product_option_value'] as $option_value) {
				if(!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
					if($show_price && (float)$option_value['price']) {
						$price = $this->calcPrice($option_value['price'], $tax_class_id, true);
					}else{
						$price = false;
					}

					$product_option_value_data[$option_value['product_option_value_id']] = array(
						'product_option_value_id' => $option_value['product_option_value_id'],
						'option_value_id'         => $option_value['option_value_id'],
						'name'                    => $option_value['name'],
						'image'                   => ($option_value['image'])? $this->model_tool_image->resize($option_value['image'], 50, 50) : null,
						'price'                   => $price,
						'price_prefix'            => $option_value['price_prefix']
					);
				}
			}

			$options[$option['product_option_id']] = array(
				'product_option_id'    => $option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $option['option_id'],
				'name'                 => $option['name'],
				'type'                 => $option['type'],
				'value'                => $option['value'],
				'required'             => $option['required']
			);
		}

		return $options;
	}


	public function calcPrice($value, $tax_class_id, $price_options = false) {
		if($price_options) {
			$tax = ($this->config->get('config_tax'))? 'P' : false;
		}else{
			$tax = $this->config->get('config_tax');
		}

		return $this->tax->calculate((float)$value, (int)$tax_class_id, $tax);
	}


	public function getProductImages() {
		$output = array();

		if(isset($this->request->post['product_id'])) {
			$this->load->model('catalog/product');

			$data['position'] = isset($setting['position']) ? $setting['position'] : '';
			
			$this->load->model('tool/image');

			if($img_data = $this->model_catalog_product->getProductImages((int)$this->request->post['product_id'])) {
				$img_width = (!empty($this->settings['img_w']))? (int)$this->settings['img_w'] : $this->def_img_size;
				$img_height = (!empty($this->settings['img_h']))? (int)$this->settings['img_h'] : $this->def_img_size;

				foreach ($img_data as $img_item) {
					if($img_item['image']) {
						$output[] = $this->model_tool_image->resize($img_item['image'], $img_width, $img_height);
					}
				}
			}
		}

		$this->responseJSON($output);
	}


	private function getPresetThisPage() {
		if($preset = $this->model->getPresetThisPage()) {
			$preset_id = $preset['id'];

			$this->meta_title	= $preset['meta_title'];
			$this->meta_desc	= $preset['meta_desc'];
			$this->meta_keyword	= $preset['meta_keyword'];
			$this->main_title	= $preset['name'];
			$this->main_desc	= html_entity_decode($preset['main_desc'], ENT_QUOTES);

			if($this->settings['rvw_status'] && $preset['reviews_num']) {
				$rvw_limit = (isset($this->settings['rvw_limit']))? $this->settings['rvw_limit'] : 15;
				$reviews = $this->getReviewsOfPreset($preset_id, 0, $rvw_limit);
			}else{
				$reviews = array();
			}

			if(!empty($preset['img_path'])) {
				$img_width = (!empty($this->settings['w_p_img']))? (int)$this->settings['w_p_img'] : 400;
				$img_height = (!empty($this->settings['h_p_img']))? (int)$this->settings['h_p_img'] : 400;
				$preset_img = $this->model_tool_image->resize($preset['img_path'], $img_width, $img_height);
			}else{
				$preset_img = null;
			}

			$preset_data = array(
				'id'				=> $preset_id,
				'name'				=> $preset['name'],
				'link_md5'			=> $preset['link_md5'],
				'category_id'		=> $preset['category_id'],
				'viewed'			=> $preset['viewed'],
				'status'			=> $preset['status'],
				'date_added'		=> $preset['date_added'],
				'average_rate'		=> round($preset['average_rate'], 1),
				'reviews_num'		=> $preset['reviews_num'],
				'rating_details'	=> explode('-', $preset['rating_details']),
				'reviews'			=> $reviews,
				'image'				=> $preset_img,
			);

			if(empty($this->session->data['cfg_preset_vwd'][$preset_id]) || $this->session->data['cfg_preset_vwd'][$preset_id] <= time()) {
				$this->model->updateViewedOfPreset($preset_id);
				$this->session->data['cfg_preset_vwd'][$preset_id] = (time() + 3600);

				$event_text = sprintf($this->history_lang['preset_viewed'],
					$preset['link'],
					$preset['name'],
					$this->getClientIP()
				);

				$this->addToHistory(array(
					'preset_id' 	=> $preset_id,
					'customer_id' 	=> ($this->customer->isLogged())? $this->customer->getId() : 0,
					'client_ip' 	=> $this->getClientIP(),
					'text' 			=> $event_text,
					'link' 			=> $preset['link'],
				), 'preset_viewed');
			}

			return $preset_data;
		}else{
			return array();
		}
	}


	private function getReviewsOfPreset($preset_id, $start, $limit) {
		$reviews = $this->model->getReviewsOfPreset($preset_id, $start, $limit);
		$sess_rvw_vote = &$this->session->data['cfg_preset_rvw_vote'];

		foreach($reviews as &$review) {
			$rvw_id = $review['id'];
			$review['date_added_format'] = date('d.m.Y H:i', strtotime($review['date_added']));
			$review['voted'] = (isset($sess_rvw_vote[$rvw_id]))? $sess_rvw_vote[$rvw_id] : false;
		}

		return $reviews;
	}


	public function addReviewOfPreset() {
		$this->load->language($this->path);
		$errors = array();
		$vulues = array();
		$keys = array('l_code', 'email', 'autor', 'positive', 'negative', 'rating', 'recommend', 'review');

		foreach($keys as $key) {
			if(isset($this->request->post[$key]) && is_string($this->request->post[$key])) {
				$vulue_str = strip_tags(html_entity_decode($this->request->post[$key], ENT_QUOTES));
				$vulues[$key] = htmlspecialchars(trim($vulue_str), ENT_QUOTES);
			}else{
				$errors['common_err'] = $this->language->get('txt_rvw_err_unkn');
				break;
			}
		}

		if(!$errors
			&& $this->customer->isLogged()
			&& mb_strlen($vulues['l_code']) == 32
			&& $preset_data = $this->model->getPresetData($vulues['l_code'], array('id', 'name', 'link'))
		) {
			$vulues['preset_id'] = $preset_data['id'];
			$vulues['customer_id'] = $this->customer->getId();
			$vulues['recommend'] = (bool)$vulues['recommend'];
			$vulues['rating'] = ($vulues['rating'] >= 1 && $vulues['rating'] <= 5)? $vulues['rating'] : 5;

			$ranges = array(
				'autor' 	=> ['{min}' => 2, '{max}' => 50],
				'positive' 	=> ['{min}' => 3, '{max}' => 250],
				'negative' 	=> ['{min}' => 3, '{max}' => 250],
				'review' 	=> ['{min}' => 3, '{max}' => 3000],
			);

			foreach($ranges as $key => $range) {
				$len = mb_strlen($vulues[$key]);
				if($len < $range['{min}'] || $range['{max}'] < $len) {
					$errors[$key] = strtr($this->language->get('txt_rvw_err_len'), $range);
				}
			}

			if(preg_match('/[^\w\s\d\_\-]/ui', $vulues['autor'])) {
				$errors['autor'] = $this->language->get('txt_rvw_err_symbol');
			}

			if(!filter_var($vulues['email'], FILTER_VALIDATE_EMAIL)) {
				$errors['email'] = $this->language->get('txt_rvw_err_email');
			}

			if(!$errors) {
				$added_review_id = $this->model->setReviewOfPreset($vulues);

				if($added_review_id === 'review_exists') {
					$errors['common_err'] = $this->language->get('txt_rvw_err_exists');
				}elseif($added_review_id === 'review_moderation') {
					$errors['common_err'] = $this->language->get('txt_rvw_err_moder');
				}else{
					$event_text = sprintf($this->history_lang['rvw_added'],
						$preset_data['link'],
						$preset_data['name'],
						$vulues['autor'],
						$vulues['rating']
					);

					$this->addToHistory(array(
						'customer_id' 	=> $vulues['customer_id'],
						'preset_id' 	=> $vulues['preset_id'],
						'review_id' 	=> $added_review_id,
						'client_ip' 	=> $this->getClientIP(),
						'text' 			=> $event_text,
						'link' 			=> $preset_data['link'],
					), 'rvw_added');
				}
			}
		}else{
			$errors['common_err'] = $this->language->get('txt_rvw_err_unkn');
		}

		$this->responseJSON($errors);
	}


	public function getRestReviewsOfPreset() {
		$reviews = array();

		if(isset($this->request->post['l_code'], $this->request->post['rvw_start'])) {
			$link_code = $this->request->post['l_code'];
			$rvw_start = $this->request->post['rvw_start'];
			$rvw_limit = 1000;

			if(is_string($link_code) && mb_strlen($link_code) == 32 && is_numeric($rvw_start)) {
				if($preset = $this->model->getPresetData($link_code, array('id'))) {
					$reviews = $this->getReviewsOfPreset($preset['id'], $rvw_start, $rvw_limit);
				}
			}
		}

		$this->responseJSON($reviews);
	}


	public function voteForReviewOfPreset() {
		$errors = '';

		if(isset($this->request->post['review_id'], $this->request->post['vote'])) {
			$review_id = (int)$this->request->post['review_id'];
			$vote = (int)((bool)$this->request->post['vote']);

			if(empty($this->session->data['cfg_preset_rvw_vote'][$review_id])) {
				$user_id = ($this->customer->isLogged())?
					$this->customer->getId() : 0;

				$ip_hash = ($client_ip = $this->getClientIP())?
					md5($client_ip . '#' . $review_id) : null;

				$ua_hash = (!empty($this->request->server['HTTP_USER_AGENT']))?
					md5($this->request->server['HTTP_USER_AGENT'] . '#'. date('d') . '#' . $review_id) : null;

				if($ip_hash) {
					$cache_data = ($this->cache->get('cfg_preset_rvw_vote'))?: array();
					$cache_entry_exists = false;

					if(!empty($cache_data[$review_id])) {
						foreach($cache_data[$review_id] as $cache_entry) {
							if(
								$user_id && $user_id === $cache_entry['user_id']
								or
								$ip_hash === $cache_entry['ip_hash']
								or
								$ua_hash && $ua_hash === $cache_entry['ua_hash']
							) {
								$cache_entry_exists = true;
								break;
							}
						}
					}

					if(!$cache_entry_exists) {
						$this->model->setVoteForReviewOfPreset($review_id, $vote);

						$cache_data[$review_id][] = array(
							'user_id'	=> $user_id,
							'ip_hash'	=> $ip_hash,
							'ua_hash'	=> $ua_hash,
							'vote'		=> $vote
						);
						$this->cache->set('cfg_preset_rvw_vote', $cache_data);
						$this->session->data['cfg_preset_rvw_vote'][$review_id] = $vote;
					}else{
						$this->session->data['cfg_preset_rvw_vote'][$review_id] = $cache_data[$review_id]['vote'];
						$errors = 'not_unique';
					}
				}else{
					$errors = 'unkn_err';
				}
			}else{
				$errors = 'not_unique';
			}
		}else{
			$errors = 'unkn_err';
		}

		$this->responseJSON($errors);
	}


	private function getClientIP() {
		$headers = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
		$ip_val = null;

		foreach($headers as $header) {
			if(isset($this->request->server[$header]) && $val = $this->request->server[$header]) {
				if(strpos($val, ',') !== false) {
					$val = explode(',', $val);
					$val = trim(array_pop($val));
				}

				$ip_val = (filter_var($val, FILTER_VALIDATE_IP))? $val : null;
			}

			if($ip_val) break;
		}

		return $ip_val;
	}


	private function getCurrentUrl() {
		if(isset($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) !== 'OFF') {
			$url = 'https://';
		}else{
			$url = 'http://';
		}

		$url .= $this->request->server['SERVER_NAME'];
		$url .= $this->request->server['REQUEST_URI'];

		return $url;
	}

	private function addToHistory($data, $type = 'info') {
		//'info',
		//'rvw_added',
		//'preset_viewed',
		//'unav_prod_preset',
		//'unav_prod_page',
		//'added_to_cart'
		//'added_to_cart_err',
		//'error'

		$customer_id = 0;
		$preset_id = 0;
		$review_id = 0;
		$client_ip = '';
		$text = '';
		$link = '';

		if($data && is_array($data)) {
			if(isset($data['customer_id'])) {
				$customer_id = (is_numeric($data['customer_id']))? (int)$data['customer_id'] : 0;
				unset($data['customer_id']);
			}

			if(isset($data['preset_id'])) {
				$preset_id = (is_numeric($data['preset_id']))? (int)$data['preset_id'] : 0;
				unset($data['preset_id']);
			}

			if(isset($data['review_id'])) {
				$review_id = (is_numeric($data['review_id']))? (int)$data['review_id'] : 0;
				unset($data['review_id']);
			}

			if(isset($data['client_ip'])) {
				$client_ip = (filter_var($data['client_ip'], FILTER_VALIDATE_IP))? $data['client_ip'] : '';
				unset($data['client_ip']);
			}

			if(isset($data['text'])) {
				$text = (is_string($data['text']))? $data['text'] : '';
				unset($data['text']);
			}

			if(isset($data['link'])) {
				$link = (is_string($data['link']))? $data['link'] : '';
				unset($data['link']);
			}

			$data = ($data)? serialize($data) : '';
		}elseif(is_string($data)){
			$text = $data;
		}

		return $this->model->addToHistory(array(
			'type' 			=> substr($type, 0, 64),
			'customer_id'	=> $customer_id,
			'preset_id'		=> $preset_id,
			'review_id'		=> $review_id,
			'client_ip'		=> $client_ip,
			'text'			=> $text,
			'link'			=> $link,
			'data'			=> $data,
		));
	}


	public function reportAddedToCart() {
		$adding_url	= (isset($this->session->data['cfg_curr_page']))? $this->session->data['cfg_curr_page'] : '#';
		$total_cost	= (isset($this->session->data['cfg_curr_total']))? $this->session->data['cfg_curr_total'] : 0;
		$preset_id	= (isset($this->session->data['cfg_curr_preset_id']))? $this->session->data['cfg_curr_preset_id'] : 0;
		$event_type	= (empty($this->request->post['wrong_prod_id_arr']))? 'added_to_cart' : 'added_to_cart_err';

		$event_text = sprintf($this->history_lang[$event_type],
			$adding_url,
			$this->currency->format($total_cost, $this->config->get('config_currency'))
		);

		$adding_report = array(
			'customer_id' 	=> ($this->customer->isLogged())? $this->customer->getId() : 0,
			'preset_id'		=> $preset_id,
			'client_ip'		=> $this->getClientIP(),
			'text'			=> $event_text,
			'link'			=> $adding_url,
		);

		$this->addToHistory($adding_report, $event_type);
	}


	private function responseJSON($value) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($value));
	}

	public function save() {

		$json = array();

		$sections = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$status = $this->request->post['preset']['status'];

			$category_id = $this->request->post['preset']['category_id'];

			$link = $this->request->post['preset']['link'];

			$link = str_replace("https://a-energ.ru/configurator/?", "", $link);

			$parametrs = explode("&amp;", $link);

			$json = $parametrs;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));


	}

	public function configurationCreate() {
		$prods = $this->request->post['data'];
		// $prods = json_decode($data);

		$new_conf = [];
		$new_conf['name'] = 'Новая конфигурация';
		$new_conf['model'] = 'Новая конфигурация модель';
		$new_conf['quantity'] = 1;
		$new_conf['language_id'] = 1;
		$new_conf['status'] = 1;
		$new_conf['product_store'] = [0];

		$new_conf['kits'] = [];
		foreach($prods as $k => $prod){
			if($prod['options']){
				$arr_tmp = array_values($prod['options']);
				$new_conf['kits'][$k]['kit_product_option_value_id'] = $arr_tmp[0];
			}
			$new_conf['kits'][$k]['kit_product_id'] = $prod['product_id'];
			$new_conf['kits'][$k]['kit_quantity'] = $prod['quantity'];
		}

		$product_id = $this->model->addConfiguratorProduct($new_conf);
		echo $product_id;

		// $test = json_encode($new_conf);
		// echo $test;
	}

	public function test() {
		$numb = 6;
		echo 'www' . $numb . '<br>';
		return;
		// if(isset($this->request->get['test'])){
		// 	$test = $this->request->get['test'];
		// 	echo $test;
		// }	

		$data = [];
		$data['name'] = 'qqq' . $numb;
		$data['model'] = 'www' . $numb;
		$data['quantity'] = 1;
		$data['language_id'] = 1;
		// status ??
		$data['status'] = 1;
		// Основной магазин ?? 
		$data['product_store'] = [0];
		// +дата поступления date_available
		
		

		$data['kits'] = [
			[
				'kit_product_option_value_id' => 5808322,
				'kit_product_id' => 189098,
				'kit_quantity' => 1,
			]
		];
		
		$product_id = $this->model->addConfiguratorProduct($data);
		
	}

}