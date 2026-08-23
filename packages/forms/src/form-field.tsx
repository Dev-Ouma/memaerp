'use client';

import React from 'react';
import { Input } from '@mema/ui';

export interface FormFieldProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: string;
  error?: string;
  description?: string;
}

export function FormField({ label, error, description, id, ...props }: FormFieldProps) {
  const fieldId = id ?? props.name;

  return (
    <div className="space-y-2">
      <label htmlFor={fieldId} className="text-sm font-medium text-slate-700">
        {label}
      </label>
      <Input id={fieldId} error={error} {...props} />
      {description && !error ? (
        <p className="text-xs text-slate-500">{description}</p>
      ) : null}
    </div>
  );
}
