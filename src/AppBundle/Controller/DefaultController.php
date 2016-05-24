<?php

namespace AppBundle\Controller;

use Hoa\Ruler\Context;
use Hoa\Ruler\Exception\Asserter;
use Hoa\Ruler\Ruler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/", methods={"POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkAction(Request $request)
    {
        $data = $request->request->all();

        $ruler = new Ruler();
        $rule = 'group in ["customer", "guest"] and points > 30';
        $context = new Context($data);

        try {
            $valid = $ruler->assert($rule, $context);
        } catch (Asserter $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        return new JsonResponse([
            'valid' => $valid,
            'data' => $data,
        ]);
    }
}
