<?php
class EllakPoQController {
    public function routeRequest() {
        global $wp_query, $wpdb;;
        ob_start();
        if($wp_query->query_vars['page'] == 0) {
            $this->showIndexAction();
        } else {
            // Fetch the question
            $question = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'poq_questions LIMIT 1 OFFSET %d', $wp_query->query_vars['page']-1 ), OBJECT);
            $question = $question[0];
            // Check if we have already answered this question
            $userAnswers = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'poq_user_answers pua JOIN '.$wpdb->prefix.'poq_answers pa ON pua.answer_id = pa.id WHERE pa.question_id = %d', $question->id ), OBJECT );
            if(count($userAnswers) <= 0) {
                $this->showQuestionAction($wp_query->query_vars['page'], $question);
            } else {
                $userAnswers = $userAnswers[0];
                $this->showAnswerAction($wp_query->query_vars['page'], $question, $userAnswers);
            }
        }
        return ob_get_clean();
    }
    
    private function showIndexAction() {
        require_once('views/ellak_poq_index.php');
    }
    
    private function showQuestionAction($page, $question) {
        global $wpdb;
        // Handle POST - User has submitted an answer
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $wpdb->query($wpdb->prepare( 'INSERT INTO '.$wpdb->prefix.'poq_user_answers (time, user_id, answer_id) VALUES (%s, %d, %d)', date('Y-m-d H:i:s'), get_current_user_id(), $_POST['answer'] ));
            wp_redirect( add_query_arg( 'page', get_query_var('page', 1)+1 ), 301 );
            exit;
        }
        

        // Fetch the answers
        $answers = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'poq_answers WHERE question_id = %d', $question->id ), OBJECT );
        // Util variables
        $hasPrevious = $page <= 1 ? false : true;
        $questionCount = $wpdb->get_results( 'SELECT COUNT(*) as c FROM '.$wpdb->prefix.'poq_questions', OBJECT );
        $hasNext = $page >= $questionCount[0]->c ? false : true;
        require_once('views/ellak_poq_question.php');
    }
    
    private function showAnswerAction($page, $question, $userAnswer) {
        require_once('views/ellak_poq_answer.php');
    }
}