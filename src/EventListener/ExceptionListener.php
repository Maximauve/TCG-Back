<?php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: 'kernel.exception')]
readonly class ExceptionListener
{
    public function __construct(
        private TranslatorInterface $translator,
        private LoggerInterface $logger
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $this->logger->error($exception->getMessage(), [
            'exception' => $exception
        ]);

        $message = $this->translator->trans('unexpected_error');
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $response = new JsonResponse([
            'error' => $message,
            'message' => $exception->getMessage(),
        ], $statusCode);

        $event->setResponse($response);
    }
}

