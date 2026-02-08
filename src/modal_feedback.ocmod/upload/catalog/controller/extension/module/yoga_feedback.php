<?php
class ControllerExtensionModuleYogaFeedback extends Controller {

	private $error = array();

	public function index(){
		$data = array();

    	if (!$this->config->get('module_yoga_feedback_status')) return false;

		$this->load->language('extension/module/yoga_feedback');
		$data['api_key'] = $this->config->get('module_yoga_feedback_api_key');
		$data['action'] = $this->url->link('extension/module/yoga_feedback/send');
		$data['yoga_feedback_agree_text'] = html_entity_decode($this->config->get('yoga_feedback_agree_text'), ENT_QUOTES, 'UTF-8');
		$data['yoga_feedback_agree_show'] = $this->config->get('yoga_feedback_agree_show');

		
		// Captcha
		$use_captcha = $this->config->get('yoga_feedback_native_captcha');
		$data['yoga_feedback_native_captcha'] = $use_captcha;
		if(
			$use_captcha 
			&& $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')
			// && in_array('register', (array)$this->config->get('config_captcha_page'))
		){
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		}else{
			$data['captcha'] = '';
		}
		
		$data['feedback_form'] = $this->load->view('extension/module/yoga_feedback', $data);

		return $this->load->view('extension/module/yoga_feedback_modal', $data);
	}

  	public function send(){

		$json = array();

		$this->load->language('extension/module/yoga_feedback');

    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->load->model('extension/module/yoga_feedback');
			$data = [];
			foreach ($this->request->post as $key => $value) {
				$data[$key] = $this->db->escape($value);
			}
			$this->model_extension_module_yoga_feedback->createNewFeedback($data);

			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->error;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

  	}

  	protected function validate() {

		if ((utf8_strlen($this->request->post['firstname']) < 3) || (utf8_strlen($this->request->post['firstname']) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['telephone']) < 9) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if (!isset($this->request->post['agree']) && $this->config->get('yoga_feedback_agree_show')) {
			$this->error['agree'] = $this->language->get('error_agree');
		}

		// Captcha
		if (
			$this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')
			&& $this->config->get('yoga_feedback_native_captcha')
		) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');
			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		return !$this->error;
	}

	protected function mail($data){
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($this->config->get('config_email'));
		$mail->setFrom($this->config->get('config_email'));
		$mail->setReplyTo($data['email']);
		$mail->setSender(html_entity_decode($data['firstname'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $data['firstname']), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('extension/module/yoga_feedback_mail', $data));
		$mail->send();
	}

}