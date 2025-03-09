<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProductVoter extends Voter
{
    public const string EDIT = 'POST_EDIT';
    public const string VIEW = 'POST_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return \in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Product;
    }

    /**
     * @param \App\Entity\Product $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return \in_array('ROLE_ADMIN', $user->getRoles(), true);
                break;
            case self::VIEW:
                return \in_array('ROLE_USER', $user->getRoles(), true);
                break;
        }

        return false;
    }
}
