<?php
class ModelExtensionModuleYogaFeedback extends Model {

    /**
     * Запись в БД данных с форм
     * @param array $data массив с данными формы name, phone, email, comment
     * @return bool
     */
	public function createNewFeedback($data){
        $query = $this->db->query("INSERT INTO oc_feedbacks
            (`name`, phone, email, comment)
            VALUES ('{$data['firstname']}', '{$data['telephone']}', '{$data['email']}', '{$data['comment']}')
        ;");
    
        return $query;
    }

}