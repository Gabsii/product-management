<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductManagement\Communication\Form\Product;

use Everon\Component\Collection\Collection;
use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use Spryker\Zed\ProductManagement\Business\Attribute\AttributeInputManager;
use Spryker\Zed\ProductManagement\Communication\Form\AbstractSubForm;
use Spryker\Zed\ProductManagement\Communication\Form\DataProvider\AbstractProductFormDataProvider;
use Spryker\Zed\ProductManagement\Communication\Form\ProductFormAdd;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @method \Spryker\Zed\ProductManagement\Persistence\ProductManagementQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductManagement\Business\ProductManagementFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductManagement\Communication\ProductManagementCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductManagement\ProductManagementConfig getConfig()
 * @method \Spryker\Zed\ProductManagement\Persistence\ProductManagementRepositoryInterface getRepository()
 */
class AttributeAbstractForm extends AbstractSubForm
{
    /**
     * @var string
     */
    public const FIELD_NAME = 'name';

    /**
     * @var string
     */
    public const FIELD_VALUE = 'value';

    /**
     * @var string
     */
    public const FIELD_VALUE_HIDDEN_ID = 'value_hidden_id';

    /**
     * @var string
     */
    public const OPTION_ATTRIBUTE = 'option_attribute';

    /**
     * @var string
     */
    public const OPTION_PRODUCT_MANAGEMENT_QUERY_CONTAINER = 'product-management-query-container';

    /**
     * @var string
     */
    public const OPTION_LOCALE_PROVIDER = 'locale-provider';

    /**
     * @var string
     */
    public const OPTION_LOCALE_TRANSFER = 'locale-transfer';

    /**
     * @var string
     */
    public const VALIDATION_GROUP_ATTRIBUTE_VALUE = 'validation_group_attribute_value';

    /**
     * @var \Generated\Shared\Transfer\LocaleTransfer
     */
    protected $localeTransfer;

    /**
     * @param string $name
     * @param array $attributes
     *
     * @return array
     */
    protected function getValueFieldConfig($name, $attributes)
    {
        return [
            'label' => false,
            'required' => true,
            'attr' => [
                'class' => 'attribute_metadata_value',
                'style' => '',
                'product_specific' => $attributes[$name][AbstractProductFormDataProvider::FORM_FIELD_PRODUCT_SPECIFIC],
                'id_attribute' => $attributes[$name][AbstractProductFormDataProvider::FORM_FIELD_ID],
                'readonly' => 'readonly',
            ],
            'constraints' => [
                new NotBlank([
                    'groups' => [static::VALIDATION_GROUP_ATTRIBUTE_VALUE],
                ]),
            ],
        ];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(static::OPTION_ATTRIBUTE);
        $resolver->setDefined(static::OPTION_LOCALE_TRANSFER);

        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                $groups = [ProductFormAdd::VALIDATION_GROUP_ATTRIBUTE_ABSTRACT];
                $submittedData = $form->getData();

                if ($submittedData[self::FIELD_NAME] && !$submittedData[self::FIELD_VALUE]) {
                    $groups[] = self::VALIDATION_GROUP_ATTRIBUTE_VALUE;
                }

                return $groups;
            },
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        parent::buildForm($builder, $options);

        $this->localeTransfer = $options[static::OPTION_LOCALE_TRANSFER] ?? null;

