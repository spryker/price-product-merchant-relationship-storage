<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Discount\Business\QueryString\Comparator;

use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\GreaterEqual;

class GreaterEqualTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testAcceptShouldReturnTrueWhenMoreEqualExpressionProvided()
    {
        $moreEqual = $this->createMoreEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('>=');

        $isAccepted = $moreEqual->accept($clauseTransfer);

        $this->assertTrue($isAccepted);
    }

    /**
     * @return void
     */
    public function testCompareWhenClauseValueIsBiggerThanProvidedShouldReturnTrue()
    {
        $moreEqual = $this->createMoreEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1');

        $isMatching = $moreEqual->compare($clauseTransfer, '2');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenClauseValueIsSmallerThanProvidedShouldReturnFalse()
    {
        $moreEqual = $this->createMoreEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('2');

        $isMatching = $moreEqual->compare($clauseTransfer, '1');

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenClauseValueIsEqualShouldReturnTrue()
    {
        $moreEqual = $this->createMoreEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1');

        $isMatching = $moreEqual->compare($clauseTransfer, '1');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenNonNumericValueUsedShouldThrowException()
    {
        $this->expectException(ComparatorException::class);

        $moreEqual = $this->createMoreEqual();

        $clauseTransfer = new ClauseTransfer();

        $moreEqual->compare($clauseTransfer, 'as');
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\GreaterEqual
     */
    protected function createMoreEqual()
    {
        return new GreaterEqual();
    }

}