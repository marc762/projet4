<?php


namespace AppBundle\Responder;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class HomeResponder extends AbstractResponder
{
    private $twig;

    /**
     * HomeResponder constructor.
     * @param EngineInterface $twig
     */
    public function __construct(EngineInterface $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return Response
     */
    public function __invoke()
    {
        return new Response($this->twig->render(':default:index.html.twig'));
    }
}