        $this
            ->addCheckboxNameField($builder, $options)
            ->addValueField($builder, $options)
            ->addValueIdHiddenField($builder, $options);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addCheckboxNameField(FormBuilderInterface $builder, array $options = [])
    {
        $attributes = $options[static::OPTION_ATTRIBUTE];

        $name = $builder->getName();
        $label = $attributes[$name][AbstractProductFormDataProvider::FORM_FIELD_LABEL];
        $isDisabled = $attributes[$name][AbstractProductFormDataProvider::FORM_FIELD_NAME_DISABLED];
        $isProductSpecific = $attributes[$name][AbstractProductFormDataProvider::FORM_FIELD_PRODUCT_SPECIFIC];

        $builder
            ->add(static::FIELD_NAME, CheckboxType::class, [
                'label' => $label,
                'disabled' => $isDisabled,
                'attr' => [
                    'class' => 'attribute_metadata_checkbox',
                    'product_specific' => $isProductSpecific,
                    'readonly' => $isDisabled, // Check if this will work too or if we need to add readonly only to the attr if $isDisabled is true
                ],
            ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addValueIdHiddenField(FormBuilderInterface $builder, array $options = [])
    {
        $builder
            ->add(static::FIELD_VALUE_HIDDEN_ID, HiddenType::class, []);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addValueField(FormBuilderInterface $builder, array $options = [])
    {
        $name = $builder->getName();
        $attributes = $options[static::OPTION_ATTRIBUTE];
        $attributeData = new Collection($attributes[$name]);

        $inputManager = new AttributeInputManager();
        $inputType = $attributeData->get(AbstractProductFormDataProvider::FORM_FIELD_INPUT_TYPE);
        $allowInput = $attributeData->get(AbstractProductFormDataProvider::FORM_FIELD_ALLOW_INPUT);
        $isDisabled = $attributeData->get(AbstractProductFormDataProvider::FORM_FIELD_NAME_DISABLED);
        $isProductSpecific = $attributeData->get(AbstractProductFormDataProvider::FORM_FIELD_PRODUCT_SPECIFIC);
        $value = $attributeData->get(AbstractProductFormDataProvider::FORM_FIELD_VALUE);
        $input = $inputManager->getSymfonyInputType($inputType, $value);
        $config = $this->getValueFieldConfig($name, $attributes);

        if ($isProductSpecific) {
            $isDisabled = true;
        }

        $config['attr']['data-value'] = null;

        if ($isDisabled) {
            $config = $this->getValueFieldConfig($name, $attributes);
            $config['attr']['readonly'] = true; // Check if this will work too or if we need to add readonly only to the attr if $isDisabled is true
            $input = $inputManager->getSymfonyInputType('', $value);
        } else {
            if (strtolower($input) === Select2ComboBoxType::class) {
                $idLocale = $this->getFactory()->createLocaleProvider()->getCurrentLocale()->getIdLocale();
                if ($this->localeTransfer instanceof LocaleTransfer) {
                    $idLocale = $this->localeTransfer->getIdLocale();
                }

                $existingValue = $this->getQueryContainer()
                    ->queryFindAttributeByValueOrTranslation(
                        $attributeData->get(AbstractProductFormDataProvider::FORM_FIELD_ID),
                        $idLocale,
                        $value,
                    )->findOne();

                $input = Select2ComboBoxType::class;
                $config['multiple'] = false;
                $config['placeholder'] = '-';
                $config['choices'] = array_flip($this->getChoiceList($name, $attributes[$name], $existingValue, $idLocale));

                if ($allowInput) {
                    $config['attr']['tags'] = true;
                }
            } else {
                $config['attr']['class'] .= ' kv_attribute_autocomplete';
            }
        }

        $builder->add(static::FIELD_VALUE, $input, $config);

        return $this;
    }

    /**
     * @param string $name
     * @param array $attributes
     * @param mixed $existingValue
     * @param int $idLocale
     *
     * @return array
     */
    protected function getChoiceList($name, array $attributes, $existingValue, $idLocale)
    {
        $result = [];
        $attributeValue = $attributes[AbstractProductFormDataProvider::FORM_FIELD_VALUE];

        $valueCollection = $this->getQueryContainer()
            ->queryFindAttributeByValueOrTranslation(
                $attributes[AbstractProductFormDataProvider::FORM_FIELD_ID],
                $idLocale,
            )->find();

        if (!$existingValue && $attributeValue !== null) {
            $result[$attributeValue] = $attributeValue;
        }

        foreach ($valueCollection as $entity) {
            $data = $entity->toArray();
            $value = $data['value'];
            $originalValue = $value;

            if (isset($data['translation'])) {
                $value = $data['translation'];
            }

            $result[$originalValue] = $value;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'product_attribute_abstract';
    }

    /**
     * @deprecated Use {@link getBlockPrefix()} instead.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
