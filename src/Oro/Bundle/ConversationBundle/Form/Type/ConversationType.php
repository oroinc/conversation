<?php

namespace Oro\Bundle\ConversationBundle\Form\Type;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserSelectType;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for conversation entity.
 */
class ConversationType extends AbstractType
{
    private ManagerRegistry $doctrine;
    private EntityRoutingHelper $entityRoutingHelper;
    private RequestStack $requestStack;

    public function __construct(
        ManagerRegistry $doctrine,
        EntityRoutingHelper $entityRoutingHelper,
        RequestStack $requestStack
    ) {
        $this->doctrine = $doctrine;
        $this->entityRoutingHelper = $entityRoutingHelper;
        $this->requestStack = $requestStack;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'customerUser',
                CustomerUserSelectType::class,
                [
                    'label' => 'oro.conversation.customer_user.label',
                    'required' => true,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'label'    => 'oro.conversation.name.label'
                ]
            )->add(
                'source',
                ConversationTargetSelectType::class,
                [
                    'required' => false,
                    'mapped' => false,
                    'label'    => 'oro.conversation.source.label',
                    'error_bubbling' => false,
                    'placeholder' => 'oro.conversation.source.placeholder',
                    'autocomplete_route_name' => 'oro_conversation_source_autocomplete_search',
                    'autocomplete_route_parameters' => ['name' => 'conversations'],
                    'dialog_route' => 'oro_conversation_source_grid_dialog',
                    'dialog_title' => 'oro.conversation.source.dialogue.label'
                ]
            );

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->addSourceValueToForm($event);
        });

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $this->setSourceValue($event);
            },
            100 // the listener should be executed before validation listener
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Conversation::class
            ]
        );
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_conversation';
    }

    /**
     * Adds source value to form.
     */
    private function addSourceValueToForm(FormEvent $event): void
    {
        /** @var Conversation $conversation */
        $conversation = $event->getData();
        $form = $event->getForm();

        if ($conversation) {
            if ($conversation->getId() && $conversation->getSourceEntityId()) {
                $sourceId = $conversation->getSourceEntityId();
                $form->get('source')->setData(
                    $this->doctrine->getRepository($conversation->getSourceEntityClass())->find($sourceId)
                );
            } else {
                // adds the source value if conversation starts from target entity
                $request = $this->requestStack->getCurrentRequest();
                $targetEntityClass = $this->entityRoutingHelper->getEntityClassName($request);
                $targetEntityId = $this->entityRoutingHelper->getEntityId($request);

                if ($targetEntityClass && $request->getMethod() === 'GET') {
                    $form->get('source')->setData(
                        $this->doctrine->getRepository($targetEntityClass)->find($targetEntityId)
                    );
                }
            }
        }
    }

    /**
     * Sets the source information to the conversation entity.
     */
    private function setSourceValue(FormEvent $event): void
    {
        if (!$event->getForm()->has('source')) {
            return;
        }

        $sourceClass = $sourceId = null;
        $source = $event->getForm()->get('source')->getData();
        if ($source) {
            $sourceClass = ClassUtils::getClass($source);
            $sourceId = $source->getId();
        }

        /** @var Conversation $conversation */
        $conversation = $event->getData();
        $conversation->setSourceEntity($sourceClass, $sourceId);
    }
}
