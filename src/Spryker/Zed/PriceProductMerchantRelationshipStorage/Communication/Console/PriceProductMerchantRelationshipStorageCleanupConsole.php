<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\Console;

use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyMerchantRelationBusinessUnitStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductAbstractMerchantRelationshipStorageQuery;
use Orm\Zed\PriceProductMerchantRelationshipStorage\Persistence\SpyPriceProductConcreteMerchantRelationshipStorageQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Spryker\Zed\Propel\Persistence\BatchProcessor\ActiveRecordBatchProcessorTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\Business\PriceProductMerchantRelationshipStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\PriceProductMerchantRelationshipStorage\Communication\PriceProductMerchantRelationshipStorageCommunicationFactory getFactory()
 */
class PriceProductMerchantRelationshipStorageCleanupConsole extends Console
{
    use ActiveRecordBatchProcessorTrait;

    protected const string COMMAND_NAME = 'price-product-merchant-relationship-storage:cleanup';

    protected const string COMMAND_DESCRIPTION = 'Deletes the merchant-relationship price storage rows of the inactive price key shape, together with their storage keys. Flag on: deletes the business-unit shape rows. Flag off: deletes the merchant-relationship shape rows and the business-unit lookup rows.';

    protected const string OPTION_CHUNK_SIZE = 'chunk-size';

    protected const string OPTION_FORCE = 'force';

    protected const string OPTION_FORCE_SHORT = 'f';

    protected const int DEFAULT_CHUNK_SIZE = 500;

    protected const string ERROR_INVALID_CHUNK_SIZE = 'Chunk size must be a positive integer, got "%s".';

    protected const string QUESTION_CONFIRM_BUSINESS_UNIT_SHAPE = 'All merchant-relationship prices with the business-unit price key shape will be removed from the storage. Do you want to proceed? [y/N] ';

    protected const string QUESTION_CONFIRM_MERCHANT_RELATIONSHIP_SHAPE = 'All merchant-relationship prices with the merchant-relationship price key shape and all business-unit lookup entries will be removed from the storage. Do you want to proceed? [y/N] ';

    protected const string MESSAGE_CANCELLED = 'Operation cancelled.';

    protected const string ERROR_FORCE_REQUIRED = 'The input is not interactive. Run the command with --force to confirm the cleanup.';

    protected const string COLUMN_ID_PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_STORAGE = 'IdPriceProductAbstractMerchantRelationshipStorage';

    protected const string COLUMN_ID_PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_STORAGE = 'IdPriceProductConcreteMerchantRelationshipStorage';

    protected const string COLUMN_ID_MERCHANT_RELATION_BUSINESS_UNIT_STORAGE = 'IdMerchantRelationBusinessUnitStorage';

    protected function configure(): void
    {
        parent::configure();

        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::COMMAND_DESCRIPTION);

        $this->addOption(
            static::OPTION_CHUNK_SIZE,
            null,
            InputOption::VALUE_REQUIRED,
            'Rows deleted per commit. Every deleted row emits one storage delete message. The messages of a chunk are synchronized right after its commit: with direct synchronization they go to the storage in one bulk call, otherwise each message is one publish to the sync.storage.price queue.',
            (string)static::DEFAULT_CHUNK_SIZE,
        );
        $this->addOption(
            static::OPTION_FORCE,
            static::OPTION_FORCE_SHORT,
            InputOption::VALUE_NONE,
            'Skips the confirmation question. Use it for the deployment.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $chunkSize = (int)$input->getOption(static::OPTION_CHUNK_SIZE);

        if ($chunkSize < 1) {
            $output->writeln(sprintf('<error>%s</error>', sprintf(static::ERROR_INVALID_CHUNK_SIZE, (string)$chunkSize)));

            return static::CODE_ERROR;
        }

        if (!$input->getOption(static::OPTION_FORCE) && !$input->isInteractive()) {
            $output->writeln(sprintf('<error>%s</error>', static::ERROR_FORCE_REQUIRED));

            return static::CODE_ERROR;
        }

        $isMerchantRelationPriceKeyEnabled = $this->getFactory()->getConfig()->isMerchantRelationPriceKeyEnabled();

        if (!$input->getOption(static::OPTION_FORCE) && !$this->getQuestionHelper()->ask($input, $output, $this->createConfirmationQuestion($isMerchantRelationPriceKeyEnabled))) {
            $output->writeln(static::MESSAGE_CANCELLED);

            return static::CODE_SUCCESS;
        }

        if ($isMerchantRelationPriceKeyEnabled) {
            $this->cleanupBusinessUnitShape($chunkSize, $output);

            return static::CODE_SUCCESS;
        }

        $this->cleanupMerchantRelationshipShape($chunkSize, $output);

        return static::CODE_SUCCESS;
    }

