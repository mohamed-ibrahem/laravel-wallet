<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Exceptions\TransactionStartException;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class DatabaseTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testCheckCode(): void
    {
        $this->expectException(TransactionFailedException::class);
        $this->expectExceptionCode(ExceptionInterface::TRANSACTION_FAILED);
        $this->expectExceptionMessage('Transaction failed. Message: hello');

        app(DatabaseServiceInterface::class)->transaction(static function () {
            throw new \RuntimeException('hello');
        });
    }

    /**
     * @throws ExceptionInterface
     */
    public function testCheckInTransaction(): void
    {
        $this->expectException(TransactionStartException::class);
        $this->expectExceptionCode(ExceptionInterface::TRANSACTION_START);

        DB::beginTransaction();
        app(DatabaseServiceInterface::class)->transaction(static fn (): int => 42);
    }

    public function testInterceptionErrorStartTransaction(): void
    {
        try {
            DB::beginTransaction();
            app(DatabaseServiceInterface::class)->transaction(static fn (): int => 42);
            self::fail(__METHOD__);
        } catch (TransactionStartException) {
            DB::rollBack(0); // for success
            $result = app(DatabaseServiceInterface::class)->transaction(static fn (): int => 42);
            self::assertSame(42, $result);
        }
    }
}
