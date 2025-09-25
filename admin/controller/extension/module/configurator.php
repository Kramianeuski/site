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
	private $errors = array();
	private $store_id = 0;
	private $curr_lang_id = 0;
	private $token_param = '';
	private $SSL = true;
	private $cfg_nav = 'main';
	private $path = 'extension/module/configurator';
	private $model = null;
	private $ext_page = 'marketplace/extension';

	public function __construct($params) {
		parent::__construct($params);

		$this->store_id = $this->config->get('config_store_id');
		$this->curr_lang_id = $this->config->get('config_language_id');

		if(version_compare(VERSION, '3.0.0.0', '>=')) {
			$this->token_param = 'user_token=' . $this->session->data['user_token'];
			$this->load->model($this->path);
			$this->model = $this->model_extension_module_configurator;
		}elseif(version_compare(VERSION, '2.3.0.0', '>=')) {
			$this->token_param = 'token=' . $this->session->data['token'];
			$this->load->model($this->path);
			$this->model = $this->model_extension_module_configurator;
			$this->ext_page = 'extension/extension';
		}elseif(version_compare(VERSION, '2.2.0.0', '>=')) {
			$this->token_param = 'token=' . $this->session->data['token'];
			$this->path = 'module/configurator';
			$this->load->model($this->path);
			$this->model = $this->model_module_configurator;
			$this->ext_page = 'extension/module';
		}else{
			$this->token_param = 'token=' . $this->session->data['token'];
			$this->path = 'module/configurator';
			$this->load->model($this->path);
			$this->model = $this->model_module_configurator;
			$this->SSL = 'SSL';
			$this->ext_page = 'extension/module';
		}

		if(isset($this->request->get['cfg_nav'])) {
			switch($this->request->get['cfg_nav']) {
				default:
					$this->cfg_nav = 'main';
					break;
				case 'sections':
					$this->cfg_nav = 'sections';
					break;
				case 'conditions':
					$this->cfg_nav = 'conditions';
					break;
				case 'attr_excl':
					$this->cfg_nav = 'attr_excl';
					break;
				case 'presets':
					$this->cfg_nav = 'presets';
					break;
				case 'preset_edit':
					$this->cfg_nav = 'preset_edit';
					break;
				case 'reviews':
					$this->cfg_nav = 'reviews';
					break;
				case 'review_edit':
					$this->cfg_nav = 'review_edit';
					break;
				case 'toolkit':
					$this->cfg_nav = 'toolkit';
					break;
				case 'settings':
					$this->cfg_nav = 'settings';
					break;
			}
		}
	}


	public function index() {
		$this->load->language($this->path);
		$this->load->model('setting/setting');
		$this->load->model('tool/image');

		//save data
		if(strtoupper($this->request->server['REQUEST_METHOD']) === 'POST' && $this->verify()) {
			$save_redirect_url = $this->url->link($this->ext_page, $this->token_param, $this->SSL);
			$update_redirect_url = $this->url->link($this->path, 'cfg_nav=' . $this->cfg_nav . '&' . $this->token_param, $this->SSL);

			if($this->cfg_nav === 'sections') {
				if(isset($this->request->post['sections']) && is_array($this->request->post['sections'])) {
					$lang_id_list = array_keys($this->getActiveLanguages());
					$sections = array();

					foreach($this->request->post['sections'] as $section) {
						if(!isset($section['id'])) continue;

						$lang_values = array();

						foreach($lang_id_list as $lang_id) {
							$lang_values[$lang_id]['name'] = '';
							$lang_values[$lang_id]['description'] = '';

							if(isset($section['lang_values'][$lang_id])) {
								$lang_val = $section['lang_values'][$lang_id];
							}else{
								continue;
							}

							if(!empty($lang_val['name']) && is_string($lang_val['name'])) {
								$lang_values[$lang_id]['name'] = $this->strSaveFormat($lang_val['name'], false, 250);
							}

							if(!empty($lang_val['description']) && is_string($lang_val['description'])) {
								$lang_values[$lang_id]['description'] = $this->strSaveFormat($lang_val['description'], false, 1000);
							}
						}

						if(!empty($section['inc_categories']) && is_array($section['inc_categories'])) {
							$category_id_list = implode(',', $section['inc_categories']);
						}else{
							$category_id_list = '';
						}

						if(!empty($section['img_path']) && is_string($section['img_path']) && is_file(DIR_IMAGE . $section['img_path'])) {
							$img_path = $section['img_path'];
						}else{
							$img_path = '';
						}

						$sections[] = array(
							'id'				=> (int)$section['id'],
							'group_id'			=> (isset($section['group_id']))? (int)$section['group_id'] : 0,
							'img_path'			=> $img_path,
							'lang_values'		=> $lang_values,
							'category_id_list'	=> $category_id_list,
							'id_main_section'	=> $section['id_main_section'],
							'id_dop_sections'	=> $section['id_dop_sections'],
							'sort_order'		=> (isset($section['sort_order']))? (int)$section['sort_order'] : (int)$section['id'],
							'qty_choice'		=> (isset($section['qty_choice']))? (int)$section['qty_choice'] : 1,
							'progress'			=> (isset($section['progress']))? (int)$section['progress'] : 0,
							'required'			=> (isset($section['required']))? (int)$section['required'] : 0,
							'hide'				=> (isset($section['hide']))? (int)$section['hide'] : 0,
							'status'			=> (isset($section['status']))? (int)$section['status'] : 1,
						);
					}

					if(!$this->model->updateSections($sections)) {
						$this->errors['warning'] = $this->language->get('txt_err_unknow');
					}
				}else{
					$this->errors['warning'] = $this->language->get('txt_err_data');
				}
			}

			if($this->cfg_nav === 'preset_edit') {
				if(isset($this->request->post['preset']) && $preset_inp = $this->request->post['preset']) {
					if(isset(
						$preset_inp['id'],
						$preset_inp['category_id'],
						$preset_inp['status'],
						$preset_inp['link'],
						$preset_inp['img_path'],
						$preset_inp['lang_values']
					)) {
						$preset_link = trim($preset_inp['link']);
						$decoded_link = htmlspecialchars_decode($preset_link, ENT_QUOTES);
						preg_match('/s\d+\=\d+q.+$/', $decoded_link, $path_matches);
						$cfg_path = array_shift($path_matches);
						$link_md5 = ($cfg_path)? md5($cfg_path) : '';

						if($link_md5) {
							$check_response = $this->checkPresetLink($decoded_link);

							if($check_response === 'valid') {
								if($found_preset = $this->model->getPresetOfDuplicateLinkCode($link_md5, $preset_inp['id'])) {
									if($found_preset['id'] != (int)$preset_inp['id']) {
										$found_preset_link = $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $found_preset['id'] . '&' . $this->token_param, $this->SSL);
										$found_preset_err = $this->language->get('txt_err_link_dbl_in');
										$found_preset_err .= ' <a href="' . $found_preset_link . '" target="_blank">' . $found_preset['name'] . '</a>';
										$this->errors['preset']['link'] = $found_preset_err;
									}
								}
							}elseif($check_response === 'prod_missing') {
								$this->errors['preset']['link'] = $this->language->get('txt_err_prod_missing');
							}elseif($check_response === 'prod_unav') {
								$this->errors['preset']['link'] = $this->language->get('txt_err_prod_unav');
							}else{
								$this->errors['preset']['link'] = $this->language->get('txt_err_link');
							}
						}else{
							$this->errors['preset']['link'] = $this->language->get('txt_err_link');
						}
					}else{
						$this->errors['warning'] = $this->language->get('txt_err_data');
					}

					if(!$this->errors) {
						$lang_id_list = array_keys($this->getActiveLanguages());
						$lang_values = array();

						foreach($lang_id_list as $lang_id) {
							$lang_values[$lang_id] = array(
								'name' 			=> '',
								'brief_desc'	=> '',
								'main_desc'		=> '',
								'meta_title'	=> '',
								'meta_desc'		=> '',
								'meta_keyword'	=> '',
							);

							if(isset($preset_inp['lang_values'][$lang_id])) {
								$lang_val = $preset_inp['lang_values'][$lang_id];
							}else{
								$this->errors['warning'] = $this->language->get('txt_err_data');
								break;
							}

							if(isset($lang_val['name']) && is_string($lang_val['name'])) {
								if(mb_strlen($lang_val['name']) >= 3) {
									$lang_values[$lang_id]['name'] = $this->strSaveFormat($lang_val['name'], false, 250);
								}else{
									$this->errors['preset']['name'] = sprintf($this->language->get('txt_err_short_value'), 3);
								}
							}else{
								$this->errors['preset']['name'] = $this->language->get('txt_err_data');
							}

							if(!empty($lang_val['brief_desc']) && is_string($lang_val['brief_desc'])) {
								$lang_values[$lang_id]['brief_desc'] = $this->strSaveFormat($lang_val['brief_desc'], false);
							}

							if(!empty($lang_val['main_desc']) && is_string($lang_val['main_desc'])) {
								$lang_values[$lang_id]['main_desc'] = $this->strSaveFormat($lang_val['main_desc']);
							}

							if(!empty($lang_val['meta_title']) && is_string($lang_val['meta_title'])) {
								$lang_values[$lang_id]['meta_title'] = $this->strSaveFormat($lang_val['meta_title'], false, 250);
							}

							if(!empty($lang_val['meta_desc']) && is_string($lang_val['meta_desc'])) {
								$lang_values[$lang_id]['meta_desc'] = $this->strSaveFormat($lang_val['meta_desc'], false, 500);
							}

							if(!empty($lang_val['meta_keyword']) && is_string($lang_val['meta_keyword'])) {
								$lang_values[$lang_id]['meta_keyword'] = $this->strSaveFormat($lang_val['meta_keyword'], false, 500);
							}
						}
					}

					if(!$this->errors) {
						if($preset_inp['img_path']) {
							if(is_file(DIR_IMAGE . $preset_inp['img_path'])) {
								$img_path = $preset_inp['img_path'];
							}else{
								$this->errors['warning'] = $this->language->get('txt_err_img_path');
							}
						}else{
							$img_path = '';
						}
					}

					if(!$this->errors) {
						$preset = array(
							'id'			=> (int)$preset_inp['id'],
							'category_id'	=> (int)$preset_inp['category_id'],
							'lang_values'	=> $lang_values,
							'link_md5'		=> $link_md5,
							'img_path'		=> $img_path,
							'link'			=> $preset_link,
							'status'		=> (int)$preset_inp['status'],
						);
					}

					if(!$this->errors) {
						if($edited_id = $this->model->setPreset($preset)) {
							$save_redirect_url = $this->url->link($this->path, 'cfg_nav=presets&page=1&' . $this->token_param, $this->SSL);
							$update_redirect_url = $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $edited_id . '&' . $this->token_param, $this->SSL);
						}else{
							$this->errors['warning'] = $this->language->get('txt_err_unknow');
						}
					}
				}else{
					$this->errors['warning'] = $this->language->get('txt_err_data');
				}
			}

			if($this->cfg_nav === 'review_edit') {
				if(isset($this->request->post['review']) && $review = $this->request->post['review']) {
					$review_keys = array(
						'id',
						'preset_id',
						'customer_id',
						'email',
						'autor',
						'positive',
						'negative',
						'review',
						'rating',
						'recommend',
						'likes',
						'dislikes',
						'status',
						'date_added'
					);

					foreach($review_keys as $key) {
						if(isset($review[$key]) && is_string($review[$key])) {
							$review[$key] = $this->strSaveFormat($review[$key], false);
						}else{
							$this->errors['warning'] = $this->language->get('txt_err_data');
							break;
						}
					}

					if(!$this->errors) {
						$ranges = array(
							'autor' 	=> ['{min}' => 2, '{max}' => 50],
							'positive' 	=> ['{min}' => 3, '{max}' => 250],
							'negative' 	=> ['{min}' => 3, '{max}' => 250],
							'review' 	=> ['{min}' => 3, '{max}' => 3000],
						);

						foreach($ranges as $key => $range) {
							$len = mb_strlen($review[$key]);
							if($len < $range['{min}'] || $range['{max}'] < $len) {
								$this->errors['review'][$key] = strtr($this->language->get('txt_err_len_field'), $range);
							}
						}

						if(!ctype_digit($review['preset_id']) || !$review['preset_id']) {
							$this->errors['review']['preset_id'] = $this->language->get('txt_err_need_preset');
						}

						if(preg_match('/[^\w\s\d\_\-]/ui', $review['autor'])) {
							$this->errors['review']['autor'] = $this->language->get('txt_err_symbol');
						}

						if(!filter_var($review['email'], FILTER_VALIDATE_EMAIL)) {
							$this->errors['review']['email'] = $this->language->get('txt_err_format');
						}

						if(!DateTime::createFromFormat('Y-m-d H:i', $review['date_added'])) {
							$this->errors['review']['date_added'] = $this->language->get('txt_err_format');
						}
					}

					if(!$this->errors) {
						if($edited_id = $this->model->setReview($review)) {
							if($edited_id !== 'review_exists') {
								$save_redirect_url = $this->url->link($this->path, 'cfg_nav=reviews&page=1&' . $this->token_param, $this->SSL);
								$update_redirect_url = $this->url->link($this->path, 'cfg_nav=review_edit&review_id=' . $edited_id . '&' . $this->token_param, $this->SSL);
							}else{
								$this->errors['warning'] = $this->language->get('txt_err_rvw_exists');
							}
						}else{
							$this->errors['warning'] = $this->language->get('txt_err_unknow');
						}
					}
				}else{
					$this->errors['warning'] = $this->language->get('txt_err_data');
				}
			}

			if($this->cfg_nav === 'toolkit') {
				if(isset($this->request->post['configurator_toolkit_data'])) {
					$post['configurator_toolkit_data'] = $this->request->post['configurator_toolkit_data'];

					$this->model_setting_setting->editSetting('configurator_toolkit', $post);
				}else{
					$this->errors['warning'] = $this->language->get('txt_err_data');
				}
			}

			if($this->cfg_nav === 'settings') {
				if(isset($this->request->post['configurator_settings'])) {
					$post['configurator_settings'] = $this->request->post['configurator_settings'];
					$post['configurator_settings']['history_text'] = array(
						'rvw_added' 		=> $this->language->get('txt_hst_rvw_added'),
						'preset_viewed' 	=> $this->language->get('txt_hst_preset_viewed'),
						'unav_prod_preset' 	=> $this->language->get('txt_hst_unav_prod_preset'),
						'unav_prod_page'	=> $this->language->get('txt_hst_unav_prod_page'),
						'added_to_cart' 	=> $this->language->get('txt_hst_added_to_cart'),
						'added_to_cart_err'	=> $this->language->get('txt_hst_added_to_cart_err'),
					);

					$this->model_setting_setting->editSetting('configurator', $post);

					if(isset($post['configurator_settings']['seo_url'])) {
						$this->model->setURLAliasSEO($post['configurator_settings']['seo_url']);
					}

					if(isset($post['configurator_settings']['s_groups'])) {
						$s_group_id_list = implode(',', array_keys($post['configurator_settings']['s_groups']));
						$this->model->setDefaultMissingSectionGroups($s_group_id_list);
					}

					if(isset($post['configurator_settings']['p_ctgrs'])) {
						$p_ctgr_id_list = implode(',', array_keys($post['configurator_settings']['p_ctgrs']));
						$this->model->setDefaultMissingPresetCategories($p_ctgr_id_list);
					}
				}else{
					$this->errors['warning'] = $this->language->get('txt_err_data');
				}
			}

			if($this->errors) {
				$this->errors['danger'] = $this->language->get('txt_save_error');
			}else{
				$this->session->data['success'] = $this->language->get('txt_success');

				if(isset($this->request->post['button_type']) && $this->request->post['button_type'] === 'save') {
					$this->response->redirect($save_redirect_url);
				}else{
					$this->response->redirect($update_redirect_url);
				}
			}
		}else{
			unset($this->request->post);
		}

		//page resources
		$this->document->setTitle($this->language->get('txt_module_name'));
		$this->document->addStyle('view/stylesheet/configurator/configurator.css', 'stylesheet');
		$data['summernote'] = $this->addSummernoteEditor(array('settings', 'preset_edit'));

		//links
		$data['link_main']			= $this->url->link($this->path, '&' . $this->token_param, $this->SSL);
		$data['link_settings']		= $this->url->link($this->path, 'cfg_nav=settings&' . $this->token_param, $this->SSL);
		$data['link_sections']		= $this->url->link($this->path, 'cfg_nav=sections&' . $this->token_param, $this->SSL);
		$data['link_conditions']	= $this->url->link($this->path, 'cfg_nav=conditions&' . $this->token_param, $this->SSL);
		$data['link_attr_excl']		= $this->url->link($this->path, 'cfg_nav=attr_excl&' . $this->token_param, $this->SSL);
		$data['link_presets']		= $this->url->link($this->path, 'cfg_nav=presets&page=1&' . $this->token_param, $this->SSL);
		$data['link_reviews']		= $this->url->link($this->path, 'cfg_nav=reviews&page=1&' . $this->token_param, $this->SSL);
		$data['link_toolkit']		= $this->url->link($this->path, 'cfg_nav=toolkit&' . $this->token_param, $this->SSL);
		$data['link_cancel']		= $this->url->link($this->ext_page, $this->token_param, $this->SSL);

		$data['breadcrumbs'] = array(
			[
				'text' => $this->language->get('txt_bc_home'),
				'href' => $this->url->link('common/dashboard', $this->token_param, $this->SSL)
			],
			[
				'text' => $this->language->get('txt_bc_modules'),
				'href' => $this->url->link($this->ext_page, $this->token_param, $this->SSL)
			],
			[
				'text' => $this->language->get('txt_module_name'),
				'href' => $this->url->link($this->path, $this->token_param, $this->SSL)
			],
		);

		//main page
		if($this->cfg_nav === 'main') {
			/*main statistics*/
			$main_stat = $this->model->getMainStat();

			foreach($main_stat as $key => $el_stat) {
				if(strripos($key, '_avg') !== false) {
					$main_stat[$key] = round((float)$el_stat, 1);
				}else{
					$main_stat[$key] = (int)$el_stat;
				}
			}

			$data['main_stat'] = $main_stat;

			/*history section*/
			$type_arr = ($type_list = $this->model->getEventTypes())? explode(',', $type_list) : array();
			$limit_arr = array('25', '50', '100', '200');
			$event_filter = (isset($this->request->get['event_filter']) && in_array($this->request->get['event_filter'], $type_arr))? $this->request->get['event_filter'] : '';
			$event_limit = (isset($this->request->get['event_limit']) && in_array($this->request->get['event_limit'], $limit_arr))? $this->request->get['event_limit'] : $limit_arr[0];

			$type_title = array(
				'info'				=> $this->language->get('txt_ht_info'),
				'rvw_added'			=> $this->language->get('txt_ht_rvw_added'),
				'preset_viewed'		=> $this->language->get('txt_ht_preset_viewed'),
				'unav_prod_preset'	=> $this->language->get('txt_ht_unav_prod_preset'),
				'unav_prod_page'	=> $this->language->get('txt_ht_unav_prod_page'),
				'added_to_cart'		=> $this->language->get('txt_ht_added_to_cart'),
				'added_to_cart_err'	=> $this->language->get('txt_ht_added_to_cart_err'),
				'error'				=> $this->language->get('txt_ht_error'),
			);

			$data['event_filters']['all'] = array(
				'link' => $this->url->link($this->path, 'event_limit='.$event_limit.'&' . $this->token_param, $this->SSL),
				'title' => 'Все',
			);

			foreach($type_arr as $type) {
				$data['event_filters'][$type] = array(
					'link' => $data['event_filters'][$type] = $this->url->link($this->path, 'event_filter='.$type.'&event_limit='.$event_limit.'&' . $this->token_param, $this->SSL),
					'title' => (isset($type_title[$type]))? $type_title[$type] : $event['type'],
				);
			}

			foreach($limit_arr as $limit) {
				$data['event_limits'][$limit] = array(
					'link' => $this->url->link($this->path, 'event_filter='.$event_filter.'&event_limit='.$limit.'&' . $this->token_param, $this->SSL),
					'title' => $limit,
				);
			}

			$history = array();
			$request = array(
				'start'			=> 0,
				'limit'			=> $event_limit,
				'type_filter'	=> $event_filter,
			);

			foreach($this->model->getHistory($request) as $event) {
				$history[] = array(
					'id'	=> $event['id'],
					'type'	=> (isset($type_title[$event['type']]))? $type_title[$event['type']] : $event['type'],
					'text' 	=> $event['text'],
					'date' 	=> $event['date'],
				);
			}

			$data['history'] = $history;
			$data['event_filter'] = $event_filter;
			$data['event_limit'] = $event_limit;
		}

		//section list
		if($this->cfg_nav === 'sections') {
			$settings = $this->config->get('configurator_settings');
			$section_groups = (empty($settings['s_groups']))? [0 => ['name' => $this->language->get('txt_no_group')]] : $settings['s_groups'];
			$section_no_img = $this->model_tool_image->resize('configurator/section-no-img.png', 100, 100);
			$sections = array();

			foreach($this->model->getSectionList() as $section) {
				$sct_id = (int)$section['id'];
				$sections[$sct_id] = array(
					'id'				=> $sct_id,
					'group_id'			=> $section['group_id'],
					'img_path'			=> $section['img_path'],
					'img_tumb'			=> (!empty($section['img_path']))? $this->model_tool_image->resize($section['img_path'], 100, 100) : $section_no_img,
					'lang_values'		=> $this->model->getSectionLangValues($sct_id),
					'inc_categories'	=> $this->model->getIncludedCategories($section['category_id_list']),
					'id_main_section'	=> $section['id_main_section'],
					'id_dop_sections'	=> $section['id_dop_sections'],
					'sort_order'		=> $section['sort_order'],
					'qty_choice'		=> $section['qty_choice'],
					'progress'			=> $section['progress'],
					'required'			=> $section['required'],
					'hide'				=> $section['hide'],
					'status'			=> $section['status'],
				);
			}

			$group_sort = array_flip(array_keys($section_groups));

			usort($sections, function ($a, $b) use($group_sort) {
				if($group_sort[$a['group_id']] != $group_sort[$b['group_id']]) {
					return $group_sort[$a['group_id']] > $group_sort[$b['group_id']];
				}elseif($a['sort_order'] != $b['sort_order']) {
					return $a['sort_order'] > $b['sort_order'];
				}else{
					return $a['id'] > $b['id'];
				}
			});

			$data['section_groups'] = $section_groups;
			$data['sections'] = array_combine(array_column($sections, 'id'), $sections);
			$data['section_no_img'] = $section_no_img;

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_bc_sections'),
				'href' => ''
			));
		}

		//conditions map
		if($this->cfg_nav === 'conditions') {
			$settings = $this->config->get('configurator_settings');
			$section_groups = (empty($settings['s_groups']))? [0 => ['name' => $this->language->get('txt_no_group')]] : $settings['s_groups'];
			$sections = $this->model->getSectionList($this->curr_lang_id);
			$group_sort = array_flip(array_keys($section_groups));

			usort($sections, function ($a, $b) use($group_sort) {
				if($group_sort[$a['group_id']] != $group_sort[$b['group_id']]) {
					return $group_sort[$a['group_id']] > $group_sort[$b['group_id']];
				}elseif($a['sort_order'] != $b['sort_order']) {
					return $a['sort_order'] > $b['sort_order'];
				}else{
					return $a['id'] > $b['id'];
				}
			});

			$sections = array_combine(array_column($sections, 'id'), $sections);
			$cnd_type_lang = array(
				'progress'		=> $this->language->get('txt_cnd_progress'),
				'filled'		=> $this->language->get('txt_cnd_s_filled'),
				'filled_prod'	=> $this->language->get('txt_cnd_s_filled_prod'),
				'active'		=> $this->language->get('txt_cnd_s_active'),
				'inactive'		=> $this->language->get('txt_cnd_s_inactive'),
			);

			foreach($sections as $sct_id => $section) {
				$conditions = array();

				foreach($this->model->getSectionConditions($sct_id) as $n => $condition) {
					$conditions[$n] = array(
						'id'			=> $condition['id'],
						'type'			=> $condition['type'],
						'type_format'	=> $cnd_type_lang[$condition['type']],
						'target'		=> '',
						'target_format'	=> '',
						'qty_min'		=> 0,
						'qty_max'		=> 0,
					);

					if($condition['type'] === 'progress') {
						$conditions[$n]['target'] = (int)$condition['progress_level'];
						$conditions[$n]['target_format'] = ((int)$condition['progress_level']).'%';
					}elseif(in_array($condition['type'], array('filled', 'filled_prod', 'active', 'inactive'))) {
						$trg_s_id = (int)$condition['trg_section_id'];
						$conditions[$n]['target'] = $trg_s_id;
						$conditions[$n]['target_format'] = (isset($sections[$trg_s_id]['name']))? $sections[$trg_s_id]['name'] : '---';

						if($condition['type'] === 'filled') {
							$conditions[$n]['qty_min'] = $qty_filled_min = (int)$condition['qty_filled_min'];
							$conditions[$n]['qty_max'] = $qty_filled_max = (int)$condition['qty_filled_max'];

							if($qty_filled_min || $qty_filled_max) {
								$qty_range = ' <span>(min: '.$qty_filled_min . (($qty_filled_max)? ' max: '.$qty_filled_max.')' : ')</span>');
								$conditions[$n]['target_format'] .= $qty_range;
							}
						}elseif($condition['type'] === 'filled_prod') {
							$product_num = $this->model->getConditionProductNum($condition['id']);

							if($product_num) {
								$txt_prod_num = $this->language->get('txt_cnd_prod_num');
								$conditions[$n]['target_format'] .= ' <span>('.$txt_prod_num.': '.$product_num.')</span>';
							}
						}
					}
				}

				$sections[$sct_id]['conditions'] = $conditions;
			}

			$data['section_groups'] = $section_groups;
			$data['sections'] = $sections;

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_bc_conditions'),
				'href' => ''
			));
		}

		//attribute exclusions
		if($this->cfg_nav === 'attr_excl') {
			$this->load->model('catalog/attribute_group');
			$this->load->model('catalog/attribute');

			$filter_data = array(
				'sort'  => 'sort_order',
				'order' => 'ASK',
				'start' => NULL,
				'limit' => NULL
			);

			$attribute_group = $this->model_catalog_attribute_group->getAttributeGroups($filter_data);
			$attributes = $this->model_catalog_attribute->getAttributes($filter_data);
			$count_excl_attr = $this->model->getNumAttrExclusions();

			$data['attribute_group'] = array();

			foreach($attribute_group as $k_grp => $attr_group) {
				$attr_grp_id = $attr_group['attribute_group_id'];
				$data['attribute_group'][$attr_grp_id] = $attr_group;

				foreach($attributes as $k => $attr) {
					$attr_id = $attr['attribute_id'];
					$attr['count'] = (isset($count_excl_attr[$attr_id]))? $count_excl_attr[$attr_id] : 0;

					if($attr_grp_id == $attr['attribute_group_id']) {
						$data['attribute_group'][$attr_grp_id]['attributes'][$attr_id] = $attr;
						unset($attributes[$k]);
					}
				}
				unset($attribute_group[$k_grp]);
			}

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_bc_attr_excl'),
				'href' => ''
			));
		}

		//preset list
		if($this->cfg_nav === 'presets') {
			$settings = $this->config->get('configurator_settings');
			$preset_categories = (empty($settings['p_ctgrs']))? [0 => ['name' => $this->language->get('txt_no_ctgr')]] : $settings['p_ctgrs'];
			$presets = array();
			$presets_num = (int)($this->model->getNumPresets());

			if($presets_num) {
				$limit = 15;
				$page = (isset($this->request->get['page']))? (int)$this->request->get['page'] : 1;
				$max_page = ceil($presets_num / $limit);

				if($page > $max_page) {
					$this->response->redirect($this->url->link($this->path, 'cfg_nav=presets&page=' . $max_page . '&' . $this->token_param, $this->SSL));
				}

				$request = array('limit' => $limit, 'start' => $limit * ($page - 1));
				$preset_no_img = $this->model_tool_image->resize('configurator/preset-no-img.png', 100, 100);

				foreach($this->model->getPresetList($request) as $preset) {
					$p_id = (int)$preset['id'];
					$presets[$p_id] = array(
						'id' 					=> $p_id,
						'category_id'			=> $preset['category_id'],
						'name'					=> $preset['name'],
						'brief_desc'			=> $preset['brief_desc'],
						'link'					=> $preset['link'],
						'viewed'				=> $preset['viewed'],
						'status'				=> $preset['status'],
						'average_rate'			=> round($preset['average_rate'], 1),
						'reviews_num'			=> $preset['reviews_num'],
						'active_reviews_num'	=> $preset['active_reviews_num'],
						'img_path'				=> $preset['img_path'],
						'img_tumb'				=> (!empty($preset['img_path']))?  $this->model_tool_image->resize($preset['img_path'], 100, 100) : $preset_no_img,
						'reviews_link'			=> $this->url->link($this->path, 'cfg_nav=reviews&preset_id=' . $p_id . '&' . $this->token_param, $this->SSL),
						'link_edit'				=> $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $p_id . '&' . $this->token_param, $this->SSL),
					);
				}

				$pagination = new Pagination();
				$pagination->total = $presets_num;
				$pagination->page = $page;
				$pagination->limit = $limit;
				$pagination->url = $this->url->link($this->path, 'cfg_nav=presets&page={page}&' . $this->token_param, $this->SSL);

				$data['pagination'] = $pagination->render();
				$data['results'] = sprintf($this->language->get('txt_pagination'), ($presets_num) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($presets_num - $limit)) ? $presets_num : ((($page - 1) * $limit) + $limit), $presets_num, ceil($presets_num / $limit));
				$data['preset_no_img'] = $preset_no_img;
			}

			$data['preset_categories'] = $preset_categories;
			$data['presets'] = $presets;
			$data['link_preset_edit'] = $this->url->link($this->path, 'cfg_nav=preset_edit&' . $this->token_param, $this->SSL);

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_bc_presets'),
				'href' => ''
			));
		}

		//adding and editing a preset
		if($this->cfg_nav === 'preset_edit') {
			$settings = $this->config->get('configurator_settings');
			$preset_categories = (empty($settings['p_ctgrs']))? [0 => ['name' => $this->language->get('txt_no_ctgr')]] : $settings['p_ctgrs'];
			$preset_no_img = $this->model_tool_image->resize('configurator/preset-no-img.png', 100, 100);
			$rqst_preset_id = (!empty($this->request->get['preset_id']))? (int)$this->request->get['preset_id'] : null;

			if($rqst_preset_id && $result = $this->model->getPresetByID($rqst_preset_id)) {
				$p_id = (int)$result['id'];
				$preset = array(
					'id'					=> $p_id,
					'category_id'			=> $result['category_id'],
					'lang_values'			=> $this->model->getPresetLangValues($p_id),
					'link'					=> $result['link'],
					'viewed'				=> $result['viewed'],
					'status'				=> $result['status'],
					'date_added'			=> $result['date_added'],
					'average_rate'			=> round($result['average_rate'], 1),
					'reviews_num'			=> $result['reviews_num'],
					'active_reviews_num'	=> $result['active_reviews_num'],
					'reviews_link'			=> $this->url->link($this->path, 'cfg_nav=reviews&preset_id=' . $p_id . '&' . $this->token_param, $this->SSL),
					'img_path'				=> $result['img_path'],
					'img_tumb'				=> (!empty($result['img_path']))? $this->model_tool_image->resize($result['img_path'], 100, 100) : $preset_no_img,
				);
			}else{
				$preset = array(
					'id'			=> '',
					'category_id'	=> 0,
					'lang_values'	=> '',
					'link'			=> '',
					'viewed'		=> 0,
					'status'		=> 1,
					'date_added'	=> '',
					'average_rate'	=> 0,
					'reviews_num'	=> 0,
					'reviews_link'	=> '',
					'img_path'		=> '',
					'img_tumb'		=> $preset_no_img,
				);

				if($rqst_preset_id) {
					$this->errors['warning'] = $this->language->get('txt_preset_not_exist');
				}
			}

			$data['preset_categories'] = $preset_categories;
			$data['preset'] = (isset($this->request->post['preset']))? array_replace($preset, $this->request->post['preset']) : $preset;
			$data['preset_no_img'] = $preset_no_img;

			$data['link_cancel'] = htmlspecialchars_decode($data['link_presets'], ENT_QUOTES);
			$data['del_btn_target_id'] = $preset['id'];

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_nav_presets'),
				'href' => $data['link_presets']
			));

			array_push($data['breadcrumbs'], array(
				'text' => ($preset['id'])? $this->language->get('txt_bc_preset_edit') : $this->language->get('txt_bc_preset_make'),
				'href' => ''
			));
		}

		//review list
		if($this->cfg_nav === 'reviews') {
			if(isset($this->request->get['preset_id'])) {
				$preset_id = (int)$this->request->get['preset_id'];
				$preset_param = '&preset_id=' . $preset_id;
			}else{
				$preset_id = '';
				$preset_param = '';
			}

			$reviews = array();
			$reviews_num = (int)($this->model->getNumReviews($preset_id));

			if($reviews_num) {
				$limit = 25;
				$page = (isset($this->request->get['page']))? (int)$this->request->get['page'] : 1;
				$max_page = ceil($reviews_num / $limit);

				if($page > $max_page) {
					$this->response->redirect($this->url->link($this->path, 'cfg_nav=reviews' . $preset_param . '&page=' . $max_page . '&' . $this->token_param, $this->SSL));
				}

				$request = array(
					'limit'		=> $limit,
					'start'		=> $limit * ($page - 1),
					'preset_id'	=> $preset_id,
				);

				foreach($this->model->getReviewList($request) as $review) {
					$customer = $this->getCustomer($review['customer_id']);

					$r_id = (int)$review['id'];
					$reviews[$r_id] = array(
						'id'			=> $r_id,
						'customer_id'	=> $customer['id'],
						'customer_name'	=> $customer['full_name'],
						'customer_link'	=> $customer['link'],
						'email'			=> $review['email'],
						'autor'			=> $review['autor'],
						'rating'		=> $review['rating'],
						'recommend'		=> $review['recommend'],
						'likes'			=> $review['likes'],
						'dislikes'		=> $review['dislikes'],
						'moderated'		=> $review['moderated'],
						'status'		=> $review['status'],
						'date_added'	=> date('Y-m-d H:i', strtotime($review['date_added'])),
						'link_edit'		=> $this->url->link($this->path, 'cfg_nav=review_edit&review_id=' . $r_id . '&' . $this->token_param, $this->SSL),
						'preset_name'	=> $review['preset_name'],
						'preset_link'	=> $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $review['preset_id'] . '&' . $this->token_param, $this->SSL),
					);
				}

				$pagination = new Pagination();
				$pagination->total = $reviews_num;
				$pagination->page = $page;
				$pagination->limit = $limit;
				$pagination->url = $this->url->link($this->path, 'cfg_nav=reviews' . $preset_param . '&page={page}&' . $this->token_param, $this->SSL);

				$data['pagination'] = $pagination->render();
				$data['results'] = sprintf($this->language->get('txt_pagination'), ($reviews_num) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($reviews_num - $limit)) ? $reviews_num : ((($page - 1) * $limit) + $limit), $reviews_num, ceil($reviews_num / $limit));
			}

			$data['reviews'] = $reviews;
			$data['link_review_edit'] = $this->url->link($this->path, 'cfg_nav=review_edit' . $preset_param . '&' . $this->token_param, $this->SSL);

			array_push($data['breadcrumbs'], array(
				'text' => ($preset_id)? $this->language->get('txt_bc_preset_reviews') : $this->language->get('txt_bc_reviews'),
				'href' => ''
			));
		}

		//adding and editing a review
		if($this->cfg_nav === 'review_edit') {
			$rqst_review_id = (!empty($this->request->get['review_id']))? (int)$this->request->get['review_id'] : null;

			if($rqst_review_id && $result = $this->model->getReviewByID($rqst_review_id)) {
				$customer = $this->getCustomer($result['customer_id']);

				$review = array(
					'id'			=> $result['id'],
					'customer_id'	=> $customer['id'],
					'customer_name'	=> $customer['full_name'],
					'customer_link'	=> $customer['link'],
					'email'			=> $result['email'],
					'autor'			=> $result['autor'],
					'positive'		=> $result['positive'],
					'negative'		=> $result['negative'],
					'review'		=> $result['review'],
					'rating'		=> $result['rating'],
					'recommend'		=> $result['recommend'],
					'likes'			=> $result['likes'],
					'dislikes'		=> $result['dislikes'],
					'status'		=> $result['status'],
					'date_added'	=> date('Y-m-d H:i', strtotime($result['date_added'])),
					'preset_id'		=> $result['preset_id'],
					'preset_name'	=> $result['preset_name'],
					'preset_link'	=> $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $result['preset_id'] . '&' . $this->token_param, $this->SSL),
				);

				if(!$result['moderated'] && $this->user->hasPermission('modify', $this->path)) {
					$this->model->setModerationReviewIsTrue($result['id']);
				}
			}else{
				$review = array(
					'id'			=> '',
					'customer_id'	=> '',
					'customer_name'	=> '',
					'customer_link'	=> '',
					'autor'			=> '',
					'email'			=> '',
					'date_added'	=> date('Y-m-d H:i'),
					'rating'		=> 5,
					'likes'			=> 0,
					'dislikes'		=> 0,
					'recommend'		=> 1,
					'positive'		=> '',
					'negative'		=> '',
					'review'		=> '',
					'status'		=> 0,
					'preset_id'		=> '',
					'preset_name'	=> '',
					'preset_link'	=> '',
				);

				if($preset = (isset($this->request->get['preset_id']))? $this->model->getPresetByID((int)$this->request->get['preset_id']) : '') {
					$review['preset_id'] = $preset['id'];
					$review['preset_name'] = $preset['name'];
					$review['preset_link'] = $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $preset['id'] . '&' . $this->token_param, $this->SSL);
				}

				if($rqst_review_id) {
					$this->errors['warning'] = $this->language->get('txt_review_not_exist');
				}
			}

			$data['review'] = (isset($this->request->post['review']))? array_replace($review, $this->request->post['review']) : $review;
			$data['link_cancel'] = htmlspecialchars_decode($data['link_reviews'], ENT_QUOTES);
			$data['del_btn_target_id'] = $review['id'];

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_nav_reviews'),
				'href' => $data['link_reviews']
			));

			array_push($data['breadcrumbs'], array(
				'text' => ($review['id'])? $this->language->get('txt_bc_review_edit') : $this->language->get('txt_bc_review_make'),
				'href' => ''
			));
		}

		//toolkit
		if($this->cfg_nav === 'toolkit') {
			$toolkit = $this->config->get('configurator_toolkit_data');
			$data['toolkit'] = array(
				'custom_css' => !empty($toolkit['custom_css'])? $toolkit['custom_css'] : '',
				'custom_js' => !empty($toolkit['custom_js'])? $toolkit['custom_js'] : '',
				'service_code' => !empty($toolkit['service_code'])? $toolkit['service_code'] : '',
			);

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_bc_toolkit'),
				'href' => ''
			));
		}

		//settings
		if($this->cfg_nav === 'settings') {
			$data['settings'] = array(
				's_groups' 			=> [0 => ['name' => $this->language->get('txt_no_group')]],
				'p_ctgrs' 			=> [0 => ['name' => $this->language->get('txt_no_ctgr')]],
				'seo_url'			=> '',
				'checkout_url'		=> '/index.php?route=checkout/checkout',
				'brdcrmb' 			=> 1,
				'img_w' 			=> 120,
				'img_h' 			=> 120,
				'another_img' 		=> 0,
				'prod_title_a'		=> 0,
				'prod_load' 		=> 50,
				'progress_min' 		=> 0,
				'w_p_img'			=> 400,
				'h_p_img'			=> 400,
				'rvw_status'		=> 1,
				'rvw_limit'			=> 25,
				'section_grid'		=> 'cell-3',
				'desc_trim' 		=> 200,
				'prt_logo'			=> 'configurator/logo-print.png',
				'lang_values'		=> [$this->curr_lang_id => [
					'main_title'		=> '',
					'main_desc'			=> '',
					'meta_title'		=> '',
					'meta_desc' 		=> '',
					'meta_keyword' 		=> '',
					'txt_cost' 			=> '',
					'prt_title' 		=> $this->language->get('txt_demo_prt_title'),
					'prt_tbl_title'		=> $this->language->get('txt_demo_prt_tbl_title'),
					'prt_qr_code'		=> $this->language->get('txt_demo_prt_qr_code'),
					'prt_contcs'		=> $this->language->get('txt_demo_prt_contcs'),
					'prt_text'			=> $this->language->get('txt_demo_prt_text'),
					'prt_notice'		=> $this->language->get('txt_demo_prt_notice'),
				]],
				'license_key'		=> ''
			);

			if($settings = $this->config->get('configurator_settings')) {
				$data['settings'] = array_replace($data['settings'], $settings);
			}

			$data['prt_logo_no_img'] = $this->model_tool_image->resize('configurator/logo-print-no-img.png', 100, 100);
			$data['prt_logo_img_tumb'] = (!empty($data['settings']['prt_logo']))? $this->model_tool_image->resize($data['settings']['prt_logo'], 100, 100) : $data['prt_logo_no_img'];

			array_push($data['breadcrumbs'], array(
				'text' => $this->language->get('txt_bc_settings'),
				'href' => ''
			));
		}

		//common data
		$data = array_merge($data, $this->language->get('lang_data'));
		$data['curr_lang_id'] = $this->curr_lang_id;
		$data['languages'] = $this->getActiveLanguages();

		$data['button_refresh'] = $this->language->get('button_refresh');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['token_param'] = $this->token_param;
		$data['cfg_nav'] = $this->cfg_nav;
		$data['ext_route'] = 'index.php?route=' . $this->path;
		$data['action'] = $this->request->server['REQUEST_URI'];
		$data['errors'] = $this->errors;
		$data['success'] = '';

		if(isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if(version_compare(VERSION, '2.2.0.0', '>=')) {
			$this->response->setOutput($this->load->view($this->path, $data));
		}else{
			$this->response->setOutput($this->load->view($this->path . '.tpl', $data));
		}
	}


	public function deleteItems() {
		if($this->user->hasPermission('modify', $this->path)) {
			$response = true;

			if(!empty($this->request->post['cfg_nav']) && !empty($this->request->post['id_list'])) {
				switch($this->request->post['cfg_nav']) {
					case 'presets':
					case 'preset_edit':
						$response = $this->model->deletePresets($this->request->post['id_list']);
						break;
					case 'reviews':
					case 'review_edit':
						$response = $this->model->deleteReviews($this->request->post['id_list']);
						break;
				}
			}
		}else{
			$response = 'error_permission';
		}

		$this->responseJSON($response);
	}


	public function getExclusions() {
		$excl_data = array();

		if(isset($this->request->post['excl_type']) && !empty($this->request->post['target_id_list'])) {
			$type = $this->request->post['excl_type'];
			$target_id_list = $this->request->post['target_id_list'];

			if($type === 'attribute') {
				$excl_data = $this->model->getExclusionsOfAttribute($target_id_list, null, true);
			}elseif($type === 'category' || $type === 'product') {
				$key_id = $type.'_id';
				$key_excl_id = 'exclusion_'.$type.'_id';
				$excl_data = $this->model->getIncludedItemsOfSection($target_id_list, $type);
				$item_id_list = implode(',', array_column($excl_data, $key_id));
				$section_excl_arr = $this->model->getExclusionsOfSection($item_id_list, $type);

				foreach($excl_data as &$item) {
					$item['exclusions'] = array();

					foreach($section_excl_arr as $section_excl) {
						if($section_excl[$key_id] == $item[$key_id]) {
							$item['exclusions'][] = array(
								'id'	=> $section_excl[$key_excl_id],
								'name'	=> $section_excl['name']
							);
						}
					}
				}
			}
		}

		$this->responseJSON($excl_data);
	}


	public function actionOnExclusion() {
		if(!$this->user->hasPermission('modify', $this->path)) {
			$this->responseJSON('error_permission');
			return;
		}elseif(!isset($this->request->post['type'], $this->request->post['event'], $this->request->post['data'])) {
			$this->responseJSON(false);
			return;
		}

		$type = $this->request->post['type'];
		$event = $this->request->post['event'];
		$data = $this->request->post['data'];
		$response = false;

		if($type === 'attribute' && $event === 'quick_add') {
			$data_is_set = isset($data['attr_id'], $data['excl_attr_id_arr']);

			if($data_is_set && is_array($data['excl_attr_id_arr']) && $data['excl_attr_id_arr']) {
				list($existing, $errors) = 0;

				foreach($data['excl_attr_id_arr'] as $excl_attr_id) {
					foreach($this->getActiveLanguages() as $key => $lang) {
						$excl_val_data[$key] = array(
							'lang_id'			=> $lang['language_id'],
							'attr_value'		=> '*',
							'excl_attr_value'	=> '*',
						);
					}

					if(!empty($excl_val_data)) {
						$report = $this->model->setExclusion($type, array(
							'excl_id'		=> '',
							'attr_id'		=> (int)$data['attr_id'],
							'excl_attr_id'	=> (int)$excl_attr_id,
							'excl_val_data'	=> $excl_val_data,
						));

						if($report === 'attr_excl_exists') {
							++$existing;
						}elseif($report === false) {
							++$errors;
						}
					}
				}

				$response = ($errors)? 'some_attr_excl_err' : ($existing)? 'some_attr_excl_exists' : true;
			}
		}elseif($type === 'attribute' && $event === 'quick_delete') {
			$data_is_set = isset($data['attr_id'], $data['excl_attr_id_arr']);

			if($data_is_set && is_array($data['excl_attr_id_arr']) && $data['excl_attr_id_arr']) {
				$target_attr_id = (int)$data['attr_id'];
				$attr_id_list = implode(',', $data['excl_attr_id_arr']);

				$response = $this->model->deleteTargetRelatedExclusions($target_attr_id, $attr_id_list);
			}
		}elseif($type === 'attribute' && $event === 'add') {
			$data_is_set = isset($data['excl_id'], $data['attr_id'], $data['excl_attr_id'], $data['excl_val_data']);

			if($data_is_set && is_array($data['excl_val_data']) && $data['excl_val_data']) {
				foreach($data['excl_val_data'] as &$val) {
					if(isset($val['lang_id'], $val['attr_value'], $val['excl_attr_value'])) {
						$val['lang_id'] = (int)$val['lang_id'];
						$val['attr_value'] = $this->strSaveFormat($val['attr_value'], false, 155);
						$val['excl_attr_value'] = $this->strSaveFormat($val['excl_attr_value'], false, 155);
					}else{
						$data_is_set = false;
						break;
					}
				}

				if($data_is_set) {
					$response = $this->model->setExclusion($type, array(
						'excl_id'		=> (int)$data['excl_id'],
						'attr_id'		=> (int)$data['attr_id'],
						'excl_attr_id'	=> (int)$data['excl_attr_id'],
						'excl_val_data'	=> $data['excl_val_data'],
					));
				}
			}
		}elseif($type === 'attribute' && $event === 'delete') {
			if(!empty($data['target_id'])) {
				$response = $this->model->deleteExclusion($type, array(
					'target_id'	=> (int)$data['target_id'],
				));
			}
		}elseif($type === 'category' || $type === 'product') {
			if(!empty($data['excl_id']) && !empty($data['target_id'])) {
				$params = array(
					'excl_id'	=> (int)$data['excl_id'],
					'target_id'	=> (int)$data['target_id'],
				);

				if($event === 'add') {
					$response = $this->model->setExclusion($type, $params);
				}elseif($event === 'delete') {
					$response = $this->model->deleteExclusion($type, $params);
				}
			}
		}

		$this->responseJSON($response);
	}


	public function getIdListRelatedAttrExclusions() {
		$output = array();

		if(isset($this->request->post['attr_id']) && $attr_id = (int)$this->request->post['attr_id']) {
			foreach($this->model->getExclusionsOfAttribute($attr_id) as $excl) {
				$first_val = array_shift($excl);

				if(isset($first_val['excl_attr_id'])) {
					array_push($output, $first_val['excl_attr_id']);
				}
			}
		}

		$this->responseJSON($output);
	}


	public function deleteRelatedExclusions() {
		if($this->user->hasPermission('modify', $this->path)) {
			$response = false;

			if(!empty($this->request->post['rel_id_list']) && !empty($this->request->post['excl_type'])) {
				$response = $this->model->deleteRelatedExclusions(
					$this->request->post['rel_id_list'],
					$this->request->post['excl_type']
				);
			}
		}else{
			$response = 'error_permission';
		}

		$this->responseJSON($response);
	}


	public function getNumAttrExclusions () {
		$attr_id_list = (isset($this->request->post['attr_id_list']))? $this->request->post['attr_id_list'] : null;
		$output = $this->model->getNumAttrExclusions($attr_id_list);

		$this->responseJSON($output);
	}


	public function getAttributeData() {
		$output			= array();
		$filter_name	= &$this->request->post['filter_name'];
		$attr_data		= &$this->request->post['attr_data'];
		$target_data	= &$this->request->post['target_data'];
		$post_is_set	= isset($filter_name, $attr_data, $target_data);

		if($post_is_set && is_string($filter_name) && is_array($attr_data) && is_string($target_data)) {
			foreach(array('attr_id', 'excl_attr_id', 'lang_id') as $key) {
				$attr_data[$key] = ($attr_data[$key] && is_numeric($attr_data[$key]))? (int)$attr_data[$key] : 0;
			}

			$output = $this->model->getAttributeData(array(
				'filter_name'	=> substr(trim($filter_name), 0, 155),
				'target_data'	=> $target_data,
				'attr_data'		=> $attr_data,
				'limit'			=> (!empty($this->request->post['limit']))? (int)$this->request->post['limit'] : 5,
				'start'			=> 0
			));
		}

		$this->responseJSON($output);
	}


	public function setSectionInitState() {
		$response = false;

		if($this->user->hasPermission('modify', $this->path)) {
			$section_id = &$this->request->post['section_id'];
			$value = &$this->request->post['value'];

			if(isset($section_id) && isset($value)) {
				$curr_sct_cnds = $this->model->getSectionConditions($section_id);

				if(count($curr_sct_cnds)) {
					if($value == 1 || $value == 0 || $value == -1) {
						$response = $this->model->updateSectionInitState($section_id, $value);
					}
				}else{
					$response = 'error_conditions_not_found';
				}
			}
		}else{
			$response = 'error_permission';
		}

		$this->responseJSON($response);
	}

	public function getConditionData() {
		$output = array();
		$cnd_id = &$this->request->post['condition_id'];

		if(isset($cnd_id) && is_numeric($cnd_id)) {
			$output = $this->model->getConditionData($cnd_id);
		}

		$this->responseJSON($output);
	}

	public function setCondition() {
		$cnd = &$this->request->post['condition'];

		if(!$this->user->hasPermission('modify', $this->path)) {
			return $this->responseJSON('error_permission');
		}elseif(empty($cnd['section_id']) || empty($cnd['type']) || !isset($cnd['help_text'])) {
			return $this->responseJSON('error_data');
		}elseif(!$this->model->checkSectionExistence($cnd['section_id'])) {
			return $this->responseJSON('error_section_not_exists');
		}

		$edit_cnd_id = (!empty($cnd['id']) && is_numeric($cnd['id']))? $cnd['id'] : null;
		$curr_sct_cnds = $this->model->getSectionConditions($cnd['section_id']);

		if(in_array($cnd['type'], array('progress', 'filled', 'filled_prod', 'active', 'inactive'))) {
			if(!is_array($cnd['help_text'])) {
				return $this->responseJSON('error_data');
			}

			foreach($cnd['help_text'] as &$lang_val) {
				if(!isset($lang_val['lang_id'], $lang_val['text'])
					|| !is_numeric($lang_val['lang_id'])
					|| !is_string($lang_val['text'])
				) {
					return $this->responseJSON('error_data');
				}elseif($lang_val['text']) {
					$lang_val['text'] = $this->strSaveFormat($lang_val['text'], false, 500);
				}
			}
		}

		if($cnd['type'] === 'progress') {
			if(!isset($cnd['progress_level'])
				|| !is_numeric($cnd['progress_level'])
				|| $cnd['progress_level'] < 1
				|| $cnd['progress_level'] > 100
			) {
				return $this->responseJSON('error_data');
			}

			foreach($curr_sct_cnds as $curr_cnd) {
				if($curr_cnd['id'] != $edit_cnd_id && $curr_cnd['type'] == 'progress') {
					return $this->responseJSON('error_cnd_exists');
				}
			}
		}elseif(in_array($cnd['type'], array('filled', 'filled_prod', 'active', 'inactive'))) {
			if(empty($cnd['trg_section_id'])) {
				return $this->responseJSON('error_data');
			}elseif(!$this->model->checkSectionExistence($cnd['trg_section_id'])){
				return $this->responseJSON('error_target_section_not_exists');
			}

			foreach($curr_sct_cnds as $curr_cnd) {
				if($curr_cnd['id'] != $edit_cnd_id
					&& $curr_cnd['type'] == $cnd['type']
					&& $curr_cnd['trg_section_id'] == $cnd['trg_section_id']
				) {
					return $this->responseJSON('error_cnd_exists');
				}
			}

			if($cnd['type'] === 'filled') {
				$qty_filled_min = (!empty($cnd['qty_filled_min']))? (int)$cnd['qty_filled_min'] : 0;
				$qty_filled_max = (!empty($cnd['qty_filled_max']))? (int)$cnd['qty_filled_max'] : 0;

				if($qty_filled_min && $qty_filled_max && $qty_filled_min > $qty_filled_max) {
					return $this->responseJSON('error_data');
				}
			}elseif($cnd['type'] === 'filled_prod') {
				if(empty($cnd['section_products']) || !is_array($cnd['section_products'])) {
					return $this->responseJSON('error_data');
				}

				foreach($cnd['section_products'] as $prod) {
					if(!isset($prod['id'], $prod['qty_min'], $prod['qty_max'])
						|| $prod['id'] && !is_numeric($prod['id'])
						|| $prod['qty_min'] && !is_numeric($prod['qty_min'])
						|| $prod['qty_max'] && !is_numeric($prod['qty_max'])
						|| $prod['qty_min'] && $prod['qty_max'] && $prod['qty_min'] > $prod['qty_max']
					) {
						return $this->responseJSON('error_data');
					}
				}
			}
		}else{
			return $this->responseJSON('error_data');
		}

		$this->responseJSON($this->model->setSectionCondition($cnd));
	}


	public function deleteSectionConditions() {
		if($this->user->hasPermission('modify', $this->path)) {
			$response = false;

			if(!empty($this->request->post['id_list'])) {
				$response = $this->model->deleteSectionConditions($this->request->post['id_list']);
			}
		}else{
			$response = 'error_permission';
		}

		$this->responseJSON($response);
	}


	public function getProdListByNameForCondition() {
		$output 		= array();
		$section_id		= &$this->request->post['section_id'];
		$filter_name	= &$this->request->post['filter_name'];
		$qty_min		= &$this->request->post['qty_min'];
		$qty_max		= &$this->request->post['qty_max'];
		$limit			= &$this->request->post['limit'];

		if(!empty($section_id) && !empty($filter_name) && isset($qty_min, $qty_max)) {
			$error_data = (
				!is_numeric($section_id)
				|| !is_string($filter_name)
				|| $qty_min && !is_numeric($qty_min)
				|| $qty_max && !is_numeric($qty_max)
				|| $qty_min && $qty_max && $qty_min > $qty_max
			);

			if(!$error_data){
				$output = $this->model->getProdListByNameForCondition(array(
					'section_id'	=> $section_id,
					'filter_name'	=> substr(trim($filter_name), 0, 155),
					'qty_min'		=> $qty_min,
					'qty_max'		=> $qty_max,
					'limit'			=> (!empty($limit))? (int)$limit : 5,
					'start'			=> 0
				));
			}
		}

		$this->responseJSON($output);
	}


	public function getPresetListByName() {
		$output = array();
		$filter_name = &$this->request->post['filter_name'];

		if(!empty($filter_name) && is_string($filter_name)) {
			$output = $this->model->getPresetListByName(array(
				'filter_name'	=> substr(trim($filter_name), 0, 155),
				'limit'			=> (!empty($this->request->post['limit']))? (int)$this->request->post['limit'] : 5,
				'start'			=> 0
			));
		}

		foreach($output as &$preset) {
			$preset['name'] = htmlspecialchars_decode($preset['name'], ENT_QUOTES);
		}

		$this->responseJSON($output);
	}


	private function checkPresetLink($link) {
		if($link) {
			$correct_values = array(
				'valid',
				'prod_missing',
				'prod_unav',
			);

			$ch = curl_init($link);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'check_preset_link=1');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, 15000);
			$check_result = curl_exec($ch);
			curl_close($ch);

			if($check_result !== false && mb_strlen($check_result) <= 32 && in_array($check_result, $correct_values)) {
				return $check_result;
			}else{
				return false;
			}
		}else{
			return 'link_missing';
		}
	}


	public function checkPresetLinks() {
		if($this->user->hasPermission('modify', $this->path)) {
			$response = array();

			if($presets = $this->model->getPresetLinkList()) {
				foreach($presets as $preset) {
					if($preset['link'] && $preset['link_md5']) {
						$decoded_link = htmlspecialchars_decode($preset['link'], ENT_QUOTES);
						$check_report = $this->checkPresetLink($decoded_link);
					}else{
						$check_report = 'link_missing';
					}

					if($check_report === 'valid') {
						if($found_preset = $this->model->getPresetOfDuplicateLinkCode($preset['link_md5'], $preset['id'])) {
							$response[] = array(
								'report'		=> 'duplicate_link',
								'id' 			=> $preset['id'],
								'name'			=> $preset['name'],
								'edit_link'		=> $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $preset['id'] . '&' . $this->token_param, $this->SSL),
								'dbl_name'		=> $found_preset['name'],
								'dbl_edit_link'	=> $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $found_preset['id'] . '&' . $this->token_param, $this->SSL),
							);
						}
					}else{
						$response[] = array(
							'report'	=> $check_report,
							'id' 		=> $preset['id'],
							'name'		=> $preset['name'],
							'edit_link'	=> $this->url->link($this->path, 'cfg_nav=preset_edit&preset_id=' . $preset['id'] . '&' . $this->token_param, $this->SSL),
						);
					}
				}
			}else{
				$response = 'presets_not_found';
			}
		}else{
			$response = 'error_permission';
		}

		$this->responseJSON($response);
	}


	public function performToolkitOperations() {
		if($this->user->hasPermission('modify', $this->path)) {
			$response = false;

			if(!empty($this->request->post['operation_name'])) {
				$response = $this->model->performToolkitOperations($this->request->post['operation_name']);
			}
		}else{
			$response = 'error_permission';
		}

		$this->responseJSON($response);
	}


	protected function getActiveLanguages() {
		$this->load->model('localisation/language');
		$languages = array();
		$admin_lang_code = $this->config->get('config_admin_language');

		foreach($this->model_localisation_language->getLanguages() as $lang) {
			if($lang['status']) {
				$lang_id = (int)$lang['language_id'];
				$lang['flag_img'] = (version_compare(VERSION, '2.2.0.0', '>='))? 'language/'.$lang['code'].'/'.$lang['code'].'.png' : 'view/image/flags/'.$lang['image'];
				$lang['admin_lang'] = ($admin_lang_code == $lang['code'])? 1 : 0;
				$languages[$lang_id] = $lang;
			}
		}

		return $languages;
	}


	private function getCustomer($customer_id) {
		$customer = array(
			'id' 		=> null,
			'full_name'	=> '',
			'link'		=> ''
		);

		if(!empty($customer_id)) {
			if(version_compare(VERSION, '2.1.0.0', '>=')) {
				$this->load->model('customer/customer');
				$cstmr_data = $this->model_customer_customer->getCustomer($customer_id);
				$link_path = 'customer/customer/edit';
			}else{
				$this->load->model('sale/customer');
				$cstmr_data = $this->model_sale_customer->getCustomer($customer_id);
				$link_path = 'sale/customer/edit';
			}

			$full_name = array();
			if(!empty($cstmr_data['firstname'])) array_push($full_name, $cstmr_data['firstname']);
			if(!empty($cstmr_data['lastname'])) array_push($full_name, $cstmr_data['lastname']);

			if($cstmr_data) {
				$customer['id'] = (int)$customer_id;
				$customer['full_name'] = implode(' ', $full_name);
				$customer['link'] = $this->url->link($link_path, 'customer_id=' . $customer_id . '&' . $this->token_param, $this->SSL);
			}
		}

		return $customer;
	}


	private function addSummernoteEditor($page_arr = array()) {
		$editor = array('lang_attr' => '', 'js' => '');

		if(empty($page_arr) || array_key_exists($this->cfg_nav, array_flip($page_arr))) {
			if(version_compare(VERSION, '2.2.0.0', '>=')) {
				$admin_lang_code = $this->config->get('config_admin_language');
				$editor_lang_arr = array(
					'ru-ru' => 'ru-RU',
					'ua-uk' => 'uk-UA',
				);

				if(array_key_exists($admin_lang_code, $editor_lang_arr)) {
					$editor_lang = $editor_lang_arr[$admin_lang_code];
					$editor['lang_attr'] = 'data-lang="'.$editor_lang.'"';

					if(version_compare(VERSION, '2.3.0.0', '<')) {
						$this->document->addScript('view/javascript/summernote/lang/summernote-'.$editor_lang_arr[$admin_lang_code].'.js');
					}
				}
			}

			if(version_compare(VERSION, '2.3.0.0', '>=')) {
				$this->document->addStyle('view/javascript/summernote/summernote.css', 'stylesheet');
				$this->document->addScript('view/javascript/summernote/summernote.min.js');
				$this->document->addScript('view/javascript/summernote/opencart.js');
			}else{
				$editor['js'] = "
					$('[data-toggle=\"summernote\"]').each(function() {
						var editor = $(this);
						var editorLang = editor.attr('data-lang');
						var editorParams = (editorLang)? { height: 300, lang: editorLang } : { height: 300 };

						editor.summernote(editorParams);
					});
				";
			}
		}

		return $editor;
	}


	private function strSaveFormat($str, $html_allowed = true, $max_length = null, $escape = true) {
		$str = html_entity_decode(trim($str), ENT_QUOTES);

		if(!$html_allowed || $max_length) $str = trim(strip_tags($str));
		if($max_length) $str = substr($str, 0, (int)$max_length);
		if($escape) $str = htmlspecialchars($str, ENT_QUOTES);

		return $str;
	}

	private function responseJSON($value) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($value));
	}


	public function checkImputLicense() {
		$input_key = &$this->request->post['input_key'];
		$response = (
			!empty($input_key)
			&& is_string($input_key)
			&& mb_strlen($input_key) >= 5
			&& $this->model->checkLicense($input_key)
		)? true : false;

		$this->responseJSON($response);
	}


	private function checkLicense() {
		if($this->cfg_nav !== 'settings') {
			$settings = $this->config->get('configurator_settings');
			$license_key = (!empty($settings['license_key']))? $settings['license_key'] : null;

			return ($license_key && $this->model->checkLicense($license_key))? true : false;
		}else{
			$license_key = &$this->request->post['configurator_settings']['license_key'];

			return (
				!empty($license_key)
				&& is_string($license_key)
				&& mb_strlen($license_key) >= 5
				&& $this->model->checkLicense($license_key)
			)? true : false;
		}
	}


	private function verify() {
		if(!$this->user->hasPermission('modify', $this->path)) {
			$this->errors['danger'] = $this->language->get('txt_err_permission');
		}

		if(!$this->checkLicense()) {
			$this->errors['warning'] = $this->language->get('txt_err_license_key');
		}

		return (!$this->errors)? true : false;
	}


	public function install() {
		$this->model->createModuleLayout();
		$this->model->setURLAliasSEO();
		$this->model->createModuleTables();
    }


    public function uninstall() {
		$this->model->deleteModuleData();

		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('configurator');
		$this->model_setting_setting->deleteSetting('configurator_toolkit');
    }

}