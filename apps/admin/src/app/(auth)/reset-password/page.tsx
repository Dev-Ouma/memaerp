import { AuthLayout } from '@mema/auth';
import { Alert, AlertDescription, AlertTitle } from '@mema/ui';

export default function ResetPasswordPage() {
  return (
    <AuthLayout appName="ERP Administration">
      <div className="space-y-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 font-heading">Reset password</h1>
          <p className="mt-2 text-sm text-slate-600">
            Request a secure reset link for your institutional account.
          </p>
        </div>
        <Alert variant="info">
          <AlertTitle>Password reset API pending</AlertTitle>
          <AlertDescription>
            The reset form will be enabled once Codex publishes the password reset contract. Contact
            ICT support if you are locked out of your account.
          </AlertDescription>
        </Alert>
      </div>
    </AuthLayout>
  );
}
