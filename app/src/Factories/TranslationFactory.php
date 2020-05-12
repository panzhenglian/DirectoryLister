<?php

namespace App\Factories;

use DI\Container;
use RuntimeException;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tightenco\Collect\Support\Collection;

class TranslationFactory
{
    /** @var Container The applicaiton container */
    protected $container;

    /**
     * Create a new TranslationFactory object.
     *
     * @param \DI\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Initialize and return the translation component.
     *
     * @return \Symfony\Contracts\Translation\TranslatorInterface
     */
    public function __invoke(): TranslatorInterface
    {
        $language = $this->container->get('language');

        if (! in_array($language, $this->container->get('translations'))) {
            throw new RuntimeException("Invalid language option '{$language}'");
        }

        $translator = new Translator($language);
        $translator->addLoader('yaml', new YamlFileLoader());

        Collection::make($this->container->get('translations'))->each(
            function (string $language) use ($translator): void {
                $resource = sprintf($this->container->get('translations_path') . '/%s.yaml', $language);
                $translator->addResource('yaml', $resource, $language);
            }
        );

        return $translator;
    }
}
