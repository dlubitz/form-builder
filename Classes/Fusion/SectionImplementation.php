<?php
namespace Neos\Form\Builder\Fusion;

use Neos\Flow\Validation\Validator\NotEmptyValidator;
use Neos\Form\Core\Model\Page;
use Neos\Form\FormElements\Section;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class SectionImplementation extends AbstractFusionObject
{

    public function getPath(): string
    {
        return $this->path;
    }

    public function evaluate()
    {
        $context = $this->runtime->getCurrentContext();
        // TODO error handling if "parentRenderable" is not available
        /** @var Page $renderable */
        $renderable = $context['parentRenderable'];

        /** @var Section $sectionElement */
        $sectionElement = $renderable->createElement($this->getIdentifier(), $this->getFormElementType());
        $sectionElement->setLabel($this->getLabel());

        foreach ($this->getProperties() as $propertyName => $propertyValue) {
            $sectionElement->setProperty($propertyName, $propertyValue);
        }
        foreach ($this->getRenderingOptions() as $optionName => $optionValue) {
            $sectionElement->setRenderingOption($optionName, $optionValue);
        }

        if ($this->isRequired()) {
            $sectionElement->addValidator(new NotEmptyValidator());
        }

        $this->runtime->pushContext('element', $sectionElement);
        $this->runtime->evaluate($this->path . '/validators');
        $this->runtime->popContext();

        $this->runtime->pushContext('parentRenderable', $sectionElement);
        $this->runtime->evaluate($this->path . '/elements');
        $this->runtime->popContext();
    }

    private function getFormElementType(): string
    {
        return $this->fusionValue('formElementType');
    }

    private function getIdentifier(): string
    {
        $identifier = $this->fusionValue('identifier');
        // HACK is there a cleaner way to determine the element "key"
        if ($identifier === null) {
            preg_match('/\/([^\/<>]+)(?!.*\/)/', $this->path, $matches);
            $identifier = $matches[1];
        }
        return $identifier;
    }

    /**
     * @return string|null
     */
    private function getLabel()
    {
        return $this->fusionValue('label');
    }

    private function getProperties(): array
    {
        return $this->fusionValue('properties');
    }

    private function getRenderingOptions(): array
    {
        return $this->fusionValue('renderingOptions');
    }

    private function isRequired(): bool
    {
        return (bool)$this->fusionValue('required');
    }
}