<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\UserAnswer;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle::index.html.twig', array(
            'page' => 1,
            'questionCount' => 63,
        ));
    }

    /**
     * @Route("/question/{page}", name="question")
     */
    public function questionAction($page, Request $request)
    {
        $question = $this->getQuestion($page);

        if($request->getMethod() == 'POST') {
            $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('sessionId' => $request->getSession()->getId()));
            $answer = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\Answer')->find($request->get('answer'));
            if(!$answer || $answer->getQuestion() != $question) {
                throw new \Exception('Invalid answer selected: '.$request->get('answer'));
            }
            $userAnswer = $this->container->get('doctrine')->getManager()->createQuery('SELECT ua FROM AppBundle\Entity\UserAnswer ua JOIN ua.answer a WHERE ua.user = :user AND a.question = :question')->setParameter('user', $user)->setParameter('question', $question)->getOneOrNullResult();
            if(!$userAnswer) {
                $userAnswer = new UserAnswer();
            }
            $userAnswer->setUser($user);
            $userAnswer->setAnswer($answer);
            $this->container->get('doctrine')->getManager()->persist($userAnswer);
            $this->container->get('doctrine')->getManager()->flush($userAnswer);
            return new RedirectResponse($this->container->get('router')->generate('answer', array('page' => $page)));
        }

        return $this->render('AppBundle::question.html.twig', array(
            'question' => $question,
            'page' => $page,
            'hasPrevious' => $page <= 1 ? false : true,
            'questionCount' => 63,
        ));
    }

    /**
     * @Route("/answer/{page}", name="answer")
     */
    public function answerAction($page, Request $request) {
        $question = $this->getQuestion($page);
        $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('sessionId' => $request->getSession()->getId()));
        $answer = $this->container->get('doctrine')->getManager()->createQuery('SELECT ua FROM AppBundle\Entity\UserAnswer ua JOIN ua.answer a WHERE a.question = :question and ua.user = :user')->setParameter('user', $user)->setParameter('question', $question)->getResult();
        if(count($answer) <= 0) { throw new \Exception('Answer not found!'); }
        $answer = reset($answer);
        $answer = $answer->getAnswer();
        // Process answer stats
        $answerStats = $this->container->get('app.answer.manager')->getAnswerStatsFlattenedSorted($answer);
        $answerStatsProcessed = array();
        for($i = 0; $i <= 1; $i++) {
            $value = reset($answerStats);
            $key = key($answerStats);
            unset($answerStats[$key]);
            $answerStatsProcessed[] = array('label' => $key, 'value' => $value);
        }
        for($i = 0; $i <= 1; $i++) {
            $value = end($answerStats);
            $key = key($answerStats);
            unset($answerStats[$key]);
            $answerStatsProcessed[] = array('label' => $key, 'value' => $value);
        }
        return $this->render('AppBundle::answer.html.twig', array(
            'question' => $question,
            'answer' => $answer,
            'answerStatsProcessed' => $answerStatsProcessed,
            'page' => $page,
            'hasPrevious' => $page <= 1 ? false : true,
            'questionCount' => 63,
        ));
    }

    /**
     * @Route("/pause", name="pause")
     */
    public function pauseAction(Request $request) {
        $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('sessionId' => $request->getSession()->getId()));
        if($request->getMethod() == 'POST') {
            $user->setEmail($request->get('email'));
            $validator = $this->get('validator');
            $errors = $validator->validate($user);
            if (count($errors) <= 0) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Hello Email')
                    ->setFrom('info@poq.ellak.gr')
                    ->setTo($request->get('email'))
                    ->setBody('Για να συνεχίσεις το ερωτηματολόγιο επισκέψου τον σύνδεσμο '.$this->container->get('router')->generate('resume', array(
                        'email' => $request->get('email'),
                    ), true), 'text/html')
                ;
                $this->get('mailer')->send($message);
                $this->container->get('doctrine')->getManager()->persist($user);
                $this->container->get('doctrine')->getManager()->flush($user);
                return $this->render('AppBundle::pause_success.html.twig', array(
                    'page' => 1,
                    'questionCount' => 63,
                    'email' => $request->get('email'),
                ));
            }
        }
        return $this->render('AppBundle::pause.html.twig', array(
            'page' => 1,
            'questionCount' => 63,
        ));
    }

    /**
     * @Route("/resume/{email}", name="resume")
     */
    public function resumeAction($email, Request $request) {
        $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $email));
        if(!$user) { echo 'User not found to resume'; die(); }
        $user->setSessionId($request->getSession()->getId());
        $page = $this->container->get('doctrine')->getManager()->createQuery('SELECT COUNT(ua.id) FROM AppBundle\Entity\UserAnswer ua WHERE ua.user = :user')->setParameter('user', $user)->getSingleScalarResult();
        $this->container->get('doctrine')->getManager()->persist($user);
        $this->container->get('doctrine')->getManager()->flush($user);
        return new RedirectResponse($this->container->get('router')->generate('question', array(
            'page' => $page,
        )));
    }

    private function getQuestion($page) {
        if($page < 1) { $page = 1; }
        $question = $this->container->get('doctrine')->getManager()->createQuery('SELECT q FROM AppBundle\Entity\Question q')->setMaxResults(1)->setFirstResult($page-1)->getResult();
        $question = reset($question);
        return $question;
    }
}
