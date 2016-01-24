<?php
class EllakPoQController {
    public function routeRequest() {
        global $wp_query, $wpdb;
        ob_start();
        if($wp_query->query_vars['page'] == 0) {
            $this->showIndexAction();
        } else {
            // Fetch the question
            $question = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'poq_questions LIMIT 1 OFFSET %d', $wp_query->query_vars['page']-1 ), OBJECT);
            $question = $question[0];
            // Fetch the answers
            $answers = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'poq_answers WHERE question_id = %d', $question->id ), OBJECT );
            // Check if we have already answered this question
            $userAnswers = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'poq_user_answers pua JOIN '.$wpdb->prefix.'poq_answers pa ON pua.answer_id = pa.id WHERE pa.question_id = %d', $question->id ), OBJECT );
            if(count($userAnswers) <= 0) {
                $this->showQuestionAction($wp_query->query_vars['page'], $question, $answers);
            } else {
                $userAnswers = $userAnswers[0];
                $this->showAnswerAction($wp_query->query_vars['page'], $question, $answers, $userAnswers);
            }
        }
        return ob_get_clean();
    }
    
    private function showIndexAction() {
        $data = Timber::get_context();
        Timber::render('views/ellak_poq_index.html.twig', $data);
    }
    
    private function showQuestionAction($page, $question, $answers) {
        global $wpdb;
        // Handle POST - User has submitted an answer
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $wpdb->query($wpdb->prepare( 'INSERT INTO '.$wpdb->prefix.'poq_user_answers (time, session_id, user_id, answer_id) VALUES (%s, %s, %d, %d)', date('Y-m-d H:i:s'), $_COOKIE['ellak-poq-session'], get_current_user_id(), $_POST['answer'] ));
            wp_redirect( add_query_arg( 'page', get_query_var('page', 1)+1 ), 301 );
            exit;
        }

        $data = Timber::get_context();
        $data['question'] = $question;
        $data['answers'] = $answers;
        Timber::render('views/ellak_poq_question.html.twig', $data);
    }
    
    private function showAnswerAction($page, $question, $answers, $userAnswer) {
        $data = Timber::get_context();
        $data['question'] = $question;
        $data['answers'] = $answers;
        $data['userAnswer'] = $userAnswer;
        Timber::render('views/ellak_poq_answer.html.twig', $data);
    }
}

function add_to_twig($twig) {
    global $wp_query, $wpdb;
    $the_page_name = get_option( "ellak_poq_page_name" );
    $twig->addFunction(new Twig_SimpleFunction('add_query_arg', 'add_query_arg'));
    $twig->addFunction(new Twig_SimpleFunction('get_query_var', 'get_query_var'));
    // Execute query only when we are in the questionnaire page
    if ( is_page( $the_page_name ) ) {
        $page = $wp_query->query_vars['page'];
        $questionCount = $wpdb->get_results( 'SELECT COUNT(*) as c FROM '.$wpdb->prefix.'poq_questions', OBJECT );
        $twig->addGlobal('questionCount', $questionCount[0]->c);
        $twig->addGlobal('page', $page);
        $twig->addGlobal('hasPrevious', $page <= 1 ? false : true);
        $twig->addGlobal('hasNext', $page >= $questionCount[0]->c ? false : true);
    }
    return $twig;
}
add_filter('get_twig', 'add_to_twig');