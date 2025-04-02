import { useLayoutEffect, useMemo, useState } from 'react'
import styles from './GenericSelectField.module.scss'
import Select from 'react-select'
import classNames from 'classnames'
import { Label } from '../Label/Label'
import { FormFieldMisc, FormFieldProps, IOption } from '../../types'
import { FormComponentConstructor } from '../../lib/types'
import { useField } from '../../lib/hooks/useField'
import {
    fetchConnectedOptions,
    isConnectedField,
    isDependentField,
    isModelOptions,
    useOptions,
} from './utils'
import { useForm } from '../../lib/FormProvider'
import { string } from 'zod'
import { getFormState } from '../../lib/utils'
import { useSelect } from '@wordpress/data'
import { store_name } from '../../../../../store/backend'

export const createGenericSelectField: FormComponentConstructor<any> = ({
    field,
    fieldConfig,
}) => {
    return ({ name, label, misc }: FormFieldProps) => {
        const { value, setValue, errors } = useField(field)
        const [touched, setTouched] = useState(false)
        const isValid = !errors?.length
        const showErrors = !isValid && touched
        const [firstError] = errors ?? []
        const multiple = misc?.multiple || false
        const form = useForm()

        const options: IOption[] = useOptions({
            options:
                ((fieldConfig.misc as FormFieldMisc).options as Record<
                    string,
                    string
                >) || string,
            model: fieldConfig?.modelName as string,
            field: name,
            formData: getFormState(form).values,
            nullValue: fieldConfig.misc?.null_value,
        })

        const valueObject = useMemo(
            () =>
                options.filter((option: IOption) => {
                    let parsedValue = value

                    try {
                        parsedValue = JSON.parse(value)
                    } catch (e) {}

                    if (multiple && parsedValue && Array.isArray(parsedValue)) {
                        return parsedValue.includes(option.value)
                    }

                    return parsedValue == option.value
                }) as IOption[],
            [value, options]
        )

        const handleChange = (selectedOptions: any) => {
            if (multiple && selectedOptions && selectedOptions[0]?.value) {
                setValue(selectedOptions.map((option: IOption) => option.value))
            } else if (!multiple && selectedOptions.value) {
                setValue(selectedOptions?.value as string)
            } else {
                setValue([])
            }

            if (isConnectedField(fieldConfig?.modelName as string, name)) {
                fetchConnectedOptions(fieldConfig?.modelName as string, name, {
                    ...getFormState(form).values,
                    id: form.defaultValue?.id,
                })
            }
        }

        const isLoading = useSelect((select) => {
            if (
                isModelOptions(
                    (fieldConfig.misc as FormFieldMisc).options as any
                )
            ) {
                // @ts-ignore
                return select(store_name).getModelFieldLoading(
                    (fieldConfig.misc as FormFieldMisc).options
                )
            } else {
                // @ts-ignore
                return select(store_name).getFieldLoading(
                    fieldConfig?.modelName as string,
                    name
                )
            }
        }, [])

        useLayoutEffect(() => {
            if (
                isConnectedField(fieldConfig?.modelName as string, name) &&
                !isDependentField(fieldConfig?.modelName as string, name) &&
                form.defaultValue.id &&
                form.defaultValue[name]
            ) {
                fetchConnectedOptions(fieldConfig?.modelName as string, name, {
                    ...getFormState(form).values,
                    id: form.defaultValue.id,
                })
            }
        }, [options, form.fields[name].value])

        return (
            <div
                className={classNames(styles.selectField, {
                    [styles.invalid]: showErrors,
                })}
            >
                <Label id={name} title={label} tooltip={misc?.tooltip} />
                <div>
                    <Select
                        value={valueObject}
                        options={options}
                        onChange={(selectedOptions: IOption[] | unknown) =>
                            handleChange(selectedOptions as IOption[])
                        }
                        classNames={{
                            control: (state) => styles.selectInput,
                        }}
                        id={name}
                        isMulti={multiple}
                        onBlur={() => setTouched(true)}
                        isSearchable={false}
                        isDisabled={isLoading}
                        isLoading={isLoading}
                    />
                    {showErrors && firstError && (
                        <div className={styles.errorContainer}>
                            {firstError}
                        </div>
                    )}
                </div>
            </div>
        )
    }
}
