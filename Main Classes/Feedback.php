<?php
require_once 'DbConnector.php';
class Feedback{
    private $feedback_id;
    private $user_id;
    private $feedback;
    private $date;
    private $pdo;

    public function __construct()
    {
        $db = new DBConnector();
        $this->pdo = $db->connect();
    }

    public function getAllFeedbacks()
    {
        try{
            $stmt = $this -> pdo-> prepare("SELECT
                                              feedback.feedback_id,
                                              feedback.feedback,
                                              feedback.date,
                                              user.name AS name,
                                              user.user_type AS user_type,
                                              user.email AS email
                                              FROM feedback
                                              INNER JOIN user ON feedback.user_id = user.user_id");
            $stmt -> execute();
            $feedback = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            return $feedback;

        }catch(PDOException $e)
        {
              http_response_code(500);
              echo json_encode(["message" => "Failed to retrieve feedbacks.".$e->getMessage()]);
              exit;
        }
    }

    public function getFeedbacksById($user_id)
    {
        $this->user_id = $user_id;

        try{
            $sql = "SELECT * FROM feedback WHERE user_id = :user_id ORDER BY 
                     date DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Check if feedbacks were retrieved
            if ($feedbacks) {
                return $feedbacks;
            } else {
                return [];
            }
        }
        catch(PDOException $e)
        {
            http_response_code(500);
            echo json_encode(["message" => "Failed to retrieve feedbacks. " . $e->getMessage()]);
            exit;
        }
    }

    public function writeFeedback($user_id,$feedback){
         $this->user_id = $user_id;
         $this->feedback = $feedback;

         try{
            $sql = "INSERT INTO feedback (user_id,feedback) VALUES (:user_id,:feedback)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id',$this->user_id);
            $stmt->bindParam(':feedback',$this->feedback);
            $rs = $stmt->execute();

            if($rs)
            {
                return true;
            }
            return false;

         }
         catch(PDOException $e)
         {
            http_response_code(500);
            echo json_encode(["message" => "Failed to write feedback. " . $e->getMessage()]);
         }
    }

    public function deleteFeedback($feedback_id) {
        $this->feedback_id = $feedback_id;
        try {
            $stmt = $this->pdo->prepare("DELETE FROM feedback WHERE feedback_id = :feedback_id");
            $stmt->bindParam(':feedback_id', $this->feedback_id);
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete feedback. " . $e->getMessage()]);
            exit;
        }
    }
}