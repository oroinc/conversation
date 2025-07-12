# Extend\Entity\EV_Conversation_Status

## ACTIONS

### get

Retrieve a specific conversation status record.

Conversation status is the state the conversation is in (Active, Closed).

### get_list

Retrieve a collection of conversation statuses.

Conversation status is the state the task is in (Active, Closed).

# Oro\Bundle\ConversationBundle\Entity\Conversation

## ACTIONS

### get

Retrieve a specific conversation record.

{@inheritdoc}

### get_list

Retrieve a collection of conversation records.

{@inheritdoc}

### create

Create a new conversation record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "conversations",
    "attributes": {
      "name": "Conversation name"
    },
    "relationships": {
      "source": {
        "data": {
          "type": "orders",
          "id": "1"
        }
      }
    }
  }
}
```
{@/request}

## FIELDS

### messagesNumber

The number of the messages the conversation have.

{@inheritdoc}

#### create

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### name

The conversation name.

{@inheritdoc}

#### create

{@inheritdoc}

**The required field. If the source field is not null, and value of the field is null, it will be generated
from the source field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### customer

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### customerUser

The customer user responsible for this conversation.

### customer

The customer responsible for this conversation according to the customer user record.

### lastMessage

The link to the last message for the conversation.

#### create

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### messages

Represents collection of the conversation messages.

### participants

Represents collection of the participants who are participating in conversation.

#### create

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### source

The conversation starting entity.

### status

The status of the conversation (Active, Closed).

### sourceTitle

String representation of the conversation starting entity

#### create

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### sourceUrl

URL to the conversation starting entity

#### create

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

## SUBRESOURCES

### messages

#### get_subresource

Retrieve a list of conversation messages.

#### get_relationship

Retrieve the IDs of conversation messages.

### organization

#### get_subresource

Retrieve a record of the organization a specific conversation belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific conversation belongs to.

### owner

#### get_subresource

Retrieve a record of the user who is an owner of a specific conversation record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific conversation record.

### participants

#### get_subresource

Retrieve a list of the conversation participants.

#### get_relationship

Retrieve the IDs of the conversation participants.

### source

#### get_subresource

Retrieve the source entity record from what the conversation was started.

#### get_relationship

Retrieve the ID of the source entity record from what the conversation was started.

### status

#### get_subresource

Retrieve the status of the conversation.

#### get_relationship

Retrieve the ID of the status of the conversation.

### customer

#### get_subresource

Retrieve a record of the customer that a specific conversation belongs to.

#### get_relationship

Retrieve the ID of the customer that a specific conversation belongs to.

### customerUser

#### get_subresource

Retrieve a record of the customer user that a specific conversation belongs to.

#### get_relationship

Retrieve the ID of the customer user that a specific conversation belongs to.

### lastMessage

#### get_subresource

Retrieve a record of the customer user that a specific conversation belongs to.

#### get_relationship

Retrieve the ID of the customer user that a specific conversation belongs to.
