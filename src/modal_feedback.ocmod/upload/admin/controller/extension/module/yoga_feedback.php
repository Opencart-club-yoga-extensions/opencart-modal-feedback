<?php
class ControllerExtensionModuleYogaFeedback extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/yoga_feedback');

		$this->document->setTitle($this->language->get('extension_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			if($this->validate()){
				$this->model_setting_setting->editSetting('module_yoga_feedback', $this->request->post);

				$this->session->data['success'] = $this->language->get('text_success');
			}
			
			if($this->validateAgreeText() && $this->request->post['form_setting']){
				$this->load->model('setting/setting');
				$this->model_setting_setting->editSettingValue('yoga_feedback', 'yoga_feedback_agree_text', $this->request->post['agree_text']);
				$this->model_setting_setting->editSettingValue('yoga_feedback', 'yoga_feedback_agree_show', $this->request->post['agree_show']);
				$this->model_setting_setting->editSettingValue('yoga_feedback', 'yoga_feedback_native_captcha', $this->request->post['native_captcha']);
			}
			
		}
		$data['yoga_feedback_agree_text'] = $this->config->get('yoga_feedback_agree_text');
		$data['yoga_feedback_agree_show'] = $this->config->get('yoga_feedback_agree_show');
		$data['yoga_feedback_native_captcha'] = $this->config->get('yoga_feedback_native_captcha');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('extension_title'),
				'href' => $this->url->link('extension/module/yoga_feedback', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('extension_title'),
				'href' => $this->url->link('extension/module/yoga_feedback', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/yoga_feedback', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/yoga_feedback', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['module_yoga_feedback_status'])) {
			$data['status'] = $this->request->post['module_yoga_feedback_status'];
		} else {
			$data['status'] = $this->config->get('module_yoga_feedback_status');
    	}

    	if (isset($this->request->post['module_yoga_search_api_key'])) {
			$data['module_yoga_search_api_key'] = $this->request->post['module_yoga_search_api_key'];
		} elseif ($this->config->get('module_yoga_search_api_key')) {
			$data['module_yoga_search_api_key'] = $this->config->get('module_yoga_search_api_key');
		} else {
			$data['module_yoga_search_api_key'] = 0;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->load->model('extension/module/yoga_feedback');			
		$data['feedbacks'] = $this->model_extension_module_yoga_feedback->getFeedbacks();

		$this->response->setOutput($this->load->view('extension/module/yoga_feedback', $data));
	}

	public function updateAdminText(){
		
		$this->response->addHeader('Content-Type: application/json');

		$data = file_get_contents("php://input");
		$data = json_decode($data, true);

		foreach ($data as $key => $value) {
			$data[$key] = $this->db->escape($value);
		}
		$this->load->model('extension/module/yoga_feedback');
		$status = $this->model_extension_module_yoga_feedback->addAdminCommentFeedback($data['feedback_id'], $data["admin_comment_text"]);

        $this->response->setOutput(json_encode(
            [
                "success" => $status, 
                "message" => "ok", 
                "data" => [
					"admin_comment" => $data["admin_comment_text"]
				]
            ]
        ));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/yoga_feedback')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateAgreeText(){
		
		if (!isset($this->request->post['agree_text']) && $this->request->post['agree_show']) {
			$this->error['error'] = 'Укажите для поля agreement';
		}

		return !$this->error;
	}

	public function install(){
		$sql_create_table = "CREATE TABLE IF NOT EXISTS `oc_feedbacks` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
				`phone` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
				`email` VARCHAR(511) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
				`comment` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
				`admin_comment` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
				PRIMARY KEY (`id`) USING BTREE
			)
			COLLATE='utf8mb3_general_ci'
			ENGINE=InnoDB
		;";

		$this->db->query($sql_create_table);

		$this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('yoga_feedback', [
            'yoga_feedback_agree_text' => '',
            'yoga_feedback_agree_show' => '',
            'yoga_feedback_native_captcha' => '',
        ]);
	}

	public function uninstall(){
		// $sql_drop = "DROP TABLE `oc_feedbacks`;";
		// $this->db->query($sql_drop);
	}
}
