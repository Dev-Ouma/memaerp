import './globals.css';
import { AppProviders } from '@mema/auth';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <title>MEMA Staff Portal</title>
        <meta name="description" content="Mema University Staff Services Portal" />
      </head>
      <body>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
