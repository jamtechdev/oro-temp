<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RestContactAddressApiTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures(['@OroContactBundle/Tests/Functional/DataFixtures/contact_addresses.yml']);
    }

    public function testGetList()
    {
        $contactId = $this->getReference('Contact_Brenda')->getId();

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_contact_addresses', ['contactId' => $contactId])
        );
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $this->assertArrayHasKey(0, $result);
        $this->assertCount(1, $result);
        $this->assertArraySubset(
            [
                'primary'        => true,
                'label'          => 'Address 1',
                'street'         => 'Street 1',
                'street2'        => 'Street 2',
                'city'           => 'Los Angeles',
                'postalCode'     => '90001',
                'country'        => 'United States',
                'region'         => 'California',
                'organization'   => 'Acme',
                'namePrefix'     => 'Mr.',
                'nameSuffix'     => 'M.D.',
                'firstName'      => 'John',
                'middleName'     => 'Edgar',
                'lastName'       => 'Doo',
                'types'          => [
                    ['name' => 'billing', 'label' => 'Billing']
                ],
                'countryIso2'    => 'US',
                'countryIso3'    => 'US',
                'regionCode'     => 'CA',
                'customField1'   => 'val1',
                'custom_field_2' => 'val2'
            ],
            $result[0]
        );
    }
}
