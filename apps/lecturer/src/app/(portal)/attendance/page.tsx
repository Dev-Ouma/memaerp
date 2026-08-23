'use client';

import React, { FormEvent, useEffect, useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import QRCode from 'qrcode';
import { api, ApiError } from '@mema/api-client';
import {
  Alert,
  AlertDescription,
  AlertTitle,
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Input,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
  MemaLoaderInline,
} from '@mema/ui';
import { BookOpenCheck, QrCode, RefreshCw, StopCircle } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'The attendance operation failed.';

type OpenSession = {
  session?: { id?: string; expires_at?: string };
  qr_token?: string;
  qr_payload?: { token?: string; session_id?: string };
};

export default function LecturerAttendancePage() {
  const [selectedOfferingId, setSelectedOfferingId] = useState('');
  const [openSession, setOpenSession] = useState<OpenSession | null>(null);
  const [qrDataUrl, setQrDataUrl] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });
  const activeSessions = useQuery({
    queryKey: ['attendance', 'active'],
    queryFn: () => api.getActiveAttendanceSessions(),
    refetchInterval: 30_000,
  });

  const myOfferings = useMemo(
    () => offerings.data?.filter((item) => item.status === 'OFFERED' || (item.status as string) === 'ACTIVE') ?? [],
    [offerings.data],
  );

  useEffect(() => {
    if (!selectedOfferingId && myOfferings[0]) setSelectedOfferingId(myOfferings[0].id);
  }, [myOfferings, selectedOfferingId]);

  useEffect(() => {
    const token = openSession?.qr_token;
    if (!token) {
      setQrDataUrl(null);
      return;
    }
    QRCode.toDataURL(JSON.stringify({ token, type: 'mema-attendance' }), { width: 280, margin: 2 })
      .then(setQrDataUrl)
      .catch(() => setQrDataUrl(null));
  }, [openSession?.qr_token]);

  async function handleOpen(event: FormEvent) {
    event.preventDefault();
    if (!selectedOfferingId) return;
    setBusy(true);
    setError(null);
    setNotice(null);
    try {
      const data = (await api.openAttendanceSession(selectedOfferingId)) as OpenSession;
      setOpenSession(data);
      setNotice('Attendance session opened. Display the QR code for students to scan.');
      await activeSessions.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusy(false);
    }
  }

  async function handleClose(sessionId: string) {
    setBusy(true);
    setError(null);
    try {
      await api.closeAttendanceSession(sessionId);
      if (openSession?.session?.id === sessionId) setOpenSession(null);
      setNotice('Session closed and register finalised.');
      await activeSessions.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusy(false);
    }
  }

  const selectedOffering = myOfferings.find((item) => item.id === selectedOfferingId);

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Class Attendance</h2>
        <p className="text-sm text-slate-500 mt-1">
          Open a timed QR session, let students check in, then close the register.
        </p>
      </div>

      {notice && (
        <Alert>
          <AlertTitle>Success</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}
      {error && (
        <Alert variant="destructive">
          <AlertTitle>Action failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <BookOpenCheck className="h-5 w-5 text-mema-teal-700" />
              Start Session
            </CardTitle>
            <CardDescription>Select an offering and generate a QR code valid for five minutes.</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleOpen} className="space-y-4">
              <label className="block space-y-1 text-sm font-medium text-slate-700">
                Course offering
                <select
                  className="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm"
                  value={selectedOfferingId}
                  onChange={(event) => setSelectedOfferingId(event.target.value)}
                >
                  {myOfferings.map((offering) => (
                    <option key={offering.id} value={offering.id}>
                      {offering.course?.code ?? 'Course'} · {offering.section_code}
                    </option>
                  ))}
                </select>
              </label>
              {selectedOffering && (
                <p className="text-xs text-slate-500">
                  Capacity {selectedOffering.enrolled_count}/{selectedOffering.max_capacity} ·{' '}
                  {selectedOffering.campus?.name ?? 'Main campus'}
                </p>
              )}
              <Button type="submit" disabled={busy || !selectedOfferingId} className="gap-2">
                {busy ? <MemaLoaderInline size={40} /> : <QrCode className="h-4 w-4" />}
                Open QR Session
              </Button>
            </form>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Live QR Display</CardTitle>
            <CardDescription>Students scan this code or paste the token in their portal.</CardDescription>
          </CardHeader>
          <CardContent className="flex flex-col items-center gap-4">
            {qrDataUrl ? (
              <>
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={qrDataUrl} alt="Attendance QR code" className="rounded-lg border border-slate-200" />
                <Input readOnly value={openSession?.qr_token ?? ''} className="font-mono text-xs" />
                {openSession?.session?.id && (
                  <Button
                    variant="destructive"
                    className="gap-2"
                    disabled={busy}
                    onClick={() => handleClose(openSession.session!.id!)}
                  >
                    <StopCircle className="h-4 w-4" /> Close Session
                  </Button>
                )}
              </>
            ) : (
              <p className="text-sm text-slate-500 py-12 text-center">No active QR session.</p>
            )}
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle>Open Sessions</CardTitle>
            <CardDescription>Sessions awaiting closure on your offerings.</CardDescription>
          </div>
          <Button variant="outline" size="sm" onClick={() => activeSessions.refetch()} className="gap-2">
            <RefreshCw className="h-4 w-4" /> Refresh
          </Button>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Course</TableHead>
                <TableHead>Opened</TableHead>
                <TableHead>Expires</TableHead>
                <TableHead>Status</TableHead>
                <TableHead />
              </TableRow>
            </TableHeader>
            <TableBody>
              {(activeSessions.data ?? []).map((session) => {
                const offering = session.course_offering as { course?: { code?: string }; section_code?: string };
                return (
                  <TableRow key={String(session.id)}>
                    <TableCell>
                      {offering?.course?.code ?? '—'} · {offering?.section_code ?? '—'}
                    </TableCell>
                    <TableCell>{session.opened_at ? new Date(String(session.opened_at)).toLocaleString() : '—'}</TableCell>
                    <TableCell>{session.expires_at ? new Date(String(session.expires_at)).toLocaleString() : '—'}</TableCell>
                    <TableCell>
                      <Badge variant="success">{String(session.status)}</Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      <Button
                        size="sm"
                        variant="outline"
                        disabled={busy}
                        onClick={() => handleClose(String(session.id))}
                      >
                        Close
                      </Button>
                    </TableCell>
                  </TableRow>
                );
              })}
              {(activeSessions.data ?? []).length === 0 && (
                <TableRow>
                  <TableCell colSpan={5} className="text-center text-slate-500">
                    No open sessions.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
