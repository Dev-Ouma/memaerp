'use client';

import React from 'react';
import {
  Alert,
  AlertDescription,
  AlertTitle,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@mema/ui';

export interface DataTableColumn<T> {
  key: string;
  header: React.ReactNode;
  className?: string;
  cell: (row: T) => React.ReactNode;
}

export interface DataTableProps<T> {
  columns: DataTableColumn<T>[];
  data: T[];
  isLoading?: boolean;
  isError?: boolean;
  errorMessage?: string;
  emptyMessage?: string;
  getRowKey: (row: T) => string;
}

export function DataTable<T>({
  columns,
  data,
  isLoading,
  isError,
  errorMessage = 'Unable to load records.',
  emptyMessage = 'No records found.',
  getRowKey,
}: DataTableProps<T>) {
  if (isError) {
    return (
      <Alert variant="destructive">
        <AlertTitle>Load failed</AlertTitle>
        <AlertDescription>{errorMessage}</AlertDescription>
      </Alert>
    );
  }

  if (isLoading) {
    return (
      <div className="py-12 text-center text-sm text-slate-500" role="status">
        Loading records...
      </div>
    );
  }

  if (data.length === 0) {
    return (
      <div className="py-12 text-center text-sm text-slate-500" role="status">
        {emptyMessage}
      </div>
    );
  }

  return (
    <Table>
      <TableHeader>
        <TableRow>
          {columns.map((column) => (
            <TableHead key={column.key} className={column.className}>
              {column.header}
            </TableHead>
          ))}
        </TableRow>
      </TableHeader>
      <TableBody>
        {data.map((row) => (
          <TableRow key={getRowKey(row)}>
            {columns.map((column) => (
              <TableCell key={column.key} className={column.className}>
                {column.cell(row)}
              </TableCell>
            ))}
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
