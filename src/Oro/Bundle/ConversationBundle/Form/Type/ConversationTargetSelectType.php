<?php

namespace Oro\Bundle\ConversationBundle\Form\Type;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type that allow to select entity for multi-target association field.
 */
class ConversationTargetSelectType extends AbstractType
{
    private DataTransformerInterface $transformer;
    private ParticipantInfoProvider $participantInfoProvider;

    public function __construct(
        DataTransformerInterface $transformer,
        ParticipantInfoProvider $participantInfoProvider
    ) {
        $this->transformer = $transformer;
        $this->participantInfoProvider = $participantInfoProvider;
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($form->getData()) {
            $view->vars['attr']['data-selected-data'] = $this->getSelectedData($form);
        }
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'placeholder' => '',
                'autocomplete_route_name' => '',
                'autocomplete_route_parameters' => [],
                'dialog_route' => '',
                'dialog_title' => '',
                'configs' => function (Options $options, $value) {
                    return [
                        'renderedPropertyName' => 'text',
                        'allowClear' => true,
                        'placeholder' => $options['placeholder'],
                        'per_page' => 10,
                        'dropdownCssClass' => 'target-select-autocomplete',
                        'route_name' => $options['autocomplete_route_name'],
                        'route_parameters' => $options['autocomplete_route_parameters'],
                        'dialog_route' => $options['dialog_route'],
                        'dialog_title' => $options['dialog_title'],
                    ];
                },
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return Select2HiddenType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_conversation_target_select';
    }

    private function getSelectedData(FormInterface $form): string
    {
        $target = $form->getData();
        $targetClass = ClassUtils::getClass($target);

        $title = $this->participantInfoProvider->getTitle($target);
        $label = $this->participantInfoProvider->getTypeString($target);
        if ($label) {
            $title .= ' (' . $label . ')';
        }

        $item = [
            'id' => json_encode(
                ['entityClass' => $targetClass, 'entityId' => $target->getId()],
                JSON_THROW_ON_ERROR
            ),
            'text' => $title
        ];

        return json_encode($item, JSON_THROW_ON_ERROR);
    }
}
