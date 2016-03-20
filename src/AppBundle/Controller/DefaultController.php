<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\Section;
use AppBundle\Entity\UserAnswer;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        $sections = $this->container->get('doctrine')->getRepository('AppBundle\Entity\Section')->findAll();
        return $this->render('AppBundle::index.html.twig', array(
            'sections' => $sections,
        ));
    }

    /**
     * @Route("/section", name="select_section")
     */
    public function selectSectionAction(Request $request) {
        $sections = $this->container->get('doctrine')->getRepository('AppBundle\Entity\Section')->findAll();
        return $this->render('AppBundle::select_section.html.twig', array(
            'sections' => $sections,
        ));
    }

    /**
     * @Route("/section/{section}/question/{page}", name="question")
     */
    public function questionAction(Section $section, $page, Request $request)
    {
        $question = $this->getQuestion($section, $page);

        if($request->getMethod() == 'POST' && $request->get('answer') != null) {
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
            return new RedirectResponse($this->container->get('router')->generate('answer', array('section' => $section->getId(), 'page' => $page)));
        }

        return $this->render('AppBundle::question.html.twig', array(
            'question' => $question,
            'page' => $page,
            'hasPrevious' => $page <= 1 ? false : true,
            'section' => $section,
        ));
    }

    /**
     * @Route("/section/{section}/answer/{page}", name="answer")
     */
    public function answerAction(Section $section, $page, Request $request) {
        $question = $this->getQuestion($section, $page);
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
            'section' => $section,
        ));
    }

    /**
     * @Route("/section_results/{section}", name="section_results")
     */
    public function sectionResultsAction(Section $section, Request $request) {
        $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('sessionId' => $request->getSession()->getId()));
        // Process answer stats
        $answerStats = $this->container->get('app.section.manager')->getSectionStatsFlattenedSorted($section, $user);
        $answerStatsProcessed = array();
        $shownDimensions = array();
        $i = 0;
        foreach(array_reverse($answerStats) as $key => $value) {
            $dimension = explode('.', $key);
            if(in_array($dimension[0], $shownDimensions)) { continue; }
            $answerStatsProcessed[] = array('label' => $key, 'value' => $value);
            $shownDimensions[] = $dimension[0];
            $i++;
            if($i > 3) { break; }
        }
        $nextSection = $this->container->get('doctrine')->getRepository(get_class($section))->find($section->getId()+1);
        return $this->render('AppBundle::section_results.html.twig', array(
            'section' => $section,
            'nextSection' => $nextSection,
            'answerStats' => $answerStatsProcessed,
            'page' => $section->getQuestions()->count(),
        ));
    }

    /**
     * @Route("/final_results", name="final_results")
     */
    public function finalResultsAction(Request $request) {
        $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('sessionId' => $request->getSession()->getId()));
        // Process answer stats
        $answerStats = $this->container->get('app.section.manager')->getSectionStats(null, $user);
        return $this->render('AppBundle::final_results.html.twig', array(
            'answerStats' => $answerStats,
        ));
    }

    /**
     * @Route("/pause/{section}", name="pause")
     */
    public function pauseAction(Section $section, Request $request) {
        $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('sessionId' => $request->getSession()->getId()));
        if($request->getMethod() == 'POST') {
            $user->setEmail($request->get('email'));
            $validator = $this->get('validator');
            $errors = $validator->validate($user);
            if (count($errors) <= 0) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Συνέχιση ερωτηματολογίου')
                    ->setFrom('info@poq.ellak.gr')
                    ->setTo($request->get('email'))
                    ->setBody('Για να συνεχίσετε το ερωτηματολόγιο, επισκεφθείτε το σύνδεσμο '.$this->container->get('router')->generate('resume', array(
                        'section' => $section->getId(),
                        'email' => $request->get('email'),
                    ), true), 'text/html')
                ;
                $this->get('mailer')->send($message);
                $this->container->get('doctrine')->getManager()->persist($user);
                $this->container->get('doctrine')->getManager()->flush($user);
                return $this->render('AppBundle::pause_success.html.twig', array(
                    'section' => $section,
                    'email' => $request->get('email'),
                ));
            }
        }
        return $this->render('AppBundle::pause.html.twig', array(
            'section' => $section,
        ));
    }

    /**
     * @Route("/resume/{section}", name="resume")
     */
    public function resumeAction(Section $section, Request $request) {
        if($this->getRequest()->get('email') != null) {
            $email = $this->getRequest()->get('email');
            $user = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $email));
            if(!$user) { echo 'User not found to resume'; die(); }
            $user->setSessionId($request->getSession()->getId());
            $page = $this->container->get('doctrine')->getManager()->createQuery('SELECT COUNT(ua.id) FROM AppBundle\Entity\UserAnswer ua JOIN ua.answer a JOIn a.question q WHERE q.section = :section AND ua.user = :user')->setParameter('section', $section)->setParameter('user', $user)->getSingleScalarResult();
            $this->container->get('doctrine')->getManager()->persist($user);
            $this->container->get('doctrine')->getManager()->flush($user);
            return new RedirectResponse($this->container->get('router')->generate('question', array(
                'section' => $section->getId(),
                'page' => $page,
            )));
        } else {
            return $this->render('AppBundle::resume.html.twig', array(
                'section' => $section,
            ));
        }
    }

    private function getQuestion(Section $section, $page) {
        if($page < 1) { $page = 1; }
        $question = $this->container->get('doctrine')->getManager()->createQuery('SELECT q FROM AppBundle\Entity\Question q WHERE q.section = :section')->setParameter('section', $section)->setMaxResults(1)->setFirstResult($page-1)->getResult();
        $question = reset($question);
        return $question;
    }
}
