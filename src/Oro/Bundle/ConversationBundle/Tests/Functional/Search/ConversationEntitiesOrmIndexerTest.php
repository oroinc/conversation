<?php

declare(strict_types=1);

namespace Oro\Bundle\ConversationBundle\Tests\Functional\Search;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SearchBundle\Tests\Functional\Engine\AbstractEntitiesOrmIndexerTest;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Tests that Conversation entities can be indexed without type casting errors with the ORM search engine.
 *
 * @group search
 * @dbIsolationPerTest
 */
class ConversationEntitiesOrmIndexerTest extends AbstractEntitiesOrmIndexerTest
{
    #[\Override]
    protected function getSearchableEntityClassesToTest(): array
    {
        return [Conversation::class];
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadOrganization::class, LoadUser::class]);

        $manager = $this->getDoctrine()->getManagerForClass(Conversation::class);
        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        /** @var User $owner */
        $owner = $this->getReference(LoadUser::USER);

        $conversation = (new Conversation())
            ->setOrganization($organization)
            ->setOwner($owner)
            ->setName('Test Conversation');
        $this->persistTestEntity($conversation);

        $manager->flush();
    }
}
