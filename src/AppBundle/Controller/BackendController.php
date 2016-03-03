<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
            $this->container->get('spss.importer')->import($SPSS, $request->files->get('fileToUpload')->getClientOriginalName());

            return $this->render('AppBundle:Backend:import_results.html.twig', array(
                'filename' => $request->files->get('fileToUpload')->getClientOriginalName(),
                'spss' => $SPSS,
            ));
        }
        return $this->render('AppBundle:Backend:import.html.twig', array());
    }
}
