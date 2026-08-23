'use client';

import React from 'react';
import Link from 'next/link';
import { Badge, Card, CardContent, CardDescription, CardHeader, CardTitle } from '@mema/ui';
import { ArrowRight } from 'lucide-react';
import type { NavItem } from '@mema/ui';

type ModuleHubProps = {
  title?: string;
  description?: string;
  items: NavItem[];
};

export function ModuleHub({
  title = 'Student Services',
  description = 'Quick access to every module in your sidebar.',
  items,
}: ModuleHubProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>{title}</CardTitle>
        <CardDescription>{description}</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
          {items.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className="group flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 hover:border-mema-teal-700 hover:bg-mema-teal-50/40 transition-colors"
            >
              <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-mema-teal-800 shadow-sm border border-slate-100">
                {item.icon}
              </span>
              <span className="min-w-0 flex-1">
                <span className="flex items-center gap-2">
                  <span className="font-semibold text-sm text-slate-900 group-hover:text-mema-teal-900">
                    {item.title}
                  </span>
                  {item.badge ? (
                    <Badge variant="warning" className="text-[10px] py-0">
                      {item.badge}
                    </Badge>
                  ) : null}
                </span>
                <span className="text-xs text-slate-500">{item.href}</span>
              </span>
              <ArrowRight className="h-4 w-4 shrink-0 text-slate-400 group-hover:text-mema-teal-800" />
            </Link>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