    protected function createConfirmationQuestion(bool $isMerchantRelationPriceKeyEnabled): ConfirmationQuestion
    {
        return new ConfirmationQuestion(
            $isMerchantRelationPriceKeyEnabled ? static::QUESTION_CONFIRM_BUSINESS_UNIT_SHAPE : static::QUESTION_CONFIRM_MERCHANT_RELATIONSHIP_SHAPE,
            false,
        );
    }

    protected function cleanupBusinessUnitShape(int $chunkSize, OutputInterface $output): void
    {
        $deletedAbstractCount = $this->deleteInChunks(
            SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()->filterByFkMerchantRelationship(null, Criteria::ISNULL),
            static::COLUMN_ID_PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_STORAGE,
            $chunkSize,
        );
        $deletedConcreteCount = $this->deleteInChunks(
            SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()->filterByFkMerchantRelationship(null, Criteria::ISNULL),
            static::COLUMN_ID_PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_STORAGE,
            $chunkSize,
        );

        $output->writeln(sprintf(
            'Deleted business-unit shape rows. Abstract: %d, concrete: %d',
            $deletedAbstractCount,
            $deletedConcreteCount,
        ));
    }

    protected function cleanupMerchantRelationshipShape(int $chunkSize, OutputInterface $output): void
    {
        $deletedAbstractCount = $this->deleteInChunks(
            SpyPriceProductAbstractMerchantRelationshipStorageQuery::create()->filterByFkMerchantRelationship(null, Criteria::ISNOTNULL),
            static::COLUMN_ID_PRICE_PRODUCT_ABSTRACT_MERCHANT_RELATIONSHIP_STORAGE,
            $chunkSize,
        );
        $deletedConcreteCount = $this->deleteInChunks(
            SpyPriceProductConcreteMerchantRelationshipStorageQuery::create()->filterByFkMerchantRelationship(null, Criteria::ISNOTNULL),
            static::COLUMN_ID_PRICE_PRODUCT_CONCRETE_MERCHANT_RELATIONSHIP_STORAGE,
            $chunkSize,
        );
        $deletedLookupCount = $this->deleteInChunks(
            SpyMerchantRelationBusinessUnitStorageQuery::create(),
            static::COLUMN_ID_MERCHANT_RELATION_BUSINESS_UNIT_STORAGE,
            $chunkSize,
        );

        $output->writeln(sprintf(
            'Deleted merchant-relationship shape rows. Abstract: %d, concrete: %d, business-unit lookup: %d',
            $deletedAbstractCount,
            $deletedConcreteCount,
            $deletedLookupCount,
        ));
    }

    protected function deleteInChunks(ModelCriteria $query, string $idColumn, int $chunkSize): int
    {
        $deletedCount = 0;
        $lastId = 0;
        $isSendingToQueue = $this->getFactory()->getConfig()->isSendingToQueue();

        while (true) {
            $entities = (clone $query)
                ->filterBy($idColumn, $lastId, Criteria::GREATER_THAN)
                ->orderBy($idColumn)
                ->limit($chunkSize)
                ->find()
                ->getArrayCopy();

            if (!$entities) {
                return $deletedCount;
            }

            $lastId = (int)end($entities)->getPrimaryKey();

            foreach ($entities as $entity) {
                $entity->setIsSendingToQueue($isSendingToQueue);
                $this->remove($entity);
            }
            $this->commit();
            $this->getFactory()->getSynchronizationFacade()->flushSynchronizationMessagesFromBuffer();

            $deletedCount += count($entities);
        }
    }
}
