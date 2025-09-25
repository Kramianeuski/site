<?php

class ControllerProductConfiguration extends Controller {

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


	public function index() {
		$this->load->language('product/configuration');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('extension/module/cfg_viewer');

		$this->load->model('tool/image');

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$preset_categories = $this->cfg_settings['p_ctgrs'];

		$data['categories'] = array();


		foreach ($preset_categories as $key => $result) {

			if($key != 0){

				$data['categories'][] = array(
					'name' 	=> 	$result['name'][1],
					'image' =>  $this->model_tool_image->resize('case.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')),
					'href' 	=> 	$this->url->link('product/configuration', 'cfg_path=' . $key)
				);

			}
		}

		$data['breadcrumbs'][] = array(
			'text' => 'Конфигурации',
			'href' => $this->url->link('information/configurations')
		);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$path = '';

		$category_name = '';

		$data['breadcrumbs'][] = array(
			'text' => 'Каталог конфигураций',
			'href' => $this->url->link('product/configuration')
		);

		if (isset($this->request->get['cfg_path'])) {

			$path = (int)$this->request->get['cfg_path'];

			if (array_key_exists($path, $preset_categories)) {

				$chosen_source = (int)$this->request->get['cfg_path'];

				$category_name =  $preset_categories[$path]['name'][1];

				if ($category_name) {
					$data['breadcrumbs'][] = array(
						'text' => $category_name,
						'href' => $this->url->link('product/configuration', 'cfg_path=' . $path . $url)
					);
				}

			}else{
				$chosen_source = 'all';
			}

		} else {

			$chosen_source = 'all';

		}


		if ($category_name) {
			$this->document->setTitle('Каталог конфигураций ' . $category_name);
			$this->document->setDescription('Каталог конфигураций ' . $category_name);
			$this->document->setKeywords('');
			$data['heading_title'] = 'Каталог конфигураций ' . $category_name;
		}else{
			$this->document->setTitle('Каталог конфигураций');
			$this->document->setDescription('Каталог конфигураций');
			$this->document->setKeywords('');
			$data['heading_title'] = 'Каталог конфигураций';
		}

		static $mod_index = 1;

		$cfg_presets = array();

		if($this->cfg_settings && !empty($this->cfg_settings['p_ctgrs'])) {

			$preset_categories = $this->cfg_settings['p_ctgrs'];

			$request = array(
				'start'		=> 0,
				'limit'		=> 20,
				'sorting'	=> 'def',
			);

			if($chosen_source === 'all') {
				$cfg_presets = $this->model->getCFGPresets($request);

			}elseif(isset($preset_categories[$chosen_source])) {
				$request['category_id'] = (int)$chosen_source;

				$cfg_presets = $this->model->getCFGPresets($request);
			}

			//echo "<pre>";
			//var_dump($cfg_presets);
			//echo "</pre>";
		}


