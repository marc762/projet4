<?php

namespace AppBundle\Action\Api;


use AppBundle\Domain\Manager\CommandManager;
use AppBundle\Responder\Api\CommandResponder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommandCreateAction
{
    private $responder;
    private $manager;

    public function __construct(CommandResponder $responder, CommandManager $manager)
    {
        $this->responder = $responder;
        $this->manager = $manager;
    }

    /**
     * @Route("/api/command")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $payload = $this->manager->createCommand($data);

        $this->responder->setPayload($payload);

        return $this->responder->__invoke();
    }
}
