<?php

namespace App\Tests\Message;

use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class SendMessageHandlerTest extends WebTestCase
{
    use InteractsWithMessenger;
    private SendMessageHandler $handler;
    private MessageRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var SendMessageHandler $handler */
        $handler = $this->getContainer()->get(SendMessageHandler::class);
        $this->handler = $handler;

        /** @var MessageRepository $repository */
        $repository = $this->getContainer()->get(MessageRepository::class);
        $this->repository = $repository;

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $entityManager->beginTransaction();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_invocation_saves_message(): void
    {
        $messages = $this->repository->findAll();
        $this->assertEmpty($messages);

        $messageText = 'test_invocation_saves_message';

        $message = new SendMessage($messageText);

        $this->handler->__invoke($message);

        $message = $this->repository->findOneBy(['text' => $messageText]);

        $this->assertNotNull($message);
    }

    public function test_handler_gets_invoked(): void
    {
        $this->transport('sync')->send(new SendMessage('test_handler_gets_invoked'));
        $this->transport('sync')->process();

        $message = $this->repository->findOneBy(['text' => 'test_handler_gets_invoked']);
        $this->assertNotNull($message);
    }
}
