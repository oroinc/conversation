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
      "owner": {
        "data": {
          "type": "users",
          "id": "1"
        }
      },
      "organization": {
        "data": {
          "type": "organizations",
          "id": "1"
        }
      },
      "customerUser": {
        "data": {
          "type": "customerusers",
          "id": "1"
        }
      },
      "customer": {
        "data": {
          "type": "customers",
          "id": "1"
        }
      },
      "status": {
        "data": {
          "type": "conversationstatuses",
          "id": "active"
        }
      },
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

### update

Edit a specific conversation record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "conversations",
    "id": "1",
    "attributes": {
      "name": "Conversation name"
    },
    "relationships": {
      "owner": {
        "data": {
          "type": "users",
          "id": "1"
        }
      },
      "organization": {
        "data": {
          "type": "organizations",
          "id": "1"
        }
      },
      "customerUser": {
        "data": {
          "type": "customerusers",
          "id": "1"
        }
      },
      "customer": {
        "data": {
          "type": "customers",
          "id": "1"
        }
      },
      "status": {
        "data": {
          "type": "conversationstatuses",
          "id": "active"
        }
      },
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

### delete

Delete a specific conversation record.

{@inheritdoc}

### delete_list

Delete a collection of conversation records.

{@inheritdoc}

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

## SUBRESOURCES

### customer

#### get_subresource

Retrieve a record of the customer that a specific conversation belongs to.

#### get_relationship

Retrieve the ID of the customer that a specific conversation belongs to.

#### update_relationship

Replace the customer that a specific conversation belongs to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customers",
    "id": "1"
  }
}
```
{@/request}

### customerUser

#### get_subresource

Retrieve a record of the customer user that a specific conversation belongs to.

#### get_relationship

Retrieve the ID of the customer user that a specific conversation belongs to.

#### update_relationship

Replace the customer user that a specific conversation belongs to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customerusers",
    "id": "1"
  }
}
```
{@/request}

### lastMessage

#### get_subresource

Retrieve the last conversation message record.

#### get_relationship

Retrieve the ID of the last conversation message record.

#### update_relationship

Replace the last conversation message record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "conversationmessages",
    "id": "1"
  }
}
```
{@/request}

## SUBRESOURCES

### messages

#### get_subresource

Retrieve the list of conversation messages.

#### get_relationship

Retrieve the IDs of conversation messages.

#### add_relationship

Set the messages to the conversation.

#### update_relationship

Replace the messages of the conversation.

#### delete_relationship

Remove messages from the conversation.

### organization

#### get_subresource

Retrieve a record of the organization a specific conversation belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific conversation belongs to.

#### update_relationship

Replace the organization that a specific conversation belongs to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "organizations",
    "id": "1"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve a record of the user who is an owner of a specific conversation record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific conversation record.

#### update_relationship

Replace the owner of a specific conversation record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "35"
  }
}
```
{@/request}

### participants

#### get_subresource

Retrieve the list of the conversation participants.

#### get_relationship

Retrieve the list IDs of the conversation participants.

#### add_relationship

Set the participant to the conversation.

#### update_relationship

Replace the participants of the conversation.

#### delete_relationship

Remove participant from the conversation.

### source

#### get_subresource

Retrieve the source entity record from what the conversation was started.

#### get_relationship

Retrieve the ID of the source entity record from what the conversation was started.

#### update_relationship

Replace the source entity record from what the conversation was started.

### status

#### get_subresource

Retrieve the status of the conversation.

#### get_relationship

Retrieve the ID of the status of the conversation.

#### update_relationship

Replace the status of the conversation.
