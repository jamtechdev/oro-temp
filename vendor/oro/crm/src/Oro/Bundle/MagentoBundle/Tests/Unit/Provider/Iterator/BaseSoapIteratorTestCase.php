<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;

class BaseSoapIteratorTestCase extends \PHPUnit\Framework\TestCase
{
    /** @var AbstractBridgeIterator */
    protected $iterator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|MagentoSoapTransportInterface */
    protected $transport;

    /** @var array */
    protected $settings;

    protected function setUp()
    {
        $this->transport = $this->createMock(MagentoSoapTransportInterface::class);

        $this->settings  = ['start_sync_date' => new \DateTime('NOW'), 'website_id' => 0];
    }

    public function tearDown()
    {
        unset($this->iterator, $this->transport);
    }
}
