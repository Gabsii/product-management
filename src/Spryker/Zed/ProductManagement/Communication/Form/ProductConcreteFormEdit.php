<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductManagement\Communication\Form;

use Spryker\Zed\ProductManagement\Communication\Form\Product\Concrete\ConcreteGeneralForm;
use Spryker\Zed\ProductManagement\Communication\Form\Product\Concrete\PriceForm as ConcretePriceForm;
use Spryker\Zed\ProductManagement\Communication\Form\Product\Concrete\StockForm;
use Spryker\Zed\ProductManagement\Communication\Form\Product\PriceForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductConcreteFormEdit extends ProductFormAdd
{

    const FIELD_ID_PRODUCT_ABSTRACT = 'id_product_abstract';
    const FIELD_ID_PRODUCT_CONCRETE = 'id_product';

    /**
     * @return string
     */
    public function getName()
    {
        return 'ProductConcreteFormEdit';
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addSkuField($builder)
            ->addProductAbstractIdHiddenField($builder)
            ->addProductConcreteIdHiddenField($builder)
            ->addGeneralLocalizedForms($builder)
            ->addAttributeAbstractForms($builder, $options[self::OPTION_ATTRIBUTE_ABSTRACT])
            ->addPriceForm($builder, $options)
            ->addStockForm($builder)
            ->addImageLocalizedForms($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addSkuField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::FIELD_SKU, 'text', [
                'label' => 'SKU',
                'read_only' => true
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addProductAbstractIdHiddenField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::FIELD_ID_PRODUCT_ABSTRACT, 'hidden', []);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addProductConcreteIdHiddenField(FormBuilderInterface $builder)
    {
        $builder
            ->add(self::FIELD_ID_PRODUCT_CONCRETE, 'hidden', []);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return $this
     */
    protected function addPriceForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder
            ->add(self::FORM_PRICE_AND_TAX, new ConcretePriceForm($this->moneyFacade), [
                'label' => false,
                'constraints' => [new Callback([
                    'methods' => [
                        function ($dataToValidate, ExecutionContextInterface $context) {
                            if ((int)$dataToValidate[PriceForm::FIELD_PRICE] < 0) {
                                $context->addViolation('Please enter Price information under Price & Taxes');
                            }
                        },
                    ],
                    'groups' => [self::VALIDATION_GROUP_PRICE_AND_TAX]
                ])],
                ConcretePriceForm::OPTION_TAX_RATE_CHOICES => $options[self::OPTION_TAX_RATES],
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return $this
     */
    protected function addStockForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder
            ->add(self::FORM_PRICE_AND_STOCK, 'collection', [
                'type' => new StockForm(),
                'label' => false,
                'constraints' => [new Callback([
                    'methods' => [
                        function ($dataToValidate, ExecutionContextInterface $context) {
                            foreach ($dataToValidate as $data) {
                                $stockCount = (int)$data[StockForm::FIELD_QUANTITY];
                                $stockType = $data[StockForm::FIELD_TYPE];
                                $isNeverOutOfStock = (bool)$data[StockForm::FIELD_IS_NEVER_OUT_OF_STOCK];

                                if ($stockCount === 0 && !$isNeverOutOfStock) {
                                    $context->addViolation(
                                        sprintf(
                                            'Please enter Stock information under Price & Stock for stock type: %s',
                                            $stockType
                                        ),
                                        [$context->getGroup()]
                                    );
                                }
                            }
                        },
                    ],
                    'groups' => [self::VALIDATION_GROUP_PRICE_AND_STOCK]
                ])]
            ]);

        return $this;
    }

    /**
     * @return \Spryker\Zed\ProductManagement\Communication\Form\Product\GeneralForm
     */
    protected function createGeneralForm()
    {
        return new ConcreteGeneralForm();
    }

}