		if($cfg_presets) {
			$this->load->language($this->path);
			$this->load->model('tool/image');
			$trim_len	= 0;
			$img_w		= $this->def_img_size;
			$img_h		= $this->def_img_size;
			$no_img		= $this->model_tool_image->resize('configurator/preset-no-img.png', $img_w, $img_h);

			foreach($cfg_presets as &$preset) {
				if(!empty($preset['img_path'])) {
					$preset_img = $this->model_tool_image->resize($preset['img_path'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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

			$data['view_type'] = 'list';

			$data['title_class'] = 'title_one_line';

			$data['vsbl_title']		= 1;
			$data['vsbl_img']		= 1;
			$data['vsbl_desc']		= 1;
			$data['vsbl_rating']	= 0;
			$data['vsbl_views']		= 0;
			$data['vsbl_reviews']	= 0;

			$data['crsl_items']  = 4;

			$data['crsl_autoplay'] = 'false';

			$data['crsl_speed']			= 1000;
			$data['crsl_nav']			= 1;
			$data['crsl_pagination']	= 1;

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

			//return $view_html;
		}




		//$category_info = $this->model_catalog_category->getCategory($category_id);

		$category_info = '1';

		if ($category_info) {

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}





			/*

			$data['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($result['special']) && (float)$result['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$tax_price = (float)$result['special'];
				} else {
					$special = false;
					$tax_price = (float)$result['price'];
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format($tax_price, $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'href'        => $this->url->link('product/product', 'cfg_path=' . $this->request->get['cfg_path'] . '&product_id=' . $result['product_id'] . $url)
				);
			}

			*/

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

            if (isset($this->request->get['cfg_path'])) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_default'),
					'value' => 'p.sort_order-ASC',
					'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . '&sort=p.sort_order&order=ASC' . $url)
				);
			}else{
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_default'),
					'value' => 'p.sort_order-ASC',
					'href'  => $this->url->link('product/configuration', '&sort=p.sort_order&order=ASC' . $url)
				);

			}

			if (isset($this->request->get['cfg_path'])) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_name_asc'),
					'value' => 'pd.name-ASC',
					'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . '&sort=pd.name&order=ASC' . $url)
				);
			}else{
				$data['sorts'][] = array(
						'text'  => $this->language->get('text_name_asc'),
						'value' => 'pd.name-ASC',
						'href'  => $this->url->link('product/configuration', '&sort=pd.name&order=ASC' . $url)
				);
			}

			if (isset($this->request->get['cfg_path'])) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_name_desc'),
					'value' => 'pd.name-DESC',
					'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . '&sort=pd.name&order=DESC' . $url)
				);
			}else{
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_name_desc'),
					'value' => 'pd.name-DESC',
					'href'  => $this->url->link('product/configuration', '&sort=pd.name&order=DESC' . $url)
				);
			}

			if (isset($this->request->get['cfg_path'])) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_price_asc'),
					'value' => 'p.price-ASC',
					'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . '&sort=p.price&order=ASC' . $url)
				);
			}else{
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_price_asc'),
					'value' => 'p.price-ASC',
					'href'  => $this->url->link('product/configuration', '&sort=p.price&order=ASC' . $url)
				);
			}

			if (isset($this->request->get['cfg_path'])) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_price_desc'),
					'value' => 'p.price-DESC',
					'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] .  '&sort=p.price&order=DESC' . $url)
				);
			}else{
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_price_desc'),
					'value' => 'p.price-DESC',
					'href'  => $this->url->link('product/configuration', '&sort=p.price&order=DESC' . $url)
				);
			}

			if (isset($this->request->get['cfg_path'])) {
				if ($this->config->get('config_review_status')) {
					$data['sorts'][] = array(
						'text'  => $this->language->get('text_rating_desc'),
						'value' => 'rating-DESC',
						'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . '&sort=rating&order=DESC' . $url)
					);

					$data['sorts'][] = array(
						'text'  => $this->language->get('text_rating_asc'),
						'value' => 'rating-ASC',
						'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . '&sort=rating&order=ASC' . $url)
					);
				}
			}else{
				if ($this->config->get('config_review_status')) {
					$data['sorts'][] = array(
						'text'  => $this->language->get('text_rating_desc'),
						'value' => 'rating-DESC',
						'href'  => $this->url->link('product/configuration', '&sort=rating&order=DESC' . $url)
					);

					$data['sorts'][] = array(
						'text'  => $this->language->get('text_rating_asc'),
						'value' => 'rating-ASC',
						'href'  => $this->url->link('product/configuration', '&sort=rating&order=ASC' . $url)
					);
				}
			}

			if (isset($this->request->get['cfg_path'])) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_model_desc'),
					'value' => 'p.model-DESC',
					'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . '&sort=p.model&order=DESC' . $url)
				);
			}else{
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_model_desc'),
					'value' => 'p.model-DESC',
					'href'  => $this->url->link('product/configuration', '&sort=p.model&order=DESC' . $url)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			if (isset($this->request->get['cfg_path'])) {
				foreach($limits as $value) {
					$data['limits'][] = array(
						'text'  => $value,
						'value' => $value,
						'href'  => $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . $url . '&limit=' . $value)
					);
				}
			}else{
				foreach($limits as $value) {
					$data['limits'][] = array(
						'text'  => $value,
						'value' => $value,
						'href'  => $this->url->link('product/configuration', $url . '&limit=' . $value)
					);
				}
			}



			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

            /*

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/configuration', 'cfg_path=' . $this->request->get['cfg_path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));



			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/configuration', 'cfg_path=' . $category_info['category_id']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/configuration', 'cfg_path=' . $category_info['category_id'] . '&page='. $page), 'canonical');
			}

			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/configuration', 'cfg_path=' . $category_info['category_id'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/configuration', 'cfg_path=' . $category_info['category_id'] . '&page='. ($page + 1)), 'next');
			}

			*/

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;


		}

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/configuration', $data));


	}
}