import { AuthLayout } from '@mema/auth';
import { Alert, AlertDescription, AlertTitle } from '@mema/ui';

export default function MfaPage() {
  return (
    <AuthLayout appName="Student Portal">
      <div className="space-y-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 font-heading">
            Multi-factor authentication
          </h1>
          <p className="mt-2 text-sm text-slate-600">
            Enter the 6-digit code from your authenticator app to continue.
          </p>
        </div>
        <Alert variant="info">
          <AlertTitle>Backend MFA not wired yet</AlertTitle>
          <AlertDescription>
            This screen is ready for Codex to connect the TOTP verification endpoint.
          </AlertDescription>
        </Alert>
      </div>
    </AuthLayout>
  );
}
