<?php

namespace AppBundle\Domain\Manager;

use AppBundle\Domain\Entity\Command;
use AppBundle\Domain\Entity\Ticket;
use AppBundle\Domain\Payload\PayloadFactory;
use AppBundle\Domain\Service\PriceCalculatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Charge;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\ValidatorBuilderInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CommandManager
{
    private $doctrine;
    private $validator;
    private $calculator;
    private $payload;

    public function __construct(
        EntityManagerInterface $doctrine,
        ValidatorBuilderInterface $validator,
        PriceCalculatorInterface $priceCalculator,
        PayloadFactory $payload
    )
    {
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->calculator = $priceCalculator;
        $this->payload = $payload;
    }

    /**
     * Start a command, with validation of the entity and saving in the session.
     *
     * @param $content
     * @return \AppBundle\Domain\Payload\BadRequest|\AppBundle\Domain\Payload\Found
     */
    public function createCommand($content)
    {
        $hydrator = new DoctrineHydrator($this->doctrine);

        $ticketRemaining = $this->doctrine->getRepository('AppBundle:Ticket')->getTicketsRemaining($content['entryAt']);

        if (count($content['tickets']) > $ticketRemaining) {
            return $this->payload->badRequest(['content' => 'Not enough ticket remaining']);
        }

        $command = new Command();
        $command->setType($content['type']);
        $command->setEmail($content['email']);
        $command->setEntryAt(\DateTime::createFromFormat('d/m/Y', $content['entryAt']));

        foreach ($content['tickets'] as $data) {
            $ticket = new Ticket();
            $ticket = $hydrator->hydrate($data, $ticket);
            $ticket->setCommand($command);
            $ticket->setEntryAt($command->getEntryAt());
        }

        $this->calculator->calculatePriceFromCommand($command);
        $errors = $this->validator->getValidator()->validate($command);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                array_push($messages, $error->getMessage());
            }
            return $this->payload->badRequest(['content' => (string) $messages[0]]);
        }

        $session = new Session();
        $session->set('command', $command);

        $message = ['content' => [
                'price' => $command->getPrice(),
                'nbTickets' => $command->getTickets()->count(),
                'started' => true
            ]
        ];

        return $this->payload->found($message);
    }

    /**
     * Payment for the command started.
     *
     * @param $token
     * @return \AppBundle\Domain\Payload\BadRequest|\AppBundle\Domain\Payload\Created
     */
    public function payment($token)
    {
        $session = new Session();
        $command = $session->get('command');

        if (!isset($command)) {
            return $this->payload->badRequest(['content' => 'Erreur commande déjà réglé, ou inexistante']);
        }


        Stripe::setApiKey("sk_test_ZejfvxMqrtcsR2P4A09QKR0i");

        $response = Charge::create([
            'amount' => $command->getPrice() * 100,
            'currency' => 'eur',
            'source' => $token,
            'metadata' => [
                'command_email' => $command->getEmail(),
            ],
            'description' => 'Ticketing, Museum of louvre',
        ]);

        if ($response->paid) {
            $command->setPayment(true);

            $this->doctrine->persist($command);
            $this->doctrine->flush();

            $session->remove('command');

            return $this->payload->created(['content' => 'Merci, votre paiement a été validé']);
        }
        return $this->payload->badRequest(['content' => 'Erreur lors du paiement']);
    }
}
