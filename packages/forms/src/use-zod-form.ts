'use client';

import { useForm, type DefaultValues, type FieldValues, type UseFormReturn } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import type { ZodType } from 'zod';

export interface UseZodFormOptions<T extends FieldValues> {
  schema: ZodType<T>;
  defaultValues?: DefaultValues<T>;
}

export function useZodForm<T extends FieldValues>({
  schema,
  defaultValues,
}: UseZodFormOptions<T>): UseFormReturn<T> {
  return useForm<T>({
    resolver: zodResolver(schema),
    defaultValues,
    mode: 'onBlur',
  });
}
