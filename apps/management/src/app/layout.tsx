import './globals.css';
import { AppProviders } from '@mema/auth';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <title>MEMA Management Dashboard</title>
        <meta name="description" content="Mema University Executive Management Dashboard" />
      </head>
      <body>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
