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
            $userAnswer = $this->container->get('doctrine')->getManager()->getRepository('AppBundle\Entity\UserAnswer')->findOneBy(array(
                'user' => $user,
                'answer' => $answer,
            ));
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
        $answer = $this->container->get('doctrine')->getManager()->createQuery('SELECT q FROM AppBundle\Entity\UserAnswer HIDDEN ua JOIN ua.answer a WHERE a.question = :question and ua.user = :user')->setParameter('user', $user)->setParameter('question', $question);
        if(count($answer) <= 0) { throw new \Exception('Answer not found!'); }
        $answer = reset($answer);
        return $this->render('AppBundle::answer.html.twig', array(
            'question' => $question,
            'answer' => $question,
            'page' => $page,
            'hasPrevious' => $page <= 1 ? false : true,
            'questionCount' => 63,
        ));
    }

    private function getQuestion($page) {
        $question = $this->container->get('doctrine')->getManager()->createQuery('SELECT q FROM AppBundle\Entity\Question q')->setMaxResults(1)->setFirstResult($page-1)->getResult();
        $question = reset($question);
        return $question;
    }
}
