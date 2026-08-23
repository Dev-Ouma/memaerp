'use client';

import React from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Button,
  Badge,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@mema/ui';
import { Plus } from 'lucide-react';

export default function AdminSecurityPage() {
  const roles = [
    {
      id: 'role-1',
      name: 'SUPERADMIN',
      displayName: 'System Super Administrator',
      scope: 'Global Institution',
      usersCount: 2,
      permissionsCount: 57,
    },
    {
      id: 'role-2',
      name: 'REGISTRAR',
      displayName: 'University Academic Registrar',
      scope: 'All Faculties & Campuses',
      usersCount: 4,
      permissionsCount: 38,
    },
    {
      id: 'role-3',
      name: 'DEAN',
      displayName: 'Dean of Faculty',
      scope: 'Scoped by Faculty ID',
      usersCount: 6,
      permissionsCount: 24,
    },
    {
      id: 'role-4',
      name: 'HOD',
      displayName: 'Head of Department',
      scope: 'Scoped by Department ID',
      usersCount: 18,
      permissionsCount: 18,
    },
    {
      id: 'role-5',
      name: 'LECTURER',
      displayName: 'Academic Lecturer',
      scope: 'Scoped by Assigned Sections',
      usersCount: 140,
      permissionsCount: 8,
    },
  ];

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            IAM, RBAC & Security Governance
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Granular permission catalogue, role assignments with campus/faculty/department scopes (MOD-00-01, MOD-00-02)
          </p>
        </div>
        <Button className="bg-mema-teal-800 hover:bg-mema-teal-700 text-white gap-2 self-start sm:self-auto">
          <Plus className="h-4 w-4" /> Define Custom Role
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Role Families & Scoped Access Boundaries</CardTitle>
          <CardDescription>
            Enforced by Laravel Gate & Policy traits with deny-by-default rules.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Role Identifier</TableHead>
                <TableHead>Display Name</TableHead>
                <TableHead>Scope Boundary</TableHead>
                <TableHead className="text-center">Assigned Users</TableHead>
                <TableHead className="text-center">Permissions</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {roles.map((r) => (
                <TableRow key={r.id}>
                  <TableCell className="font-mono font-bold text-mema-teal-900">
                    {r.name}
                  </TableCell>
                  <TableCell className="font-semibold text-slate-900">
                    {r.displayName}
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline" className="text-xs">
                      {r.scope}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-center font-mono font-bold text-slate-800">
                    {r.usersCount}
                  </TableCell>
                  <TableCell className="text-center font-mono text-slate-700">
                    {r.permissionsCount} Perms
                  </TableCell>
                  <TableCell className="text-right">
                    <Button size="sm" variant="ghost" className="h-8 text-xs text-mema-teal-800">
                      Edit Permissions
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
