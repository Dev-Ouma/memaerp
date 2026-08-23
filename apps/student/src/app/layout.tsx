import './globals.css';
import { AppProviders } from '@mema/auth';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <title>MEMA Student Portal — University Information System</title>
        <meta
          name="description"
          content="Official Mema University Student Information & Lifecycle Portal"
        />
      </head>
      <body>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
