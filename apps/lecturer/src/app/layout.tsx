import './globals.css';
import { AppProviders } from '@mema/auth';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <title>MEMA Lecturer Portal</title>
        <meta name="description" content="Mema University Lecturer Teaching Portal" />
      </head>
      <body>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
