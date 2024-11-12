<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\MessageSendRequest;
use App\Dto\Request\MessagesListRequest;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Repository\MessageRepository;
use App\Services\Mapper\MessageMapper;
use Controller\MessageControllerTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see MessageControllerTest
 * TODO: review both methods and also the `openapi.yaml` specification
 *       Add Comments for your Code-Review, so that the developer can understand why changes are needed.
 */
class MessageController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * TODO: cover this method with tests, and refactor the code (including other files that need to be refactored)
     */
    #[Route('/messages', methods: ['GET'])]
    public function list(Request $request, MessageRepository $messageRepository): Response
    {
        $requestObject = new MessagesListRequest($request);
        $violations = $this->validator->validate($requestObject);
        if ($violations->count() > 0) {
            throw new BadRequestHttpException(json_encode([$violations->get(0)->getPropertyPath() => $violations->get(0)->getMessage()]) ?: '');
        }

        $messages = $messageRepository->searchBy(
            status: $requestObject->getStatus()
        );

        $response = array_map(static fn (Message $message) => MessageMapper::mapEntityToDto($message), $messages);
        
        return new Response(json_encode([
            'messages' => $response,
        ], JSON_THROW_ON_ERROR), headers: ['Content-Type' => 'application/json']);
    }

    #[Route('/messages/send', methods: ['POST'])]
    public function send(Request $request, MessageBusInterface $bus): Response
    {
        /** @var MessageSendRequest $requestObject */
        $requestObject = $this->serializer->deserialize($request->getContent(), MessageSendRequest::class, 'json');

        $violations = $this->validator->validate($requestObject);
        if ($violations->count() > 0) {
            throw new BadRequestHttpException(json_encode([$violations->get(0)->getPropertyPath() => $violations->get(0)->getMessage()]) ?: '');
        }

        if (null !== $requestObject->getText()) {
            $bus->dispatch(new SendMessage($requestObject->getText()));
        }
        
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}