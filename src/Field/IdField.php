<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class IdField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/id')
            ->setFormType(TextType::class)
            ->addCssClass('field-id');
    }
}
