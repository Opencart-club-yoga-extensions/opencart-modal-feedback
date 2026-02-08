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

    /**
     * Добавление админ коммента
     * @param string $comment текс админ коммента
     * @param int $feedback_id id фидбека
     * @return bool
     */
    public function addAdminCommentFeedback($feedback_id, $comment){
        $query = $this->db->query("UPDATE oc_feedbacks SET admin_comment = '{$comment}' WHERE id = {$feedback_id};");
        return $query;
    }

    /**
     * Получить список отправленных форм
     * @return array список отправленных форм
     */
    public function getFeedbacks(){
        $query = $this->db->query("SELECT * FROM oc_feedbacks;");
        return $query->rows;
    }

}