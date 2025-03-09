<?php

namespace App\Twig;

use App\Form\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('search_form_factory', [$this, 'createSearchForm'])
        ];
    }

    public function createSearchForm()
    {
        $form = $this->formFactory->create(SearchType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false
        ]);

        return $form->createView();
    }
}
