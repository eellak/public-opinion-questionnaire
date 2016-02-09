<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\Dataset;

class BackendController extends Controller
{
    /**
     * @Route("/backend", name="backend")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Backend:index.html.twig', array());
    }

    /**
     * @Route("/backend/import", name="backend_import")
     */
    public function importAction(Request $request)
    {
        if($request->getMethod() == 'POST' && $request->files->get('fileToUpload') != null) {
            $SPSS = new \SPSSReader($request->files->get('fileToUpload')->getPathname());
            $dataset = new Dataset();
            $dataset->setFilename($request->files->get('fileToUpload')->getClientOriginalName());
            //$profileDimensions = $this->setupProfileDimensions($SPSS);
            //$questions = $this->setupQuestions($SPSS);

            return $this->render('AppBundle:Backend:import_results.html.twig', array(
                'filename' => $dataset->getFilename(),
                'spss' => $SPSS,
            ));
        }
        return $this->render('AppBundle:Backend:import.html.twig', array());
    }

    /*private function setupQuestions(\SPSSReader $SPSS) {
        $questions = array();
        foreach($SPSS->variables as $var) {
            $var->getLabel()
        }
    }*/
}
