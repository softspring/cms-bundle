<?php

namespace Softspring\CmsBundle\Form\Traits;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\UninitializedPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait DataMapperTrait
{
    public function mapDataToForms($data, iterable $forms)
    {
        $empty = null === $data || [] === $data;

        if (!$empty && !\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            if (!$empty && null !== $propertyPath && $config->getMapped()) {
                $form->setData($this->getPropertyValue($data, $propertyPath, $form));
            } else {
                $form->setData($config->getData());
            }
        }
    }

    public function mapFormsToData(iterable $forms, &$data)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (null === $data) {
            return;
        }

        if (!\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            // Write-back is disabled if the form is not synchronized (transformation failed),
            // if the form was not submitted and if the form is disabled (modification not allowed)
            if (null !== $propertyPath && $config->getMapped() && $form->isSubmitted() && $form->isSynchronized() && !$form->isDisabled()) {
                $propertyValue = $form->getData();
                // If the field is of type DateTimeInterface and the data is the same skip the update to
                // keep the original object hash
                if ($propertyValue instanceof \DateTimeInterface && $propertyValue == $this->getPropertyValue($data, $propertyPath, $form)) {
                    continue;
                }

                // If the data is identical to the value in $data, we are
                // dealing with a reference
                if (!\is_object($data) || !$config->getByReference() || $propertyValue !== $this->getPropertyValue($data, $propertyPath, $form)) {
                    $propertyAccessor->setValue($data, $propertyPath, $propertyValue);
                }
            }
        }
    }

    private function getPropertyValue($data, $propertyPath, FormInterface $form)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        try {
            return $this->cleanPropertyValue($propertyAccessor->getValue($data, $propertyPath), $form);
        } catch (AccessException $e) {
            if (!$e instanceof UninitializedPropertyException
                // For versions without UninitializedPropertyException check the exception message
                && (class_exists(UninitializedPropertyException::class) || !str_contains($e->getMessage(), 'You should initialize it'))
            ) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * This function prevents invalid mapping values on moving modules up and down, and cleans up old module fields
     */
    private function cleanPropertyValue($value, FormInterface $form)
    {
        if (!is_array($value)) {
            return $value;
        }

        $fields = [];
        /** @var FormInterface $field */
        foreach ($form as $field) {
            $fields[$field->getName()] = $field->getConfig()->getCompound();
        }

        // CUSTOM CODE, TO CLEAN NOT VALID FIELDS
        foreach ($fields as $field => $isCompound) {
            if (!array_key_exists($field, $value)) {
                continue;
            } elseif ($isCompound && !is_array($value[$field])) {
                $value[$field] = [];
            } else if (!$isCompound && is_array($value[$field])) {
                $value[$field] = '';
            }
        }

        // REMOVE OTHER MODULES FIELDS
        foreach ($value as $field => $fieldValue) {
            if (!isset($fields[$field])) {
                unset($value[$field]);
            }
        }

        return $value;
    }
}