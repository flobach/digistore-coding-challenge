<?php
declare(strict_types=1);

namespace Controller;

use App\Entity\Message;
use App\Message\SendMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
        $this->entityManager->beginTransaction();

    }

    function test_list_action_no_filtering(): void
    {
        $this->loadFixtures();

        $this->client->request('GET', '/messages');
        /** @var array<string, string> $messages */
        ['messages' => $messages] = (array) json_decode($this->client->getResponse()->getContent() ?: '', true);
        $this->assertCount(3, $messages);
    }

    public function statusFilterDataProvider(): \Generator
    {
        yield ['status' => 'sent', 'expectedResults' => 2];
        yield ['status' => 'read', 'expectedResults' => 1];
    }

    /**
     * @dataProvider statusFilterDataProvider
     */
    function test_list_action_status_filtering(string $status, int $expectedResults): void
    {
        $this->loadFixtures();
        $this->client->request('GET', '/messages', ['status' => $status]);
        /** @var array<string, string> $messages */
        ['messages' => $messages] = (array) json_decode($this->client->getResponse()->getContent() ?: '', true);
        $this->assertCount($expectedResults, $messages);
    }

    function test_list_action_validates_status(): void
    {
        $this->client->request('GET', '/messages', ['status' => 'invalid']);
        /** @var array<string, string> $response */
        $response = (array) json_decode($this->client->getResponse()->getContent() ?: '', true);
        // Don't check exact message as it might change
        $this->assertArrayHasKey('status', $response);
    }
    
    function test_send_action_sends_a_message(): void
    {
        $this->client->request('POST', '/messages/send', [], [], [], json_encode([
            'text' => 'Hello World',
        ]) ?: null);

        $this->assertResponseIsSuccessful();
        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertContains(SendMessage::class, 1);
    }

    function test_send_action_validates_body(): void
    {
        $this->client->request('POST', '/messages/send', [], [], [], json_encode([
            'no-text' => 'Hello World',
        ]) ?: null);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertNotContains(SendMessage::class);
    }

    private function loadFixtures(): void
    {
        $now = new \DateTime('now');
        $message1 = (new Message())->setText('Hello-1')->setStatus('sent')->setUuid(Uuid::v6()->toRfc4122())->setCreatedAt($now);
        $message2 = (new Message())->setText('Hello-2')->setStatus('sent')->setUuid(Uuid::v6()->toRfc4122())->setCreatedAt($now);
        $message3 = (new Message())->setText('Hello-3')->setStatus('read')->setUuid(Uuid::v6()->toRfc4122())->setCreatedAt($now);
        $this->entityManager->persist($message1);
        $this->entityManager->persist($message2);
        $this->entityManager->persist($message3);
        $this->entityManager->flush();
    }
}