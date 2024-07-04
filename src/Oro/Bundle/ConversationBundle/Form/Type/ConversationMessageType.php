<?php

namespace Oro\Bundle\ConversationBundle\Form\Type;

use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\FormBundle\Validator\Constraints\HtmlNotBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for conversation message entity.
 */
class ConversationMessageType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'id',
            HiddenType::class,
            ['mapped' => false]
        )->add(
            'body',
            OroResizeableRichTextType::class,
            [
                'required' => true,
                'label'    => 'oro.conversation.conversationmessage.body.label',
                'attr'     => [
                    'placeholder' => 'oro.conversation.conversationmessage.body.placeholder'
                ],
                'empty_data' => '',
                'constraints' => [ new HtmlNotBlank() ]
            ]
        )
        ->add(
            'participant',
            ConversationTargetSelectType::class,
            [
                'required' => false,
                'mapped' => false,
                'label'    => 'oro.conversation.conversationmessage.from_participant.label',
                'error_bubbling' => false,
                'placeholder' => 'oro.conversation.conversationmessage.from_participant.placeholder',
                'autocomplete_route_name' => 'oro_conversation_messages_participants_autocomplete_search',
                'autocomplete_route_parameters' => ['name' => 'conversationmessages'],
                'dialog_route' => 'oro_conversation_messages_participants_grid_dialog',
                'dialog_title' => 'oro.conversation.conversationmessage.from_participant.dialogue.label'
            ]
        );

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->addParticipantValueToForm($event);
        });
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'create_new' => false,
            'data_class' => ConversationMessage::class
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_conversation_message';
    }

    /**
     * Adds participant value to form.
     */
    private function addParticipantValueToForm(FormEvent $event): void
    {
        /** @var ConversationMessage $message */
        $message = $event->getData();
        $participant = $message->getParticipant();
        if ($participant && $participant->getConversationParticipantTarget()) {
            $event->getForm()->get('participant')->setData($participant->getConversationParticipantTarget());
        }
    }
}
