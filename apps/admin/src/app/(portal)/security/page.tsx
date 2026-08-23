'use client';

import React, { useEffect, useState } from 'react';
import { api, ApiError } from '@mema/api-client';
import { Alert, AlertDescription, AlertTitle, Badge, Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@mema/ui';
import { RefreshCw, ShieldCheck, Users } from 'lucide-react';

interface IamRole { id: string; code: string; name: string; family: string; is_system: boolean; permissions_count: number; assignments_count: number; }
interface IamUser { id: string; email: string; username: string | null; name: string | null; status: string; is_active: boolean; mfa_enabled: boolean; last_login_at: string | null; roles: Array<{ id: string; code: string; name: string; scope_type: string }>; }

export default function AdminSecurityPage() {
  const [users, setUsers] = useState<IamUser[]>([]);
  const [roles, setRoles] = useState<IamRole[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = async () => {
    setLoading(true); setError(null);
    try {
      const [userResponse, roleResponse] = await Promise.all([api.getIamUsers(), api.getIamRoles()]);
      setUsers(userResponse.data as unknown as IamUser[]);
      setRoles(roleResponse.data as unknown as IamRole[]);
    } catch (reason) {
      setError(reason instanceof ApiError ? reason.message : 'IAM data could not be loaded.');
    } finally { setLoading(false); }
  };

  useEffect(() => { void load(); }, []);

  return <div className="space-y-8">
    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h2 className="font-heading text-2xl font-bold text-slate-900">Identity & Access Management</h2><p className="mt-1 text-sm text-slate-500">Live user accounts, account status, MFA posture, scoped role assignments, and permission bundles.</p></div><Button variant="outline" onClick={() => void load()} isLoading={loading}><RefreshCw className="mr-2 h-4 w-4" />Refresh</Button></div>
    {error && <Alert variant="destructive"><AlertTitle>Unable to load IAM</AlertTitle><AlertDescription>{error}</AlertDescription></Alert>}
    <div className="grid gap-4 sm:grid-cols-3"><Card><CardContent className="flex items-center gap-3 p-5"><Users className="h-7 w-7 text-mema-teal-700" /><div><div className="text-2xl font-bold">{users.length}</div><div className="text-xs text-slate-500">Visible accounts</div></div></CardContent></Card><Card><CardContent className="flex items-center gap-3 p-5"><ShieldCheck className="h-7 w-7 text-mema-teal-700" /><div><div className="text-2xl font-bold">{roles.length}</div><div className="text-xs text-slate-500">Institution roles</div></div></CardContent></Card><Card><CardContent className="p-5"><div className="text-2xl font-bold">{users.filter((user) => user.mfa_enabled).length}</div><div className="text-xs text-slate-500">MFA-enrolled accounts</div></CardContent></Card></div>
    <Card><CardHeader><CardTitle>User account governance</CardTitle><CardDescription>Account state and role assignments are returned by the protected IAM API.</CardDescription></CardHeader><CardContent><Table><TableHeader><TableRow><TableHead>User</TableHead><TableHead>Status</TableHead><TableHead>MFA</TableHead><TableHead>Scoped roles</TableHead><TableHead>Last login</TableHead></TableRow></TableHeader><TableBody>{users.map((user) => <TableRow key={user.id}><TableCell><div className="font-medium text-slate-900">{user.name || user.username || user.email}</div><div className="text-xs text-slate-500">{user.email}</div></TableCell><TableCell><Badge variant={user.is_active ? 'success' : 'warning'}>{user.status}</Badge></TableCell><TableCell><Badge variant={user.mfa_enabled ? 'success' : 'outline'}>{user.mfa_enabled ? 'Enabled' : 'Not enrolled'}</Badge></TableCell><TableCell><div className="flex flex-wrap gap-1">{user.roles.map((role) => <Badge key={role.id} variant="outline">{role.code} · {role.scope_type}</Badge>)}</div></TableCell><TableCell className="text-xs text-slate-600">{user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never'}</TableCell></TableRow>)}</TableBody></Table></CardContent></Card>
    <Card><CardHeader><CardTitle>Role directory</CardTitle><CardDescription>System roles are protected from ad-hoc deletion and bundle atomic permissions.</CardDescription></CardHeader><CardContent><Table><TableHeader><TableRow><TableHead>Role</TableHead><TableHead>Family</TableHead><TableHead>Type</TableHead><TableHead className="text-right">Assignments</TableHead><TableHead className="text-right">Permissions</TableHead></TableRow></TableHeader><TableBody>{roles.map((role) => <TableRow key={role.id}><TableCell><div className="font-medium">{role.name}</div><div className="font-mono text-xs text-mema-teal-800">{role.code}</div></TableCell><TableCell>{role.family}</TableCell><TableCell><Badge variant={role.is_system ? 'info' : 'outline'}>{role.is_system ? 'System' : 'Custom'}</Badge></TableCell><TableCell className="text-right">{role.assignments_count}</TableCell><TableCell className="text-right">{role.permissions_count}</TableCell></TableRow>)}</TableBody></Table></CardContent></Card>
  </div>;
}
