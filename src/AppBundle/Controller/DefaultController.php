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
     * @var \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected $checkResponse;

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
        $this->checkResponse = new JsonResponse(['message' => 'Something wrong happened'], 500);

        $sender = $request->get('sender');
        $postcode = $request->get('postcode');

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
         * Define actions
         */
        $showAgeCheck = [$this, 'showAgeCheck'];
        $showLocation = [$this, 'showLocation'];
        $showGeneric = [$this, 'showGeneric'];

        /*
         * Define conditions
         */
        $conditionUserIsNull = $rb->create($rb['user']->equalTo(null));
        $conditionUserIsNull = serialize($conditionUserIsNull);
        $conditionUserIsNull = unserialize($conditionUserIsNull);
        $conditionPostcodeIsCorrect = $rb->create($rb['postcode']->isPostcode());
        $conditionUserIsNotNullAndPostcodeIsCorrect = $rb->create(
            $rb->logicalAnd(
                $rb->logicalNot($conditionUserIsNull),
                $conditionPostcodeIsCorrect
            )
        );
        $userIsNotNullAndPostcodeIsNotCorrect = $rb->create(
            $rb->logicalAnd(
                $rb->logicalNot($conditionUserIsNull),
                $rb->logicalNot($conditionPostcodeIsCorrect)
            )
        );

        /*
         * Define rules (condition and action)
         */
        $ruleFirstVisit = $rb->create($conditionUserIsNull, $showAgeCheck);
        $ruleGetLocation = $rb->create($conditionUserIsNotNullAndPostcodeIsCorrect, $showLocation);
        $ruleGeneric = $rb->create($userIsNotNullAndPostcodeIsNotCorrect, $showGeneric);

        /*
         * Build and run rule set
         */
        $ruleSet = new RuleSet([
            $ruleFirstVisit,
            $ruleGetLocation,
            $ruleGeneric,
        ]);

        // $ruleSet = serialize($ruleSet);
        // $ruleSet = unserialize($ruleSet);

        $ruleSet->executeRules($context);

        return $this->checkResponse;
    }

    /**
     * @throws \Exception
     */
    public function showAgeCheck()
    {
        $this->checkResponse->setData(['message' => 'show_age_check'])->setStatusCode(200);
    }

    /**
     * @throws \Exception
     */
    public function showLocation()
    {
        $this->checkResponse->setData(['message' => 'show_location'])->setStatusCode(200);
    }

    /**
     * @throws \Exception
     */
    public function showGeneric()
    {
        $this->checkResponse->setData(['message' => 'show_generic'])->setStatusCode(200);
    }
}
