<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class AtomicServiceTest extends TestCase
{
    public function testBlock(): void
    {
        $atomic = app(AtomicServiceInterface::class);

        /** @var Buyer $user1 */
        /** @var Buyer $user2 */
        [$user1, $user2] = BuyerFactory::times(2)->create();

        $user1->deposit(1000);

        $atomic->blocks(
            [$user1->wallet, $user2->wallet],
            fn () => collect([
                fn () => $user1->transfer($user2, 500),
                fn () => $user1->transfer($user2, 500),
                fn () => $user2->transfer($user1, 500),
            ])
                ->map(fn ($fx) => $fx()),
        );

        self::assertSame(1, $user2->transfers()->count());
        self::assertSame(2, $user1->transfers()->count());
        self::assertSame(3, $user2->operations()->count());
        self::assertSame(4, $user1->operations()->count());

        self::assertSame(500, $user1->balanceInt);
        self::assertSame(500, $user2->balanceInt);
    }
}
