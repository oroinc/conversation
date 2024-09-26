# Extend\Entity\EV_Conversation_Message_Type

## ACTIONS

### get

Retrieve a specific conversation message type record.

Conversation message type is the type of the conversation message is in (Text, System).

### get_list

Retrieve a collection of conversation message types.

Conversation message type is the type of the conversation message is in (Text, System).

# Oro\Bundle\ConversationBundle\Entity\ConversationMessage

## ACTIONS

### get

Retrieve a specific conversation message record.

{@inheritdoc}

### get_list

Retrieve a collection of conversation message records.

{@inheritdoc}

### create

Create a new conversation message record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "conversationmessages",
    "attributes": {
      "body": "<p>The message body</p>"
    },
    "relationships": {
      "conversation": {
        "data": {
          "type": "conversations",
          "id": "1"
        }
      },
      "participant": {
        "data": {
          "type": "conversationparticipants",
          "id": "1"
        }
      },
      "conversationMessageType": {
        "data": {
          "type": "conversationmessagestypes",
          "id": "text"
        }
      }
    }
  }
}
```
{@/request}

## FIELDS

### body

The conversation text message body.

{@inheritdoc}

### index

The message number in the conversation.

{@inheritdoc}

#### create

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### conversation

The conversation this message belongs to.

{@inheritdoc}

### conversationMessageType

The message type.

{@inheritdoc}

### participant

Participant who send the message.

{@inheritdoc}

## SUBRESOURCES

### conversation

#### get_subresource

Retrieve the conversation for which this message was sent.

#### get_relationship

Retrieve the ID of the conversation for which this message was sent.

### conversationMessageType

#### get_subresource

Retrieve the message type record.

#### get_relationship

Retrieve the ID of the message type record.

### participant

#### get_subresource

Retrieve the participant record that was send the message.

#### get_relationship

Retrieve the ID of the participant record that was send the message.
