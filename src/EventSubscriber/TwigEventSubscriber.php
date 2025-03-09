<?php

namespace App\EventSubscriber;

use App\Form\SearchType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $searchForm = $this->formFactory->create(SearchType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false
        ]);

        $this->twig->addGlobal('search_form', $searchForm->createView());
    }
}
