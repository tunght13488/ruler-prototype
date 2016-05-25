<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Ruler\Context;
use Ruler\RuleBuilder;
use Ruler\RuleSet;
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
        $sender = $request->get('sender');
        $postcode = $request->get('postcode');

        $response = new JsonResponse(['message' => 'Something wrong happened'], 500);

        /*
         * Register operators
         */
        $rb = new RuleBuilder();
        $rb->registerOperatorNamespace('AppBundle\Ruler\Operator');

        /*
         * Find user
         */
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $user = $userRepo->findOneBy(['facebookSenderId' => $sender]);

        /*
         * Create context
         */
        $context = new Context([
            'user' => $user,
            'postcode' => $postcode,
        ]);

        /*
         * Define rules
         */
        $userIsNull = $rb->create($rb['user']->equalTo(null), function () use (&$response) {
            $response = new JsonResponse(['message' => 'show_age_check']);
        });
        $postcodeIsCorrect = $rb->create($rb['postcode']->isPostcode());
        $userIsNotNullAndPostcodeIsCorrect = $rb->create($rb->logicalAnd(
            $rb->logicalNot($userIsNull),
            $postcodeIsCorrect
        ), function () use (&$response) {
            $response = new JsonResponse(['message' => 'show_location']);
        });
        $userIsNotNullAndPostcodeIsNotCorrect = $rb->create($rb->logicalAnd(
            $rb->logicalNot($userIsNull),
            $rb->logicalNot($postcodeIsCorrect)
        ), function () use (&$response) {
            $response = new JsonResponse(['message' => 'show_generic']);
        });

        /*
         * Build and run rule set
         */
        $ruleSet = new RuleSet([
            $userIsNull,
            $userIsNotNullAndPostcodeIsCorrect,
            $userIsNotNullAndPostcodeIsNotCorrect,
        ]);
        $ruleSet->executeRules($context);

        return $response;
    }
}
