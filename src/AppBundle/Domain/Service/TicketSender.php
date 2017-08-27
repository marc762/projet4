<?php


namespace AppBundle\Domain\Service;


use AppBundle\Domain\Entity\Command;
use Symfony\Component\Templating\EngineInterface;

class TicketSender
{
    private $mailer;
    private $twig;

    /**
     * TicketSender constructor.
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $twig
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * send a email with the ticket of the reservation.
     *
     * @param $data
     * @return void
     */
    public function send(Command $data)
    {
        $numCommand = (string) $data->getId();
        $numCommand .= $data->getEntryAt()->format('dmy');

        $message = (new \Swift_Message('Ticket'))
            ->setFrom('marc.arnoult76@gmail.com')
            ->setTo($data->getEmail())
            ->setBody(
                $this->twig->render(
                    'command.html.twig', [
                        'command' => $data,
                        'numCommand' => $numCommand
                    ]),
                'text/html'
            );

        $this->mailer->send($message);
    }
}
