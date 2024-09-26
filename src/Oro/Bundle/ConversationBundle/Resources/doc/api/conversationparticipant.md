# Oro\Bundle\ConversationBundle\Entity\ConversationParticipant

## ACTIONS

### get

Retrieve a specific conversation participant record.

{@inheritdoc}

### get_list

Retrieve a collection of conversation participant records.

{@inheritdoc}

### update

Update a new conversation participant record.

Allow to update last read message of the participant.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "conversationparticipants",
    "id": "1",
    "relationships": {
      "lastReadMessage": {
        "data": {
          "type": "conversationmessages",
          "id": "7"
        }
      }
    }
  }
}
```
{@/request}

## FIELDS

### isMe

Determinate whether participant belongs to current logged user or customer user. 

{@inheritdoc}

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### lastReadDate

Determinate when user or customer user seen the messages last time.

{@inheritdoc}

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### lastReadMessageIndex

Internal index of the last viewed message.

{@inheritdoc}

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### author

The user or customer user record that belongs to current participant.

{@inheritdoc}

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### conversation

The conversation this participant belongs to.

{@inheritdoc}

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### lastReadMessage

The last message viewed by this participant

{@inheritdoc}

## SUBRESOURCES

### author

#### get_subresource

Retrieve the author record for given participant.

#### get_relationship

Retrieve the ID of the author record for given participant.

### conversation

#### get_subresource

Retrieve the conversation record for given participant.

#### get_relationship

Retrieve the ID of the conversation record for given participant.

### lastReadMessage

#### get_subresource

Retrieve the last message was viewed by given participant.

#### get_relationship

Retrieve the ID of the last message was viewed by given participant.

#### update_relationship

Replace the ID of the last message was viewed by given participant.

