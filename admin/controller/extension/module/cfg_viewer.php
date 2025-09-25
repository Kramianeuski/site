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
	private $errors = array();
	private $store_id = 0;
	private $curr_lang_id = 0;
	private $token_param = '';
	private $SSL = true;
	private $page = 'main';
	private $path = 'extension/module/cfg_viewer';
	private $ext_page = 'marketplace/extension';
	private $mod_settings =  null;
	private $cfg_settings =  null;
	private $settings_model =  null;
	
	public function __construct($params) {
		parent::__construct($params);
		
		$this->store_id = $this->config->get('config_store_id');
		$this->curr_lang_id = $this->config->get('config_language_id');
		$this->cfg_settings = $this->config->get('configurator_settings');
		
		if(version_compare(VERSION, '3.0.0.0', '>=')) {
			$this->token_param = 'user_token=' . $this->session->data['user_token'];
			$this->load->model('setting/module');
			$this->settings_model = $this->model_setting_module;
		}elseif(version_compare(VERSION, '2.3.0.0', '>=')) {
			$this->token_param = 'token=' . $this->session->data['token'];
			$this->ext_page = 'extension/extension';
			$this->load->model('extension/module');
			$this->settings_model = $this->model_extension_module;
		}elseif(version_compare(VERSION, '2.2.0.0', '>=')) {
			$this->token_param = 'token=' . $this->session->data['token'];
			$this->path = 'module/configurator';
			$this->ext_page = 'extension/module';
			$this->load->model('extension/module');
			$this->settings_model = $this->model_extension_module;
		}else{
			$this->token_param = 'token=' . $this->session->data['token'];
			$this->path = 'module/cfg_viewer';
			$this->SSL = 'SSL';
			$this->ext_page = 'extension/module';
			$this->load->model('extension/module');
			$this->settings_model = $this->model_extension_module;
		}
	}

	public function index() {
		$this->load->language($this->path);

		//save
		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$module_id = &$this->request->get['module_id'];
			$button_type = &$this->request->post['button_type'];
			$post_data = $this->request->post;
			unset($post_data['button_type']);
			
			if(!isset($module_id)) {
				$this->settings_model->addModule('cfg_viewer', $post_data);
			}else{
				$this->settings_model->editModule($module_id, $post_data);
			}
			
			$this->session->data['success'] = $this->language->get('txt_success');
			
			if(isset($module_id, $button_type) && $button_type == 'refresh') {
				$this->response->redirect($this->url->link($this->path, $this->token_param . '&module_id=' . $module_id, $this->SSL));
			}else{
				$this->response->redirect($this->url->link($this->ext_page, $this->token_param, $this->SSL));
			}
		}

		//Output data
		$module_name = $this->language->get('heading_title');
		$this->document->setTitle($module_name);

		if(!isset($this->request->get['module_id'])) {
			$module_link = $this->url->link($this->path, $this->token_param, $this->SSL);
		}else{
			$module_link = $this->url->link($this->path, $this->token_param . '&module_id=' . $this->request->get['module_id'], $this->SSL);		
		}
		
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
				'text' => $module_name,
				'href' => $module_link
			],
		);

		$data['action'] = $module_link;
		$data['cancel'] = $this->url->link($this->ext_page, $this->token_param, $this->SSL);
		
		if(isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$this->mod_settings = $this->settings_model->getModule($this->request->get['module_id']);
		}
		
		if(isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		}else{
			$data['name'] = (!empty($this->mod_settings['name']))? $this->mod_settings['name'] : '';
		}
		
		if(isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		}else{
			$data['title'] = (!empty($this->mod_settings['title']))? $this->mod_settings['title'] : array();
		}

		if(isset($this->cfg_settings['p_ctgrs'])) {
			$data['preset_categories'] = $this->cfg_settings['p_ctgrs'];
		}else{
			$data['preset_categories'] = false;
		}

		$data['preset_source']		= $this->getSetValue('preset_source', 'all');
		$data['sorting']			= $this->getSetValue('sorting', 'def');
		$data['limit']				= $this->getSetValue('limit', 16, 0);
		$data['view_type']			= $this->getSetValue('view_type', 'list');
		$data['title_line']			= $this->getSetValue('title_line', 0);
		$data['desc_trim_len']		= $this->getSetValue('desc_trim_len', 0, 0);
		$data['img_w']				= $this->getSetValue('img_w', 120, 50, 800);
		$data['img_h']				= $this->getSetValue('img_h', 120, 50, 800);
		$data['status']				= $this->getSetValue('status', 1);
		
		$data['vsbl_title']			= $this->getSetValue('vsbl_title', 1);
		$data['vsbl_img']			= $this->getSetValue('vsbl_img', 1);
		$data['vsbl_desc']			= $this->getSetValue('vsbl_desc', 1);
		$data['vsbl_rating']		= $this->getSetValue('vsbl_rating', 0);
		$data['vsbl_views']			= $this->getSetValue('vsbl_views', 0);
		$data['vsbl_reviews']		= $this->getSetValue('vsbl_reviews', 0);

		$data['crsl_items']			= $this->getSetValue('crsl_items', 4, 1, 8);
		$data['crsl_autoplay']		= $this->getSetValue('crsl_autoplay', 3000, 0);
		$data['crsl_speed']			= $this->getSetValue('crsl_speed', 1000, 300);
		$data['crsl_nav']			= $this->getSetValue('crsl_nav', 0);
		$data['crsl_pagination']	= $this->getSetValue('crsl_pagination', 0);
		
		$data['button_refresh'] = $this->language->get('button_refresh');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data = array_merge($data, $this->language->get('lang_data'));
		$data['curr_lang_id'] = $this->curr_lang_id;
		$data['languages'] = $this->getActiveLanguages();
		
		$data['errors'] = $this->errors;
		$data['success'] = '';
		
		if(isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}
		
		$data['header']			= $this->load->controller('common/header');
		$data['column_left']	= $this->load->controller('common/column_left');
		$data['footer']			= $this->load->controller('common/footer');

		if(version_compare(VERSION, '2.2.0.0', '>=')) {
			$this->response->setOutput($this->load->view($this->path, $data));
		}else{
			$this->response->setOutput($this->load->view($this->path . '.tpl', $data));
		}
	}
	
	
	private function getSetValue($val_name, $default = null, $min = null, $max = null) {
		$output_val = null;

		if(isset($this->request->post[$val_name])) {
			$output_val = $this->request->post[$val_name];
		}elseif(isset($this->mod_settings[$val_name])){
			$output_val = $this->mod_settings[$val_name];
		}else{
			return $default;
		}	
		
		if($min !== null && $max !== null) {
			$min		= (int)$min;
			$max		= (int)$max;
			$output_val = (int)$output_val;
			$output_val = ($output_val < $min)? $min : (($output_val > $max)? $max : $output_val);
		}elseif($min !== null && $max === null) {
			$min		= (int)$min;
			$output_val = (int)$output_val;
			$output_val = ($output_val < $min)? $min : (int)$output_val;
		}
		
		return $output_val;
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
	

	protected function validate() {
		if(!$this->user->hasPermission('modify', $this->path)) {
			$this->errors['alert'] = $this->language->get('error_permission');
		}
		
		if(!$this->cfg_settings || !isset($this->cfg_settings['p_ctgrs'])) {
			$this->errors['alert'] = $this->language->get('error_cfg_settings');
		}
		
		if((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->errors['name'] = $this->language->get('error_name');
		}

		if(
			!$this->request->post['vsbl_title'] 
			&& !$this->request->post['vsbl_img']
			&& !$this->request->post['vsbl_desc']
			&& !$this->request->post['vsbl_rating']
			&& !$this->request->post['vsbl_views']
			&& !$this->request->post['vsbl_reviews']
		) {
			$this->errors['alert'] = $this->language->get('error_cfg_all_hidden');
		}

		return !$this->errors;
	}
}