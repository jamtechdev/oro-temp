<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Oro\Bundle\UserBundle\Entity\BaseUserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Provider\UserLoggingInfoProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Logs failed user login attempts.
 */
class LoginAttemptsLogSubscriber implements EventSubscriberInterface
{
    const SUCCESSFUL_LOGIN_MESSAGE = 'Successful login';
    const UNSUCCESSFUL_LOGIN_MESSAGE = 'Unsuccessful login';

    /** @var BaseUserManager */
    private $userManager;

    /** @var UserLoggingInfoProvider */
    private $infoProvider;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param BaseUserManager $userManager
     * @param UserLoggingInfoProvider $infoProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        BaseUserManager $userManager,
        UserLoggingInfoProvider $infoProvider,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->infoProvider = $infoProvider;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            SecurityEvents::INTERACTIVE_LOGIN            => 'onInteractiveLogin',
        ];
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if (is_string($user)) {
            $user = $this->userManager->findUserByUsernameOrEmail($user);
        }

        if ($user instanceof User) {
            $this->logger->notice(
                self::UNSUCCESSFUL_LOGIN_MESSAGE,
                $this->infoProvider->getUserLoggingInfo($user)
            );
        } elseif ($token instanceof UsernamePasswordToken && $token->getProviderKey() === 'main') {
            $this->logger->notice(
                self::UNSUCCESSFUL_LOGIN_MESSAGE,
                $this->infoProvider->getUserLoggingInfo($token->getUser())
            );
        }
    }

    /**
     * @param  InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if ($user instanceof User) {
            $this->logger->info(
                self::SUCCESSFUL_LOGIN_MESSAGE,
                $this->infoProvider->getUserLoggingInfo($user)
            );
        }
    }
}
