<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\SelectField;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class SelectConfigurator implements FieldConfiguratorInterface
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return SelectField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $choices = $field->getCustomOption(SelectField::OPTION_CHOICES);
        if (empty($choices)) {
            throw new \InvalidArgumentException(sprintf('The "%s" select field must define its possible choices using the setChoices() method.', $field->getProperty()));
        }

        $translatedChoices = [];
        $translationParameters = $context->getI18n()->getTranslationParameters();
        foreach ($choices as $key => $value) {
            $translatedKey = $this->translator->trans($key, $translationParameters);
            $translatedChoices[$translatedKey] = $value;
        }
        $field->setFormTypeOptionIfNotSet('choices', $translatedChoices);

        if (null !== $value = $field->getValue()) {
            $selectedChoice = array_flip($choices)[$value];
            $field->setFormattedValue($this->translator->trans($selectedChoice, $translationParameters));
        }

        if (true === $field->getCustomOption(SelectField::OPTION_AUTOCOMPLETE)) {
            $field->setFormTypeOptionIfNotSet('attr.data-widget', 'select2');
        }
    }
}
