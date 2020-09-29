<?php

namespace Oro\Bundle\PaymentBundle\Method\Action;

use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Payment Method with Capture support
 */
interface CaptureActionInterface
{
    /**
     * @param PaymentTransaction $paymentTransaction
     * @return array
     */
    public function capture(PaymentTransaction $paymentTransaction): array;

    /**
     * Configure source transaction action, e.g. authorize, pending
     *
     * @return string
     */
    public function getSourceAction(): string;

    /**
     * Create new transaction when false, use existing pending transaction when true
     *
     * @return bool
     */
    public function useSourcePaymentTransaction(): bool;
}
