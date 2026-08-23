import './globals.css';
import { AppProviders } from '@mema/auth';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <title>MEMA ERP — Enterprise Administration</title>
        <meta
          name="description"
          content="Mema University Enterprise Resource Planning & Administration"
        />
      </head>
      <body>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
